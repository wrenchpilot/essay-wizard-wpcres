<?php
/*
 * wpcres-shortcode-dropdown.php
 *
 * This code hooks in to the WordPress editor to create an user friendly
 * method for inserting the wpCRES shortcode.
 *
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @package wpCRES
 */

/**
 * Initialize function
 */
function wpcres_dropdown() {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }

    if (get_user_option('rich_editing') == 'true') {
        add_action('admin_head', 'shortcodes_in_js');
        add_filter('mce_external_plugins', 'add_plugin');
        add_filter('mce_buttons', 'register_button');
    }
}

/**
  Register Button
 */
function register_button($buttons) {
    array_push($buttons, "|", "wpcres");
    return $buttons;
}

/**
  Register TinyMCE Plugin
 */
function add_plugin($plugin_array) {
    $plugin_array[ 'wpcres' ] = plugins_url('/script/wpcres-dropdown.js', __FILE__);
    return $plugin_array;
}

/**

 * Get the current post type in the WordPress Admin

 */
function get_current_post_type() {
    global $post, $typenow, $current_screen;

    //we have a post so we can just get the post type from that
    if ($post && $post->post_type) {
        return $post->post_type;
    }

    //check the global $typenow - set in admin.php
    elseif ($typenow) {
        return $typenow;
    }

    //check the global $current_screen object - set in sceen.php
    elseif ($current_screen && $current_screen->post_type) {
        return $current_screen->post_type;
    }

    //check the post_type querystring
    elseif (isset($_REQUEST[ 'post_type' ])) {
        return sanitize_key($_REQUEST[ 'post_type' ]);
    }

    //Go nuclear
    elseif (isset($_REQUEST[ 'post' ])) {
        return get_post_type($_REQUEST[ 'post' ]);
    }

    //screw it, we do not know the post type!
    return null;
}

// Dynamically generate the JS to pass to the tinyMCE dropdown
function shortcodes_in_js() {
    // get custom post types for dropdown
    $post = get_posts(array(
        'post_type' => 'wpcres_assignment',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'post_date',
        'order' => 'ASC'
    )); ?>
<script type="text/javascript">
	var my_wpcres_posts = new Array();
	var counter = 0;
	<?php foreach ($post as $p) { ?>
	my_wpcres_posts[counter] = ['<?php echo $p->post_title ?>',
		'<?php echo $p->ID ?>'
	];
	counter++;
	<?php }; ?>
</script>

<?php
} ?>

<?php
    // create dropdown if post_type = page
    if (get_current_post_type() == 'page') {
        wpcres_dropdown();
    }
