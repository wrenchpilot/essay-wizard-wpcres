<?php
/*
 * wpcres-help.php
 *
 * This file will display help and instructions for the administration of wpCRES.
 *
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @package wpCRES
 */

add_action('admin_menu', function() {
            add_submenu_page('edit.php?post_type=wpcres_assignment', 'Help', 'Help', 'manage_options', 'wpcres-help', 'render_help_page');
        });

function render_help_page() {
    ?>

<div class="wrap">
  <div id="icon-options-general" class="icon32"></div>
  <h2>wpCRES Help</h2>
  <br />
  <div id="custom-branding" class="metabox-holder">
    <div class="postbox-container" style="width: 50%;">
      <div id="normal-sortables" class="meta-box-sortables">
      
        <div id="newassdiv" class="postbox">
          <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Creating a new assignment</span></h3>
          <div class="inside">
            <ol>
              <li>Click the <strong><a href="post-new.php?post_type=wpcres_assignment" target="_blank">Add New Assignment</a></strong> link in the <strong>CRES Assignments</strong> menu.</li>
              <li>Add a title for your new essay assignment</li>
              <li>Next, use the editor to enter your essay assignment question.</li>
              <li>Next, click the "Add Question" button to add "Scaffolding Questions".  You can add as many of these questions as you like.</li>
              <li>Each scaffolding question requires the question, the answer to the question (Yes or No) and a follow-up prompt for the user when answered incorrectly.</li>
              <li>Finally, enter the <strong>Final Follow-up</strong>.  This will be displayed after all of the scaffolding questions have been answered by the user.</li>
            </ol>
          </div>
        </div>
        
        <div id="pubnewdiv" class="postbox">
          <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Publishing a new assignment</span></h3>
          <div class="inside">
            <ol>
              <li>Under the WordPress <strong><a href="edit.php?post_type=page" target="_blank">Pages</a></strong> menu, click the <strong><a href="post-new.php?post_type=page" target="_blank">Add New</a></strong> page link.</li>
              <li>In the main page editor, there will be a drop down box labeled <strong>--wpCRES--</strong>.</li>
              <li>Choose one of the assignments in the list.  This will automatically add a page title and shortcode for the essay assigment.</li>
              <li>Now you can click the <strong>Publish</strong> button on the right to publish your assignment.</li>
            </ol>
          </div>
        </div>
        
        <div id="responsesdiv" class="postbox">
          <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Viewing, approving, rejecting and deleting responses</span></h3>
          <div class="inside">
            <ol>
              <li>Click the <strong><a href="edit.php?post_type=wpcres_assignment&page=wpcres-view-responses" target="_blank">Responses</a></strong> link in the <strong>CRES Assignments</strong> menu.</li>
              <li>Move your mouse over a response.</li>
              <li>From here you will see a menu <strong>View | Approve | Reject | Delete</strong> </li>
              <li>Choosing the <strong>View</strong> action will display the user's response above the response table.</li>
              <li>Choosing <strong>Approve | Reject | Delete</strong> will perform the corresponding action. <strong>Approving</strong> or <strong>Rejecting</strong> will also send an email to the user notifying them of the status change.</li>
            </ol>
          </div>
        </div>
        
        <div id="bulkdiv" class="postbox">
          <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Bulk actions & exporting responses</span></h3>
          <div class="inside">
            <ol>
              <li>Click the <strong><a href="edit.php?post_type=wpcres_assignment&page=wpcres-view-responses" target="_blank">Responses</a></strong> link in the <strong>CRES Assignments</strong> menu.</li>
              <li>In the upper right, use the <strong>Assignment Filter</strong> and <strong>Status Filter</strong> to filter the responses you want to view.</li>
              <li>Select responses using either the "Check All" box at the top and bottom of the response table, or by checking individual responses.</li>
              <li>In the <strong>Bulk Actions</strong> drop down menu select the action you would like to perform on the selected responses.</li>
              <li>Selecting the <strong>Export</strong> option will create a zip file and display the last five exports above the response table for downloading.</li>
            </ol>
          </div>
        </div>
        
        <div id="settingsdiv" class="postbox">
          <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Settings</span></h3>
          <div class="inside">
            <ol>
              <li><strong>Admin Name</strong>: This will be the name used on any automated email sent from the wpCRES system (Approval and Rejection letters).</li>
              <li><strong>Admin E-mail</strong>: This will be the e-mail used on any automated email sent from the wpCRES system (Approval and Rejection letters).</li>
              <li><strong>Approval Letter</strong>:  Message sent to the user when an essay is approved.  Three shortcodes are available [name], [essay_title], [essay].</li>
              <li><strong>Rejection Letter</strong>: Message sent to the user when an essay is rejected.  Three shortcodes are available [name], [essay_title], [essay].</li>
              <li><strong>Submission Letter</strong>: Message sent to the user when the user submits an essay response.  Three shortcodes are available [name], [essay_title], [essay].</li>
              <li><strong>After the Deadline Editor Plugin Path</strong>: Filesystem path to the After the Deadline plugin.  Default: ../../../wp-includes/js/tinymce/plugins/AtD.</li>
              <li><strong>Responses Per Page</strong>: The number of responses to display per page on the <strong><a href="edit.php?post_type=wpcres_assignment&page=wpcres-view-responses" target="_blank">Responses</a></strong> page.</li>
            </ol>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</div>
<?php } ?>
