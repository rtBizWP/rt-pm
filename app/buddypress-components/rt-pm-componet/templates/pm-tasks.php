<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 1/4/15
 * Time: 1:15 PM
 */

global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_task, $rt_pm_time_entries_model;

$post_id = 0;

$author_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'author' );
if( current_user_can( 'edit_rt_tasks' ) ){

    $user_edit = true;
}else {

    $user_edit = false;
}

if( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] != $rt_pm_project->post_type ) {
    wp_die("Opsss!! You are in restricted area");
}

$post_type=$_REQUEST['post_type'];
$task_post_type=$rt_pm_task->post_type;
$task_labels=$rt_pm_task->labels;

if( isset( $_REQUEST["{$post_type}_id"] ) )
    $project_id = $_REQUEST["{$post_type}_id"];

$form_ulr = $rt_pm_bp_pm->get_component_root_url().bp_current_action() . "?post_type={$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-task";
///alert Notification
if ( isset( $action_complete ) && $action_complete){
    if (isset($_REQUEST["new"])) {
        ?>
        <div class="alert-box success">
            <?php _e('New '.  ucfirst($task_labels['name']).' Inserted Sucessfully.'); ?>
            <a href="#" class="close">&times;</a>
        </div>
    <?php
    }
    if(isset($updateFlag) && $updateFlag){ ?>
        <div class="alert-box success">
            <?php _e(ucfirst($task_labels['name'])." Updated Sucessfully."); ?>
            <a href="#" class="close">&times;</a>
        </div>
    <?php }
}
if (isset($_REQUEST["{$task_post_type}_id"])) {
    $form_ulr .= "&{$task_post_type}_id=" . $_REQUEST["{$task_post_type}_id"];
    $post_id = $_REQUEST["{$task_post_type}_id"];
    $post = get_post($_REQUEST["{$task_post_type}_id"]);
    if (!$post) {
        ?>
        <div class="alert-box alert">
            Invalid Post ID
            <a href="" class="close">&times;</a>
        </div>
        <?php
        $post = false;
    }
    if ( $post->post_type != $task_post_type ) {
        ?>
        <div class="alert-box alert">
            Invalid Post Type
            <a href="" class="close">&times;</a>
        </div>
        <?php
        $post = false;
    }

    $create = rt_convert_strdate_to_usertimestamp($post->post_date_gmt);
    $modify = rt_convert_strdate_to_usertimestamp($post->post_modified_gmt);
    $createdate = $create->format("M d, Y h:i A");
    $modifydate = $modify->format("M d, Y h:i A");

}else{
    $post=null;
}

// get project meta
if (isset($post->ID)) {
    $due =rt_convert_strdate_to_usertimestamp(get_post_meta($post->ID, 'post_duedate', true));
    $due_date = $due->format("M d, Y h:i A");
}

$task_type = get_post_meta( $post_id, 'rtpm_task_type', true );

//Disable working days
$rt_pm_task->disable_working_days( $project_id );
?>

<script>
    var rtpm_task_edit;

    (function($) {

        rtpm_task_edit = {
            init: function() {
                $( document).on( 'click', 'a.rtpm_task_edit_link', rtpm_task_edit.open_task_edit_side_panel );
                $( document).on( 'click', 'a.rtpm_task_timeentries_link', rtpm_task_edit.open_timeentries_side_panel );
            },

            open_task_edit_side_panel: function( e ) {

                e.preventDefault();

                block_ui();
                $element = $( this );
                $url = $element.attr('href');

                var task_id = get_parameter_by_name( $url, 'rt_task_id' );
                render_project_slide_panel('open', task_id, <?php echo get_current_blog_id(); ?>, '', 'task');
            },

            open_timeentries_side_panel: function( e ) {
                e.preventDefault();

                block_ui();
                $element = $( this );
                $url = $element.attr('href');

                var task_id = get_parameter_by_name( $url, 'task_id' );
                render_project_slide_panel( 'add_time_entry', task_id, <?php echo get_current_blog_id(); ?>, '', 'time-entries' );
            }
        };

        $( document).ready( function() { rtpm_task_edit.init(); } );

    })(jQuery);
</script>

<style type="text/css">
    .column-rtpm_title button.toggle-row {
        display: none !important;
    }
</style>
<div id="wp-custom-list-table">
    <?php
    if( $user_edit ) {

        ?>
        <div class="list-heading">
            <div class="large-8 columns">
                <h2><?php _e( '#'.get_post_meta(  $project_id, 'rtpm_job_no', true ).' '. get_post_field( 'post_title', $project_id ), RT_PM_TEXT_DOMAIN );?></h2>
            </div>
            <div class="large-4 columns">
                <button class="mybutton add-task" type="button" ><?php _e( 'Add Task' ); ?></button>
                <button class="mybutton add-sub-task" type="button" ><?php _e('Add Sub Task'); ?></button>
                <button class="mybutton add-milestone" type="button" ><?php _e('Add Milestone'); ?></button>
            </div>

        </div>
    <?php
    }
    $rtpm_task_list= new Rt_PM_BP_PM_Task_List_View( $user_edit );
    $rtpm_task_list->prepare_items();
    $rtpm_task_list->display();
    ?>
</div>
	<div class="rt-post-per-page-container">
		<select class="rt-post-per-page-select" data-value="task-list">
			<?php
			$option_array = array( 10,25,50 );
			$user_id = get_current_user_id();
			$user_post_per_page = get_user_meta($user_id,'rt_task_per_page',true);
			if( isset($user_post_per_page) ){
					$selected_option = $user_post_per_page;
				}else{
					$selected_option = '';
				}
			for($opt = 0 ; $opt < count( $option_array ) ; $opt++) { 
				$opt_value = $option_array[$opt];
				if( (int)$selected_option == $opt_value ){
					$is_selected = 'selected';
				}else if( $selected_option == '' && $opt_value == 25 ){
					$is_selected = 'selected';
				}else{
					$is_selected = '';
				}
				?>
			<option value="<?php echo $opt_value; ?>" <?php echo $is_selected;?>><?php echo $opt_value; ?></option>
			<?php } ?>
		</select>
	</div>
<!--reveal-modal-add-task -->
<div id="div-add-task" class="reveal-modal">

    <form method="post" id="form-add-post" data-posttype="<?php echo $task_post_type; ?>" action="<?php echo $form_ulr; ?>">
        <?php wp_nonce_field('rtpm_save_task','rtpm_save_task_nonce') ?>
        <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $project_id; ?>" />
        <input type="hidden" name="post[task_type]" value="" />
        <?php if (isset($post->ID) && $user_edit ) { ?>
            <input type="hidden" name="post[post_id]" id='task_id' value="<?php echo $post->ID; ?>" />
        <?php } ?>
        <h4> <?php  _e( 'Add New Task', RT_PM_TEXT_DOMAIN ) ; ?></h4>
        <div class="row">
            <div class="large-6 columns">

                <div class="row parent-task-dropdown" style="display: none;">
                    <div class="large-12 mobile-large-1 columns">
                        <?php if( $user_edit ) {
                            $rt_pm_task->rtpm_render_parent_tasks_dropdown( $project_id, $post_id );
                        } else { ?>
                            <span><?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?></span><br /><br />
                        <?php } ?>
                    </div>
                </div>

                <label><?php _e(ucfirst($task_labels['name'])." Name"); ?><small class="required"> * </small></label>
                <?php if( $user_edit ) { ?>
                    <input required="required" name="post[post_title]" id="new_<?php echo $task_post_type ?>_title" type="text" placeholder="<?php _e(ucfirst($task_labels['name'])." Name"); ?>" value="<?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?>" />
                <?php } else { ?>
                    <span><?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?></span><br /><br />
                <?php } ?>
                <label><?php _e("Message"); ?><small class="required"> * </small></label>
                <?php
                if( $user_edit ) {
                    ?>
                    <textarea required="required" name="post[post_content]" rows="5" type="text" placeholder="<?php _e("Message"); ?>" ><?php echo ( isset($post->ID ) ) ? trim($post->post_content) : ""; ?></textarea>
                    <?php
                    //wp_editor( ( isset( $post->ID ) ) ? $post->post_content : "", "post_content", array( 'textarea_name' => 'post[post_content]', 'media_buttons' => false, 'tinymce' => false, 'quicktags' => false, 'textarea_rows' => 5 ) );
                } else {
                    echo ucfirst($labels['name']).' Content : <br /><br /><span>'.(( isset($post->ID) ) ? trim($post->post_content) : '').'</span><br /><br />';
                }
                ?>

                <div class="row hide-for-milestone">
                    <div class="large-6 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                        <span title="Create Date"><label>Create Date<small class="required"> * </small></label></span>
                        <?php if( $user_edit ) { ?>
                            <input required="required" class="datetimepicker moment-from-now" name="post[post_date]" type="text" placeholder="Select Create Date"
                                   value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                                   title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" id="create_<?php echo $task_post_type ?>_date">

                        <?php } else { ?>
                            <span class="rtpm_view_mode moment-from-now"><?php echo $createdate ?></span>
                        <?php } ?>
                    </div>
                    <div class="large-6 columns hide-for-milestone <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                        <span title="Status"><label>Status<small class="required"> * </small></label></span>
                        <?php
                        if (isset($post->ID))
                            $pstatus = $post->post_status;
                        else
                            $pstatus = "";
                        $post_status = $rt_pm_task->get_custom_statuses();
                        $custom_status_flag = true;
                        ?>
                        <?php if( $user_edit ) { ?>
                            <select required="required" id="rtpm_post_status" class="right" name="post[post_status]">
                                <?php foreach ($post_status as $status) {
                                    if ($status['slug'] == $pstatus) {
                                        $selected = 'selected="selected"';
                                        $custom_status_flag = false;
                                    } else {
                                        $selected = '';
                                    }
                                    printf('<option value="%s" %s >%s</option>', $status['slug'], $selected, $status['name']);
                                } ?>
                                <?php if ( $custom_status_flag && isset( $post->ID ) ) { echo '<option selected="selected" value="'.$pstatus.'">'.$pstatus.'</option>'; } ?>
                            </select>
                        <?php } else {
                            foreach ( $post_status as $status ) {
                                if($status['slug'] == $pstatus) {
                                    echo '<span class="rtpm_view_mode">'.$status['name'].'</span>';
                                    break;
                                }
                            }
                        } ?>
                    </div>
                </div>


                <div class="row">
                    <div class="large-6 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                        <span  title="Due Date"><label>Due Date<small class="required"> * </small></label></span>
                        <?php if( $user_edit  ) { ?>
                            <input class="datetimepicker moment-from-now" type="text" name="post[post_duedate]" placeholder="Select Due Date"
                                   value="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>"
                                   title="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>" id="due_<?php echo $task_post_type ?>_date">

                        <?php } else { ?>
                            <span class="rtpm_view_mode moment-from-now"><?php echo $due_date ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="row hide-for-milestone">
                    <span title="Estimated time"><label><?php _e('Resources') ?></label></span>
                    <div class="rt-resources-parent-row">
                        <div class="row rt-resources-row" data-resource-id="0">
                            <div class="small-4 medium-4 columns">
                                <input type="text" class="search-contact" placeholder="Assignee"  />
                                <input type="hidden" class="contact-wp-user-id" name="post[resource_wp_user_id][]" />
                            </div>

                            <div class="small-3 medium-3 large-2 columns">
                                <input type="number" step=".25" min="0" placeholder="Duration" name="post[time_duration][]" />
                            </div>

                            <div class="small-4 medium-4 large-5 columns">

                                <input type="text" class="datetimepicker" placeholder="Due" name="post[timestamp][]">
                            </div>

                            <div class="small-1 columns left">
                                <a class="resources-add-multiple button"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <?php

                        $task_resources = array();
                        if( isset( $post->ID ) ) {
                            $task_resources = $rt_pm_task->rtpm_get_task_resources( $post->ID, $_REQUEST["{$post_type}_id"] );
                        }

                        foreach( $task_resources as  $resource ) {

                            $dr = rt_convert_strdate_to_usertimestamp( $resource->timestamp )
                            ?>
                            <div class="row rt-resources-row" data-resource-id="<?php echo $resource->id ?>">
                                <div class="small-4 medium-4 columns">
                                    <input type="text" class="search-contact" value="<?php echo rtbiz_get_user_displayname( $resource->user_id ) ?>"/>
                                    <input type="hidden" class="contact-wp-user-id" name="post[resource_wp_user_id][]"  value="<?php echo $resource->user_id ?>" />
                                </div>

                                <div class="small-3 medium-3 large-2 columns">
                                    <input type="number" step=".25" min="0" name="post[time_duration][]" value="<?php echo $resource->time_duration ?>" />
                                </div>

                                <div class="small-4 medium-4 large-5 columns">

                                    <input type="text" class="datetimepicker" name="post[timestamp][]" value="<?php echo $dr->format('M d, Y h:i A'); ?>">
                                </div>

                                <div class="small-1 columns left">
                                    <a class="resources-delete-multiple button"><i class="fa fa-times"></i></a>
                                </div>
                            </div>
                        <?php }
                        ?>
                    </div>
                </div>
            </div>

            <div class="large-6 column hide-for-milestone">
                <?php $attachments = array();
                if ( isset( $post->ID ) ) {
                    $attachments = get_posts( array(
                        'posts_per_page' => -1,
                        'post_parent' => $post->ID,
                        'post_type' => 'attachment',
                    ));
                }
                ?>
                <div class="inside">
                    <div class="row collapse" id="attachment-container">
                        <?php if( $user_edit ) { ?>
                            <a href="#" class="button right" id="add_pm_attachment"><?php _e('Add Docs'); ?></a>
                        <?php } ?>
                        <div class="scroll-height">
                            <table>
                                <?php if ( ! empty($attachments)){?>
                                    <tr>
                                        <th scope="column">Type</th>
                                        <th scope="column">Name</th>
                                        <th scope="column">Size</th>
                                        <th scope="column"></th>
                                    </tr>
                                    <?php foreach ($attachments as $attachment) { ?>
                                        <?php $extn_array = explode('.', $attachment->guid); $extn = $extn_array[count($extn_array) - 1]; ?>
                                        <tr class="large-12 mobile-large-3 attachment-item" data-attachment-id="<?php echo $attachment->ID; ?>">
                                            <td scope="column"><img height="20px" width="20px" src="<?php echo RT_PM_URL . "app/assets/file-type/" . $extn . ".png"; ?>" /></td>
                                            <td scope="column">
                                                <a target="_blank" href="<?php echo wp_get_attachment_url($attachment->ID); ?>">
                                                    <?php echo '<span>'.$attachment->post_title .".".$extn.'</span>'; ?>
                                                </a>
                                            </td>
                                            <td scope="column">
                                                <?php
                                                $attached_file = get_attached_file( $attachment->ID );
                                                if ( file_exists( $attached_file ) ) {
                                                    $bytes = filesize( $attached_file );
                                                    $response['filesizeInBytes'] = $bytes;
                                                    echo '<span>'. $response['filesizeHumanReadable'] = size_format( $bytes ) .'</span>';
                                                }
                                                ?>
                                            </td>
                                            <td scope="column">
                                                <?php if( $user_edit ) { ?>
                                                    <a href="#" class="rtpm_delete_attachment  button add-button removeMeta"><i class="fa fa-times"></i></a>
                                                <?php } ?>
                                                <input type="hidden" name="attachment[]" value="<?php echo $attachment->ID; ?>" />
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="large-12 columns">
                <button class="mybutton right" type="submit" id="save-task">Save task</button>
            </div>
        </div>

    </form>

    <a class="close-reveal-modal">×</a>
</div>
<?php rtpm_validate_user_assigned_hours_script(); ?>
