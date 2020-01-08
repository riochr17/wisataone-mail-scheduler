<?php



global $wisataone_X1_plugin_db_version, $wisataone_X1_tblname;
$wisataone_X1_plugin_db_version = '1.0.0';
$wisataone_X1_tblname = 'wisataone_mail_scheduler';

function wisataone_X1_res($status, $str) {
    return [
        'status' => $status,
        'data' => $str
    ];
}

/**
 * Get all notification from database.
 */
function wisataone_X1_update_order($data) {
    global $wpdb, $wisataone_X1_tblname;
    $wp_track_table = $wpdb->prefix . $wisataone_X1_tblname;

    $copy_data = (array) $data;
    $id = $data->id;
    unset($copy_data['id']);
    unset($copy_data['time_to_trip']);

    $updated = $wpdb->update($wp_track_table, $copy_data, array(
        'id' => $id
    ));
 
    if ( false === $updated ) {
        $wpdb->show_errors();
        return false;
    } else {
        return true;
    }
}

/**
 * Get all notification from database.
 */
function wisataone_X1_get_all_order() {
    global $wpdb, $wisataone_X1_tblname;
    $wp_track_table = $wpdb->prefix . $wisataone_X1_tblname;

    return $wpdb->get_results( 
        "
        SELECT *
        FROM {$wp_track_table}
        "
    );
}

/**
 * Get all notification from database.
 */
function wisataone_X1_get_single_order($id) {
    global $wpdb, $wisataone_X1_tblname;
    $wp_track_table = $wpdb->prefix . $wisataone_X1_tblname;

    $res = $wpdb->get_results( 
        "
        SELECT *
        FROM {$wp_track_table}
        WHERE {$wp_track_table}.id = {$id}
        LIMIT 1
        "
    );

    if ($res) {
        return $res[0];
    }

    return null;
}

/**
 * Get all notification from database.
 */
function wisataone_X1_delete_single_order($id) {
    global $wpdb, $wisataone_X1_tblname;
    $wp_track_table = $wpdb->prefix . $wisataone_X1_tblname;

    $res = $wpdb->get_results( 
        "
        DELETE FROM {$wp_track_table}
        WHERE {$wp_track_table}.id = {$id}
        "
    );

    if ($res) {
        return $res[0];
    }

    return null;
}

/**
 * Get all notification from database.
 */
function wisataone_X1_is_order_exist($id_booking) {
    global $wpdb, $wisataone_X1_tblname;
    $wp_track_table = $wpdb->prefix . $wisataone_X1_tblname;

    $res = $wpdb->get_results(
        "
        SELECT *
        FROM {$wp_track_table}
        WHERE {$wp_track_table}.id_order = {$id_booking}
        LIMIT 1
        "
    );

    if ($res) {
        return $res[0];
    }

    return null;
}

function wisataone_X1_get_tour_data($id_booking) {
    $data = tourmaster_get_booking_data(array('id' => $id_booking), array('single' => true));
    $billing_info = json_decode($data->billing_info);

    return [
        'id_tour' => $data->tour_id, 
        'id_order' => $data->id, 
        'traveler_name' => $billing_info->first_name . ' ' . $billing_info->last_name, 
        'traveler_email' => $billing_info->email, 
        'tour_name' => get_the_title($data->tour_id), 
        'destination' => '.', 
        'price' => $data->total_price, 
        'trip_date' => $data->travel_date . ' 07:42:52', 
        'number_of_traveler' => $data->traveller_amount, 
        'current_step' => 0, 
        'booking_date' => $data->booking_date,
    ];
}

/**
 * Insert new order to database.
 */
function wisataone_X1_insert_order($id_booking) {
    if (wisataone_X1_is_order_exist($id_booking)) { return false; };
    $y = wisataone_X1_get_tour_data($id_booking);
    $res_y = wisataone_X1_insert_order_full_data($y);

    if (!$res_y[0]) {
        return false;
    }

    $schedule = (new WSTX1Scheduler([]))->load($res_y[1]);
    $schedule->checkMailQueue();

    return true;
}

/**
 * Insert new order to database.
 */
function wisataone_X1_insert_order_full_data ($datas) {
    global $wpdb, $wisataone_X1_tblname;
    $wp_track_table = $wpdb->prefix . $wisataone_X1_tblname;

    if (!$datas['id_tour']) return wisataone_X1_res(false, 'ID Tour tidak boleh kosong');
    if (!$datas['id_order']) return wisataone_X1_res(false, 'ID Order tidak boleh kosong');
    if (!$datas['traveler_name']) return wisataone_X1_res(false, 'Nama Traveler tidak boleh kosong');
    if (!$datas['tour_name']) return wisataone_X1_res(false, 'Nama Tur tidak boleh kosong');
    if (!$datas['destination']) return wisataone_X1_res(false, 'Tujuan tidak boleh kosong');
    if (!$datas['price']) return wisataone_X1_res(false, 'Harga tidak boleh kosong');
    if (!$datas['trip_date']) return wisataone_X1_res(false, 'Tanggal Trip tidak boleh kosong');
    if (!$datas['number_of_traveler']) return wisataone_X1_res(false, 'Banyak Traveler tidak boleh kosong');

    $res = $wpdb->insert(
        $wp_track_table, 
        $datas
    );

    return wisataone_X1_res(true, $wpdb->insert_id);
}

/**
 * Initialize Plugin
 * Create database for payment notification.
 */
function wisataone_X1_create_plugin_database_table() {
    global $wpdb, $wisataone_X1_plugin_db_version, $wisataone_X1_tblname;

    $wp_track_table = $wpdb->prefix . $wisataone_X1_tblname;
    $charset_collate = $wpdb->get_charset_collate();

    #Check to see if the table exists already, if not, then create it

    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) {
        $sql = "CREATE TABLE {$wp_track_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            id_tour bigint(20) NOT NULL,
            id_order bigint(20) NOT NULL,
            current_step integer DEFAULT 0,
            booking_date DATETIME DEFAULT NULL,
            order_datetime DATETIME DEFAULT NULL,

            traveler_name VARCHAR(191) NOT NULL,
            traveler_email VARCHAR(191) NOT NULL,
            tour_name VARCHAR(191) NOT NULL,
            destination VARCHAR(191) NOT NULL,
            price bigint(20) NOT NULL,
            trip_date DATETIME NOT NULL,
            number_of_traveler integer NOT NULL,

            email_p_10 DATETIME DEFAULT NULL,
            email_p_10_h3 DATETIME DEFAULT NULL,
            email_p_10_h6 DATETIME DEFAULT NULL,
            email_p_10_h7 DATETIME DEFAULT NULL,
            email_s_10 DATETIME DEFAULT NULL,
            email_c_10 DATETIME DEFAULT NULL,

            email_p_50 DATETIME DEFAULT NULL,
            email_p_50_h3 DATETIME DEFAULT NULL,
            email_p_50_h6 DATETIME DEFAULT NULL,
            email_p_50_h7 DATETIME DEFAULT NULL,
            email_c_50 DATETIME DEFAULT NULL,
            email_s_50 DATETIME DEFAULT NULL,

            email_p_40 DATETIME DEFAULT NULL,
            email_p_40_h3 DATETIME DEFAULT NULL,
            email_p_40_h6 DATETIME DEFAULT NULL,
            email_p_40_h7 DATETIME DEFAULT NULL,
            email_s_40 DATETIME DEFAULT NULL,
            email_c_40 DATETIME DEFAULT NULL,

            ts_payment_10 DATETIME DEFAULT NULL,
            ts_payment_50 DATETIME DEFAULT NULL,
            ts_payment_40 DATETIME DEFAULT NULL,

            email_kuota_terpenuhi DATETIME DEFAULT NULL,
            email_kuota_tidak_terpenuhi DATETIME DEFAULT NULL,
            email_kuota_gagal_batas_h_45 DATETIME DEFAULT NULL,

            PRIMARY KEY  (id)
        ) {$charset_collate};";
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
        add_option('wisataone_X1_plugin_db_version', $wisataone_X1_plugin_db_version);
    }
}

function wisataone_X1_drop_plugin_database_table() {
    global $wpdb, $wisataone_X1_plugin_db_version, $wisataone_X1_tblname;

    $wp_track_table = $wpdb->prefix . $wisataone_X1_tblname;
    $sql = "DROP TABLE IF EXISTS {$wp_track_table};";
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $wpdb->query($sql);
    delete_option("wisataone_X1_plugin_db_version");
}

function wisataone_X1_init_db() {
    wisataone_X1_create_plugin_database_table();
}

function wisataone_X1_drop_db() {
    wisataone_X1_drop_plugin_database_table();
}