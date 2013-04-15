<?php

/*
  Plugin Name: wpCRES - WordPress Critical Review Exam System
  Plugin URI: http://essaywizard.gatech.edu/welcome/about/
  Description: A wordpress implementation of Alan Tyree's Critical Review Exam System
  Author: Shawn Carnley <Shawn.Carnley@gatech.edu>
  Version: 1.0
  Author URI: http://www.assessment.gatech.edu/
 */

/* wpCRES
 * 
 * Copyright 2012 James Shawn Carnley <shawn.carnley@gatech.edu>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Pre-2.6 compatibility
if (!defined('WP_CONTENT_URL'))
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');

// Wordpress version control. No compatibility with older versions. ( wp_die )
if (version_compare(get_bloginfo('version'), '3.4', '<')) {
    wp_die('wpCRES can be used only with Wordpress 3.4+ version');
}

// PHP version control. No compatibility with older versions. ( wp_die )
if (version_compare(PHP_VERSION, '5.3', '<')) {
    wp_die('wpCRES can be used only with PHP 5.3+ version');
}

define('WPCRES_DIR', WP_PLUGIN_DIR . '/wpCRES');
define('WPCRES_URL', WP_PLUGIN_URL . '/wpCRES');
define('WPCRES_MAIN_PLUGIN_FILE', __FILE__);

// It took me two days to find out I needed to declare this outside the
// main plugin class.  
register_activation_hook(__FILE__, array('WPCRES', 'wpcres_install'));

// Deactivate
register_uninstall_hook(__FILE__, array('WPCRES', 'wpcres_uninstall'));

if (!class_exists("WPCRES")) :

    class WPCRES {

        public function __construct() {
            // nothing to do here.
        }

        static function wpcres_install() {
            include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-install.php';
        }
        
        static function wpcres_uninstall(){
            include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/uninstall.php';
        }

    }

// end class
endif;

global $wpcres_assignment;
if (class_exists("WPCRES") && !$wpcres_assignment) {

    add_action('init', function() {

                // Instantiate the main class        
                $wpcres_assignment = new WPCRES();

                // define the shortcode
                include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-shortcode.php';

                // register the post type
                include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-post-type.php';

                // add action to save posts
                include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-save-post.php';

                // create meta box
                include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-metabox.php';

                // shortcode dropdown in editor
                include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-shortcode-dropdown.php';

                // view results in admin
                include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-view-results.php';
                
                // settings page
                include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-settings.php';
                
                // import question pack
                include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-import-export.php';

                // help page
                include_once dirname(WPCRES_MAIN_PLUGIN_FILE) . '/wpcres-help.php';
            });
}
?>