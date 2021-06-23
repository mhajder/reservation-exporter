<?php
/*
Plugin Name: Reservation Exporter
Description: Export reservations from Webba Booking plugin to a CSV file
Version: 1.0
Author: Mateusz Hajder
Text Domain: reservation-exporter
*/

if (!class_exists('ReservationExporter')) {
    /**
     * Class ReservationExporter
     */
    class ReservationExporter
    {
        /**
         * Whether the plugin is in debugging mode
         *
         * @since 1.0
         */
        var bool $debugging = false;

        /**
         * The current version of Reservation Exporter
         *
         * @since 1.0
         */
        var string $version = '1.0';

        /**
         * The array of service names in the database
         *
         * @since 1.0
         */
        var array $services = array();

        /**
         * The name of the WordPress database
         *
         * @since 1.0
         */
        var ?string $database_name = null;

        /**
         * The URL to which forms are submitted
         *
         * @since 1.0
         */
        var string $formURL;

        /**
         * Run when an instance of this class is constructed
         *
         * @since 1.0
         */
        function __construct()
        {
            if (function_exists('plugin_dir_path')) {
                $this->formURL = remove_query_arg('p');
            }
        }

        // =====================================================================================================================
        // Initialisation

        /**
         * Sets up the plugin when the WordPress admin area is initialised
         *
         * @throws Exception
         * @since 1.0
         */
        function on_admin_init()
        {
            // ensure a session has been started
            if (!isset($_SESSION)) {
                session_start();
            }

            // set the database name
            $this->database_name = DB_NAME;

            // load the tables
            $this->loadServices();

            // if exporting, do the export
            if (wp_verify_nonce(@$_POST['_wpnonce'], 'generate') && @$_POST['service'] != '' && @$_POST['datetimepicker'] != '') {
                $this->export();
            }
        }

        /**
         * Adds the plugin item as a submenu item to the 'Tools' menu
         *
         * @since 1.0
         */
        function on_admin_menu()
        {
            add_menu_page(__('Reservation Exporter', 'reservation-exporter'), __('Reservation Exporter', 'reservation-exporter'),
                'manage_options', 'reservation-exporter', array($this, 'adminPage'));
        }

        // =====================================================================================================================
        // Admin page

        /**
         * Handles displaying of the DatabaseBrowser admin page
         *
         * @since 1.0
         */
        function adminPage()
        {
            echo '
			<div class="wrap" id="reservationexporter">
				<h2>' . __('Reservation Exporter', 'reservation-exporter') . '</h2>
			';

            include_once('views/generate.php');
        }

        // =====================================================================================================================
        // Database manipulation

        /**
         * Loads the services from database
         *
         * @since 1.0
         */
        function loadServices()
        {
            global $wpdb;

            $sql = 'SELECT `id`, `name` FROM `wbk_services` ORDER BY `name` ASC';
            $this->services = $wpdb->get_results($sql, ARRAY_A);
        }

        // =====================================================================================================================
        // Export

        /**
         * Handles exporting the current data
         *
         * @throws Exception
         * @since 1.0
         */
        function export()
        {
            global $wpdb;

            $vService = $_POST['service'];
            $tmpDateTimePicker = explode('-', str_replace('+', '', $_POST['datetimepicker']));
            $vTimeFrom = strtotime(str_replace('/', '-', $tmpDateTimePicker[0]));
            $vTimeTo = strtotime(str_replace('/', '-', $tmpDateTimePicker[1]));

            // Set csv export filename
            $filename = 'Appointments_' . $wpdb->prepare('%d', $vService) . '.csv';

            $sql = $wpdb->prepare('SELECT * FROM `wbk_appointments`
                                                WHERE `service_id` = %d
                                                AND `time` BETWEEN %d AND %d
                                                ORDER BY `id` ASC', $vService, $vTimeFrom, $vTimeTo);

            $results = $wpdb->get_results($sql, ARRAY_A);

            if (!empty($results) && sizeof($results) > 0 && count($results) > 0) {
                $this->forceDownload($filename);

                $f = fopen('php://memory', 'w');

                fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Create CSV file headers
                $fields = array(
                    __('Date', 'reservation-exporter'),
                    __('Hour', 'reservation-exporter'),
                    __('Name', 'reservation-exporter'),
                    __('Email address', 'reservation-exporter'),
                    __('Phone number', 'reservation-exporter'),
                    __('Additional comment', 'reservation-exporter'),
                );

                // Add dynamic field to headers
                $getLastRecordExtraField = $results[count($results) >= 1 ? count($results) - 1 : 1]['extra'];
                foreach (json_decode($getLastRecordExtraField, TRUE) as $id => $arr2) {
                    array_push($fields, $arr2[1]);
                }

                // Add headers to CSV file
                fputcsv($f, $fields);

                foreach ($results as $row) {
                    $dateTime = new DateTime('@' . $row['time']);
                    $dateTime->setTimeZone(new DateTimeZone(wp_timezone_string()));

                    $lineData = array(
                        $dateTime->format('d.m.Y'), // Date
                        $dateTime->format('H:i'), // Hour
                        $row['name'],
                        $row['email'],
                        $row['phone'],
                        $row['description'],
                    );

                    // Add dynamic fields to export file
                    foreach (json_decode($row['extra'], TRUE) as $id => $arr2) {
                        array_push($lineData, $arr2[2]);
                    }

                    // Add data to CSV file
                    fputcsv($f, $lineData);
                }

                fseek($f, 0);
                fpassthru($f);
            }
            header('Refresh:0');
            exit();
        }

        /**
         * Adds the headers required to force the browser to download the requested file as the given file format
         *
         * @param $filename
         * @since 1.0
         */
        function forceDownload($filename)
        {
            // when debugging allow the files to be shown in the browser
            if ($this->debugging) {
                header('Content-Type: text');
                return;
            }

            header('Cache-Control: public');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Type: application/octet-stream');
        }
    }
}

/**
 * Creates a new instance of the ReservationExporter class and ensures it will be loaded in the admin area
 *
 * @since 1.0
 */
if (class_exists('ReservationExporter') && function_exists('add_action')) {
    $reservationexporter = new ReservationExporter();
    add_action('admin_menu', array(&$reservationexporter, 'on_admin_menu'));
    add_action('admin_init', array(&$reservationexporter, 'on_admin_init'));
}