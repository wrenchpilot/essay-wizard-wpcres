<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

add_action('admin_menu', function() {
            add_submenu_page('edit.php?post_type=wpcres_assignment', 'Import/Export', 'Import/Export', 'manage_options', 'wpcres-import', 'render_import_export_page');
        });

function render_import_export_page() {
    ?>
    <div class="wrap">
        <div id="icon-tools" class="icon32"><br/></div>
        <h2>Import/Export</h2><br />
        <div class="postbox">
            <h3>Export</h3>
            <div class="inside">
                <ol>
                    <li>Use WordPress's built in <strong><a href="export.php">Export Tool</a></strong>.</li>
                    <li>Select "<strong>CRES Assignments</strong>" from the list of content types.</li>
                    <li>Click the "Download Export File" button</li>
                </ol>
            </div>
        </div>

        <div class="postbox">
            <h3>Import</h3>
            <div class="inside">
                <ol>
                    <li>Use WordPress's built in <strong><a href="admin.php?import=wordpress">Import Tool</a></strong>.</li>
                    <li>Note: This requires the <a href="http://wordpress.org/extend/plugins/wordpress-importer/" target="_blank">WordPress Importer</a> plugin to be installed and activated prior to import.</li>
                    <li>Select the file you wish to import.  This file should be in WordPress eXtended RSS (WXR) format </li>
                    <li>Click the "Upload file and import" button</li>
                </ol>
            </div>
        </div>
    </div>

<?php } ?>
