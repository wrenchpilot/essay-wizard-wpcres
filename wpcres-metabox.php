<?php
/*
 * wpcres-metabox.php
 *
 * This code creates meta boxes in the administration panel for the wpCRES
 * custom post-type.
 *
 * @author Shawn Carnley <Shawn.Carnley@gatech.edu>
 * @version 1.0
 * @package wpCRES
 */

// Activate the metaboxes defined below
add_action('add_meta_boxes', function () {
    // css id, title, cd func, page, priority, cb func args
    add_meta_box('wpcres_scafolding_meta_box', 'CRES Scaffolding Questions', 'scaffolding_display_meta_box', 'wpcres_assignment', 'normal', 'high', 'post');
    add_meta_box('wpcres_final_followup_meta_box', 'CRES Final Followup', 'final_followup_display_meta_box', 'wpcres_assignment', 'normal', 'high', 'post');
});

// Create metabox container for the scaffolding qestions
function scaffolding_display_meta_box($post) {
    // create nonce
    wp_nonce_field(plugin_basename(WPCRES_MAIN_PLUGIN_FILE), 'dynamic_meta_nonce'); ?>
<div id="meta_inner">
    <?php
        $questions = get_post_meta($post->ID, 'wpcres_question', true);
    $c = 0;
    if (is_array($questions)) {
        foreach ($questions as $question) {
            if (isset($question['scaffold_question']) || isset($question['scaffold_answer'])) {
                $q = str_replace("'", "&#39;", $question['scaffold_question']);
                $f = str_replace("'", "&#39;", $question['scaffold_followup']);
                $a = $question['scaffold_answer'];
                echo "<p><strong>Question " . ($c + 1) . "</strong> :<br /><textarea name='questions[$c][scaffold_question]' cols='60' />$q</textarea> <br />";
                echo "<strong>Follow up</strong>:<br /><textarea name='questions[$c][scaffold_followup]' cols='60' />$f</textarea> <br />";
                echo "<strong>Answer</strong>: No  <input type='radio' name='questions[$c][scaffold_answer]' value='0' " . (($a == 0) ? 'checked' : '') . " /> &nbsp; ";
                echo "         Yes <input type='radio' name='questions[$c][scaffold_answer]' value='1' " . (($a == 1) ? 'checked' : '') . " /> &nbsp;&nbsp;&nbsp;";
                echo "<input type='button' class='remove button-secondary' value='Remove Question' /></p><hr />";
                $c++;
            }
        }
    } ?>
    <span id="here"></span>
    <input type="button" class="add button-primary" value="Add Question" />
    <script>
        var $ = jQuery.noConflict();
        $(document).ready(function() {
            var count = <?php echo $c ?> ;
            $(".add").click(function() {
                $('#here').append('<p> <strong>Question</strong> ' + (count + 1) +
                    ' :<br /><textarea name="questions[' + count +
                    '][scaffold_question]" cols="60" /></textarea> <br /> <strong>Follow up</strong>:<br /><textarea name="questions[' +
                    count +
                    '][scaffold_followup]" cols="60" /> <br /> <strong>Answer</strong>: No <input type="radio" name="questions[' +
                    count +
                    '][scaffold_answer]" value="0" /> &nbsp; Yes <input type="radio" name="questions[' +
                    count +
                    '][scaffold_answer]" value="1" />&nbsp;&nbsp;&nbsp;<input type="button" class="remove button-secondary" value="Remove Question" /></p><hr />'
                    );
                count++;
                return false;
            });
            $(".remove").live('click', function() {
                $(this).parent().remove();
            });
        });
    </script>
</div>
<?php
}
?>
<?php

// Create metabox container for the final followup comments
function final_followup_display_meta_box($post) {
    $followup_meta = get_post_meta($post->ID, 'wpcres_final_followup', true);

    if (isset($followup_meta)) {
        $followup_meta = str_replace("'", "&#39;", $followup_meta);
    } ?>
<div id="meta_inner">
    <?php
        wp_editor($followup_meta, 'final_followup', array(
            'wpautop' => 0,
            'media_buttons' => 0, // this does not work
            'quicktags' => 1,
            'teeny' => 1,
            'apply_source_formatting' => 1,
            'textarea_rows' => '15',
            'textarea_name' => 'final_followup')); ?>
</div>
<?php
}
