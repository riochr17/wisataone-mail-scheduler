<?php


include_once(dirname(__FILE__) . '/data-manager.php');
include_once(dirname(__FILE__) . '/scheduler.php');

class WSTX1SchedulerTest {

    private $buffer_output = '';
    private $step_test = 0;
    private $email = 'riochr17@gmail.com';

    function __construct() {
        $this->appendOutput("Start testing ...");
    }

    private function appendOutput($a, $color = 'green') {
        $this->buffer_output .= (++$this->step_test) . ". [" . (new DateTime())->format('Y-m-d H:i:s') . "]: " . $a . "\n";
    }

    private function wisataone_X1_test_insert_new_schedule_to_database() {
        $id_order = mt_rand(1000000, 9999999);
        $postfix_trip_name = mt_rand(10, 999);
        $sample_data = [
            'id_tour' => 5, 
            'id_order' => $id_order, 
            'traveler_name' => 'Testing', 
            'traveler_email' => $this->email, 
            'tour_name' => 'Test Tour ' . strval($postfix_trip_name), 
            'destination' => '.', 
            'price' => 88888, 
            'trip_date' => '2020-05-19 07:42:52', 
            'number_of_traveler' => 1, 
            'current_step' => 0, 
            'booking_date' => (new DateTime())->format('Y-m-d H:i:s'),
        ];
        return wisataone_X1_insert_order_full_data($sample_data);
    }

    private function wisataone_X1_test_finalize($status, $id = null) {
        if ($id) {
            //wisataone_X1_delete_single_order($id);
        }
        if (!$status) {
            $this->appendOutput("Testing stopped.", 'red');
            return false;
        } else {
            $this->appendOutput("Testing passed.");
            return true;
        }
    }

    private function next_mail($schedulerInstance) {
        $this->appendOutput("Trying to send mail for next step ...");
        if (!$schedulerInstance->checkMailQueue()) {
            $this->appendOutput("Send mail for step = {$schedulerInstance->current_step} to {$schedulerInstance->traveler_email}, failed.", 'red');
            $this->wisataone_X1_test_finalize(false, $schedulerInstance->id);
            return false;
        }
        $this->appendOutput("Send mail for step = {$schedulerInstance->current_step} to {$schedulerInstance->traveler_email}, mail sent.");
        return true;
    }

    private function wisataone_X1_test_new_schedule_and_instance() {
        $insert_res = $this->wisataone_X1_test_insert_new_schedule_to_database();
        if (!$insert_res) {
            $this->appendOutput("Insert new order failed.");
            $this->wisataone_X1_test_finalize(false);
            return false;
        }
        $id = $insert_res['data'];
        $this->appendOutput("Data successfully inserted with id = {$id}.");
        $this->appendOutput("Loading scheduler data id = {$id} ...");
        $schedulerInstance = (new WSTX1Scheduler([]))->load($id);
        $this->appendOutput("Scheduler data id = {$id} loaded.");
        $this->appendOutput("Checking current email step -> current_step = {$schedulerInstance->current_step}");
        return $schedulerInstance;
    }

    private function setEmailTimestampTo($schedulerInstance, $key, $ts) {
        $date1 = DateTime::createFromFormat('Y-m-d H:i:s', $schedulerInstance->{$key});
        $dateAddedTs = new DateTime();
        $dateAddedTs->setTimestamp($date1->getTimestamp() - $ts);
        $begin_ts_str = $date1->format('Y-m-d H:i:s');
        $ts_str = $dateAddedTs->format('Y-m-d H:i:s');
        $this->appendOutput("Field {$key} timestamp adjusted from {$begin_ts_str} to {$ts_str}.");
        $schedulerInstance->debug_update($key, $ts_str);
    }

    private function setBookingDateToNDaysPlus5Secs($schedulerInstance, $n) {
        $date1 = DateTime::createFromFormat('Y-m-d H:i:s', $schedulerInstance->trip_date);
        $dateAddedTs = new DateTime();
        $dateAddedTs->setTimestamp($dateAddedTs->getTimestamp() + 60 * 60 * 24 * $n + 5);
        $begin_ts_str = $date1->format('Y-m-d');
        $ts_str = $dateAddedTs->format('Y-m-d');
        $this->appendOutput("Booking date adjusted from {$begin_ts_str} to {$ts_str}.");
        $schedulerInstance->debug_update('trip_date', $dateAddedTs->format('Y-m-d H:i:s'));
    }

    private function test_payment_notification($id, $status) {
        $this->appendOutput("Payment notification incoming, status = {$status} with order id = {$id}");
        $data = [
            "transaction_time" => "2019-09-22 14:12:16",
            "transaction_status" => $status,
            "transaction_id" => "43f865f4-6483-4a50-9562-9574e7d87500",
            "status_message" => "midtrans payment notification",
            "status_code" => "200",
            "signature_key" => "895d5608bade91306a8fd4d5f8de440a511452a2a7b1a7187440cddc4659d477711f1843ed6b3bbe7aede6637357a3f4acae0f6fa4b95a45a483a361feaba054",
            "payment_type" => "bri_epay",
            "order_id" => "dp-" . $id,
            "gross_amount" => "22199000.00",
            "fraud_status" => "accept",
            "currency" => "IDR"
        ];
        wp_remote_post(get_site_url() . '/wp-json/step-payment/v1/notify', ['body' => $data]);
    }


    /**
     * ------------------------
     * ------- DIVIDER --------
     * ------------------------
     */


    /**
     * Start testing scene
     */
    public function start($email) {
        if ($email) {
            $this->email = $email;
        }
        $this->appendOutput("SCENE #3 BEGIN");
        $this->appendOutput("Deskripsi skenario: Trip menunggu menunggu pembayaran DP 10% dan mencapai H-45");
        $this->appendOutput("SCENE #3 -----");
        $this->scene_3();
        $this->appendOutput("SCENE #3 END.");
        $this->appendOutput("--- break ---");
        $this->appendOutput("SCENE #4 BEGIN");
        $this->appendOutput("Deskripsi skenario: Trip menunggu kuota terpenuhi dan mencapai H-45");
        $this->appendOutput("SCENE #4 -----");
        $this->scene_4();
        $this->appendOutput("SCENE #4 END.");
        $this->appendOutput("--- break ---");
        return $this->buffer_output;
    }

    /**
     * Testing Scenes
     */
    private function scene_1() {

        $schedulerInstance = $this->wisataone_X1_test_new_schedule_and_instance();
        if (!$schedulerInstance) return;

        $tiga_hari_kurang_lima_detik = 60 * 60 * 24 * 3 - 5;
        $satu_hari_kurang_lima_detik = 60 * 60 * 24 * 1 - 5;
        
        /** 100 */ 
        // no set email debug time required
        if (!$this->next_mail($schedulerInstance)) return;

        /** 103 */ 
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_10', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /** 106 */ 
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_10', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /** 107 */ 
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_10', $satu_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /** -100 */ 
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_10', $satu_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        return $this->wisataone_X1_test_finalize(true, $id);
    }

    private function scene_2() {

        $schedulerInstance = $this->wisataone_X1_test_new_schedule_and_instance();
        if (!$schedulerInstance) return;

        $tiga_hari_kurang_lima_detik = 60 * 60 * 24 * 3 - 5;
        $satu_hari_kurang_lima_detik = 60 * 60 * 24 * 1 - 5;
        

        /** 100 */ 
        // no set email debug time required
        if (!$this->next_mail($schedulerInstance)) return;
        sleep(5);

        /** 103 */ 
        $schedulerInstance->load($schedulerInstance->id);
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_10', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /**
         * Successful payment
         */
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $this->test_payment_notification($schedulerInstance->id_order, "settlement");

        /** 500 */ 
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $min_quota_simulation_result = $schedulerInstance->sim_minimum_quota_passed();
        if (!$min_quota_simulation_result[0]) {
            $this->appendOutput("Send mail for step = {$schedulerInstance->current_step} to {$schedulerInstance->traveler_email}, failed.", 'red');
            $this->wisataone_X1_test_finalize(false, $schedulerInstance->id);
            return;
        }
        if (!$min_quota_simulation_result[1]) {
            $this->appendOutput("Update database for step = {$schedulerInstance->current_step}, failed.", 'red');
            $this->wisataone_X1_test_finalize(false, $schedulerInstance->id);
            return;
        }

        /** 503 */ 
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_50', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /** 506 */ 
        $schedulerInstance->load($schedulerInstance->id);
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_50', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /**
         * Successful payment
         */
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $this->test_payment_notification($schedulerInstance->id_order, "settlement");

        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $this->setBookingDateToNDaysPlus5Secs($schedulerInstance, 50);

        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        if (!$this->next_mail($schedulerInstance)) return;

        /** 403 */ 
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_40', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /** 406 */ 
        $schedulerInstance->load($schedulerInstance->id);
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_40', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /** 407 */ 
        $schedulerInstance->load($schedulerInstance->id);
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_40', $satu_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /**
         * Successful payment
         */
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $this->test_payment_notification($schedulerInstance->id_order, "settlement");

        return $this->wisataone_X1_test_finalize(true, $id);
    }
    
    private function scene_3() {

        $schedulerInstance = $this->wisataone_X1_test_new_schedule_and_instance();
        if (!$schedulerInstance) return;

        $tiga_hari_kurang_lima_detik = 60 * 60 * 24 * 3 - 5;
        $satu_hari_kurang_lima_detik = 60 * 60 * 24 * 1 - 5;
        
        /** 100 */ 
        // no set email debug time required
        if (!$this->next_mail($schedulerInstance)) return;

        /** 103 */ 
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_10', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /**
         * Simulate to H-45
         */
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $this->setBookingDateToNDaysPlus5Secs($schedulerInstance, 45);

        /**
         * Check next mail, should be trip cancelation
         */
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        if (!$this->next_mail($schedulerInstance)) return;

        return $this->wisataone_X1_test_finalize(true, $id);
    }
    
    private function scene_4() {

        $schedulerInstance = $this->wisataone_X1_test_new_schedule_and_instance();
        if (!$schedulerInstance) return;

        $tiga_hari_kurang_lima_detik = 60 * 60 * 24 * 3 - 5;
        $satu_hari_kurang_lima_detik = 60 * 60 * 24 * 1 - 5;
        
        /** 100 */ 
        // no set email debug time required
        if (!$this->next_mail($schedulerInstance)) return;

        /** 103 */ 
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_10', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /** 106 */ 
        $this->setEmailTimestampTo($schedulerInstance, 'email_p_10', $tiga_hari_kurang_lima_detik);
        sleep(5);
        if (!$this->next_mail($schedulerInstance)) return;

        /**
         * Successful payment
         */
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $this->test_payment_notification($schedulerInstance->id_order, "settlement");

        /**
         * Simulate to H-45
         */
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        $this->setBookingDateToNDaysPlus5Secs($schedulerInstance, 45);

        /**
         * Check next mail, should be trip cancelation
         */
        sleep(5);
        $schedulerInstance->load($schedulerInstance->id);
        if (!$this->next_mail($schedulerInstance)) return;

        return $this->wisataone_X1_test_finalize(true, $id);
    }
}
