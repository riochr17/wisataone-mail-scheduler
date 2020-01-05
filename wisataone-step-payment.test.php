<?php


include_once(dirname(__FILE__) . '/data-manager.php');
include_once(dirname(__FILE__) . '/scheduler.php');

class WSTX1SchedulerTest {

    private $buffer_output = '';
    private $step_test = 0;

    function __construct() {
        $this->appendOutput("Start testing ...");
    }

    private function appendOutput($a, $color = 'green') {
        $this->buffer_output .= (++$this->step_test) . ": " . $a . "\n";
    }

    private function wisataone_X1_test_insert_new_schedule_to_database() {
        $id_order = mt_rand(1000000, 9999999);
        $sample_data = [
            'id_tour' => 5, 
            'id_order' => $id_order, 
            'traveler_name' => 'Testing', 
            'traveler_email' => 'riochr17@gmail.com', 
            'tour_name' => 'Test Tour', 
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
            wisataone_X1_delete_single_order($id);
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


    /**
     * ------------------------
     * ------- DIVIDER --------
     * ------------------------
     */


    /**
     * Start testing scene
     */
    public function start() {
        $this->appendOutput("SCENE #1 BEGIN");
        $this->scene_1();
        $this->appendOutput("SCENE #1 END.");
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
        // not set email debug time required
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
}
