<?php


include_once(dirname(__FILE__) . '/utils.php');
include_once(dirname(__FILE__) . '/cron-toggler.php');
include_once(dirname(__FILE__) . '/scheduler.php');

/**
 * Debugging Menu for tracking payment 
 * notification. Can be found on Setting menu
 * on Admin Page.
 */
function wisataone_X1_setting_page() {
    $page_title = "Wisataone Mail Scheduler";
    $option_name = "Wisataone Mail Scheduler";
    $uniq_id = "wisataone-mail-scheduler";
    add_options_page(
        $page_title, 
        $option_name, 
        'manage_options', 
        $uniq_id, 
        'wisataone_X1_setting_page_builder'
    );
}

/**
 * Part of Debugging Menu
 */
function wisataone_X1_setting_page_builder() {
    global $wpdb;
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    if ($_POST['status'] == 'true') {
        echo "activated!";
        wisataone_X1_setCron(true);
    }

    if ($_POST['status'] == 'false') {
        echo "deactivated!";
        wisataone_X1_setCron(false);
    }

    if ($_GET['test']) {
        $data = wisataone_X1_insert_order($_GET['test']);
        print_r($data);
    }

    $orders = wisataone_X1_get_all_order();
    $schedulers = [];
    foreach ($orders as $row) {
        $row->time_to_trip = wisataone_X1_time_diff_by_now($row->trip_date);
        array_push($schedulers, new WSTX1Scheduler((array) $row));
    }
    echo __('<br/><br/><div>
        <form action="?page=wisataone-mail-scheduler" method="POST">
            <select name="status">
                <option value="true" ' . (isCronStatusActive() ? 'selected' : '') . '>Aktif</option>
                <option value="false" ' . (!isCronStatusActive() ? 'selected' : '') . '>Tidak aktif</option>
            </select>
            <input type="submit" value="Simpan" />
        </form><br/>
        <div style="padding-right: 24px">
        <div style="overflow-x: auto; width: 100%;">');
    echo '<div>';
    echo 'Last tick: ' . wisataone_X1_get_single_cron_tick() . ' UTC+00:00';
    echo '</div>';
    echo '<table style="border-collapse: collapse;">';
    $keys = ['id', 'next_mail_in', 'time_to_trip', 'id_tour', 'id_order', 'tour_name', 'traveler_name', 'booking_date', 'current_step', 'ts_payment_10', 'ts_payment_50', 'ts_payment_40', 'email_p_10', 'email_p_10_h3', 'email_p_10_h6', 'email_p_10_h7', 'email_c_10', 'email_s_10', 'email_p_50', 'email_p_50_h3', 'email_p_50_h6', 'email_p_50_h7', 'email_c_50', 'email_s_50', 'email_p_40', 'email_p_40_h3', 'email_p_40_h6', 'email_p_40_h7', 'email_c_40', 'email_s_40', 'email_kuota_tidak_terpenuhi', 'email_kuota_gagal_batas_h_45'];
    echo '<tr>';
    foreach ($keys as $value) {
        echo    '<td style="border: solid 1px #CCC; padding: 4px 8px; font-weight: bold">';
        echo    $value;
        echo    '</td>';
    }
    echo '</tr>';
    foreach ($schedulers as $row) {
        echo '<tr>';
        foreach ($keys as $col_key) {
            echo '<td style="border: solid 1px #CCC; padding: 4px 8px;">';
            switch ($col_key) {
                case 'id':
                    echo '<b>' . $row->id . '</b>';
                    break;
                case 'next_mail_in':
                    echo $row->getNextMail();
                    break;
                case 'time_to_trip':
                    echo formatElapsedTS($row->time_to_trip);
                    break;
                case 'booking_date':
                    echo $row->booking_date;
                    break;
                case 'current_step':
                    echo $row->getCurrentStep() . ' (' . $row->{$col_key} . ')';
                    break;
                default:
                    echo $row->{$col_key};
            }
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    echo ('</div></div>
    </div>');
    // echo '<pre>';
    // print_r(_get_cron_array());
    // echo '</pre>';
}

add_action('admin_menu', 'wisataone_X1_setting_page');