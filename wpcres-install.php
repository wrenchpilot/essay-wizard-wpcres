<?php

/*
 * wpcres-install.php
 * 
 * This code creates the custom response table for wpCRES when the plugin is
 * activated.  It also stores the database version and table name in the
 * WordPress wp_options table.
 * 
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @package wpCRES
 */

// Get access to some WordPress variables
global $wpdb;
global $wpcres_db_version;

// Include WordPress ugrade functions
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

// Set Database version and table name
$wpcres_db_version = "1.0";
$table_name = $wpdb->prefix . "wpcres_responses";
$scaffold_table = $wpdb->prefix . "wpcres_scaffold";
$response_view = $wpdb->prefix . "wpcres_response_view";
$response_version_table = $wpdb->prefix . "wpcres_response_versions";

// Prepare the SQL to create the table
$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
            `responseID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Response ID',
            `userID` bigint(20) unsigned NOT NULL COMMENT 'Wordpress User ID',
            `wpcresID` bigint(20) unsigned NOT NULL COMMENT 'wpCRES Assignment ID',
            `essay` longtext NOT NULL COMMENT 'User Essay Response',
            `status` varchar(25)  NOT NULL COMMENT 'Status Flag',
            `datetime` datetime NOT NULL COMMENT 'Date and Time of Submission',
            `userIP` varchar(15) NOT NULL COMMENT 'User IP Address',
         PRIMARY KEY (`responseID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Student Responses to a wpCRES Assignment' AUTO_INCREMENT=1;";
// Execute the SQL
dbDelta($sql);

// Prepare the SQL to create the table
$sql = "CREATE TABLE IF NOT EXISTS `$scaffold_table` (
        `scaffoldID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Scaffold ID',
        `responseID` bigint(20) unsigned NOT NULL COMMENT 'Response ID',
        `question` longtext NOT NULL COMMENT 'Question Text',
        `response` enum('0','1') NOT NULL COMMENT 'Question Response',
        PRIMARY KEY (`scaffoldID`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

// Execute the SQL
dbDelta($sql);

// Create response version table
$sql = "CREATE TABLE IF NOT EXISTS `$response_version_table` (
        `versionID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `responseID` bigint(20) unsigned NOT NULL,
        `userID` bigint(20) unsigned NOT NULL COMMENT 'Wordpress User ID',
        `wpcresID` bigint(20) unsigned NOT NULL COMMENT 'wpCRES Assignment ID',
        `essay` longtext NOT NULL COMMENT 'User Essay Response',
        `datetime` datetime NOT NULL COMMENT 'Date and Time of Submission',
        `userIP` varchar(15) NOT NULL COMMENT 'User IP Address',
        PRIMARY KEY (`versionID`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Student Responses to a wpCRES Assignment' AUTO_INCREMENT=1";

// Execute the SQL
dbDelta($sql);

// Create response view 
$sql = "CREATE OR REPLACE VIEW $response_view (
        SELECT a.*, b.display_name, b.user_login, c.post_title
          FROM $table_name as a, wp_users as b, wp_posts as c
         WHERE a.userID = b.ID
           AND a.wpcresID = c.ID)";

// Execute the SQL
dbDelta($sql);

//Prevent settings reset on plugin upgrade or reinstallation
if (get_option("wpcres_db_version") == FALSE) { // Returns false if not set, if so, set options below

// Add the application options to the wp_options table
    add_option("wpcres_db_version", $wpcres_db_version);
    add_option("wpcres_table_name", $table_name);
    add_option("wpcres_scaffold_table", $scaffold_table);
    add_option("wpcres_response_view", $response_view);
    add_option("wpcres_response_versions", $response_version_table);

// Some default settings that can be edited on the settings page.
    add_option("wpcres_admin_name", get_option('blogname') . " Administrator");  //WordPress admin
    add_option("wpcres_admin_email", get_option('admin_email'));  //defaults to wordpress admin email.
    add_option("wpcres_approv_email_enable", TRUE); // Enable approve email by default
    add_option("wpcres_reject_email_enable", TRUE); // Enable reject email by default
    add_option("wpcres_approv_email_subject", "[" . get_bloginfo('title') . "] Essay Approved");
    add_option("wpcres_approv_email_body", "Your essay response has been approved.");
    add_option("wpcres_reject_email_subject", "[" . get_bloginfo('title') . "] Essay Rejected");
    add_option("wpcres_reject_email_body", "Your essay response has been rejected.");
    add_option('wpcres_user_email_enable', TRUE);
    add_option('wpcres_user_email_subject', "[" . get_bloginfo('title') . "] Essay Submitted");
    add_option('wpcres_user_email_body', "Your essay response has been submitted.");
    add_option("wpcres_atd_dir", WPINC . "/js/tinymce/plugins/AtD");  // Set AtD filesystem path
    add_option("wpcres_atd_url", includes_url() . "js/tinymce/plugins/AtD");  //Set AtD URL (needed in case of custom permalink structure
    add_option("wpcres_responses_per_page", "200");
    add_option("wpcres_cleanup_options_on_uninstall", 0);  // Don't delete options by default upon plugin deactivation
    add_option("wpcres_cleanup_database_on_uninstall", 0); // Don't delete database tables by default upon plugin deactivation
}
?>
