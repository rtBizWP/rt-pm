<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 12/12/14
 * Time: 10:43 PM
 */

global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_task,$rt_pm_time_entries,$rt_pm_time_entries_model, $rtpm_task_list, $rt_pm_task_resources_model;

//Default value while adding new
$selected_time_entry_type = '';
$prefilled_time_duration = '0';
$prefilled_timestamp = '';
$prefilled_end_date = '';
$prefilled_message = '';
$task_id = '';

switch( $_REQUEST['template'] ) {
    case 'new_time_entry':
        $post_project_id = $_REQUEST["id"];
        break;
    case 'add_time_entry':
        $task_id = $_REQUEST["id"];
        $post_project_id = get_post_meta( $task_id, 'post_project_id', true);
        break;
    case 'edit_time_entry':
        $timeentry_id = $_REQUEST["id"];
        $time_entry = current( $rt_pm_time_entries_model->get( array( 'id' => $timeentry_id ) ) );
        $post_project_id  = $time_entry->project_id;
        $task_id = $time_entry->task_id;
        $selected_time_entry_type = $time_entry->type;
        $prefilled_time_duration = $time_entry->time_duration;
        $timestamp = $time_entry->timestamp;
        $timestamp_date_obj = date_create_from_format( 'Y-m-d H:i:s', $timestamp );
        $prefilled_timestamp = $timestamp_date_obj->format('M d, Y H:i A');
        $prefilled_message = $time_entry->message;
        break;
}

$user_edit = true;
$task_post_type=$rt_pm_task->post_type;
$timeentry_labels = $rt_pm_time_entries->labels;
$timeentry_post_type = Rt_PM_Time_Entries::$post_type;
?>
<form method="post"  action="">
    <?php wp_nonce_field('rtpm_save_timeentry','rtpm_save_timeentry_nonce') ?>

    <input type="hidden" name="post[post_project_id]" value="<?php echo $post_project_id; ?>" />

    <?php if( 'add_time_entry' === $_REQUEST['template'] ): ?>
    <input type="hidden" name="post[action]" value="<?php echo $_GET['action'] ?>" />
    <input type="hidden" name="post[template]" value="<?php echo $_GET['template'] ?>" />
    <input type="hidden" name="post[actvity_element_id]" value="<?php echo $_GET['actvity_element_id'] ?>" />
    <?php endif; ?>

    <?php if( in_array( $_REQUEST['template'], array( 'add_time_entry', 'edit_time_entry' ) ) ): ?>
    <input type="hidden" name="post[post_task_id]" value="<?php echo $task_id; ?>" />
    <?php endif;?>

    <?php if( 'edit_time_entry' === $_REQUEST['template'] ): ?>
    <input type="hidden" name="post[post_id]"  value="<?php echo $timeentry_id; ?>" />
    <?php endif; ?>
    <input type="hidden" name="post[rt_voxxi_blog_id]" value="<?php echo $_GET['rt_voxxi_blog_id'] ?>" />
    <div class="row">
        <div class="small-10 columns">
            <h2><?php _e('Time entry', RT_PM_TEXT_DOMAIN) ?></h2>
        </div>
        <div class="small-2 columns">
            <a title="Close" class="right close-sidepanel"><i class="fa fa-caret-square-o-right fa-2x"></i></a>
        </div>
    </div>

    <div class="row column-title">
        <div class="small-4 columns">
            <label for="Task">Task<small class="required"> * </small></label>
        </div>
        <div class="small-8 columns">
            <?php if( 'add_time_entry' === $_REQUEST['template'] ): ?>
            <label for="Task"><?php echo get_post_field( 'post_title', $task_id ) ?></label>
            <?php elseif(  in_array( $_REQUEST['template'], array( 'edit_time_entry', 'new_time_entry' ) ) ):

                $rt_pm_task->rtpm_tasks_dropdown( $post_project_id, $task_id );
             endif;?>
        </div>
    </div>

    <!-- Add new task fields added along time entries -->

    <div class="row task-fields">
        <div class="small-4 columns">
            <label for="post[task_start_date]">Start Date<small class="required"> * </small></label>
        </div>
        <div class="small-8 columns">
            <input name="post[task_start_date]" id="task_start_date" class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date" />
        </div>
    </div>

    <div class="row task-fields">
        <div class="small-4 columns">
            <label for="post[task_end_date]">End Date<small class="required"> * </small></label>
        </div>
        <div class="small-8 columns">
            <input name="post[task_end_date]" id="task_end_date" class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date" />
        </div>
    </div>

    <div class="row task-fields">
        <div class="small-4 columns">
            <label for="post[task_type]"><?php _e('Task Type') ?><small class="required"> * </small></label>
        </div>
        <div class="small-8 columns">
          <select name="post[task_type]">
            <option value="main_task"><?php _e('Normal Task') ?></option>
            <option value="sub_task"><?php _e('Sub Task') ?></option>
          </select>
        </div>
    </div>

    <div class="row parent-task-div" style="display: none;">
        <div class="small-4 columns">
            <label for="post[task_type]"><?php _e('Parent Task') ?><small class="required"> * </small></label>
        </div>
        <div class="small-8 columns">
        <?php $rt_pm_task->rtpm_render_parent_tasks_dropdown( $project->ID ); ?>
        </div>
    </div>

    <!-- Add new task fields added along time entries -->

    <div class="row rtpm-post-author-wrapper">
        <div class="small-4 columns">
            <label for="post[post_timeentry_type]">Type<small class="required"> * </small></label>
        </div>
        <div class="small-8 columns">
            <?php $terms = get_terms( Rt_PM_Time_Entry_Type::$time_entry_type_tax, array( 'hide_empty' => false, 'order' => 'asc' ) ); ?>
            <?php if( $user_edit ): ?>
                <select required="required" name="post[post_timeentry_type]" >
                    <?php foreach ( $terms as $term ): ?>
                        <option value="<?php echo $term->slug; ?>"<?php selected( $selected_time_entry_type, $term->slug ) ?> ><?php echo $term->name; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
    </div>
    <div class="row ">
        <div class="small-4 columns">
            <label for="post[post_duration]">Time<small class="required"> * </small></label>
        </div>
        <div class="small-8 columns">
            <?php if( $user_edit ) { ?>
                <input required="required" type="number" name="post[post_duration]" step="0.25" min="0" value="<?php echo $prefilled_time_duration; ?>"  />
            <?php } ?>
        </div>
    </div>

    <div class="row">

        <div class="small-4 columns">
            <label>Date Created<small class="required"> * </small></label>
        </div>
        <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">

            <?php  if ( $user_edit ) { ?>
                <input name="post[post_date]" required="required" class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date"
                        value="<?php echo $prefilled_timestamp; ?>"
                       title="<?php echo ( isset($prefilled_timestamp) ) ? $prefilled_timestamp : ''; ?>" id="create_<?php echo $timeentry_post_type ?>_date">

            <?php } else { ?>
                <span class="rtpm_view_mode moment-from-now"><?php echo $createdate ?></span>
            <?php } ?>
        </div>
    </div>
    <div class="row">
        <div class="smlla-12 columns">

            <?php if( $user_edit ) { ?>
                <textarea required="required"  name="post[post_title]" id="new_<?php echo $timeentry_post_type ?>_title" type="text" placeholder="<?php _e("Message"); ?>" ><?php echo $prefilled_message; ?></textarea>
            <?php } else { ?>
                <span><?php echo ( isset($post->id) ) ? trim($post->message) : ""; ?></span><br /><br />
            <?php } ?>
        </div>
    </div>

    <div class="row">
        <div class="small-12 columns right">
            <input class="right" type="submit" value="Save" >
        </div>
    </div>

 <?php

 if( 'add_time_entry' ===  $_REQUEST['template'] ):
     global $wpdb;
     $table_name = rtpm_get_time_entry_table_name();
     $result     = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE task_id = {$task_id} ORDER By id DESC LIMIT 10" );

     $project_current_budget_cost = 0;
     $project_current_time_cost   = 0;
     $time_entries                = $rt_pm_time_entries_model->get_by_project_id( $post_project_id );
     if ( $time_entries['total'] && ! empty( $time_entries['result'] ) ) {
         foreach ( $time_entries['result'] as $time_entry ) {
             $type = $time_entry['type'];
             $term = get_term_by( 'slug', $type, Rt_PM_Time_Entry_Type::$time_entry_type_tax );
             $project_current_budget_cost += floatval( $time_entry['time_duration'] ) * Rt_PM_Time_Entry_Type::get_charge_rate_meta_field( $term->term_id );
             $project_current_time_cost += $time_entry['time_duration'];
         }
     } ?>

     <table>
         <thead>
         <tr>
             <th><?php _e( 'Project Cost' ) ?></th>
             <th><?php _e( 'Budget' ) ?></th>
             <th><?php _e( 'Time spent' ) ?></th>
             <th><?php _e( 'Estimated Time' ) ?></th>
         </tr>
         </thead>

         <tbody>
         <tr>
             <td><?php echo '$ ' . $project_current_budget_cost ?></td>
             <td><?php echo '$ ' . floatval( get_post_meta( $post_project_id, '_rtpm_project_budget', true ) ) ?></td>
             <td><?php echo $project_current_time_cost . __( ' hours' ) ?></td>
             <td><?php echo $rt_pm_task_resources_model->rtpm_get_estimated_hours( array( 'project_id' => $post_project_id ) ) . __( ' hours' ) ?></td>
         </tr>
         </tbody>
     </table>

     <hr/>

     <?php
     foreach ( $result as $time_entry ) : ?>

         <div class="row">
             <div class="small-3 columns">
                 <label class="rt-voxxi-label"><?php _e( 'Type', RT_PM_TEXT_DOMAIN ) ?></label>
                 <label><?php echo $time_entry->type ?></label>
             </div>
             <div class="small-3 columns">
                 <label class="rt-voxxi-label"><?php _e( 'Date', RT_PM_TEXT_DOMAIN ) ?></label>
                 <label><?php
                     $userdate = rt_convert_strdate_to_usertimestamp( $time_entry->timestamp );
                     echo $userdate->format( 'd-M-Y' );
                     ?></label>
             </div>
             <div class="small-3 columns">
                 <label class="rt-voxxi-label"><?php _e( 'Duration', RT_PM_TEXT_DOMAIN ) ?></label>
                 <label><?php echo $time_entry->time_duration ?> hours</label>
             </div>
             <div class="small-3 columns">
                 <label class="rt-voxxi-label"><?php _e( 'Logged by', RT_PM_TEXT_DOMAIN ) ?></label>
                 <label><?php echo bp_core_get_user_displayname( $time_entry->author ) ?></label>
             </div>
             <div class="small-12 columns">
                 <label class="rt-voxxi-label"><?php _e( 'Comment', RT_PM_TEXT_DOMAIN ) ?></label>
                 <label><?php echo $time_entry->message ?></label>
             </div>
         </div>
         <hr/>
     <?php endforeach;
 endif;?>
</form>
<script type="text/javascript">

    var jq = $ = jQuery.noConflict();
    var wall_time_entries;

    (function($){
        wall_time_entries = {

          //Wall time entries sidebar init
          init: function() {

              wall_time_entries.task_date_fields();
              wall_time_entries.parent_task_type_fields();

              $(document).on( 'change', 'select[name="post[post_task_id]"]', wall_time_entries.task_date_fields );
              $(document).on( 'change', 'select[name="post[task_type]"]', wall_time_entries.parent_task_type_fields );
          },

          //Show task start date and task end date field
          task_date_fields: function() {
              var $select_task_id = $('select[name="post[post_task_id]"]');

              if( 'add-time' == $select_task_id.val() ) {

                  $('.task-fields').show();

                  //Add required attribute
                  $("#task_start_date").attr( 'required', 'required' );
                  $("#task_end_date").attr( 'required', 'required' );
              } else {

                  $('.task-fields').hide();

                  //Remove required attribute
                  $("#task_start_date").removeAttr( 'required' );
                  $("#task_end_date").removeAttr( 'required' );
              }

              wall_time_entries.parent_task_type_fields();

          },

          //Show/hide parent task drowpdown on Task Type selection
          parent_task_type_fields: function() {
            var $task_type = $('select[name="post[task_type]"]');

            var $parent_task_div = $('div.parent-task-div');

            if ( 'sub_task' == $task_type.val() && $task_type.is(':visible') ) {
                $parent_task_div.show();
            } else {
                $parent_task_div.hide();
            }
          }
        };

        $(document).ready( function( e ) { wall_time_entries.init(); } );
    })(jQuery);
</script>