<?php
/*
 * wpcres-settings.php
 *
 * This file implements the applicating settings page.
 *
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @package wpCRES
 */

add_action('admin_menu', function() {
            add_submenu_page('edit.php?post_type=wpcres_assignment', 'Settings', 'Settings', 'manage_options', 'wpcres-settings', 'render_settings_page');
        });

// Save the settings        
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'update') {

    update_option('wpcres_admin_name', $_REQUEST['wpcres_admin_name']);
    update_option('wpcres_admin_email', $_REQUEST['wpcres_admin_email']);
    update_option('wpcres_approv_email_enable', $_REQUEST['wpcres_approv_email_enable']);
    update_option('wpcres_reject_email_enable', $_REQUEST['wpcres_reject_email_enable']);
    update_option('wpcres_approv_email_subject', $_REQUEST['wpcres_approv_email_subject']);
    update_option('wpcres_approv_email_body', $_REQUEST['wpcres_approv_email_body']);
    update_option('wpcres_reject_email_subject', $_REQUEST['wpcres_reject_email_subject']);
    update_option('wpcres_reject_email_body', $_REQUEST['wpcres_reject_email_body']);
    update_option('wpcres_atd_dir', $_REQUEST['wpcres_atd_dir']);
    update_option('wpcres_atd_url', $_REQUEST['wpcres_atd_url']);
    update_option('wpcres_responses_per_page', $_REQUEST['wpcres_responses_per_page']);
    update_option('wpcres_user_email_enable', $_REQUEST['wpcres_user_email_enable']);
    update_option('wpcres_user_email_subject', $_REQUEST['wpcres_user_email_subject']);
    update_option('wpcres_user_email_body', $_REQUEST['wpcres_user_email_body']);
    update_option('wpcres_cleanup_options_on_uninstall', $_REQUEST['wpcres_cleanup_options_on_uninstall']);
    update_option('wpcres_cleanup_database_on_uninstall', $_REQUEST['wpcres_cleanup_database_on_uninstall']);
}

function render_settings_page() {
    // Get the settings
    $table_name = get_option('wpcres_table_name', $wpdb->prefix . "wpcres_responses");
    $scaffold_table = get_option('wpcres_scaffold_table', $wpdb->prefix . "wpcres_scaffold");
    $admin_name = get_option("wpcres_admin_name", get_option('admin_name'));
    $admin_email = get_option("wpcres_admin_email", get_option('admin_email'));
    $approv_enable = get_option('wpcres_approv_email_enable', TRUE);
    $reject_enable = get_option('wpcres_reject_email_enable', TRUE);
    $approv_subject = get_option('wpcres_approv_email_subject', "[" . get_bloginfo('title') . "] Essay Approved");
    $reject_subject = get_option('wpcres_reject_email_subject', "[" . get_bloginfo('title') . "] Essay Rejected");
    $approv_body = get_option('wpcres_approv_email_body', "Your essay response has been approved.");
    $reject_body = get_option('wpcres_reject_email_body', "Your essay response has been rejected.");
    $atd_dir = get_option('wpcres_atd_dir', WPINC . "/js/tinymce/plugins/AtD");
    $atd_url = get_option('wpcres_atd_url', includes_url() . "js/tinymce/plugins/AtD");
    $wpcres_responses_per_page = get_option('wpcres_responses_per_page', '200');
    $wpcres_user_email_enable = get_option('wpcres_user_email_enable', TRUE);
    $wpcres_user_email_subject = get_option('wpcres_user_email_subject', "[" . get_bloginfo('title') . "] Essay Submitted");
    $wpcres_user_email_body = get_option('wpcres_user_email_body', 'Your essay has been submitted.');
    $wpcres_cleanup_options_on_uninstall = get_option('wpcres_cleanup_options_on_uninstall', FALSE);
    $wpcres_cleanup_database_on_uninstall = get_option('wpcres_cleanup_database_on_uninstall', FALSE);

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
    );

    if ($is_atd_installed && isset($atd_url)) {
        $atd_settings = array('tinymce' => array(
                'plugins' => 'AtD',
                'atd_button_url' => $atd_url . '/atdbuttontr.gif',
                'atd_rpc_url' => $atd_url . '/server/proxy.php?url=',
                'atd_rpc_id' => 'WPORG-' . md5(get_bloginfo('wpurl')),
                'atd_css_url' => $atd_url . '/css/content.css',
                'atd_show_types' => 'Bias Language,Cliches,Complex Expression,Diacritical Marks,Double Negatives,Hidden Verbs,Jargon Language,Passive voice,Phrases to Avoid,Redundant Expression',
                'atd_ignore_strings' => 'AtD,rsmudge',
                'atd_ignore_enable' => 1,
                'atd_strip_on_get' => 1,
                'theme_advanced_buttons1_add' => 'AtD',
                'gecko_spellcheck' => 0
                ));
        $settings = array_merge($settings, $atd_settings);
    }
    ?>

<div class="wrap">
  <div id="icon-options-general" class="icon32"></div>
  <h2>wpCRES Settings</h2>
  <br />
  <div id="custom-branding" class="metabox-holder">
    <form method="post" action="" id="wpcres_settings_form" name="wpcres_settings_form">
      <div class="postbox-container" style="width: 50%;">
        <div id="normal-sortables" class="meta-box-sortables">
        
          <div id="adminnamediv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Admin Name</span></h3>
            <div class="inside">
              <input type="text" size="55" name="wpcres_admin_name" value="<?php echo $admin_name; ?>" />
            </div>
          </div>
          
          <div id="adminemaildiv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Admin E-mail</span></h3>
            <div class="inside">
              <input type="text" size="55" name="wpcres_admin_email" value="<?php echo $admin_email; ?>" />
            </div>
          </div>
          
          <div id="aproveletterdiv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Approval Letter</span></h3>
            <div class="inside"> Enable
              <input type="radio" value="1" name="wpcres_approv_email_enable" <?php echo ($approv_enable == TRUE) ? "checked" : ""; ?> />
              Disable
              <input type="radio" value="0" name="wpcres_approv_email_enable" <?php echo ($approv_enable == FALSE) ? "checked" : ""; ?>/>
              <h4>Subject</h4>
              <input type="text" size="55" name="wpcres_approv_email_subject" value="<?php echo $approv_subject; ?>" />
              <div id="meta_inner">
                <?php
                        wp_editor($approv_body, 'wpcres_approv_email_body', array_merge($settings, array('textarea_name' => 'wpcres_approv_email_body')));
                        ?>
                <p><strong>Shortcodes:</strong> [name], [essay_title], [essay]</p>
              </div>
            </div>
          </div>
          
          <div id="rejectletterdiv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Rejection Letter</span></h3>
            <div class="inside"> 
              Enable
              <input type="radio" value="1" name="wpcres_reject_email_enable" <?php echo ($reject_enable == TRUE) ? "checked" : ""; ?> />
              Disable
              <input type="radio" value="0" name="wpcres_reject_email_enable" <?php echo ($reject_enable == FALSE) ? "checked" : ""; ?>/>
              <h4>Subject</h4>
              <input type="text" size="55" name="wpcres_reject_email_subject" value="<?php echo $reject_subject; ?>" />
              <div id="meta_inner">
                <?php
                        wp_editor($reject_body, 'wpcres_reject_email_body', array_merge($settings, array('textarea_name' => 'wpcres_reject_email_body')));
                        ?>
                <p><strong>Shortcodes:</strong> [name], [essay_title], [essay]</p>
              </div>
            </div>
          </div>
          
          <div id="usersubmitletterdiv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>User Submission Letter</span></h3>
            <div class="inside"> Enable
              <input type="radio" value="1" name="wpcres_user_email_enable" <?php echo ($wpcres_user_email_enable == TRUE) ? "checked" : ""; ?> />
              Disable
              <input type="radio" value="0" name="wpcres_user_email_enable" <?php echo ($wpcres_user_email_enable == FALSE) ? "checked" : ""; ?>/>
              <h4>Subject</h4>
              <input type="text" size="55" name="wpcres_user_email_subject" value="<?php echo $wpcres_user_email_subject; ?>" />
              <div id="meta_inner">
                <?php
                        wp_editor($wpcres_user_email_body, 'wpcres_user_email_body', array_merge($settings, array('textarea_name' => 'wpcres_user_email_body')));
                        ?>
                <p><strong>Shortcodes:</strong> [name], [essay_title], [essay]</p>
              </div>
            </div>
          </div>
          
          <div id="responseperpagediv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Respsones Per Page</span></h3>
            <div class="inside">
              <input type="text" size="55" name="wpcres_responses_per_page" value="<?php echo $wpcres_responses_per_page; ?>" />
            </div>
          </div>
          
          <div id="atdpluginpathdiv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><h3><span>After the Deadline Editor Plugin Path</span></h3>
            <div class="inside">
              <input type="text" size="55" name="wpcres_atd_dir" value="<?php echo $atd_dir; ?>" />
            </div>
          </div>
          
          <div id="atdpluginurldiv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>After the Deadline Editor Plugin URL</span></h3>
            <div class="inside">
              <input type="text" size="55" name="wpcres_atd_url" value="<?php echo $atd_url; ?>" />
            </div>
          </div>
          
          <div id="cleanoptionsdiv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Cleanup wpCRES Options On Uninstall</span></h3>
            <div class="inside"> Enable
              <input type="radio" value="1" name="wpcres_cleanup_options_on_uninstall" <?php echo ($wpcres_cleanup_options_on_uninstall == TRUE) ? "checked" : ""; ?> />
              Disable
              <input type="radio" value="0" name="wpcres_cleanup_options_on_uninstall" <?php echo ($wpcres_cleanup_options_on_uninstall == FALSE) ? "checked" : ""; ?>/>
            </div>
          </div>
          
          <div id="cleandatadiv" class="postbox">
            <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Cleanup wpCRES Data On Uninstall</span></h3>
            <div class="inside"> Enable
              <input type="radio" value="1" name="wpcres_cleanup_database_on_uninstall" <?php echo ($wpcres_cleanup_database_on_uninstall == TRUE) ? "checked" : ""; ?> />
              Disable
              <input type="radio" value="0" name="wpcres_cleanup_database_on_uninstall" <?php echo ($wpcres_cleanup_database_on_uninstall == FALSE) ? "checked" : ""; ?>/>
            </div>
          </div>
          
          <input type="hidden" name="page" value="wpcres-settings" />
          <input type="hidden" name="post_type" value="wpcres_assignment" />
          <?php settings_fields('wpcres_settings'); ?>
          <input type="submit" name="Update" id="Update" value="Save Settings" class="button button-primary" />
        </div>
      </div>
    </form>
  </div>
</div>
<?php } ?>
