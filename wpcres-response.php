<?php
/*
 * wpcres-response-popup.php
 * 
 * This file implements the user response survey and stores results in a custom
 * WordPress table that was created upon plugin activation.
 * 
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @package wpCRES
 */



if (!is_user_logged_in()) {
    return '<p>You must be <a href="' . wp_login_url(get_permalink()) . '">logged in</a> to access the ' . get_bloginfo('title') . '</p>';
}

$current_user->user_ip = $_SERVER['REMOTE_ADDR'];
$current_user->date_time = date('Y-m-d H:i:s');
$table_name = get_option('wpcres_table_name', $wpdb->prefix . "wpcres_responses");
$scaffold_table = get_option('wpcres_scaffold_table', $wpdb->prefix . "wpcres_scaffold");
$response_version_table = get_option('wpcres_response_versions', $wpdb->prefix . "wpcres_response_versions");
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

extract($_POST); //I"m lazy.  I know this.

$meta = get_meta_data($wpcresID);
$meta_count = count($meta);

$wpcres_response = str_replace("'", "&#39;", stripslashes($wpcres_response));

// Create a data structure to store passed in and entered values.
// Initial page load.
if (isset($_POST['Begin']) && isset($_POST['wpcres_nonce']) &&
        wp_verify_nonce($_POST['wpcres_nonce'], 'wpcres_nonce')) {

    $curr_question = 0;
    $next_question = $curr_question + 1;

    $data = array(
        'responseID' => '', // auto-generated id
        'userID' => $current_user->ID, // user's wordpress id
        'wpcresID' => $wpcresID, // unique id for the wpcres assignment
        'essay' => $wpcres_response, // user essay response
        'status' => 'In Progress', // current status
        'datetime' => $current_user->date_time, // current date/time
        'userIP' => $current_user->user_ip); // user's IP address

    $wpdb->insert($table_name, $data);
    $insertID = $wpdb->insert_id;

    //Save a version
    $version_data = array(
        'versionID' => '', // auto-generated id
        'responseID' => $insertID, // use previous insert id
        'userID' => $current_user->ID, // user's wordpress id
        'wpcresID' => $wpcresID, // unique id for the wpcres assignment
        'essay' => $wpcres_response, // user essay response
        'datetime' => $current_user->date_time, // current date/time
        'userIP' => $current_user->user_ip); // user's IP address
    $wpdb->insert($response_version_table, $version_data);

// Process the scaffolding questions
} elseif (isset($_POST['scaffold_radio']) && isset($_POST['wpcres_nonce']) &&
        wp_verify_nonce($_POST['wpcres_nonce'], 'wpcres_nonce')) {

    if ($scaffold_radio == $meta[$curr_question]['scaffold_answer']) {

        //Save the response
        $data = array(
            'responseID' => $insertID, // responseID from the response table
            'question' => $meta[$curr_question]['scaffold_question'],
            'response' => $scaffold_radio); // list of user responses to scaffold questions

        $wpdb->insert($scaffold_table, $data);

        $curr_question = $next_question;
        $next_question = $curr_question + 1;
    } else {
        $error = $meta[$curr_question]['scaffold_followup'] . "<br />" . $meta[$curr_question]['scaffold_question'];

        // Save the invalid response
        $data = array(
            'responseID' => $insertID, // responseID from the response table
            'question' => $meta[$curr_question]['scaffold_question'],
            'response' => $scaffold_radio); // list of user responses to scaffold questions

        $wpdb->insert($scaffold_table, $data);
    }

// Process the essay response
} elseif (isset($_POST['wpcres_response']) && isset($_POST['insertID']) &&
        isset($_POST['wpcres_nonce']) &&
        wp_verify_nonce($_POST['wpcres_nonce'], 'wpcres_nonce')) {

    $curr_question = $next_question; //extracted from $_POST
    $next_question = $curr_question + 1;

    //Update to dababase
    $data = array('essay' => $wpcres_response);
    $where = array('responseID' => $insertID);  //Use the last insertID to update record

    $wpdb->update($table_name, $data, $where);

    //Save a version
    $version_data = array(
        'versionID' => '', // auto-generated id
        'responseID' => $insertID, // use previous insert id
        'userID' => $current_user->ID, // user's wordpress id
        'wpcresID' => $wpcresID, // unique id for the wpcres assignment
        'essay' => $wpcres_response, // user essay response
        'datetime' => $current_user->date_time, // current date/time
        'userIP' => $current_user->user_ip); // user's IP address
    $wpdb->insert($response_version_table, $version_data);

    // Final update
    if (isset($_POST['final']) && $final == 1)
        $wpdb->update($table_name, array('status' => 'Submitted'), $where);
}
?>
<?php
// Display the original question
echo "<h3>Hypothetical</h3>";
extract(shortcode_atts(array('id' => ''), $atts));
$page_object = get_page($id);
echo "<p>" . $page_object->post_content . "</p>";
?>

<?php if (!isset($_POST['final'])) { ?> 
    <form name="scaffold_question_form" method="post" enctype="multipart/form-data" action="" id="scaffold_question_form">
        <p>
            <?php if ($curr_question <= $meta_count - 1) { ?>
                <?php
                $submit_val = "Next Question";
                $disabled = "disabled";
                ?>
            <h3>Please respond to the following question(s) about your essay.</h3>
            <p>
                <strong><?php echo (!isset($error)) ? $meta[$curr_question]['scaffold_question'] : $error; ?></strong><br />
                No  <input type="radio"  name="scaffold_radio" value="0" onclick="scaffold_submit.disabled=false;" />
                Yes <input type="radio"  name="scaffold_radio" value="1" onclick="scaffold_submit.disabled=false;" />
            </p>
            <input type="hidden" name="next_question" value="<?php echo $next_question; ?>" />
            <input type='hidden' name='wpcres_response' value='<?php echo $wpcres_response; ?>' />
        <?php } else { ?>
            <h3>Final Followup</h3>
            <p><strong><?php echo get_post_meta($wpcresID, 'wpcres_final_followup', true); ?></strong></p><br>
            <p>Please make your final edits to your answer below before submitting your final response.</p>
            <?php $submit_val = "Sumbit Final Response"; ?>
            <?php wp_editor($wpcres_response, 'wpcres_response', $settings); ?>
            <input type="hidden" name="final" value="1" />
            <?php $disabled = ""; ?>
        <?php } ?>
        <input type="hidden" name="insertID" value="<?php echo $insertID; ?>" />
        <input type="hidden" name="curr_question" value="<?php echo $curr_question; ?>" />
        <input type='hidden' name='page_id' value='<?php echo $page_id; ?>' />
        <input type='hidden' name='wpcresID' value='<?php echo $wpcresID; ?>' />
        <?php wp_nonce_field('wpcres_nonce', 'wpcres_nonce'); ?><br /><br />
        <input type="submit" name="scaffold_submit" id="scaffold_submit" value="<?php echo $submit_val; ?>" <?php echo $disabled; ?> />
    </form>

    <?php if ($curr_question <= $meta_count - 1) { ?>
        <hr />
        <h3>Your Answer</h3>
        <p>To edit your answer, please revise the text below and click "Save Essay Response".<br><strong>You must click "Save Essay Response" to save any changes to your essay.</strong></p>
        <form name="scaffold_edit_response_form" method="post" enctype="multipart/form-data" action="" id="scaffold_edit_response_form">
            <input type="submit" name="essay_submit" value="Save Essay Response" />
            <?php wp_editor($wpcres_response, 'wpcres_response', $settings); ?>
            <?php wp_nonce_field('wpcres_nonce', 'wpcres_nonce'); ?>
            <input type="hidden" name="insertID" value="<?php echo $insertID; ?>" />
            <input type="hidden" name="next_question" value="<?php echo $curr_question; ?>" /> 
            <input type='hidden' name='page_id' value='<?php echo $page_id; ?>' />
            <input type='hidden' name='wpcresID' value='<?php echo $wpcresID; ?>' /> <br>
            <input type="submit" name="essay_submit" id="essay_submit" value="Save Essay Response" />
        </form>
    <?php } ?>

    <?php
    $responseSQL = "SELECT `scaffoldID`, `responseID`, `question`, `response` FROM `$scaffold_table` WHERE `responseID` = '$insertID'";
    $responses = $wpdb->get_results($responseSQL);

    if ($responses != NULL) {
        echo "<hr>\r\n";
        echo "<h3>Your responses</h3>\r\n";
        echo "<ol >\r\n";
        foreach ($responses as $response) {
            $r = ($response->response == '0') ? "No" : "Yes";
            echo "<li style='list-style-image: none !important; list-style-type: decimal !important; margin-left: 2em;'><strong>" . $response->question . "</strong>: " . $r . "</li>\r\n";
        }
        echo "</ol>\r\n";
    }
    ?>

<?php } else { ?>
    <h3>Your Submitted Response</h3>
    <p><?php echo $wpcres_response; ?></p>
    <?php
    $send_email = get_option("wpcres_user_email_enable");

    if ($send_email) {
        // Get User Data
        global $current_user;
        get_currentuserinfo();

        $admin_name = get_option('wpcres_admin_name');
        $from = get_option("wpcres_admin_email");
        $subject = get_option("wpcres_user_email_subject");
        $body = get_option("wpcres_user_email_body");
        $body = process_shortcodes($body, $insertID, $table_name);

        $headers = "From: $admin_name <$from>\r\n";
        $headers .= "Return-Path: $from\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // Send email
        wp_mail($current_user->user_email, $subject, $body, $headers);
    }
    ?>
<?php } ?>

<?php
/*
 * Get the scaffolding questions meta data and unserialize it.
 * @param integer $id The post ID
 * @return array
 */

function get_meta_data($id) {
    $meta = get_post_meta($id);
    $meta = maybe_unserialize($meta['wpcres_question'][0]);
    return $meta;
}
?>