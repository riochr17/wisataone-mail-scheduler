<?php



global $wisataone_X1_plugin_db_cron_tick_version, $wisataone_X1_cron_tick_tblname;
$wisataone_X1_plugin_db_cron_tick_version = '1.0.0';
$wisataone_X1_cron_tick_tblname = 'wisataone_ms_cron_tick';

/**
 * Get all notification from database.
 */
function wisataone_X1_update_cron_tick() {
    global $wpdb, $wisataone_X1_cron_tick_tblname;
    $wp_track_table = $wpdb->prefix . $wisataone_X1_cron_tick_tblname;

    $updated = $wpdb->query( 
        $wpdb->prepare( 
            "UPDATE $wp_track_table
             SET `ts` = %s",
             (new DateTime())->format('Y-m-d H:i:s')
        )
    );

    if ( false === $updated ) {
        return $wpdb->last_query;
    } else {
        return $wpdb->last_query;
    }
}

/**
 * Get all notification from database.
 */
function wisataone_X1_get_single_cron_tick() {
    global $wpdb, $wisataone_X1_cron_tick_tblname;
    $wp_track_table = $wpdb->prefix . $wisataone_X1_cron_tick_tblname;

    $res = $wpdb->get_results( 
        "
        SELECT *
        FROM {$wp_track_table}
        LIMIT 1
        "
    );

    if ($res) {
        return $res[0]->ts;
    }

    return "";
}

/**
 * Insert new order to database.
 */
function wisataone_X1_insert_cron_tick() {
    global $wpdb, $wisataone_X1_cron_tick_tblname;
    $wp_track_table = $wpdb->prefix . $wisataone_X1_cron_tick_tblname;

    $res = $wpdb->insert(
        $wp_track_table, 
        array(
            'ts' => (new DateTime())->format('Y-m-d H:i:s')
        )
    );

    return wisataone_X1_res(true, $res);
}

/**
 * Initialize Plugin
 * Create database for payment notification.
 */
function wisataone_X1_create_plugin_database_table_cron_tick() {
    global $wpdb, $wisataone_X1_plugin_db_cron_tick_version, $wisataone_X1_cron_tick_tblname;

    $wp_track_table = $wpdb->prefix . $wisataone_X1_cron_tick_tblname;
    $charset_collate = $wpdb->get_charset_collate();

    #Check to see if the table exists already, if not, then create it

    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) {
        $sql = "CREATE TABLE {$wp_track_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ts DATETIME DEFAULT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
        add_option('wisataone_X1_plugin_db_cron_tick_version', $wisataone_X1_plugin_db_cron_tick_version);
    }
}

function wisataone_X1_drop_plugin_database_table_cron_tick() {
    global $wpdb, $wisataone_X1_plugin_db_cron_tick_version, $wisataone_X1_cron_tick_tblname;

    $wp_track_table = $wpdb->prefix . $wisataone_X1_cron_tick_tblname;
    $sql = "DROP TABLE IF EXISTS {$wp_track_table};";
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $wpdb->query($sql);
    delete_option("wisataone_X1_plugin_db_cron_tick_version");
}

function wisataone_X1_init_db_cron_tick() {
    wisataone_X1_create_plugin_database_table_cron_tick();
    wisataone_X1_insert_cron_tick();
}

function wisataone_X1_drop_db_cron_tick() {
    wisataone_X1_drop_plugin_database_table_cron_tick();
}
