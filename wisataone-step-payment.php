<?php
/**
* Plugin Name: Wisataone Step Payment
* Plugin URI: https://riochr17.github.io/
* Description: Wisataone payment step system & mail
* Version: 1.0
* Author: Rio Chandra Rajagukguk
* Author URI: http://www.treasurf.com/
**/


include_once(dirname(__FILE__) . '/data-manager.php');
include_once(dirname(__FILE__) . '/setting-page.php');
include_once(dirname(__FILE__) . '/payment-page-controller.php');
include_once(dirname(__FILE__) . '/scheduler.php');
include_once(dirname(__FILE__) . '/cron-ticker-data-manager.php');
include_once(dirname(__FILE__) . '/wisataone-step-payment.test.php');

function testing_page($request) {

    // foreach (wisataone_X1_get_all_order_by_id_tour_and_step_gte_110(5, '2020-05-19 07:42:52') as $value) {
    //     echo $value->id;
    // }
    // return [5, '2020-05-19 07:42:52'];
    // $orders = wisataone_X1_get_all_order();
    // foreach ($orders as $row) {
    //     $row->time_to_trip = wisataone_X1_time_diff_by_now($row->trip_date);
    //     $schedule = new WSTX1Scheduler((array) $row);
    //     echo $schedule->checkMailQueue();
    // }
    $s = (new WSTX1Scheduler([]))->load(1);
    echo $s->get_mail_trx_template();
}

function wisataone_X1_api_get_snap_token() {
    if (isset($_POST['id'])) {
        return getSnapToken($_POST['id']);
    }

    return NULL;
}

/**
 * Main router for step payment
 */
function register_step_payment_route() {
    register_rest_route('step-payment/v1', 'testing', array(
        'methods'  => 'GET',
        'callback' => 'testing_page'
    ));
    register_rest_route('step-payment/v1', 'cron_tick', array(
        'methods'  => 'GET',
        'callback' => 'wisataone_X1_update_cron_tick'
    ));
    register_rest_route('step-payment/v1', 'get_snap_token', array(
        'methods'  => 'POST',
        'callback' => 'wisataone_X1_api_get_snap_token'
    ));
    register_rest_route('step-payment/v1', 'notify', array(
        'methods'  => 'POST',
        'callback' => 'step_payment_sample_request'
    ));
    register_rest_route('step-payment/v1', 'test', array(
        'methods'  => 'GET',
        'callback' => function() {
            
            $data = tourmaster_get_booking_data(array('id' => 42), array('single' => true));
            $tour_duration = get_post_meta($data->tour_id, 'tourmaster-tour-duration', 0);
            echo json_encode($tour_duration);
            die;
            $email = $_GET['email'];
            if (!$email) {
                echo "Parameter email tidak boleh kosong.";
                die;
            }
            echo (new WSTX1SchedulerTest())->start($email);
            die;
        }
    ));
}

/**
 * Testing purpose.
 * Can be removed.
 */
function step_payment_sample_request($request) {
    //$posts = json_encode($request->get_params());
    // $response = new WP_REST_Response(wisataone_X1_send_mail());
    // $response->set_status(200);
    // return $response;


    $payment_notification = $request->get_params();
    if (!isset($payment_notification['order_id']) 
        || !isset($payment_notification['transaction_status'])) {
        return false;
    }


    $order_id_array = explode("-", $payment_notification['order_id']);
    $is_dp = $order_id_array[0] == "dp";
    
    // return [
    //     $order_id_array[1], $payment_notification['transaction_status']
    // ];
    if ($is_dp) {
        wisataone_X1_insert_order($order_id_array[1]);
        $sch = (new WSTX1Scheduler([]))->loadByOrderId($order_id_array[1]);

        if (!$sch) { return false; }
        return $sch->processPayment($payment_notification['transaction_status']);
    }

    return "222";
}

function wisataone_X1_removeCron() {
    wisataone_X1_setCron(false);
}

function wisataone_X1_startCron() {
    wisataone_X1_setCron(true);
}

function wisataone_X1_payment_page_parse_request() {
    if (isset($_GET['wisataone-payment-id'])) {
        echo get_header();
        if (!wisataone_X1_get_single_order($_GET['wisataone-payment-id'])) {
            echo "<div style='margin: 150px 24px'>Not Found: 404</div>";
        } else {
            echo wisataone_X1_get_template("payment-page.html", getPageAttributes($_GET['wisataone-payment-id']));
        }
        echo get_footer();
        die;
    }
    return;
}

add_filter('init', 'wisataone_X1_payment_page_parse_request');

/**
 * Init api endpoint
 */
add_action('rest_api_init', 'register_step_payment_route');

/**
 * Action on plugin activated
 */
register_activation_hook(__FILE__, 'wisataone_X1_startCron');
register_activation_hook(__FILE__, 'wisataone_X1_init_db');
register_activation_hook(__FILE__, 'wisataone_X1_init_db_cron_tick');

/**
 * Action on plugin deactivated
 */
register_deactivation_hook(__FILE__, 'wisataone_X1_removeCron');
register_deactivation_hook(__FILE__, 'wisataone_X1_drop_db');
register_deactivation_hook(__FILE__, 'wisataone_X1_drop_db_cron_tick');
