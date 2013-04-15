<?php
/*
 * wpcres-post-type.php
 * 
 * This code defines the custom post type for wpCRES assignments.
 * 
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @package wpCRES
 */

register_post_type('wpcres_assignment', array(
    'labels' => array(
        'name' => 'CRES Assignments',
        'singular_name' => 'CRES Assignment',
        'add_new' => 'Add New Assignment',
        'add_new_item' => 'Add New CRES Assignment',
        'edit_item' => 'Edit CRES Assignment',
        'new_item' => 'New CRES Assignment',
        'view_item' => 'View CRES Assignment',
        'search_items' => 'Search Assignments',
        'not_found' => 'No Assignments Found',
        'not_found_in_trash' => 'No Assignments Found In Trash'),
    'query_var' => 'wpcres_assignment',
    'slug' => 'wpcres_assignment',
    'public' => true,
    'menu_position' => 25,
    'supports' => array('title', 'editor', 'thumbnail', 'author', 'revisions', 'page-attributes'),
    'taxonomies' => array(''),
    'menu_icon' => plugins_url('images/book-16x16.png', WPCRES_MAIN_PLUGIN_FILE),
    'has_archive' => true));
?>
