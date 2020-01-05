<?php


include_once(dirname(__FILE__) . '/data-manager.php');
include_once(dirname(__FILE__) . '/cron-ticker-data-manager.php');

// Add a new interval of 180 seconds
// See http://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules
add_filter('cron_schedules', 'isa_add_every_ten_seconds');
function isa_add_every_ten_seconds($schedules) {
    $schedules['wisataone_every_ten_seconds'] = array(
            'interval'  => 10,
            'display'   => __('Every 10 Seconds', 'textdomain')
    );
    return $schedules;
}

function wisataone_X1_setCron($is_active) {
    if ($is_active) {
        // Schedule an action if it's not already scheduled
        if (!wp_next_scheduled('isa_add_every_ten_seconds')) {
            wp_schedule_event(time(), 'wisataone_every_ten_seconds', 'isa_add_every_ten_seconds');
            return true;
        }
        return false;
    } else {
        if(wp_next_scheduled('isa_add_every_ten_seconds')) {
            wp_clear_scheduled_hook('isa_add_every_ten_seconds');
            return true;
        }
        return false;
    }
}

function isCronStatusActive() {
    foreach (_get_cron_array() as $value) {
        if($value['isa_add_every_ten_seconds']) {
            return true;
        }
    }

    return false;
}

// Hook into that action that'll fire every three minutes
add_action('isa_add_every_ten_seconds', 'every_ten_seconds_event_func');
function every_ten_seconds_event_func() {
    wisataone_X1_update_cron_tick();

    $orders = wisataone_X1_get_all_order();
    foreach ($orders as $row) {
        $row->time_to_trip = wisataone_X1_time_diff_by_now($row->trip_date);
        $schedule = new WSTX1Scheduler((array) $row);
        $schedule->checkMailQueue();
    }
}