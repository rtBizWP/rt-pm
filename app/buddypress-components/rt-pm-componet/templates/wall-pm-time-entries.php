<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 12/12/14
 * Time: 10:43 PM
 */

global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_task,$rt_pm_time_entries,$rt_pm_time_entries_model, $rtpm_task_list;

$task_id = $_REQUEST["id"];
$user_edit = true;
$task_post_type=$rt_pm_task->post_type;
$timeentry_labels = $rt_pm_time_entries->labels;
$timeentry_post_type = Rt_PM_Time_Entries::$post_type;

if ( ! function_exists( 'get_current_screen' ) ){
    if ( ! isset( $GLOBALS['hook_suffix'] ) )
        $GLOBALS['hook_suffix'] = '';
    require_once(ABSPATH . 'wp-admin/includes/screen.php');
}
?>
<form method="post"  action="">

    <input type="hidden" name="post[action]" value="<?php echo $_GET['action'] ?>" />
    <input type="hidden" name="post[template]" value="<?php echo $_GET['template'] ?>" />
    <input type="hidden" name="post[actvity_element_id]" value="<?php echo $_GET['actvity_element_id'] ?>" />
    <input type="hidden" name="post[rt_voxxi_blog_id]" value="<?php echo $_GET['rt_voxxi_blog_id'] ?>" />
    <input type="hidden" name="post[post_type]" value="<?php echo $timeentry_post_type; ?>" />


    <div class="row">
        <div class="small-10 columns">
            <h2><?php _e('Time entry') ?></h2>
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
            <?php
            echo '<select required="required" name="post[post_task_id]" id="task_id">';

            $query = new WP_Query( array(
                'post_type' => $rt_pm_task->post_type,
                'no_found_rows' => true,
            ));

            $records = $query->posts;
            if ( ! empty( $records ) ) {
                foreach( $records as $rec ) {

                    echo '<option value="'.$rec->ID.'" >'.$rec->post_title.'</option>';
                }
            }

            echo '</select>';
            ?>
        </div>
    </div>
    <div class="row rtpm-post-author-wrapper">
        <div class="small-4 columns">
            <label for="post[post_timeentry_type]">Type<small class="required"> * </small></label>
        </div>
        <div class="small-8 columns">
            <?php $terms = get_terms( Rt_PM_Time_Entry_Type::$time_entry_type_tax, array( 'hide_empty' => false, 'order' => 'asc' ) ); ?>
            <?php if( $user_edit ) { ?>
                <select required="required" name="post[post_timeentry_type]" >
                    <?php foreach ( $terms as $term ) { ?>
                        <option value="<?php echo $term->slug; ?>" ><?php echo $term->name; ?></option>
                    <?php } ?>
                </select>
            <?php } ?>
        </div>
    </div>
    <div class="row ">
        <div class="small-4 columns">
            <label for="post[post_duration]">Time<small class="required"> * </small></label>
        </div>
        <div class="small-8 columns">
            <?php if( $user_edit ) { ?>
                <input required="required" type="number" name="post[post_duration]" step="0.25" min="0"  />
            <?php } ?>
        </div>
        </div>

    <div class="row">

        <div class="small-4 columns">
            <label>Date Created<small class="required"> * </small></label>
        </div>
        <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
            <?php if( $user_edit && empty( $_REQUEST['rt_time_entry_id'] ) ){ ?>
                <input required="required" class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date"

                       title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" id="create_<?php echo $timeentry_post_type ?>_date">
                <input name="post[post_date]" type="hidden" value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" />
            <?php } else if ( $user_edit ) { ?>
                <input disabled="disabled" class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date"

                       title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" id="create_<?php echo $timeentry_post_type ?>_date">
                <input name="post[post_date]" type="hidden" value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" />
            <?php } else { ?>
                <span class="rtpm_view_mode moment-from-now"><?php echo $createdate ?></span>
            <?php } ?>
        </div>
    </div>
    <div class="row">
        <div class="smlla-12 columns">

            <?php if( $user_edit ) { ?>
                <textarea required="required"  name="post[post_title]" id="new_<?php echo $timeentry_post_type ?>_title" type="text" placeholder="<?php _e("Message"); ?>" ><?php echo ( isset($post->id) ) ? $post->message : ""; ?> </textarea>
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
</form>