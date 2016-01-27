<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 12/12/14
 * Time: 6:48 PM
 */

global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_task, $rt_pm_time_entries_model;

$user_edit = true;
$task_post_type = $rt_pm_task->post_type;
$blog_id = isset( $_REQUEST['rt_voxxi_blog_id'] ) ?  $_REQUEST['rt_voxxi_blog_id'] : get_current_blog_id();

if( isset( $_GET["id"] ) ) {

    $post_id = $_GET["id"];


    $post = get_post( $post_id );


// get project meta
    if (isset($post->ID)) {
        $due                = get_post_meta($post->ID, 'post_duedate', true);
        $due_date           = date_create_from_format( 'Y-m-d H:i:s', $due )->format("M d, Y h:i A");
        $post_assignee      = get_post_meta($post->ID, 'post_assignee', true);
        $post_project_id    = $post->post_parent;
        $createdate         = date_create_from_format( 'Y-m-d H:i:s', $post->post_date_gmt )->format("M d, Y h:i A");
        $modifydate         = date_create_from_format( 'Y-m-d H:i:s', $post->post_modified_gmt )->format("M d, Y h:i A");
    } else {
        $post_assignee = $post_id;
    }

}else if( isset( $_GET['project_id'] ) ) {

    $post_assignee = $_GET['user_id'];
    $post_project_id = $_GET['project_id'];

}


//Reecord time and expenses url
$timeentries_url = add_query_arg( array( 'post_type' => $rt_pm_project->post_type,  $rt_pm_project->post_type.'_id' => $post_project_id, 'tab' => $rt_pm_project->post_type .'-timeentry', 'action' => 'timeentry', 'task_id' => $post_id, 'rt_voxxi_blog_id' => $blog_id ) ,  $rt_pm_bp_pm->get_component_root_url() .'time-entries' );

//assign to
$results_member = Rt_PM_Utils::get_pm_rtcamp_user();

$rt_pm_task->disable_working_days( $post_project_id );
$task_labels=$rt_pm_task->labels;
$task_type = get_post_meta( $post_id, 'rtpm_task_type', true );

$uncommon_task_type = array( 'milestone', 'task_group' );
?>
<form method="post"   action="">
    <?php wp_nonce_field('rtpm_save_task','rtpm_save_task_nonce') ?>
    <?php if( isset( $_GET["id"] ) ){ ?>
    <input type="hidden" name="post[action]" value="<?php echo $_GET['action'] ?>" />
    <input type="hidden" name="post[template]" value="<?php echo $_GET['template'] ?>" />
	<input type="hidden" name="front_end" value="front" />
    <input type="hidden" name="post[actvity_element_id]" value="<?php echo $_GET['actvity_element_id'] ?>" />
    <?php } ?>

    <input type="hidden" name="post[task_type]" value="<?php echo $task_type ?>" />
    <input type="hidden" id="rt-pm-blog-id" name="post[rt_voxxi_blog_id]" value="<?php echo $blog_id ?>" />
    <input type="hidden" name="post[post_type]" value="<?php echo $task_post_type; ?>" />

    <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $post_project_id; ?>" />
    <?php if (isset($post->ID) && $user_edit ): ?>
    <input type="hidden" name="post[post_id]" id='rt_pm_post_id' value="<?php echo $post->ID; ?>" />
    <?php endif; ?>

    <div class="row">
        <div class="small-10 columns">
            <h2><?php echo ( isset($post->ID) ) ? $post->post_title : "Task"; ?></h2>
        </div>
        <div class="small-2 columns">
            <a title="Close" class="right close-sidepanel"><i class="fa fa-caret-square-o-right fa-2x"></i></a>
        </div>
    </div>

    <div class="row column-title parent-task-dropdown" style="display: none;">
        <div class="small-12 columns">
            <?php if( $user_edit ) {
                $rt_pm_task->rtpm_render_parent_tasks_dropdown( $post_project_id, $post_id );
            } else { ?>
                <span><?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?></span><br /><br />
            <?php } ?>
        </div>
    </div>

    <div class="row ">
        <!-- Post title START -->
        <div class="small-12 columns">
            <?php if( $user_edit ) { ?>
                <input required="required" name="post[post_title]" id="new_<?php echo $task_post_type ?>_title" type="text" placeholder="<?php _e(ucfirst($task_labels['name'])." Name"); ?>" value="<?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?>" />
            <?php } else { ?>
                <span><?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?></span><br /><br />
            <?php } ?>
        </div>
        <!-- Post title END -->
    </div>

    <div class="row">
        <div class="small-12 columns">
            <?php if( $user_edit ) { ?>
                <textarea required="required" name="post[post_content]" rows="5" type="text" placeholder="<?php _e("Message"); ?>" ><?php echo ( isset($post->ID ) ) ? trim($post->post_content) : ""; ?></textarea>
            <?php } else {
                echo ucfirst($labels['name']).' Content : <br /><br /><span>'.(( isset($post->ID) ) ? trim($post->post_content) : '').'</span><br /><br />';
            } ?>
        </div>
    </div>

    <div class="row">
        <div class="small-4 columns">
            <label><?php _e("Project Title"); ?></label>
        </div>
        <div class="small-8 columns">
            <label><?php  echo get_post_field( 'post_title', $post_project_id ); ?></label>
        </div>
    </div>

    <div class="row">
        <div class="small-4 columns">
            <label><?php _e( 'Task Type', RT_PM_TEXT_DOMAIN ) ?></label>
        </div>
        <div class="small-8 columns">
            <select name="post[task_type]">
                <option <?php selected( $task_type, 'task' ) ?> value="task">Task</option>
                <option <?php selected( $task_type, 'milestone' ) ?> value="milestone">Milestone</option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="small-4 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
            <label for="post[post_status]"><?php _e("Status"); ?></label>
        </div>
        <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
            <?php
            if (isset($post->ID))
                {$pstatus = $post->post_status;}
            else
                {$pstatus = "";}
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

    <div class="row hide-for-milestone">
        <div  class="small-4 columns">
            <span title="Create Date"><label>Create Date<small class="required"> * </small></label></span>
        </div>
        <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">

            <?php if( $user_edit ) { ?>
                <input required="required" class="datetimepicker moment-from-now" type="text" name="post[post_date]" placeholder="Select Create Date"
                       value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                       title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" id="create_<?php echo $task_post_type ?>_date">

            <?php } else { ?>
               <p><?php echo $createdate ?></p>
            <?php } ?>
        </div>
    </div>

    <div class="row">
        <div  class="small-4 columns">
            <span class="due-date-hidden" title="Due Date"><label>Due Date<small class="required"> * </small></label></span>
        </div>
        <div class="small-8 columns">
            <?php if( $user_edit ) { ?>
                <input class="datetimepicker moment-from-now" type="text" name="post[post_duedate]" placeholder="Select Due Date"
                       value="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>"
                       title="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>" id="due_<?php echo $task_post_type ?>_date">
                <input type="hidden" value="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>" />
            <?php } else { ?>
                <p><?php echo $due_date ?></p>
            <?php } ?>
        </div>
   </div>
    <?php if ( ! bp_is_current_component( BP_CRM_SLUG ) && ! in_array( $task_type, $uncommon_task_type ) ): ?>
    <div class="row hide-for-milestone">
        <span title="Resources"><label><?php _e('Resources') ?></label></span>
        <div class="rt-resources-parent-row">
            <div class="row rt-row rt-resources-row" data-resource-id="0">
                <div class="small-4 medium-4 column">
                    <input type="text" class="search-contact" placeholder="Assignee"/>
                    <input type="hidden" class="contact-wp-user-id" name="post[resource_wp_user_id][]" />
                </div>

                <div class="small-3 medium-3 large-2 columns">
                    <input type="number" step=".25" min="0" placeholder="Duration" name="post[time_duration][]" />
                </div>

                <div class="small-4 medium-4 large-5 columns">

                    <input type="text" class="datetimepicker" placeholder="Due" name="post[timestamp][]">
                </div>

                <div class="small-1 columns">
                    <a class="resources-add-multiple add-button button" title="Add Resource"><i class="fa fa-plus"></i></a>
                </div>
            </div>
            <?php

            $task_resources = array();
            if( isset( $post->ID ) ) {
                $task_resources = $rt_pm_task->rtpm_get_task_resources( $post->ID, $post_project_id );
            }

            foreach( $task_resources as  $resource ) {

                $dr = new DateTime( $resource->timestamp );
                ?>
                <div class="row rt-row rt-resources-row" data-resource-id="<?php echo $resource->id ?>">
                    <div class="small-4 medium-4 column">
                        <input type="text" class="search-contact"  value="<?php echo rtbiz_get_user_displayname( $resource->user_id ) ?>"/>
                        <input type="hidden" class="contact-wp-user-id" name="post[resource_wp_user_id][]" value="<?php echo $resource->user_id ?>" />
                    </div>

                    <div class="small-3 medium-3 large-2 columns">
                        <input type="number" va step=".25" min="0" name="post[time_duration][]" value="<?php echo $resource->time_duration ?>" />
                    </div>

                    <div class="small-4 medium-4 large-5 columns">

                        <input type="text" class="datetimepicker" name="post[timestamp][]" value="<?php echo $dr->format('M d, Y h:i A'); ?>">
                    </div>

                    <div class="small-1 columns">
                        <a class="resources-delete-multiple add-button button" title="Remove Resource"><i class="fa fa-times"></i></a>
                    </div>
                </div>
            <?php }
            ?>
        </div>
    </div>
    <?php endif; ?>

    <?php
	if( isset( $post_id ) && ! in_array( $task_type, $uncommon_task_type ) ): ?>
    <h3><?php _e('Attachments'); ?></h3>
    <hr/>
    <?php render_rt_bp_wall_documents_section( $post_id, $blog_id );
    endif;
    ?>



    <div class="row">
        <div class="small-12 columns action-bar">
            <?php if( ! bp_is_current_component( BP_CRM_SLUG ) && ! in_array( $task_type, $uncommon_task_type ) ): ?>
            <a class="button rtpm_task_timeentries"  href='<?php echo $timeentries_url; ?>'>Time and Expenses</a>
            <?php endif; ?>
            <input type="submit" value="Save" >
        </div>
    </div>


</form>
<!-- Validate user assigned hours -->
<?php rtpm_validate_user_assigned_hours_script(); ?>

<!-- Open time entries in side panel -->
<script type="text/javascript">
    var rtpm_task_timentries;

    (function( $ ) {
        rtpm_task_timentries = {
          init: function() {
              $( document).on( 'click', 'a.rtpm_task_timeentries', rtpm_task_timentries.open_timeentries_side_panel );
          },

          open_timeentries_side_panel: function( e ) {
              e.preventDefault();

              block_ui();
              $element = $( this );
              $url = $element.attr('href');

              var task_id = rtvo_get_parameter_by_name( $url, 'task_id' );
              var rtvoxxi_blog_id = rtvo_get_parameter_by_name( $url, 'rt_voxxi_blog_id' );

              render_project_slide_panel( 'add_time_entry', task_id, rtvoxxi_blog_id, '', 'time-entries' );
          }
        };

        $( document).ready( function() { rtpm_task_timentries.init() } );
    })(jQuery);
</script>