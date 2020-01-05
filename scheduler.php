<?php

include_once(dirname(__FILE__) . '/utils.php');
include_once(dirname(__FILE__) . '/data-manager.php');
include_once(dirname(__FILE__) . '/template-loader.php');

class WSTX1Scheduler { 
    public $id, 
           $id_tour, 
           $id_order, 
           $current_step, 
           $booking_date, 
           $order_datetime,

           $traveler_name, 
           $traveler_email, 
           $tour_name, 
           $destination, 
           $price, 
           $trip_date,
           $number_of_traveler,

           $email_p_10, 
           $email_p_10_h3,
           $email_p_10_h6,
           $email_p_10_h7,
           $email_s_10,
           $email_c_10,

           $email_p_50,
           $email_p_50_h3,
           $email_p_50_h6,
           $email_p_50_h7,
           $email_c_50,
           $email_s_50,

           $email_p_40,
           $email_p_40_h3,
           $email_p_40_h6,
           $email_p_40_h7,
           $email_s_40,
           $email_c_40,

           $ts_payment_10,
           $ts_payment_50,
           $ts_payment_40,

           $email_kuota_terpenuhi,
           
           $time_to_trip;

    function __construct($datas) {
        $this->id = $datas['id'];
        $this->id_tour = $datas['id_tour'];
        $this->id_order = $datas['id_order'];
        $this->current_step = $datas['current_step'];
        $this->booking_date = $datas['booking_date'];
        $this->order_datetime = $datas['order_datetime'];

        $this->traveler_name = $datas['traveler_name'];
        $this->traveler_email = $datas['traveler_email'];
        $this->tour_name = $datas['tour_name'];
        $this->destination = $datas['destination'];
        $this->price = $datas['price'];
        $this->trip_date = $datas['trip_date'];
        $this->number_of_traveler = $datas['number_of_traveler'];

        $this->email_p_10 = $datas['email_p_10'];
        $this->email_p_10_h3 = $datas['email_p_10_h3'];
        $this->email_p_10_h6 = $datas['email_p_10_h6'];
        $this->email_p_10_h7 = $datas['email_p_10_h7'];
        $this->email_s_10 = $datas['email_s_10'];
        $this->email_c_10 = $datas['email_c_10'];

        $this->email_p_50 = $datas['email_p_50'];
        $this->email_p_50_h3 = $datas['email_p_50_h3'];
        $this->email_p_50_h6 = $datas['email_p_50_h6'];
        $this->email_p_50_h7 = $datas['email_p_50_h7'];
        $this->email_c_50 = $datas['email_c_50'];
        $this->email_s_50 = $datas['email_s_50'];

        $this->email_p_40 = $datas['email_p_40'];
        $this->email_p_40_h3 = $datas['email_p_40_h3'];
        $this->email_p_40_h6 = $datas['email_p_40_h6'];
        $this->email_p_40_h7 = $datas['email_p_40_h7'];
        $this->email_s_40 = $datas['email_s_40'];
        $this->email_c_40 = $datas['email_c_40'];

        $this->ts_payment_10 = $datas['ts_payment_10'];
        $this->ts_payment_50 = $datas['ts_payment_50'];
        $this->ts_payment_40 = $datas['ts_payment_40'];

        $this->email_kuota_terpenuhi = $datas['email_kuota_terpenuhi'];

        $this->time_to_trip = $datas['time_to_trip'];
    }

    public function load($id) {
        $arr = wisataone_X1_get_single_order($id);
        $this->__construct((array) $arr);

        return $this;
    }

    public function loadByOrderId($id_booking) {
        $arr = wisataone_X1_is_order_exist($id_booking);
        $this->__construct((array) $arr);

        return $this;
    }

    public function getCurrentStep() {
        switch($this->current_step) {
            // P 10 -> H-3
            case 100:
                return "Menunggu Pembayaran DP 10%";

            // P 10 -> H-3
            case 103:
                return "Menunggu Pembayaran DP 10% (< 3 hari)";
            
            // P 10 H-3 -> H-6
            case 106:
                return "Menunggu Pembayaran DP 10% (3 s/d 6 hari)";

            // P 10 H-6 -> H-7
            case 107:
                return "Menunggu Pembayaran DP 10% (dl < 1 hari)";

            // P 10, H-3, H-6, H-7 -> S 10
            case 110:
                return "Pembayaran DP 10% selesai";

            // P 10 H-7 -> C 10
            case -100:
                return "DP 10% gagal";

            
            // Waiting for quota
            case 200:
                return "Menunggu kuota terpenuhi";


            // P 50 -> H-0
            case 500:
                return "Menunggu Pembayaran kedua 50%";

            // P 50 -> H-3
            case 503:
                return "Menunggu Pembayaran kedua 50% (< 3 hari)";
            
            // P 50 H-3 -> H-6
            case 506:
                return "Menunggu Pembayaran kedua 50% (3 s/d 6 hari)";

            // P 50 H-6 -> H-7
            case 507:
                return "Menunggu Pembayaran kedua 50% (dl < 1 hari)";

            // P 50, H-3, H-6, H-7 -> S 10
            case 510:
                return "Pembayaran kedua 50% selesai";

            // P 50 H-7 -> C 10
            case -500:
                return "Pembayaran kedua 50% gagal";




            // P 40 -> H-0
            case 400:
                return "Menunggu Pembayaran ketiga 40%";

            // P 40 -> H-3
            case 403:
                return "Menunggu Pembayaran ketiga 40% (< 3 hari)";
            
            // P 40 H-3 -> H-6
            case 406:
                return "Menunggu Pembayaran ketiga 40% (3 s/d 6 hari)";

            // P 40 H-6 -> H-7
            case 407:
                return "Menunggu Pembayaran ketiga 40% (dl < 1 hari)";

            // P 40, H-3, H-6, H-7 -> S 10
            case 410:
                return "Pembayaran ketiga 40% selesai";

            // P 40 H-7 -> C 10
            case -400:
                return "Pembayaran ketiga 40% gagal";
        }

        return '-';

    }

    public function getNextSendingTime() {
        switch($this->current_step) {
            // P 10 -> H-3
            case 100:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_10, 60 * 60 * 24 * 3);

            // P 10 -> H-3
            case 103:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_10, 60 * 60 * 24 * 6);
            
            // P 10 H-3 -> H-6
            case 106:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_10, 60 * 60 * 24 * 7);

            // P 10 H-6 -> H-7
            case 107:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_10, 60 * 60 * 24 * 8);

            // P 10, H-3, H-6, H-7 -> S 10
            case 110:
                return -1;
            case -100:
                return -1;

            
            // Waiting for quota
            case 200:
                $h_45_ts = wisataone_X1_time_diff_substract_and_by_now($this->trip_date, 60 * 60 * 24 * 45);
                return $h_45_ts;


            // P 50 -> H-3
            case 500:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_50, 60 * 60 * 24 * 3);

            // P 50 -> H-3
            case 503:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_50, 60 * 60 * 24 * 6);
            
            // P 50 H-3 -> H-6
            case 506:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_50, 60 * 60 * 24 * 7);

            // P 50 H-6 -> H-7
            case 507:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_50, 60 * 60 * 24 * 8);

            // P 50, H-3, H-6, H-7 -> S 10
            case 510:
            // P 50 H-7 -> C 10
            case -500:
                return -1;


            // P 40 -> H-3
            case 400:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_40, 60 * 60 * 24 * 3);

            // P 40 -> H-3
            case 403:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_40, 60 * 60 * 24 * 6);
            
            // P 40 H-3 -> H-6
            case 406:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_40, 60 * 60 * 24 * 7);

            // P 40 H-6 -> H-7
            case 407:
                return wisataone_X1_time_diff_add_and_by_now($this->email_p_40, 60 * 60 * 24 * 8);

            // P 40, H-3, H-6, H-7 -> S 10
            case 410:
            // P 40 H-7 -> C 10
            case -400:
                return -1;
            default:
                return -1;
        }
    }

    public function getNextMail() {
        return formatElapsedTS($this->getNextSendingTime());
    }

    public function getJenisMail() {
        switch($this->current_step) {
            // P 10 -> H-3
            case 100:
            case 103:
            case 106:
            case 107:
                return "Pembayaran DP 10%";

            // P 10, H-3, H-6, H-7 -> S 10
            case 110:
                return "Pembayaran DP 10% Berhasil";

            // P 10 H-7 -> C 10
            case -100:
                return "Pembayaran DP 10% Gagal";

            
            // Waiting for quota
            case 200:
                return "Menunggu Kuota Terpenuhi";


            // P 50 -> H-3
            case 500:
            case 503:
            case 506:
            case 507:
                return "Pembayaran kedua 50%";

            // P 50, H-3, H-6, H-7 -> S 10
            case 510:
                return "Pembayaran kedua 50% Berhasil";

            // P 50 H-7 -> C 10
            case -500:
                return "Pembayaran kedua 50% Gagal";


            // P 40 -> H-3
            case 400:
            case 403:
            case 406:
            case 407:
                return "Pembayaran ketiga 40%";

            // P 40, H-3, H-6, H-7 -> S 10
            case 410:
                return "Pembayaran ketiga 40% Berhasil";

            // P 40 H-7 -> C 10
            case -400:
                return "Pembayaran ketiga 40% Gagal";
        }

        return "Menunggu Pembayaran";
    }

    public function getJumlahBayar() {
        switch($this->current_step) {
            // P 10 -> H-3
            case 100:
            case 103:
            case 106:
            case 107:
            case 110:
            case -100:
                return $this->price * 0.1;

            
            // Waiting for quota
            case 200:
                return 0;


            // P 50 -> H-3
            case 500:
            case 503:
            case 506:
            case 507:
            case 510:
            case -500:
                return $this->price * 0.5;


            // P 40 -> H-3
            case 400:
            case 403:
            case 406:
            case 407:
            case 410:
            case -400:
                return $this->price * 0.4;
        }

        return 0;
    }

    public function getJumlahBayarMail() {
        return wisataone_X1_formatRp($this->getJumlahBayar());
    }

    public function getStatusBayarMail() {
        switch($this->current_step) {
            // P 10 -> H-3
            case 100:
            case 103:
            case 106: 
            case 107: 
            case 110:
                return $this->ts_payment_10 != NULL;

            // P 10 H-7 -> C 10
            case -100:
                return false;

            
            // Waiting for quota
            case 200:
                return true;


            // P 50 -> H-3
            case 500:
            case 503:
            case 506:
            case 507:
            case 510:
                return $this->ts_payment_50 != NULL;
            
            // P 50 H-7 -> C 10
            case -500:
                return false;


            // P 40 -> H-3
            case 400:
            case 403:
            case 406:
            case 407:
            case 410:
                return $this->ts_payment_40 != NULL;

            // P 40 H-7 -> C 10
            case -400:
                return false;
        }

        return false;
    }

    public function getTemplateClassMail() {
        switch($this->current_step) {
            // P 10 -> H-3
            case 100:
            case 103:
            case 106: 
            case 107: 
            case 110:
                $sudah_bayar = $this->getStatusBayarMail();
                return "wisataone-mail--dp-10-". ($sudah_bayar ? 'success' : 'payment');

            // P 10 H-7 -> C 10
            case -100:
                $sudah_bayar = false;
                return "wisataone-mail--dp-10-". ($sudah_bayar ? 'success' : 'payment');

            
            // Waiting for quota
            case 200:
                $sudah_bayar = true;
                return "wisataone-mail--dp-10-". ($sudah_bayar ? 'success' : 'payment');


            // P 50 -> H-3
            case 500:
            case 503:
            case 506:
            case 507:
            case 510:
                $sudah_bayar = $this->getStatusBayarMail();
                return "wisataone-mail--p-50-". ($sudah_bayar ? 'success' : 'payment');
            
            // P 50 H-7 -> C 10
            case -500:
                $sudah_bayar = false;
                return "wisataone-mail--p-50-". ($sudah_bayar ? 'success' : 'payment');


            // P 40 -> H-3
            case 400:
            case 403:
            case 406:
            case 407:
            case 410:
                $sudah_bayar = $this->getStatusBayarMail();
                return "wisataone-mail--p-40-". ($sudah_bayar ? 'success' : 'payment');

            // P 40 H-7 -> C 10
            case -400:
                $sudah_bayar = false;
                return "wisataone-mail--p-40-". ($sudah_bayar ? 'success' : 'payment');
        }

        return "wisataone-mail--dp-10-payment";
    }

    public function getTotalHarga() {
        return wisataone_X1_formatRp($this->price);
    }

    public function getMailSummary() {
        $name = $this->traveler_name;
        $jenis_pembayaran = $this->getJenisMail();
        $tour_name = $this->tour_name;
        $is_step_200 = $this->current_step == 200;
        $sudah_bayar = $this->getStatusBayarMail();

        if ($sudah_bayar) {
            $text_kuota_terpenuhi = $is_step_200 ? 'kuota trip telah terpenuhi, saatnya melakukan ' : '';
            return "Halo <b>" . $name . "</b>, " . $text_kuota_terpenuhi . "pembayaran " . $jenis_pembayaran . " untuk pemesanan <b>" . $tour_name . "</b> telah berhasil.";
        }
        return "Halo <b>" . $name . "</b>, pemesanan kamu: <b>" . $tour_name . "</b>, saatnya melakukan " . $jenis_pembayaran . ".";
    }

    public function getReadableTripDate() {
        return DateTime::createFromFormat('Y-m-d H:i:s', $this->trip_date)->format('d M Y');
    }

    public function getPaymentLink() {
        return get_site_url() . '/?wisataone-payment-id=' . $this->id;
    }

    public function getPaymentTitle() {
        $jenis_pembayaran = $this->getJenisMail();
        $tour_name = $this->tour_name;
        return $jenis_pembayaran . " pemesanan " . $tour_name . ".";
    }

    public function processPayment($payment_status) {
        $payment_position = "10";
        if ($this->ts_payment_10) { $payment_position = "50"; }
        if ($this->ts_payment_50) { $payment_position = "40"; }
        if ($this->ts_payment_40) { $payment_position = false; }

        $is_settled = $payment_status == 'settlement';
        if ($payment_position) {
            if ($is_settled) {
                $this->{"ts_payment_" . $payment_position} = (new DateTime())->format('Y-m-d H:i:s');
                switch ($payment_position) {
                    case "10":
                        $this->current_step = 110;
                        break;
                    case "50":
                        $this->current_step = 510;
                        break;
                    case "40":
                        $this->current_step = 410;
                        break;
                }
            }
        }

        /**
         * Finalize update
         */
        $update_ok = wisataone_X1_update_order($this);
        if ($update_ok) {
            return $this->tryCheckingNextMailSend();
        }

        return $update_ok;
    }

    private function tryCheckingNextMailSend() {
        if ($this->getNextSendingTime() <= 0) {
            switch($this->current_step) {
                case 100:
                    $this->current_step = 103;
                    return $this->sendMail(103);
                case 103:
                    $this->current_step = 106;
                    return $this->sendMail(106);
                case 106:
                    $this->current_step = 107;
                    return $this->sendMail(107);
                case 107:
                    $this->current_step = -100;
                    return $this->sendMail(-100);
                case 110:
                    return $this->sendMail(110);

                    /**
                     * Antara menunggu kuota terpenuhi atau batas 45 hari sudah terlewati
                     */
                    $this->current_step = 200;
                    if ($this->getNextSendingTime() > 0) {
                        return $this->sendMail(200);
                    } else {
                        $this->current_step = 500;
                        return $this->sendMail(500);
                    }

                case 500:
                    $this->current_step = 503;
                    return $this->sendMail(503);
                case 503:
                    $this->current_step = 506;
                    return $this->sendMail(506);
                case 506:
                    $this->current_step = 507;
                    return $this->sendMail(507);
                case 507:
                    $this->current_step = -500;
                    return $this->sendMail(-500);
                case 510:
                    return $this->sendMail(510);
                    $this->current_step = 400;
                    return $this->sendMail(400);

                case 400:
                    $this->current_step = 403;
                    return $this->sendMail(403);
                case 403:
                    $this->current_step = 406;
                    return $this->sendMail(406);
                case 406:
                    $this->current_step = 407;
                    return $this->sendMail(407);
                case 407:
                    $this->current_step = -400;
                    return $this->sendMail(-400);
                case 410:
                    return $this->sendMail(410);
                default:
                    $this->current_step = 100;
                    return $this->sendMail(100);
            }
        }
    }

    public function sendMail($email_id) {
        $current_date = (new DateTime())->format('Y-m-d H:i:s');
        if (!$this->mailer_do()) {
            return false;
        }
        switch($this->current_step) {
            case 100:
                $this->email_p_10 = $current_date;
                break;
            case 103:
                $this->email_p_10_h3 = $current_date;
                break;
            case 106: 
                $this->email_p_10_h6 = $current_date;
                break;
            case 107: 
                $this->email_p_10_h7 = $current_date;
                break;
            case 110:
                $this->email_s_10 = $current_date;
                break;

            // P 10 H-7 -> C 10
            case -100:
                $this->email_c_10 = $current_date;
                break;
            
            // Waiting for quota
            case 200:
                $this->email_kuota_terpenuhi = $current_date;
                break;

            case 500:
                $this->email_p_50 = $current_date;
                break;

            // P 50 -> H-3
            case 503:
                $this->email_p_50_h3 = $current_date;
                break;
            case 506: 
                $this->email_p_50_h6 = $current_date;
                break;
            case 507: 
                $this->email_p_50_h7 = $current_date;
                break;
            case 510:
                $this->email_s_50 = $current_date;
                break;

            // P 50 H-7 -> C 50
            case -500:
                $this->email_c_50 = $current_date;
                break;

            case 400:
                $this->email_p_40 = $current_date;
                break;
            
            // P 40 -> H-3
            case 403:
                $this->email_p_40_h3 = $current_date;
                break;
            case 406: 
                $this->email_p_40_h6 = $current_date;
                break;
            case 407: 
                $this->email_p_40_h7 = $current_date;
                break;
            case 410:
                $this->email_s_40 = $current_date;
                break;

            // P 40 H-7 -> C 40
            case -400:
                $this->email_c_40 = $current_date;
                break;
        }

        return wisataone_X1_update_order($this);
    }

    private function mailer_do() {
        $sender_name = tourmaster_get_option('general', 'system-email-name', 'WORDPRESS');
        $sender = tourmaster_get_option('general', 'system-email-address');

        if( !empty($sender) ){ 
            $headers  = "From: {$sender_name} <{$sender}>\r\n";
            if( !empty($settings['reply-to']) ){
                $headers .= "Reply-To: {$settings['reply-to']}\r\n";
            }
            if( !empty($settings['cc']) ){
                $headers .= "CC: {$settings['cc']}\r\n"; 
            }
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $sample_rcpt = $this->traveler_email;
            $sample_title = $this->tour_name . ": " . $this->getCurrentStep();
            $sample_html = $this->get_mail_trx_template();
            return wp_mail($sample_rcpt, $sample_title, $sample_html, $headers);
        }

        return false;
    }

    public function get_mail_trx_template() {
        if (!$this->id) return "";
        
        return wisataone_X1_get_template("general.html", [
            'template_class' => $this->getTemplateClassMail(),

            'summary' => $this->getMailSummary(),

            'tour_name' => $this->tour_name,
            'traveler_name' => $this->traveler_name,
            'id_order' => $this->id_order,
            'booking_date' => $this->booking_date,
            'img_logo' => 'https://wisataone.id/wp-content/uploads/2018/08/Logo-Wisataone.png',
            'img_bg' => 'https://www.wisataone.id/wp-content/uploads/2019/12/1080px-Tropenmuseum-1024x683.jpg',

            'id_transaksi' => $this->id,
            'current_date' => (new DateTime())->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i:s'),

            'jenis_transaksi' => $this->getJenisMail(),
            'jumlah_bayar' => $this->getJumlahBayarMail(),
            'status_bayar' => $this->getStatusBayarMail() ? "Lunas" : "Belum Lunas",

            'tour_name' => $this->tour_name,
            'trip_date' => $this->getReadableTripDate(),
            'number_of_traveler' => $this->number_of_traveler,

            'price' => $this->getTotalHarga(),

            'status_pembayaran_terakhir' => $this->getCurrentStep(),
            'payment_link' => $this->getPaymentLink()
        ]);
    }

    public function checkMailQueue() {
        return $this->tryCheckingNextMailSend();
    }



    public function getOrderIdForPayment() {
        switch($this->current_step) {
            // P 10 -> H-3
            case 0:
            case 100:
            case 103:
            case 106:
            case 107:
            case 110:
            case -100:
                return 'dp-' . $this->id_order . '-10';
            
            // Waiting for quota
            case 200:
            // P 50 -> H-3
            case 500:
            case 503:
            case 506:
            case 507:
            case 510:
            case -500:
                return 'dp-' . $this->id_order . '-50';

            case 400:
            case 403:
            case 406:
            case 407:
            case 410:
            case -400:
                return 'dp-' . $this->id_order . '-40';
        }

        return 'dp-' . $this->id_order;
    }

    public function debug_update($key, $value) {
        $this->{$key} = $value;
        return wisataone_X1_update_order($this);
    }
}
