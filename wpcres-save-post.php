<?php
/*
 * wpcres-save-post.php
 *
 * This code saves the meta data from a wpCRES assignment in the wp_postmeta
 * table.
 *
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @param integer $id  The post id.
 * @package wpCRES
 */

// Create action to save meta data into the wpdb
add_action('save_post', function ($id) {
    // verify if this is an auto save routine.
    // If it is our form has not been submitted, so we dont want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (!isset($_POST['dynamic_meta_nonce'])) {
        return;
    }
            
    if (!wp_verify_nonce($_POST['dynamic_meta_nonce'], plugin_basename(WPCRES_MAIN_PLUGIN_FILE))) {
        return;
    }
                       
    $questions = $_POST['questions'];
    $final_followup = $_POST['final_followup'];
    update_post_meta($id, 'wpcres_question', $questions);
    update_post_meta($id, 'wpcres_final_followup', $final_followup);
});
