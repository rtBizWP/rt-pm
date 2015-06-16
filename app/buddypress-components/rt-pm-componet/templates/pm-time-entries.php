<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 1/4/15
 * Time: 1:20 PM
 */
global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_task,$rt_pm_time_entries,$rt_pm_time_entries_model;

if( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] != $rt_pm_project->post_type ) {
    wp_die("Opsss!! You are in restricted area");
}

$author_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'author' );
if( current_user_can( $author_cap ) ){

    $user_edit = true;
}else {

    $user_edit = false;
}

$post_type=$_REQUEST['post_type'];
$project_id = $_REQUEST["{$post_type}_id"];
$task_post_type=$rt_pm_task->post_type;
$timeentry_labels = $rt_pm_time_entries->labels;
$timeentry_post_type = Rt_PM_Time_Entries::$post_type;

//Trash action
if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' && isset( $_REQUEST[$timeentry_post_type.'_id'] ) ) {
    $rt_pm_time_entries_model->delete_timeentry( array( 'id' => $_REQUEST[$timeentry_post_type.'_id'] ) );
    echo '<script> window.location="' . $rt_pm_bp_pm->get_component_root_url().bp_current_action(). '?post_type='.$post_type.'&page=rtpm-add-'.$post_type.'&'.$post_type.'_id='.$_REQUEST[$post_type.'_id'].'&tab='.$post_type.'-timeentry' . '"; </script> ';
    die();
}

if ( isset( $action_complete ) && $action_complete ){
    if (isset($_REQUEST["new"])) {
        ?>
        <div class="alert-box success">
            <?php _e('New '.  ucfirst($timeentry_labels['name']).' Inserted Sucessfully.'); ?>
            <a href="#" class="close">&times;</a>
        </div>
    <?php
    }
    if(isset($updateFlag) && $updateFlag){ ?>
        <div class="alert-box success">
            <?php _e(ucfirst($timeentry_labels['name'])." Updated Sucessfully."); ?>
            <a href="#" class="close">&times;</a>
        </div>
    <?php }
}

$form_ulr = $rt_pm_bp_pm->get_component_root_url().bp_current_action() . "?post_type={$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-timeentry";//&{$task_post_type}_id={$_REQUEST["{$task_post_type}_id"]}
///alert Notification
if (isset($_REQUEST["{$timeentry_post_type}_id"])) {
    $form_ulr .= "&{$timeentry_post_type}_id=" . $_REQUEST["{$timeentry_post_type}_id"];
    $post = $rt_pm_time_entries_model->get_timeentry($_REQUEST["{$timeentry_post_type}_id"]);
    if (!$post) {
        ?>
        <div class="alert-box alert">
            Invalid Post ID
            <a href="" class="close">&times;</a>
        </div>
        <?php
        $post = false;
    }

    $create = rt_convert_strdate_to_usertimestamp($post->timestamp);
    $createdate = $create->format("M d, Y h:i A");
}

//Selected task for time entry
$selected_task_id = '';
if ( isset ( $_REQUEST["task_id"] ) ) {
    $selected_task_id= $_REQUEST["task_id"];
}
?>

<?php if (isset($post->id) || ( isset( $_REQUEST["action"] ) && $_REQUEST["action"]=="timeentry")){?>
    <script>
        jQuery(document).ready(function($) {
            setTimeout(function() {
                jQuery(".add-time-entry").trigger('click');
            },10);
        });
    </script>
<?php } ?>

<div id="wp-custom-list-table">
    <?php
    if( $user_edit ) {
        ?>
        <div class="list-heading">
            <div class="large-8 columns list-title">
                <h2><?php _e( '#'.get_post_meta(  $project_id, 'rtpm_job_no', true ).' '. get_post_field( 'post_title', $project_id ), RT_PM_TEXT_DOMAIN );?></h2>
            </div>
            <div class="large-4 columns">
                <a class="right button rtpm_new_time_entry"><?php _e( 'Time and Expenses', RT_PM_TEXT_DOMAIN ); ?></a>
            </div>

        </div>
    <?php
    }


    ?>

    <div id="rtpm_project_cost_report" class="row">
        <?php $rt_pm_time_entries->rtpm_project_summary_markup( $project_id ) ?>
    </div>

    <?php
    $rtpm_bp_pm_time_entry_list = new Rt_PM_BP_PM_Time_Entry_List_View();
    $rtpm_bp_pm_time_entry_list->prepare_items();
    $rtpm_bp_pm_time_entry_list->display();
    ?>
</div>

<!-- Open task in sidr panel -->
<script type="text/javascript">
    var rtpm_timentries;

    (function( $ ) {
        rtpm_timentries = {
            init: function() {
                $( document).on( 'click', 'a.rtpm_timeentry_edit', rtpm_timentries.edit_timeentries_side_panel );
                $( document).on( 'click', 'a.rtpm_new_time_entry', rtpm_timentries.new_timeentries_side_panel );
            },

            edit_timeentries_side_panel: function( e ) {
                e.preventDefault();

                block_ui();
                $element = $( this );
                $url = $element.attr('href');

                var timeentry_id = get_parameter_by_name( $url, 'rt_time_entry_id' );
                render_project_slide_panel( 'edit_time_entry', timeentry_id, <?php echo get_current_blog_id(); ?>, '', 'time-entries' );
            },

            new_timeentries_side_panel: function( e ) {
                e.preventDefault();

                block_ui();
                $element = $( this );
                $url = $element.attr('href');

                render_project_slide_panel( 'new_time_entry', <?php echo $project_id ?>, <?php echo get_current_blog_id(); ?>, '', 'time-entries' );
            }
        };

        $( document).ready( function() { rtpm_timentries.init() } );
    })(jQuery);
</script>

<!--reveal-modal-add-task -->
<div id="div-add-time-entry" class="reveal-modal medium" data-reveal>

        <form method="post" id="form-add-post" data-posttype="<?php echo $timeentry_post_type; ?>" action="<?php echo $form_ulr; ?>">
            <?php wp_nonce_field( 'rtpm_save_timeentry', 'rtpm_save_timeentry_nonce' ); ?>
            <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $_REQUEST["{$post_type}_id"]; ?>" />
            <?php if (isset($post->id) && $user_edit ) { ?>
                <input type="hidden" name="post[post_id]" id='task_id' value="<?php echo $post->id; ?>" />
            <?php } ?>
           <h4><?php _e( 'Time Entry', RT_PM_TEXT_DOMAIN ); ?></h4>
            <br/>
            <div class="row collapse">
                <div class="large-2 mobile-large-2 columns">
                    <label for="Task">Task<small class="required"> * </small></label>
                </div>
                <div class="large-10 mobile-large-6 columns">
                    <?php
                        $rt_pm_task->rtpm_tasks_dropdown( $project_id, $selected_task_id );
                    ?>
                </div>
            </div>
            <div class="row collapse rtpm-post-author-wrapper">
                <div class="large-2 mobile-large-2 columns">
                    <label for="post[post_timeentry_type]">Type<small class="required"> * </small></label>
                </div>
                <div class="large-10 mobile-large-6 columns">
                    <?php $terms = get_terms( Rt_PM_Time_Entry_Type::$time_entry_type_tax, array( 'hide_empty' => false, 'order' => 'asc' ) ); ?>
                    <?php if( $user_edit ) { ?>
                        <select required="required" name="post[post_timeentry_type]" >
                            <?php foreach ( $terms as $term ) { ?>
                                <option <?php echo isset($post) && $post->type == $term->slug ? 'selected="selected"' :''; ?> value="<?php echo $term->slug; ?>" ><?php echo $term->name; ?></option>
                            <?php } ?>
                        </select>
                    <?php } ?>
                </div>
            </div>
            <div class="row collapse">
                <div class="large-2 mobile-large-2 columns">
                    <label for="post[post_duration]">Time<small class="required"> * </small></label>
                </div>
                <div class="large-3 mobile-large-3 columns">
                    <?php if( $user_edit ) { ?>
                        <input required="required" type="number" name="post[post_duration]" step="0.25" min="0" value="<?php echo ( isset( $post ) ) ? $post->time_duration : ''; ?>" />
                    <?php } ?>
                </div>
                <div class="large-1 mobile-large-1 columns">&nbsp;</div>
                <div class="large-2 mobile-large-4 columns">
                    <label>Date Created<small class="required"> * </small></label>
                </div>
                <div class="large-4 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                    <?php if( $user_edit && empty( $_REQUEST['rt_time_entry_id'] ) ){ ?>
                        <input required="required" class="datetimepicker moment-from-now" name="post[post_date]" type="text" placeholder="Select Create Date"
                               value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                               title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" id="create_<?php echo $timeentry_post_type ?>_date">

                    <?php } else if ( $user_edit ) { ?>
                        <input class="datetimepicker moment-from-now" type="text"   name="post[post_date]" placeholder="Select Create Date"
                               value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                               title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" id="create_<?php echo $timeentry_post_type ?>_date">

                    <?php } else { ?>
                        <span class="rtpm_view_mode moment-from-now"><?php echo $createdate ?></span>
                    <?php } ?>
                </div>
            </div>
            <div class="row collapse postbox">
                <div class="large-12 columns">
                    <label>Message<small class="required"> * </small></label>
                    <?php if( $user_edit ) { ?>
                        <textarea required="required"  name="post[post_title]" id="new_<?php echo $timeentry_post_type ?>_title" type="text" placeholder="<?php _e("Message"); ?>" ><?php echo ( isset($post->id) ) ? $post->message : ""; ?> </textarea>
                    <?php } else { ?>
                        <span><?php echo ( isset($post->id) ) ? trim($post->message) : ""; ?></span><br /><br />
                    <?php } ?>
                </div>
            </div>
            <?php
            if (isset($_REQUEST["{$timeentry_post_type}_id"])) {
                $btntitle = __( 'Save Time Entry' );
            } else {
                $btntitle = __( 'Add Time Entry' );
            }
            ?>
            <button class="mybutton right" type="submit" id="save-task"><?php _e( $btntitle ); ?></button>
        </form>

    <a class="close-reveal-modal">&#215;</a>
</div>
