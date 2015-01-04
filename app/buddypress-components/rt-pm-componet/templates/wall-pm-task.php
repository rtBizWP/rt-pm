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

if( isset( $_GET["id"] ) ){

    $post_id = $_GET["id"];


    $post = get_post( $post_id );


// get project meta
    if (isset($post->ID)) {
        $due = rt_convert_strdate_to_usertimestamp(get_post_meta($post->ID, 'post_duedate', true));
        $due_date = $due->format("M d, Y h:i A");
        $post_assignee = get_post_meta($post->ID, 'post_assignee', true);
        $post_project_id = get_post_meta($post->ID, 'post_project_id', true);
        $create = rt_convert_strdate_to_usertimestamp( $post->post_date_gmt );
        $modify = rt_convert_strdate_to_usertimestamp( $post->post_modified_gmt );
        $createdate = $create->format("M d, Y h:i A");
        $modifydate = $modify->format("M d, Y h:i A");
    } else {
        $post_assignee = $post_id;
    }

}else if( isset( $_GET['project_id'] ) ){

    $post_assignee = $_GET['user_id'];
    $post_project_id = $_GET['project_id'];

}


//assign to
$results_member = Rt_PM_Utils::get_pm_rtcamp_user();

$task_labels=$rt_pm_task->labels;
?>
<form method="post"   action="">
    <?php wp_nonce_field('rt_pm_task_edit','rt_pm_task_edit') ?>
    <?php if( isset( $_GET["id"] ) ){ ?>
    <input type="hidden" name="post[action]" value="<?php echo $_GET['action'] ?>" />
    <input type="hidden" name="post[template]" value="<?php echo $_GET['template'] ?>" />
    <input type="hidden" name="post[actvity_element_id]" value="<?php echo $_GET['actvity_element_id'] ?>" />
    <?php } ?>

    <input type="hidden" name="post[rt_voxxi_blog_id]" value="<?php echo $_GET['rt_voxxi_blog_id'] ?>" />
    <input type="hidden" name="post[post_type]" value="<?php echo $task_post_type; ?>" />

	                    <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $post_project_id; ?>" />
	                    <?php if (isset($post->ID) && $user_edit ) { ?>
    <input type="hidden" name="post[post_id]" id='task_id' value="<?php echo $post->ID; ?>" />
<?php } ?>

    <div class="row">
        <div class="small-10 columns">
            <h2><?php echo ( isset($post->ID) ) ? $post->post_title : "Task"; ?></h2>
        </div>
        <div class="small-2 columns">
            <a title="Close" class="right close-sidepanel"><i class="fa fa-caret-square-o-right fa-2x"></i></a>
        </div>
    </div>

    <div class="row column-title">
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
        <div class="small-4 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
            <label for="post[post_status]"><?php _e("Status"); ?></label>
        </div>
        <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
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
        <div  class="small-4 columns">
            <span class="hidden" title="Create Date"><label>Create Date<small class="required"> * </small></label></span>
        </div>
        <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">

            <?php if( $user_edit ) { ?>
                <input required="required" class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date"
                       value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                       title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" id="create_<?php echo $task_post_type ?>_date">
                <input name="post[post_date]" type="hidden" value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" />
            <?php } else { ?>
                <span class="rtpm_view_mode moment-from-now"><?php echo $createdate ?></span>
            <?php } ?>
        </div>
    </div>

    <div class="row">
        <div  class="small-4 columns">
            <span class="assigned-to-hidden" title="Assigned To"><label for="post[post_assignee]">Assigned To<small class="required"> * </small></label></span>
        </div>
        <div class="small-8 columns">
        <?php if( $user_edit ) { ?>
            <select required="required" name="post[post_assignee]" >
                <option value=""><?php _e( 'Select Assignee' ); ?></option>
                <?php
                if (!empty($results_member)) {
                    foreach ($results_member as $author) {
                        if ($author->ID == $post_assignee) {
                            $selected = " selected";
                        } else {
                            $selected = " ";
                        }
                        echo '<option value="' . $author->ID . '"' . $selected . '>' . $author->display_name . '</option>';
                    }
                }
                ?>
            </select>
        <?php } ?>
        </div>
   </div>

    <div class="row">
        <div  class="small-4 columns">
            <span class="due-date-hidden" title="Due Date"><label>Due Date<small class="required"> * </small></label></span>
        </div>
        <div class="small-8 columns">
            <?php if( $user_edit ) { ?>
                <input class="datetimepicker moment-from-now" type="text" placeholder="Select Due Date"
                       value="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>"
                       title="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>" id="due_<?php echo $task_post_type ?>_date">
                <input name="post[post_duedate]" type="hidden" value="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>" />
            <?php } else { ?>
                <span class="rtpm_view_mode moment-from-now"><?php echo $duedate ?></span>
            <?php } ?>
        </div>
   </div>

    <?php   if( isset( $post_id ) ){ ?>
    <h3><?php _e('Attachments'); ?></h3>
    <hr/>
    <?php render_rt_bp_wall_documents_section( $post_id );
    }
    ?>


    <div class="row">
        <div class="small-12 columns right">
            <input class="right" type="submit" value="Save" >
        </div>
    </div>

</form>