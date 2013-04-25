<?php
/*
 * wpcres-view-results.php
 * 
 * This file implements the ability to review user responses by the 
 * administrator.  Based on code from "Custom List Table Example" by 
 * Matt Van Andel <http://www.mattvanandel.com>.  
 *
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @package wpCRES
 */

// Include the WP_List_Table class
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPCRES_Response_Table extends WP_List_Table {

    function __construct() {
        global $wpdb, $page;

        parent::__construct(array(
            'singular' => 'Response',
            'plural' => 'Responses',
            'ajax' => false
        ));
    }

    function column_default($item, $column_name) {

        switch ($column_name) {
            case 'responseID':
                return $item[$column_name];
            case 'userID':
                $userinfo = get_user_by('id', $item[$column_name]);
                return $userinfo->first_name . ' ' . $userinfo->last_name . ' (' . $userinfo->user_login . ')';
            case 'wpcresID':
                return get_the_title($item[$column_name]);
            case 'status':
                return $item[$column_name];
            case 'datetime':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    function column_responseID($item) {

        //Build row actions
        $actions = array(
            //'edit' => '<a href="?post_type='.$_REQUEST['post_type'].'&page=' . $_REQUEST['page'] . '&action=edit&responseID=' . $item['responseID'] . '">Edit</a>',        
            'view' => '<a href="?post_type=' . $_REQUEST['post_type'] . '&page=' . $_REQUEST['page'] . '&action=view&responseID=' . $item['responseID'] . '">View</a>',
            'approv' => '<a href="?post_type=' . $_REQUEST['post_type'] . '&page=' . $_REQUEST['page'] . '&action=approv&responseID=' . $item['responseID'] . '">Approve</a>',
            'reject' => '<a href="?post_type=' . $_REQUEST['post_type'] . '&page=' . $_REQUEST['page'] . '&action=reject&responseID=' . $item['responseID'] . '">Reject</a>',
            'delete' => '<a href="?post_type=' . $_REQUEST['post_type'] . '&page=' . $_REQUEST['page'] . '&action=delete&responseID=' . $item['responseID'] . '">Delete</a>',
        );

        //Return the title contents
        return $item['responseID'] . $this->row_actions($actions);
    }

    function column_cb($item) {
        return '<input type="checkbox" name="responseID[]" value="' . $item['responseID'] . '" />';
    }

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'responseID' => 'Response ID',
            'userID' => 'User',
            'wpcresID' => 'Assignment',
            'status' => 'Status',
            'datetime' => 'Last Activity'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'responseID' => array('responseID', false),
            'userID' => array('userID', false), //true means it's already sorted
            'wpcresID' => array('wpcresID', false),
            'status' => array('status', false),
            'datetime' => array('datetime', false)
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'approv' => 'Approve',
            'reject' => 'Reject',
            'delete' => 'Delete',
            'export' => 'Export'
        );
        return $actions;
    }

    function process_bulk_action() {
        global $wpdb;
        $table_name = get_option('wpcres_table_name', $wpdb->prefix . "wpcres_responses");
        $scaffold_table = get_option('wpcres_scaffold_table', $wpdb->prefix . "wpcres_scaffold");

        $responseID = ( is_array($_REQUEST['responseID']) ) ? $_REQUEST['responseID'] : array($_REQUEST['responseID']);

        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            foreach ($responseID as $id) {
                $id = absint($id);
                // Delete the response
                $sql = "DELETE FROM $table_name WHERE `responseID` = '$id'";
                $wpdb->query($sql);

                // Delete the scaffold quesiton responses
                $sql = "DELETE FROM $scaffold_table WHERE `responseID` = '$id'";
                $wpdb->query($sql);
            }
        }

        if ('reject' === $this->current_action()) {
            foreach ($responseID as $id) {

                // Update status
                $id = absint($id);
                $sql = "UPDATE $table_name SET `status` = 'Rejected' WHERE `responseID` = '$id'";
                $wpdb->query($sql);

                $send_email = get_option("wpcres_reject_email_enable");

                if ($send_email) {
                    // Get User Data
                    $user = $wpdb->get_var("SELECT `userID` FROM $table_name WHERE `responseID` = '$id'");
                    $info = get_userdata($user);

                    $admin_name = get_option('wpcres_admin_name');
                    $from = get_option("wpcres_admin_email");
                    $subject = get_option("wpcres_reject_email_subject");
                    $body = get_option("wpcres_reject_email_body");
                    $body = process_shortcodes($body, $id, $table_name);

                    $headers = "From: $admin_name <$from>\r\n";
                    $headers .= "Return-Path: $from\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    // Send email
                    wp_mail($info->user_email, $subject, $body, $headers);
                }
            }
        }

        if ('approv' === $this->current_action()) {
            foreach ($responseID as $id) {
                $id = absint($id);
                $sql = "UPDATE $table_name SET `status` = 'Approved' WHERE `responseID` = '$id'";
                $wpdb->query($sql);

                $send_email = get_option("wpcres_approv_email_enable");

                if ($send_email) {
                    // Get User Data
                    $user = $wpdb->get_var("SELECT `userID` FROM $table_name WHERE `responseID` = '$id'");
                    $info = get_userdata($user);

                    $admin_name = get_option('wpcres_admin_name');
                    $from = get_option("wpcres_admin_email");
                    $subject = get_option("wpcres_approv_email_subject");
                    $body = get_option("wpcres_approv_email_body");
                    $body = process_shortcodes($body, $id, $table_name);

                    $headers = "From: $admin_name <$from>\r\n";
                    $headers .= "Return-Path: $from\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    // Send email
                    wp_mail($info->user_email, $subject, $body, $headers);
                }
            }
        }

        if ('view' === $this->current_action()) {
            foreach ($responseID as $id) {
                $id = absint($id);
                $sql = "SELECT * FROM $table_name WHERE `responseID` = '$id'";
                $response = $wpdb->get_row($sql, ARRAY_A);
                $userinfo = get_user_by('id', $response['userID']);

                // Filter the content so it displays the html correctly
                $content = apply_filters('the_content', $response['essay']);
                $content = str_replace(']]>', ']]&gt;', $content);

                echo "<h3>Essay Response - " . $userinfo->first_name . ' ' . $userinfo->last_name . ' (' . $userinfo->user_login . ')' . " </h3>\r\n";
                echo "<div style='border : solid 2px #000; padding : 4px; width : 98%; height : 300px; overflow : auto;'>$content</div>";
            }
        }

        if ('export' === $this->current_action()) {
            $filename = "wpcres_" . date("Y-m-d_H-i", time());

            $delimiter = "^";
            $enclosure = "\"";
            $idlist = implode(",", $responseID);
            $headers = "'responseID','userID','wpcresID','essay','status','datetime','userIP','display_name','user_login','post_title'";
            $dir = WPCRES_DIR . "/exports";

            $sql = "SELECT $headers
                    UNION ALL
                    SELECT a.*, b.display_name, b.user_login, c.post_title            
                    FROM $table_name as a, wp_users as b, wp_posts as c
                    WHERE a.responseID IN ($idlist) 
                    AND a.userID = b.ID
                    AND a.wpcresID = c.ID";
            $results = $wpdb->get_results($sql, ARRAY_A);

            // Export CSV
            $fh = @fopen("$dir/$filename.csv", 'w');
            foreach ($results as $data) {
                fputcsv($fh, $data, $delimiter, $enclosure);
            }
            fclose($fh);

            // Export HTML
            $fh = @fopen("$dir/$filename.html", 'w');
            fputs($fh, "<html>\r\n<head>\r\n<title>$filename</title>\r\n</head>\r\n<body>\r\n");
            $i = 0;
            foreach ($results as $data) {
                $rSQL = "SELECT * FROM $scaffold_table WHERE `responseID` = '". $data['responseID'] ."'";
                $scaffold_responses = $wpdb->get_results($rSQL, ARRAY_A);
                if ($i != 0) {
                    $data['essay'] = apply_filters('the_content', $data['essay']);
                    $data['essay'] = str_replace(']]>', ']]&gt;', $data['essay']);
                    fputs($fh, "<h1>" . $data['post_title'] . " - " . $data['display_name'] . "</h1>\r\n");
                    fputs($fh, "<h3>Submitted: " . $data['datetime'] . " | Status: " . $data['status'] . "</h3>\r\n");
                    fputs($fh, "<div>" . $data['essay'] . "</div>\r\n");
                    fputs($fh, "<h3>Your responses</h3>");
                    fputs($fh, "<ol>\r\n");
                    foreach ($scaffold_responses as $r) {
                        $answer = ($r['response'] == '0') ? "No" : "Yes";
                        fputs($fh, "<li style='list-style-image: none !important; list-style-type: decimal !important; margin-left: 2em;'><strong>" . $r['question'] . "</strong>: $answer</li>");
                    }
                    fputs($fh, "</ol>\r\n");
                    fputs($fh, "<hr>\r\n");
                }
                $i++;
            }
            fputs($fh, "</body></html>");
            fclose($fh);

            $files_to_zip = array("$dir/$filename.csv", "$dir/$filename.html");
            $zip_result = create_zip($files_to_zip, "$dir/$filename.zip");

            if ($zip_result) {
                unlink("$dir/$filename.csv");
                unlink("$dir/$filename.html");
            }
            $files = glob($dir . "/*.zip");
            arsort($files, SORT_STRING);
            echo "<h3>Last 5 Exports</h3>";
            echo "<ul style='list-style-type:disc !important; padding-left: 12px;'>\r\n";
            $i = 0;
            foreach ($files as $file) {
                if ($i <= 5) {
                    echo "<li class='page_item'><a href='" . WPCRES_URL . "exports/" . basename($file) . "' target='_new'>" . basename($file) . "</a></li>\r\n";
                    $i++;
                }
            }
            echo "</ul>\r\n";
        }
    }

    function prepare_items() {
        global $wpdb;
        $screen = get_current_screen();
        $table_name = get_option('wpcres_table_name', $wpdb->prefix . "wpcres_responses");

        $per_page = get_option('wpcres_responses_per_page', '20');

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $query = "SELECT * FROM $table_name WHERE 1=1 ";

        if (isset($_REQUEST['status_filter']) && $_REQUEST['status_filter'] != "") {
            $query .= "AND `status` = '" . $_REQUEST['status_filter'] . "' ";
        }

        if (isset($_REQUEST['assignment_filter']) && $_REQUEST['assignment_filter'] != "") {
            $query .= "AND `wpcresID` = '" . $_REQUEST['assignment_filter'] . "' ";
        }

        if (isset($_REQUEST['s'])) {
            $query .= "AND `essay` LIKE '%" . $_REQUEST['s'] . "%' ";
        }
        
        $data = $wpdb->get_results($query, ARRAY_A);

        function usort_reorder($a, $b) {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'datetime'; //If no sort, default to date
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');

        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->items = $data;

        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }

}

add_action('admin_menu', function() {
            add_submenu_page('edit.php?post_type=wpcres_assignment', 'Responses', 'Responses', 'manage_options', 'wpcres-view-responses', 'render_response_page');
        });

function create_zip($files = array(), $destination = '', $overwrite = false) {
    //if the zip file already exists and overwrite is false, return false
    if (file_exists($destination) && !$overwrite) {
        return false;
    }
    //vars
    $valid_files = array();
    //if files were passed in...
    if (is_array($files)) {
        //cycle through each file
        foreach ($files as $file) {
            //make sure the file exists
            if (file_exists($file)) {
                $valid_files[] = $file;
            }
        }
    }
    //if we have good files...
    if (count($valid_files)) {
        //create the archive
        $zip = new ZipArchive();
        if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return false;
        }
        //add the files
        foreach ($valid_files as $file) {
            $new_file = substr($file, strrpos($file, '/') + 1);
            $zip->addFile($file, $new_file);
        }
        $zip->close();

        //check to make sure the file exists
        return file_exists($destination);
    } else {
        return false;
    }
}

function process_shortcodes($content, $responseID, $table_name) {
    global $wpdb;

    // Get User Data
    $user = $wpdb->get_var("SELECT `userID` FROM $table_name WHERE `responseID` = '$responseID'");
    $info = get_userdata($user);

    // Get wpcresID
    $wpcresID = $wpdb->get_var("SELECT `wpcresID` FROM $table_name WHERE `responseID` = '$responseID'");

    // Get Essay Data
    $essay_body = $wpdb->get_var("SELECT `essay` FROM $table_name WHERE `responseID` = '$responseID'");

    $assignment_data = get_post($wpcresID, ARRAY_A);
    $essay_title = $assignment_data['post_title'];

    // Look for the [name] shortcode and replace with user display name
    $content = preg_replace('/\[name\]/', $info->display_name, $content);

    // Look for the [essay_title] shortcode and replace with essay title
    $content = preg_replace('/\[essay_title\]/', "<strong>" . $essay_title . "</strong>", $content);

    // Look for the [essay_body] shortcode and replace with essay title
    $content = preg_replace('/\[essay\]/', $essay_body, $content);

    return $content;
}

function render_response_page() {
    $wpcresListTable = new WPCRES_Response_Table();
    $wpcresListTable->prepare_items();
    $assignments = get_posts(array(
        'post_type' => 'wpcres_assignment',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'post_date',
        'order' => 'ASC'
            ));
    ?>
    <div class="wrap">
        <div id="icon-edit-pages" class="icon32"><br/></div>
        <h2>wpCRES Responses</h2>
        <form method="post">
            <p class="search-box">
                Status Filter
                <select name="status_filter" onchange="submit();">
                    <option value=""></option>
                    <option value="In Progress" <?php echo ($_REQUEST['status_filter'] == "In Progress") ? "selected" : ""; ?>>In Progress</option>
                    <option value="Submitted" <?php echo ($_REQUEST['status_filter'] == "Submitted") ? "selected" : ""; ?>>Submitted</option>
                    <option value="Approved" <?php echo ($_REQUEST['status_filter'] == "Approved") ? "selected" : ""; ?>>Approved</option>
                    <option value="Rejected" <?php echo ($_REQUEST['status_filter'] == "Rejected") ? "selected" : ""; ?>>Rejected</option>
                </select>
            </p>
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" name="assignment_filter" value="<?php echo $_REQUEST['assignment_filter']; ?>" />
        </form>

        <form method="post">
            <p class="search-box">
                Assignment Filter
                <select name="assignment_filter" onchange="submit();">
                    <option value=""></option>
                    <?php foreach ($assignments as $a){ ?>
                    <option value="<?php echo $a->ID; ?>" <?php echo ($_REQUEST['assignment_filter'] == $a->ID) ? "selected" : ""; ?>><?php echo $a->post_title; ?></option>
                    <?php } ?>
                </select>
            </p>
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" name="status_filter" value="<?php echo $_REQUEST['status_filter']; ?>" />
        </form>

        <!-- <form method="post">
        //    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
        //    <?php $wpcresListTable->search_box('search', 'search_id'); ?>
        //</form>
        //-->
        <form id="responseID-filter" method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
            <input type="hidden" name="post_type" value="wpcres_assignment" />
            <?php $wpcresListTable->display() ?>
        </form>
    </div>
<?php } ?>
