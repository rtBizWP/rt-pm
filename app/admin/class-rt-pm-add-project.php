<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rt_PM_Project_List_View
 *
 * @author dipesh
 */

if ( !class_exists( 'Rt_PM_Add_Project' ) ) {
	class Rt_PM_Add_Project  {

		public function __construct() {

		}

        public function get_project_task_tab($labels,$user_edit){
            global $rt_pm_project,$rt_pm_task;
            $post_type=$_REQUEST['post_type'];
            $task_post_type=$rt_pm_task->post_type;
            $task_labels=$rt_pm_task->labels;

            //Check Post object is init or not
            if ( isset( $_POST['post'] ) ) {
                $newTask = $_POST['post'];
                $creationdate = $newTask['post_date'];
                if ( isset( $creationdate ) && $creationdate != '' ) {
                    try {
                        $dr = date_create_from_format( 'M d, Y H:i A', $creationdate );
                        $UTC = new DateTimeZone('UTC');
                        $dr->setTimezone($UTC);
                        $timeStamp = $dr->getTimestamp();
                        $newTask['post_date'] = gmdate('Y-m-d H:i:s', (intval($timeStamp) + ( get_option('gmt_offset') * 3600 )));
                        $newTask['post_date_gmt'] = gmdate('Y-m-d H:i:s', (intval($timeStamp)));
                    } catch ( Exception $e ) {
                        $newTask['post_date'] = current_time( 'mysql' );
                        $newTask['post_date_gmt'] = gmdate('Y-m-d H:i:s');
                    }
                } else {
                    $newTask['post_date'] = current_time( 'mysql' );
                    $newTask['post_date_gmt'] = gmdate('Y-m-d H:i:s');
                }

                // Post Data to be saved.
                $post = array(
                    'post_author' => $newTask['post_author'],
                    'post_content' => $newTask['post_content'],
                    'post_status' => $newTask['post_status'],
                    'post_title' => $newTask['post_title'],
                    'post_date' => $newTask['post_date'],
                    'post_date_gmt' => $newTask['post_date_gmt'],
                    'post_type' => $task_post_type
                );

                $updateFlag = false;
                //check post request is for Update or insert
                if ( isset($newTask['post_id'] ) ) {
                    $updateFlag = true;
                    $post = array_merge( $post, array( 'ID' => $newTask['post_id'] ) );
                    $data = array(
                        'post_project_id' => $newTask['post_project_id'],
                        'post_duedate' => $newTask['post_duedate'],
                        'post_time_tracker' => $newTask['post_time_tracker'],
                        'date_update' => current_time( 'mysql' ),
                        'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                        'user_updated_by' => get_current_user_id(),
                    );
                    $post_id = @wp_update_post( $post );
                    foreach ( $data as $key=>$value ) {
                        update_post_meta( $post_id, $key, $value );
                    }
                }else{
                    $data = array(
                        'post_project_id' => $newTask['post_project_id'],
                        'post_duedate' => $newTask['post_duedate'],
                        'post_time_tracker' => $newTask['post_time_tracker'],
                        'date_update' => current_time( 'mysql' ),
                        'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                        'user_updated_by' => get_current_user_id(),
                        'user_created_by' => get_current_user_id(),
                    );
                    $post_id = @wp_insert_post($post);
                    foreach ( $data as $key=>$value ) {
                        update_post_meta( $post_id, $key, $value );
                    }
                }
            }

            //Check for wp error
            if ( is_wp_error( $post_id ) ) {
                wp_die( 'Error while creating new '. ucfirst( $rt_pm_project->labels['name'] ) );
            }

            $form_ulr = admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-task");
            ///alert Notification
            if (isset($_REQUEST["{$task_post_type}_id"])) {
                $form_ulr .= "&{$task_post_type}_id=" . $_REQUEST["{$task_post_type}_id"];
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

                $create = new DateTime($post->post_date);

                $modify = new DateTime($post->post_modified);
                $createdate = $create->format("M d, Y h:i A");
                $modifydate = $modify->format("M d, Y h:i A");

            }

            // get project meta
            if (isset($post->ID)) {
                $post_author = $post->post_author;
                $due = new DateTime(get_post_meta($post->ID, 'post_duedate', true));
                $duedate = $due->format("M d, Y h:i A");
            } else {
                $post_author = get_current_user_id();
            }

            //assign to
            $results_member = Rt_PM_Utils::get_pm_rtcamp_user();
            ?>

            <div id="wp-custom-list-table" class="row">
                <div class="row">
                    <button class="right mybutton add-task" type="button" ><?php _e('Add Task'); ?></button>
                </div>

                <div class="row">
                    <?php
                    $rtpm_task_list= new Rt_PM_Task_List_View();
                    $rtpm_task_list->prepare_items($_REQUEST["{$post_type}_id"]);
                    $rtpm_task_list->display();
                    ?>
                </div>
            </div>

            <!--reveal-modal-add-task -->
            <div id="div-add-task" class="reveal-modal large">
                <fieldset>
                    <legend><h4><i class="foundicon-address-book"></i> Add New Task</h4></legend>
                    <form method="post" id="form-add-task" action="<?php echo $form_ulr; ?>">
                        <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $_REQUEST["{$post_type}_id"]; ?>" />
                        <?php if (isset($post->ID) && $user_edit ) { ?>
                            <input type="hidden" name="post[post_id]" id='task_id' value="<?php echo $post->ID; ?>" />
                        <?php } ?>
                        <div class="row collapse postbox">
                            <div class="large-12 columns">
                                <?php if( $user_edit ) { ?>
                                    <input name="post[post_title]" id="new_<?php echo $task_post_type ?>_title" type="text" placeholder="<?php _e(ucfirst($task_labels['name'])." Name"); ?>" value="<?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?>" />
                                <?php } else { ?>
                                    <span><?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?></span><br /><br />
                                <?php } ?>
                            </div>
                        </div>
                        <div class="row collapse rtpm-lead-content-wrapper">
                            <div class="large-12 columns">
                                <?php
                                if( $user_edit ) {
                                    wp_editor( ( isset( $post->ID ) ) ? $post->post_content : "", "post_content", array( 'textarea_name' => 'post[post_content]' ) );
                                } else {
                                    echo ucfirst($labels['name']).' Content : <br /><br /><span>'.(( isset($post->ID) ) ? $post->post_content : '').'</span><br /><br />';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="row collapse rtpm-post-author-wrapper">
                            <div class="large-2 small-4 columns">
                                <span class="prefix" title="Create Date"><label>Create Date</label></span>
                            </div>
                            <div class="large-3 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                <?php if( $user_edit ) { ?>
                                    <input class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date"
                                           value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                                           title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>">
                                    <input name="post[post_date]" type="hidden" value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" />
                                <?php } else { ?>
                                    <span class="rtcrm_view_mode moment-from-now"><?php echo $createdate ?></span>
                                <?php } ?>
                            </div>
                            <div class="large-1 mobile-large-1 columns">
                                <span class="postfix datepicker-toggle" data-datepicker="closing-date"><label class="foundicon-calendar"></label></span>
                            </div>
                            <div class="large-3 mobile-large-1 columns">
                                <span class="prefix" title="Assigned To"><label for="post[post_author]"><strong>Assigned To</strong></label></span>
                            </div>
                            <div class="large-3 mobile-large-3 columns">
                                <?php if( $user_edit ) { ?>
                                    <select name="post[post_author]" >
                                        <?php
                                        if (!empty($results_member)) {
                                            foreach ($results_member as $author) {
                                                if ($author->ID == $post_author) {
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
                        <div class="row collapse rtpm-post-author-wrapper">
                            <div class="large-2 small-4 columns">
                                <span class="prefix" title="Due Date"><label>Due Date</label></span>
                            </div>
                            <div class="large-3 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                <?php if( $user_edit ) { ?>
                                    <input class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date"
                                           value="<?php echo ( isset($duedate) ) ? $duedate : ''; ?>"
                                           title="<?php echo ( isset($duedate) ) ? $duedate : ''; ?>">
                                    <input name="post[post_duedate]" type="hidden" value="<?php echo ( isset($duedate) ) ? $duedate : ''; ?>" />
                                <?php } else { ?>
                                    <span class="rtcrm_view_mode moment-from-now"><?php echo $duedate ?></span>
                                <?php } ?>
                            </div>
                            <div class="large-1 mobile-large-1 columns">
                                <span class="postfix datepicker-toggle" data-datepicker="closing-date"><label class="foundicon-calendar"></label></span>
                            </div>
                            <div class="large-3 mobile-large-1 columns">
                                <span class="prefix" title="Assigned To"><label for="post[post_time_tracker]"><strong>Time Tracker</strong></label></span>
                            </div>
                            <div class="large-3 mobile-large-3 columns">
                                <?php if( $user_edit ) { ?>
                                    <select name="post[post_time_tracker]" >
                                        <option value="office_time" >Office Time</option>
                                        <option value="field_time" >Field Time</option>
                                    </select>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="row collapse postbox">
                            <div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br /></div>
                            <h6 class="hndle"><span><i class="foundicon-paper-clip"></i> <?php _e('Attachments'); ?></span></h6>
                            <div class="inside">
                                <div class="row collapse" id="attachment-container">
                                    <?php if( $user_edit ) { ?>
                                        <a href="#" class="button" id="add_lead_attachment"><?php _e('Add'); ?></a>
                                    <?php } ?>
                                    <div class="scroll-height">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="mybutton right" type="submit" id="save-task">Save task</button>
                    </form>
                </fieldset>
                <a class="close-reveal-modal">×</a>
            </div>

        <?php }

        public function get_project_timeentry_tab($labels,$user_edit){
            global $rt_pm_project,$rt_pm_task,$rt_pm_time_entries,$rt_pm_time_entries_model;
            $post_type=$_REQUEST['post_type'];
            $task_post_type=$rt_pm_task->post_type;
            $timeentry_labels = $rt_pm_time_entries->labels;
            $timeentry_post_type = $rt_pm_time_entries->post_type;

            //Check Post object is init or not
            if ( isset( $_POST['post'] ) ) {
                $newTimeEntry = $_POST['post'];
                $creationdate = $newTimeEntry['post_date'];
                if ( isset( $creationdate ) && $creationdate != '' ) {
                    try {
                        $dr = date_create_from_format( 'M d, Y H:i A', $creationdate );
                        $UTC = new DateTimeZone('UTC');
                        $dr->setTimezone($UTC);
                        $timeStamp = $dr->getTimestamp();
                        $newTimeEntry['post_date'] = gmdate('Y-m-d H:i:s', (intval($timeStamp) + ( get_option('gmt_offset') * 3600 )));
                    } catch ( Exception $e ) {
                        $newTimeEntry['post_date'] = current_time( 'mysql' );
                    }
                } else {
                    $newTimeEntry['post_date'] = current_time( 'mysql' );
                }

                // Post Data to be saved.
                $post = array(
                    'project_id' => $newTimeEntry['post_project_id'],
                    'task_id' => $newTimeEntry['post_task_id'],
                    'type' => $newTimeEntry['post_timeentry_type'],
                    'message' => $newTimeEntry['post_title'],
                    'time_duration' => $newTimeEntry['post_duration'],
                    'timestamp' => $newTimeEntry['post_date'],
                    'author' => get_current_user_id(),
                );
                $updateFlag = false;
                //check post request is for Update or insert
                if ( isset($newTimeEntry['post_id'] ) ) {
                    $updateFlag = true;
                    $where = array( 'id' => $newTimeEntry['post_id'] );
                    $post_id = $rt_pm_time_entries_model->update_timeentry($post,$where);
                }else{
                    $post_id = $rt_pm_time_entries_model->add_timeentry($post);
                }
            }

            //Check for wp error
            /*if ( is_wp_error( $post_id ) ) {
                wp_die( 'Error while creating new '. ucfirst( $rt_pm_project->labels['name'] ) );
            }*/

            $form_ulr = admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-timeentry");//&{$task_post_type}_id={$_REQUEST["{$task_post_type}_id"]}

            ///alert Notification
            if (isset($_REQUEST["{$timeentry_post_type}_id"])) {
                $form_ulr .= "&{$timeentry_post_type}_id=" . $_REQUEST["{$timeentry_post_type}_id"];
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

                $create = new DateTime($post->timestamp);
                $createdate = $create->format("M d, Y h:i A");
            }

            // get project meta
            if (isset($post->id)) {
                $post_author = $post->author;
                $_REQUEST["{$post_type}_id"]=$post->project_id;
                $_REQUEST["{$task_post_type}_id"]=$post->task_id;
            } else {
                $post_author = get_current_user_id();
            }
            ?>

            <div id="wp-custom-list-table" class="row">
                <div class="row">
                    <button class="right mybutton add-time-entry" type="button" ><?php _e('Add Time Entry'); ?></button>
                </div>

                <div class="row">
                    <?php
                    $rtpm_time_entry_list= new Rt_PM_Time_Entry_List_View();
                    $rtpm_time_entry_list->prepare_items($_REQUEST["{$post_type}_id"]);
                    $rtpm_time_entry_list->display();
                    ?>
                </div>
            </div>

            <!--reveal-modal-add-task -->
            <div id="div-add-time-entry" class="reveal-modal large">
                <fieldset>
                    <legend><h4><i class="foundicon-address-book"></i> Add New Time Entry</h4></legend>
                    <form method="post" id="form-add-task" action="<?php echo $form_ulr; ?>">
                        <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $_REQUEST["{$post_type}_id"]; ?>" />
                        <?php if (isset($post->id) && $user_edit ) { ?>
                            <input type="hidden" name="post[post_id]" id='task_id' value="<?php echo $post->id; ?>" />
                        <?php } ?>
                        <div class="row collapse">
                            <?php
                            $rtpm_task_list= new Rt_PM_Task_List_View();
                            $rtpm_task_list->prepare_items($_REQUEST["{$post_type}_id"]);
                            $rtpm_task_list->get_drop_down($_REQUEST["{$task_post_type}_id"]);
                            ?>
                        </div>
                        <div class="row collapse">
                            <div class="large-2 small-4 columns">
                                <span class="prefix" title="Create Date"><label>Create Date</label></span>
                            </div>
                            <div class="large-3 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                <?php if( $user_edit ) { ?>
                                    <input class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date"
                                           value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                                           title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>">
                                    <input name="post[post_date]" type="hidden" value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" />
                                <?php } else { ?>
                                    <span class="rtcrm_view_mode moment-from-now"><?php echo $createdate ?></span>
                                <?php } ?>
                            </div>
                            <div class="large-1 mobile-large-1 columns">
                                <span class="postfix datepicker-toggle" data-datepicker="closing-date"><label class="foundicon-calendar"></label></span>
                            </div>
                            <div class="large-3 mobile-large-1 columns">
                                <span class="prefix" title="Assigned To"><label for="post[post_timeentry_type]"><strong>Type</strong></label></span>
                            </div>
                            <div class="large-3 mobile-large-3 columns">
                                <?php if( $user_edit ) { ?>
                                    <select name="post[post_timeentry_type]" >
                                        <option <?php echo isset($post) && $post->type == 'regular' ? 'selected="selected"' :''; ?> value="regular" >Regular</option>
                                        <option <?php echo isset($post) && $post->type == 'over_time' ? 'selected="selected"' :''; ?> value="over_time" >OverTime</option>
                                    </select>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="row collapse rtpm-post-author-wrapper">
                            <div class="large-3 mobile-large-1 columns">
                                <span class="prefix" title="Assigned To"><label for="post[post_duration]"><strong>Duration</strong></label></span>
                            </div>
                            <div class="large-3 mobile-large-3 columns">
                                <?php if( $user_edit ) { ?>
                                    <select name="post[post_duration]" >
                                        <option <?php echo isset($post) && $post->time_duration == 0.25 ? 'selected="selected"' :''; ?> value="0.25" >15 min</option>
                                        <option <?php echo isset($post) && $post->time_duration == 0.50 ? 'selected="selected"' :''; ?> value="0.50" >30 min</option>
                                    </select>
                                <?php } ?>
                            </div>
                            <div class="large-6 mobile-large-1 columns"></div>
                            <!--<div class="large-3 mobile-large-1 columns">
                                <span class="prefix" title="Assigned To"><label for="post[post_author]"><strong>Assigned To</strong></label></span>
                            </div>
                            <div class="large-3 mobile-large-3 columns">
                                <?php /*if( $user_edit ) { */?>
                                    <select name="post[post_author]" >
                                        <?php
                            /*                                        if (!empty($results_member)) {
                                                                        foreach ($results_member as $author) {
                                                                            if ($author->ID == $post_author) {
                                                                                $selected = " selected";
                                                                            } else {
                                                                                $selected = " ";
                                                                            }
                                                                            echo '<option value="' . $author->ID . '"' . $selected . '>' . $author->display_name . '</option>';
                                                                        }
                                                                    }
                                                                    */?>
                                    </select>
                                <?php /*} */?>
                            </div>-->
                        </div>
                        <div class="row collapse postbox">
                            <div class="large-12 columns">
                                <?php if( $user_edit ) { ?>
                                    <input name="post[post_title]" id="new_<?php echo $timeentry_post_type ?>_title" type="text" placeholder="<?php _e("Message"); ?>" value="<?php echo ( isset($post->id) ) ? $post->message : ""; ?>" />
                                <?php } else { ?>
                                    <span><?php echo ( isset($post->id) ) ? $post->message : ""; ?></span><br /><br />
                                <?php } ?>
                            </div>
                        </div>
                        <button class="mybutton right" type="submit" id="save-task">Save TimeEntry</button>
                    </form>
                </fieldset>
                <a class="close-reveal-modal">×</a>
            </div>

        <?php }

        public function get_project_description_tab($labels,$user_edit){
            global $rt_pm_project,$rt_crm_closing_reason,$rt_pm_project_type;

            if( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] != $rt_pm_project->post_type ) {
                wp_die("Opsss!! You are in restricted area");
            }

            $post_type=$_REQUEST['post_type'];

            //Trash action
            if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'trash' && isset( $_REQUEST[$post_type.'_id'] ) ) {
                wp_trash_post( $_REQUEST[$post_type.'_id'] );
                $return = wp_redirect( admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-all-'.$post_type ) );
                if( !$return ) {
                    echo '<script> window.location="' . admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-all-'.$post_type ) . '"; </script> ';
                }
            }

            //Check Post object is init or not
            if ( isset( $_POST['post'] ) ) {
                $newProject = $_POST['post'];
                $creationdate = $newProject['post_date'];
                if ( isset( $creationdate ) && $creationdate != '' ) {
                    try {
                        $dr = date_create_from_format( 'M d, Y H:i A', $creationdate );
                        $UTC = new DateTimeZone('UTC');
                        $dr->setTimezone($UTC);
                        $timeStamp = $dr->getTimestamp();
                        $newProject['post_date'] = gmdate('Y-m-d H:i:s', (intval($timeStamp) + ( get_option('gmt_offset') * 3600 )));
                        $newProject['post_date_gmt'] = gmdate('Y-m-d H:i:s', (intval($timeStamp)));
                    } catch ( Exception $e ) {
                        $newProject['post_date'] = current_time( 'mysql' );
                        $newProject['post_date_gmt'] = gmdate('Y-m-d H:i:s');
                    }
                } else {
                    $newProject['post_date'] = current_time( 'mysql' );
                    $newProject['post_date_gmt'] = gmdate('Y-m-d H:i:s');
                }

                // Post Data to be saved.
                $post = array(
                    'post_author' => $newProject['project_manager'],
                    'post_content' => $newProject['post_content'],
                    'post_status' => $newProject['post_status'],
                    'post_title' => $newProject['post_title'],
                    'post_date' => $newProject['post_date'],
                    'post_date_gmt' => $newProject['post_date_gmt'],
                    'post_type' => $post_type
                );

                $updateFlag = false;
                //check post request is for Update or insert
                if ( isset($newProject['post_id'] ) ) {
                    $updateFlag = true;
                    $post = array_merge( $post, array( 'ID' => $newProject['post_id'] ) );
                    $data = array(
                        'post_completiondate' => $newProject['post_completiondate'],
                        'post_address' => $newProject['post_address'],
                        'post_lot_number' => $newProject['post_lot_number'],
                        'post_dp_sp_number' => $newProject['post_dp_sp_number'],
                        'post_mother_file' => $newProject['post_mother_file'],
                        'project_client' => $newProject['project_client'],
                        'project_member' => $newProject['project_member'],
                        'date_update' => current_time( 'mysql' ),
                        'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                        'user_updated_by' => get_current_user_id(),
                    );
                    $post_id = @wp_update_post( $post );
                    foreach ( $data as $key=>$value ) {
                        update_post_meta( $post_id, $key, $value );
                    }
                }else{
                    $data = array(
                        'post_completiondate' => $newProject['post_completiondate'],
                        'post_address' => $newProject['post_address'],
                        'post_lot_number' => $newProject['post_lot_number'],
                        'post_dp_sp_number' => $newProject['post_dp_sp_number'],
                        'post_mother_file' => $newProject['post_mother_file'],
                        'project_client' => $newProject['project_client'],
                        'project_member' => $newProject['project_member'],
                        'date_update' => current_time( 'mysql' ),
                        'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                        'user_updated_by' => get_current_user_id(),
                        'user_created_by' => get_current_user_id(),
                    );
                    $post_id = @wp_insert_post($post);
                    foreach ( $data as $key=>$value ) {
                        update_post_meta( $post_id, $key, $value );
                    }
                }
                $_REQUEST[$post_type."_id"] = $post_id;
            }

            //Check for wp error
            if ( is_wp_error( $post_id ) ) {
                wp_die( 'Error while creating new '. ucfirst( $rt_pm_project->labels['name'] ) );
            }

            $form_ulr = admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}");

            ///alert Notification
            if (isset($_REQUEST["{$post_type}_id"])) {
                $form_ulr .= "&{$post_type}_id=" . $_REQUEST["{$post_type}_id"];
                if (isset($_REQUEST["new"])) {
                    ?>
                    <div class="alert-box success">
                        <?php _e('New '.  ucfirst($labels['name']).' Inserted Sucessfully.'); ?>
                        <a href="#" class="close">&times;</a>
                    </div>
                <?php
                }
                if(isset($updateFlag) && $updateFlag){ ?>
                    <div class="alert-box success">
                        <?php _e(ucfirst($labels['name'])." Updated Sucessfully."); ?>
                        <a href="#" class="close">&times;</a>
                    </div>
                <?php }
                $post = get_post($_REQUEST["{$post_type}_id"]);
                if (!$post) {
                    ?>
                    <div class="alert-box alert">
                        Invalid Post ID
                        <a href="" class="close">&times;</a>
                    </div>
                    <?php
                    $post = false;
                }
                if ( $post->post_type != $rt_pm_project->post_type ) {
                    ?>
                    <div class="alert-box alert">
                        Invalid Post Type
                        <a href="" class="close">&times;</a>
                    </div>
                    <?php
                    $post = false;
                }

                $create = new DateTime($post->post_date);

                $modify = new DateTime($post->post_modified);
                $createdate = $create->format("M d, Y h:i A");
                $modifydate = $modify->format("M d, Y h:i A");

            }

            // get project meta
            if (isset($post->ID)) {
                $post_author = $post->post_author;
                $project_member = get_post_meta($post->ID, "project_member", true);
                $project_client = get_post_meta($post->ID, "project_client", true);
                $completiondate= get_post_meta($post->ID, 'post_completiondate', true);
                $post_address= get_post_meta($post->ID, 'post_address', true);
                $post_lot_number = get_post_meta($post->ID, 'post_lot_number', true);
                $post_dp_sp_number = get_post_meta($post->ID, 'post_dp_sp_number', true);
                $post_mother_file = get_post_meta($post->ID, 'post_mother_file', true);
            } else {
                $post_author = get_current_user_id();
            }

            //project manager & project members
            $results_member = Rt_PM_Utils::get_pm_rtcamp_user();
            $arrProjectMember[] = array();
            $subProjectMemberHTML = "";
            if( !empty( $results_member ) ) {
                foreach ( $results_member as $author ) {
                    if ($project_member && !empty($project_member) && in_array($author->ID, $project_member)) {
                        $subProjectMemberHTML .= "<li id='project-member-auth-" . $author->ID
                            . "' class='contact-list'>" . get_avatar($author->user_email, 24) . '<a target="_blank" class="heading" title="'.$author->display_name.'" href="'.get_edit_user_link($author->ID).'">'.$author->display_name.'</a>'
                            . "<a class='right' href='#removeProjectMember'><i class='foundicon-remove'></i></a>
                                        <input type='hidden' name='post[project_member][]' value='" . $author->ID . "' /></li>";
                    }
                    $arrProjectMember[] = array("id" => $author->ID, "label" => $author->display_name, "imghtml" => get_avatar($author->user_email, 24), 'user_edit_link'=>  get_edit_user_link($author->ID));
                }
            }

            //Project client
            $results_client = Rt_PM_Utils::get_pm_client_user();
            $arrProjectClient[] = array();
            $subProjectClientHTML = "";
            if( !empty( $results_client ) ) {
                foreach ( $results_client as $client ) {
                    if ($project_client && !empty($project_client) && in_array($client->ID, $project_client)) {
                        $subProjectClientHTML .= "<li id='project-client-auth-" . $client->ID
                            . "' class='contact-list'>" . get_avatar($client->user_email, 24) . '<a target="_blank" class="heading" title="'.$client->post_title.'" href="'.get_edit_user_link($client->ID).'">'.$client->post_title.'</a>'
                            . "<a class='right' href='#removeProjectClient'><i class='foundicon-remove'></i></a>
                                        <input type='hidden' name='post[project_client][]' value='" . $client->ID . "' /></li>";
                    }
                    $arrProjectClient[] = array("id" => $client->ID, "label" => $client->post_title, "imghtml" => get_avatar($client->user_email, 24), 'user_edit_link'=>  get_edit_user_link($client->ID));
                }
            }

            ?>
            <div id="add-new-post" class="row">
                <?php if( $user_edit ) { ?>
                <form method="post" id="form-add-post" action="<?php echo $form_ulr; ?>">
                <?php } ?>
                    <?php if (isset($post->ID) && $user_edit ) { ?>
                        <input type="hidden" name="post[post_id]" id='project_id' value="<?php echo $post->ID; ?>" />
                    <?php } ?>
                    <div class="row">
                        <div class="large-6 small-12 columns ui-sortable meta-box-sortables">
                            <div class="row collapse postbox">
                                <div class="large-12 columns">
                                    <?php if( $user_edit ) { ?>
                                        <input name="post[post_title]" id="new_<?php echo $post_type ?>_title" type="text" placeholder="<?php _e(ucfirst($labels['name'])." Name"); ?>" value="<?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?>" />
                                    <?php } else { ?>
                                        <span><?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?></span><br /><br />
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row collapse rtpm-lead-content-wrapper">
                                <div class="large-12 columns">
                                    <?php
                                    if( $user_edit ) {
                                        wp_editor( ( isset( $post->ID ) ) ? $post->post_content : "", "post_content", array( 'textarea_name' => 'post[post_content]' ) );
                                    } else {
                                        echo ucfirst($labels['name']).' Content : <br /><br /><span>'.(( isset($post->ID) ) ? $post->post_content : '').'</span><br /><br />';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="large-3 small-12 columns ui-sortable meta-box-sortables">
                            <div class="row collapse postbox">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <h6 class="hndle"><span><i class="foundicon-idea"></i> Project Information</span></h6>
                                <div class="inside">
                                    <div class="row collapse">
                                        <div class="small-4 large-4 columns">
                                            <span class="prefix" title="Status">Status</span>
                                        </div>
                                        <div class="small-8 large-8 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                            <?php
                                            if (isset($post->ID))
                                                $pstatus = $post->post_status;
                                            else
                                                $pstatus = "";
                                            $post_status = $rt_pm_project->get_custom_statuses();
                                            $custom_status_flag = true;
                                            ?>
                                            <?php if( $user_edit ) { ?>
                                                <select id="rtpm_post_status" class="right" name="post[post_status]">
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
                                                        echo '<span class="rtcrm_view_mode">'.$status['name'].'</span>';
                                                        break;
                                                    }
                                                }
                                            } ?>
                                        </div>
                                    </div>
                                    <!--<div id="rtpm_closing_reason_wrapper" class="row collapse <?php /*echo ( $pstatus === 'closed' ) ? 'show' : 'hide'; */?> <?php /*echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; */?>">
                                        <div class="large-4 small-4 columns">
                                            <span class="prefix" title="<?php /*_e('Closing Reason'); */?>"><label><?php /*_e('Closing Reason'); */?></label></span>
                                        </div>
                                        <div class="large-8 small-8 columns"><?php /*$rt_crm_closing_reason->get_closing_reasons( ( isset( $post->ID ) ) ? $post->ID : '', $user_edit ); */?></div>
                                    </div>-->
                                    <div id="rtpm_project_type_wrapper" class="row collapse <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                                        <div class="large-4 small-4 columns">
                                            <span class="prefix" title="<?php _e('Project Type'); ?>"><label><?php _e('Project Type'); ?></label></span>
                                        </div>
                                        <div class="large-8 small-8 columns"><?php $rt_pm_project_type->get_project_types( ( isset( $post->ID ) ) ? $post->ID : '', $user_edit ); ?></div>
                                    </div>
                                    <div class="row collapse">
                                        <div class="large-4 small-4 columns">
                                            <span class="prefix" title="Create Date"><label>Create Date</label></span>
                                        </div>
                                        <div class="large-7 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                            <?php if( $user_edit ) { ?>
                                                <input class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date"
                                                       value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                                                       title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>">
                                                <input name="post[post_date]" type="hidden" value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" />
                                            <?php } else { ?>
                                                <span class="rtcrm_view_mode moment-from-now"><?php echo $createdate ?></span>
                                            <?php } ?>
                                        </div>
                                        <div class="large-1 mobile-large-1 columns">
                                            <span class="postfix datepicker-toggle" data-datepicker="closing-date"><label class="foundicon-calendar"></label></span>
                                        </div>
                                    </div>
                                    <?php if (isset($post->ID)) { ?>
                                        <div class="row collapse">
                                            <div class="large-4 mobile-large-1 columns">
                                                <span class="prefix" title="Modify Date"><label>Modify Date</label></span>
                                            </div>
                                            <div class="large-7 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                                <?php if( $user_edit ) { ?>
                                                    <input class="moment-from-now"  type="text" placeholder="Modified on Date"  value="<?php echo $modifydate; ?>"
                                                           title="<?php echo $modifydate; ?>" readonly="readonly">
                                                <?php } else { ?>
                                                    <span class="rtcrm_view_mode moment-from-now"><?php echo $modifydate; ?></span>
                                                <?php } ?>
                                            </div>
                                            <div class="large-1 mobile-large-1 columns">
                                                <span class="postfix datepicker-toggle" data-datepicker="closing-date"><label class="foundicon-calendar"></label></span>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="row collapse">
                                        <div class="large-4 small-4 columns">
                                            <span class="prefix" title="Create Date"><label>Completion Date</label></span>
                                        </div>
                                        <div class="large-7 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                            <?php if( $user_edit ) { ?>
                                                <input class="datetimepicker moment-from-now" type="text" placeholder="Select Completion Date"
                                                       value="<?php echo ( isset($completiondate) ) ? $completiondate : ''; ?>"
                                                       title="<?php echo ( isset($completiondate) ) ? $completiondate : ''; ?>">
                                                <input name="post[post_completiondate]" type="hidden" value="<?php echo ( isset($completiondate) ) ? $completiondate : ''; ?>" />
                                            <?php } else { ?>
                                                <span class="rtcrm_view_mode moment-from-now"><?php echo $completiondate ?></span>
                                            <?php } ?>
                                        </div>
                                        <div class="large-1 mobile-large-1 columns">
                                            <span class="postfix datepicker-toggle" data-datepicker="closing-date"><label class="foundicon-calendar"></label></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row collapse postbox">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <h6 class="hndle"><span><i class="foundicon-idea"></i> Other Details</span></h6>
                                <div class="inside">
                                    <div class="row collapse">
                                        <div class="small-4 large-4 columns">
                                            <span style="height:50px;line-height:50px;" class="prefix" title="Address">Address</span>
                                        </div>
                                        <div class="small-8 large-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                                            <textarea style="height:50px;margin:0;" name="post[post_address]"><?php echo ( isset($post_address) ) ? $post_address : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row collapse <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                                        <div class="large-4 small-4 columns">
                                            <span class="prefix" title="Lot Number">Lot Number</span>
                                        </div>
                                        <div class="large-8 small-8 columns">
                                            <input name="post[post_lot_number]" type="text" value="<?php echo ( isset($post_lot_number) ) ? $post_lot_number : ''; ?>" />
                                        </div>
                                    </div>
                                    <div class="row collapse <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                                        <div class="large-4 small-4 columns">
                                            <span class="prefix" title="Dp/Sp Number">Dp/Sp Number</span>
                                        </div>
                                        <div class="large-8 small-8 columns">
                                            <input name="post[post_dp_sp_number]" type="text" value="<?php echo ( isset($post_dp_sp_number) ) ? $post_dp_sp_number : ''; ?>" />
                                        </div>
                                    </div>
                                    <div class="row collapse <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                                        <div class="large-4 small-4 columns">
                                            <span class="prefix" title="Mother File">Mother File Number</span>
                                        </div>
                                        <div class="large-8 small-8 columns">
                                            <input name="post[post_mother_file]" type="text" value="<?php echo ( isset($post_mother_file) ) ? $post_mother_file : ''; ?>" />
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="large-3 columns ui-sortable meta-box-sortables">
                            <div id="rtpm-assignee" class="row collapse rtpm-post-author-wrapper">
                                <div class="large-4 mobile-large-1 columns">
                                    <span class="prefix" title="Assigned To"><label for="post[post_author]"><strong>Assigned To</strong></label></span>
                                </div>
                                <div class="large-8 mobile-large-3 columns">
                                    <?php if( $user_edit ) { ?>
                                        <select name="post[project_manager]" >
                                            <?php
                                            if (!empty($results_member)) {
                                                foreach ($results_member as $author) {
                                                    if ($author->ID == $post_author) {
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
                            <div class="row collapse postbox">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <h6 class="hndle"><span><i class="foundicon-smiley"></i> Project Client</span></h6>
                                <div class="inside">
                                    <script>
                                        var arr_project_client_user =<?php echo json_encode($arrProjectClient); ?>;
                                    </script>
                                    <?php if ( $user_edit ) { ?>
                                        <input style="margin-bottom:10px" type="text" placeholder="Type User Name to select" id="project_client_user_ac" />
                                    <?php } ?>
                                    <ul id="divProjectClientList" class="large-block-grid-1 small-block-grid-1">
                                        <?php echo $subProjectClientHTML; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="row collapse postbox">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <h6 class="hndle"><span><i class="foundicon-smiley"></i> Project Member</span></h6>
                                <div class="inside">
                                    <script>
                                        var arr_project_member_user =<?php echo json_encode($arrProjectMember); ?>;
                                        var ac_auth_token = '<?php echo get_user_meta(get_current_user_id(), 'rtcrm_activecollab_token', true); ?>';
                                        var ac_default_project = '<?php echo get_user_meta(get_current_user_id(), 'rtcrm_activecollab_default_project', true); ?>';
                                    </script>
                                    <?php if ( $user_edit ) { ?>
                                        <input style="margin-bottom:10px" type="text" placeholder="Type User Name to select" id="project_member_user_ac" />
                                    <?php } ?>
                                    <ul id="divProjectMemberList" class="large-block-grid-1 small-block-grid-1">
                                        <?php echo $subProjectMemberHTML; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="row collapse postbox hide">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <h6 class="hndle"><span><i class="foundicon-smiley"></i> Participants</span></h6>
                                <div class="inside">
                                    <ul class="rtpm-participant-list large-block-grid-1 small-block-grid-1">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="large-6 columns rtcrm-sticky right">
                            <?php
                            if (isset($post->ID)) {
                                $save_button = "Update ".ucfirst($labels['name']);
                            } else {
                                $save_button = "Save ".ucfirst($labels['name']);
                            }
                            ?>
                            <?php if( $user_edit ) { ?>
                                <button class="right mybutton success" type="submit" ><?php _e($save_button); ?></button> &nbsp;&nbsp;
                            <?php } ?>
                        </div>
                    </div>
                <?php if( $user_edit ) { ?>
                </form>
                <?php } ?>
            </div> <?php
        }
	}
}
