<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.2/css/bulma.min.css"
      integrity="sha256-O8SsQwDg1R10WnKJNyYgd9J3rlom+YSVcGbEF5RmfFk=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma-calendar@6.0.9/dist/css/bulma-calendar.min.css"
      integrity="sha256-ibg09Hplsr06zkYpajRQmDTCKvFVassHxeVTVukXmYA=" crossorigin="anonymous">
<style>
    .wp-core-ui select, .datetimepicker-dummy-wrapper {
        background: white;
    }
    .datetimepicker-dummy .datetimepicker-clear-button {
        margin: 0.2rem 0.2rem 0 0;
    }
</style>
<section class="section">
    <div class="container">
        <div class="columns is-vcentered">
            <form action="<?php echo $this->formURL ?>" method="post">
                <div class="field">
                    <label class="label" for="service"><?php _e('Choose a service', 'reservation-exporter') ?></label>
                    <div class="control">
                        <div class="select">
                            <select id="service" name="service" required>
                                <?php foreach ($this->services as $service) { ?>
                                    <option value="<?php echo $service['id'] ?>"><?php echo $service['name'] . ' (' . $service['id'] . ')' ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="datetime"><?php _e('Select a time period', 'reservation-exporter') ?></label>
                    <div id="range" class="tab-content is-active">
                        <input id="datetimepicker" name="datetimepicker" class="input" data-is-range="true"
                               type="datetime" data-color="info" required>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <input class="button is-link" type="submit" name="submit" value="<?php _e('Export', 'reservation-exporter') ?>">
                    </div>
                </div>
                <?php wp_nonce_field("generate") ?>
            </form>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/bulma-calendar@6.0.9/dist/js/bulma-calendar.min.js"
        integrity="sha256-j5R7XaUm/P3SCc5bo5XQ8maH3vkFFlXNSeg4ckGiO0k=" crossorigin="anonymous"></script>
<script>
  bulmaCalendar.attach('#datetimepicker', {
    allowSameDayRange: true,
    dateFormat: '<?php _e('DD/MM/YYYY', 'reservation-exporter') ?>',
    weekStart: <?php _e('1', 'reservation-exporter') ?>,
    cancelLabel: '<?php _e('Cancel', 'reservation-exporter') ?>',
    clearLabel: '<?php _e('Clear', 'reservation-exporter') ?>',
    todayLabel: '<?php _e('Today', 'reservation-exporter') ?>',
    nowLabel: '<?php _e('Now', 'reservation-exporter') ?>',
    validateLabel: '<?php _e('Save', 'reservation-exporter') ?>',
  });
</script>