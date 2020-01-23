<?php

/*
 * uninstall.php
 *
 * Clean up the WordPress database on uninstall
 *
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @package wpCRES
 */

// Check that code was called from WordPress with uninstallation
// constant declared

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

if (get_option('wpcres_cleanup_database_on_uninstall') == true) {
    $table_names = array(
        get_option("wpcres_table_name"),
        get_option("wpcres_scaffold_table"),
        get_option("wpcres_response_view"),
        get_option("wpcres_response_versions")
    );

    // Drop the tables;
    array_walk($table_names, 'drop_tables');
}

// Check if options exist and delete them if present
if (get_option('wpcres_cleanup_options_on_uninstall') == true) {
    // Database Table Names
    delete_option('wpcres_db_version');
    delete_option('wpcres_table_name');
    delete_option('wpcres_scaffold_table');
    delete_option('wpcres_response_view');
    delete_option('wpcres_response_versions');

    // Settings
    delete_option('wpcres_admin_name');
    delete_option('wpcres_admin_email');
    delete_option('wpcres_approv_email_enable');
    delete_option('wpcres_reject_email_enable');
    delete_option('wpcres_approv_email_subject');
    delete_option('wpcres_approv_email_body');
    delete_option('wpcres_reject_email_subject');
    delete_option('wpcres_reject_email_body');
    delete_option('wpcres_responses_per_page');
    delete_option('wpcres_user_email_enable');
    delete_option('wpcres_user_email_subject');
    delete_option('wpcres_user_email_body');
    delete_option('wpcres_atd_dir');
    delete_option('wpcres_atd_url');
    delete_option('wpcres_cleanup_options_on_uninstall');
    delete_option('wpcres_cleanup_database_on_uninstall');
}

function drop_tables($table) {
    global $wpdb;
    $sql = "DROP TABLE IF EXISTS " . $table;
    $wpdb->query($sql);
}
