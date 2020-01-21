<?php

function wisataone_X1_changeOrderStatusToOnlinePaid($order_id) {
    global $wpdb;
    $wp_track_table = $wpdb->prefix . "tourmaster_order";
    $Online_Paid = 'online-paid';

    $res = $wpdb->get_results( 
        "
        SELECT *
        FROM {$wp_track_table}
        WHERE {$wp_track_table}.id = {$order_id}
        LIMIT 1
        "
    );

    if (!$res) {
        return "404";
    }

    $wpdb->update( 
        $wp_track_table, 
        array( 
            'order_status' => $Online_Paid
        ), 
        array( 'id' => $order_id ), 
        array( 
            '%s'
        ), 
        array( '%d' ) 
    );

    return true;
}
