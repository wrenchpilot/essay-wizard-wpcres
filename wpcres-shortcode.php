<?php

/*
 * wpcres-shortcode.php
 *
 * This code implements a WordPress shortcode to display a wpCRES assignment.
 *
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @param integer $atts Custom post ID
 * @return string $output
 * @package wpCRES
 */

add_shortcode('wpcres', function ($atts) {
    // make sure the user is logged in
    if (!is_user_logged_in()) {
        return '<p>You must be <a href="' . wp_login_url(get_permalink()) . '">logged in</a> to access the ' . get_bloginfo('title') . '</p>';
    }

    //Get access to some WordPress variables
    global $post;
    global $wpdb;
    global $current_user;
    get_currentuserinfo();


    // Include the user response script
    if (isset($_POST['Begin']) || isset($_POST['Continue']) || isset($_POST['scaffold_submit'])
              || isset($_POST['scaffold_submit']) || isset($_POST['essay_submit'])) {
        include('wpcres-response.php');
        return;
    }
            
    $atd_dir = get_option('wpcres_atd_dir', WPINC . "/js/tinymce/plugins/AtD");
    $atd_url = get_option('wpcres_atd_url', includes_url() . "js/tinymce/plugins/AtD");

    // Check to see if the AtD tinyMCE plugin is installed
    $is_atd_installed = is_dir($atd_dir);

    // TinyMCE editor options
    $settings = array(
                'wpautop' => 0,
                'media_buttons' => 0,
                'quicktags' => 1,
                'teeny' => 1,
                'apply_source_formatting' => 1,
                'textarea_rows' => '15',
                'textarea_name' => 'wpcres_response'
            );

    if ($is_atd_installed && isset($atd_url)) {
        $atd_settings = array('tinymce' => array(
                        'plugins' => 'AtD,paste',
                        'atd_button_url' => $atd_url . '/atdbuttontr.gif',
                        'atd_rpc_url' => $atd_url . '/server/proxy.php?url=',
                        'atd_rpc_id' => 'WPORG-' . md5(get_bloginfo('wpurl')),
                        'atd_css_url' => $atd_url . '/css/content.css',
                        'atd_show_types' => 'Bias Language,Cliches,Complex Expression,Diacritical Marks,Double Negatives,Hidden Verbs,Jargon Language,Passive voice,Phrases to Avoid,Redundant Expression',
                        'atd_ignore_strings' => 'AtD,rsmudge',
                        'atd_ignore_enable' => 1,
                        'atd_strip_on_get' => 1,
                        'theme_advanced_buttons1_add' => 'AtD',
                        'gecko_spellcheck' => 0,
                        'cleanup' => 1,
                        'cleanup_on_startup' => 1,
                        'verify_html' => 1,
                        'paste_auto_cleanup_on_paste' => 1,
                        'paste_convert_headers_to_strong' => 1
                        ));
        $settings = array_merge($settings, $atd_settings);
    }

    // Get the user response table name from WordPress meta_options
    $table_name = get_option('wpcres_table_name', $wpdb->prefix . "wpcres_responses");

    // Initialize the output variable
    $output = "";

    // Get the post/page id, not the custom post type id.  There's a difference!
    $thePostID = $post->ID;

    // Setup the post type id
            $id = ''; // initialize the id
            // Extract the id from the shortcode attributes, store in $id
            extract(shortcode_atts(array('id' => ''), $atts));

    // Define our wp query parameters.
    $query_params = array('post_type' => 'wpcres_assignment',
                'post_status' => 'publish',
                'p' => $id);

    // Check to see if the user has already submitted
    // an essay for the current assignment
    $query = "SELECT * FROM $table_name WHERE `userID`='$current_user->ID' AND `wpcresID`='$id'";
    $user_responses = $wpdb->get_row($query, ARRAY_A);
    $user_response_count = count($user_responses);

    // Execute the query, store in $loop
    $loop = new WP_Query($query_params);

    // If we got a result, begin displaying the form.
    if ($loop->have_posts()) {
        $loop->the_post();

        // Get the content and filter it for creamy goodness.
        $content = str_replace(']]>', ']]&gt;', apply_filters('the_content', get_the_content()));

        $output .= "<h3>Hypothetical</h3>";
        $output .= $content;

        // Grab the post meta data (the scaffolding questions)
        // and unscramble err unserialize it.
        $meta = get_post_meta(get_the_id());
        $meta = maybe_unserialize($meta['wpcres_question'][0]);
        $meta_count = count($meta);

        // Create nonce to be used in the form
        $nonce = wp_create_nonce('wpcres_nonce');

        // New response logic
        if ($user_response_count == 0) {
            $output .= "<form id='wpcres_response_form' name='wpcres_response_form' method='post' enctype='multipart/form-data' action=''> \r\n";

            // Buffer the wp_editor and append to $output
            ob_start();
            wp_editor('', 'wpcres_response', $settings);
            $output .= ob_get_clean();

            $output .= "<input type='hidden' name='curr_question' value='0' />\r\n";
            $output .= "<input type='hidden' name='page_id' value='$thePostID' />\r\n";
            $output .= "<input type='hidden' name='wpcresID' value='$id' />\r\n";
            $output .= "<input type='hidden' name='wpcres_nonce' value='$nonce' />\r\n";
            $output .= "<input type='submit' name='Begin' id='Begin' value='Save Response & Begin' />\r\n";
            $output .= "</form>\r\n";

        // Continued response logic
        } elseif ($user_response_count > 0 && $user_responses['status'] != "Submitted" && $user_responses['status'] != "Approved") {
            $output .= "<form id='wpcres_response_form' name='wpcres_response_form' method='post' enctype='multipart/form-data' action=''> \r\n";
            $output .= "<h3>Essay Response</h3>";
            $output .= "<p>Continue editing your response below.  <br>Current Status: <strong>" . $user_responses['status'] . "</strong></p>";

            // Buffer the wp_editor and append to $output
            //ob_start();
            //wp_editor($user_responses['essay'], 'wpcres_response', $settings);
            //$output .= ob_get_clean();

            $output .= "<blockquote>" . $user_responses['essay'] . "</blockquote>";

            $output .= "<input type='hidden' name='curr_question' value='0' />\r\n";
            $output .= "<input type='hidden' name='next_question' value='0' />\r\n";
            $output .= "<input type='hidden' name='page_id' value='$thePostID' />\r\n";
            $output .= "<input type='hidden' name='wpcresID' value='$id' />\r\n";
            $output .= "<input type='hidden' name='wpcres_nonce' value='$nonce' />\r\n";
            $output .= "<input type='hidden' name='wpcres_response' value='" . $user_responses['essay'] . "' />\r\n";
            $output .= "<input type='hidden' name='insertID' value='" . $user_responses['responseID'] . "' /><br>\r\n";
            $output .= "<input type='submit' name='Continue' id='Continue' value='Continue Essay' />\r\n";
            $output .= "</form>\r\n";

        // Response submitted logic
        } else {
            $output .= "<hr /><h3>You have already submitted a response to this essay.  Your response is shown below.</h3>";
            $output .= "<p>Current Status: <strong>" . $user_responses['status'] . "</strong></p>";
            $output .= "<blockquote>" . $user_responses['essay'] . "</blockquote>";
        }
    } else {
        // Display error if no posts found
        $output = "No CRES assignment found with ID: " . $id;
    }

    // Be green
    wp_reset_query();

    return $output;
});
