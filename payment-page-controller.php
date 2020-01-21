<?php

include_once(dirname(__FILE__) . '/data-manager.php');
include_once(dirname(__FILE__) . '/scheduler.php');

if (!function_exists('wisataone_X1_startPayment')) {
    function wisataone_X1_startPayment($sch, $order_id, $amount, $bdata, $isDeposit) {

        $server_key = tourmaster_get_option('payment', 'midtrans-server-key', '');
        $production_mode = tourmaster_get_option('payment', 'midtrans-production-mode', 'disable');
        //Set Your server key
        Veritrans_Config::$serverKey = $server_key;

        // Uncomment for production environment
        Veritrans_Config::$isProduction = !(empty($production_mode) || $production_mode == 'disable');

        Veritrans_Config::$isSanitized = true;
        Veritrans_Config::$is3ds = true;

        $contact_info = json_decode($bdata->contact_info);
        $billing_info = json_decode($bdata->billing_info);
        $booking_detail = json_decode($bdata->booking_detail);
        $pricing_info = json_decode($bdata->pricing_info);

        // Fill transaction details
        $transaction_details = array(
            'order_id' => $sch->getOrderIdForPayment(),
            'gross_amount' => $amount // no decimal allowed
        );
        
        // Mandatory for Mandiri bill payment and BCA KlikPay
        // Optional for other payment methods
        $item1_details = array(
            'id' => $booking_detail->{"tour-id"},
            'price' => $amount,
            'quantity' => 1, // revision
            //'quantity' => intval($bdata->traveller_amount),
            'name' => $sch->getPaymentTitle()
        );
        $item_details = array ($item1_details);
        
        // Optional
        $billing_address = array(
            'first_name'    => $billing_info->first_name,
            'last_name'     => $billing_info->last_name,
            'address'       => $billing_info->contact_address,
            'city'          => "Not Found",
            'postal_code'   => "10000",
            'phone'         => $billing_info->phone,
            'country_code'  => 'IDN'
        );
        
        $customer_details = array(
            'first_name'    => $contact_info->first_name,
            'last_name'     => $contact_info->last_name,
            'email'         => $contact_info->email,
            'phone'         => $contact_info->phone, //mandatory
            'billing_address'  => $billing_address, //optional
        );
        
        // Fill transaction details
        $transaction = array(
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_details,
        );

        // print_r($transaction);
        // return;
        $snapToken = Veritrans_Snap::getSnapToken($transaction);
        return $snapToken;
    }
}

function getSnapToken($trx_id) {
    $sch = (new WSTX1Scheduler([]))->load($trx_id);
    $bdata = tourmaster_get_booking_data(array('id' => $sch->id_order), array('single' => true));
    return wisataone_X1_startPayment($sch, $sch->id_order, ((int) $sch->getJumlahBayar()), $bdata, false);
}

function getPageAttributes($trx_id) {
    $sch = (new WSTX1Scheduler([]))->load($trx_id);
    $production_mode = tourmaster_get_option('payment', 'midtrans-production-mode', 'disable');
    $midtrans_snap_url_res = !(empty($production_mode) || $production_mode == 'disable') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
    $midtrans_client_key = tourmaster_get_option('payment', 'midtrans-client-key', '');

    return [
        'id_transaksi' => $sch->id,

        'tour_name' => $sch->tour_name,
        'trip_date' => $sch->getStartTripDate(),
        'number_of_traveler' => $sch->number_of_traveler,

        'current_date' => (new DateTime())->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i:s'),
        'jenis_transaksi' => $sch->getJenisMail(),
        'jumlah_bayar' => $sch->getJumlahBayarMail(),
        'status_bayar' => $sch->getStatusBayarMail() ? "Lunas" : "Belum Lunas",

        'traveler_name' => $sch->traveler_name,

        'base_url' => get_site_url(),
        'snap_midtrans_url' => $midtrans_snap_url_res,
        'client_key_midtrans' => $midtrans_client_key
    ];
}