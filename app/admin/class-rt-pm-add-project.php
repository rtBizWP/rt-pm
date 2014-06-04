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
            add_action( 'wp_ajax_rtpm_add_attachement', array( $this, 'attachment_added_ajax' ) );
            add_action( 'wp_ajax_rtpm_remove_attachment', array( $this, 'attachment_remove_ajax') );
		}

        public function get_project_task_tab($labels,$user_edit){
            global $rt_pm_project,$rt_pm_task;

            if( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] != $rt_pm_project->post_type ) {
                wp_die("Opsss!! You are in restricted area");
            }

            $post_type=$_REQUEST['post_type'];
            $task_post_type=$rt_pm_task->post_type;
            $task_labels=$rt_pm_task->labels;

            //Trash action
            if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'trash' && isset( $_REQUEST[$task_post_type.'_id'] ) ) {
                wp_trash_post( $_REQUEST[$task_post_type.'_id'] );
                $return = wp_redirect( admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-add-'.$post_type.'&'.$post_type.'_id='.$_REQUEST[$post_type.'_id'].'&tab='.$post_type.'-task') );
                if( !$return ) {
                    echo '<script> window.location="' . admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-add-'.$post_type.'&'.$post_type.'_id='.$_REQUEST[$post_type.'_id'].'&tab='.$post_type.'-task') . '"; </script> ';
                }
            }

            //restore action
            if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'restore' && isset( $_REQUEST[$task_post_type.'_id'] ) ) {
                wp_untrash_post( $_REQUEST[$task_post_type.'_id'] );
                $return = wp_redirect( admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-add-'.$post_type.'&'.$post_type.'_id='.$_REQUEST[$post_type.'_id'].'&tab='.$post_type.'-task') );
                if( !$return ) {
                    echo '<script> window.location="' . admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-add-'.$post_type.'&'.$post_type.'_id='.$_REQUEST[$post_type.'_id'].'&tab='.$post_type.'-task') . '"; </script> ';
                }
            }

            //Delete action
            if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' && isset( $_REQUEST[$task_post_type.'_id'] ) ) {
                wp_delete_post( $_REQUEST[$task_post_type.'_id'] );
                $rt_pm_project->remove_connect_post_to_entity($task_post_type,$_REQUEST[$task_post_type.'_id']);
                $return = wp_redirect( admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-add-'.$post_type.'&'.$post_type.'_id='.$_REQUEST[$post_type.'_id'].'&tab='.$post_type.'-task') );
                if( !$return ) {
                    echo '<script> window.location="' . admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-add-'.$post_type.'&'.$post_type.'_id='.$_REQUEST[$post_type.'_id'].'&tab='.$post_type.'-task') . '"; </script> ';
                }
            }

            //Check Post object is init or not
            if ( isset( $_POST['post'] ) ) {
                $action_complete= false;
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

                $duedate = $newTask['post_duedate'];
                if ( isset( $duedate ) && $duedate != '' ) {
                    try {
                        $dr = date_create_from_format( 'M d, Y H:i A', $duedate );
                        $UTC = new DateTimeZone('UTC');
                        $dr->setTimezone($UTC);
                        $timeStamp = $dr->getTimestamp();
                        $newTask['$duedate'] = gmdate('Y-m-d H:i:s', (intval($timeStamp) + ( get_option('gmt_offset') * 3600 )));
                    } catch ( Exception $e ) {
                        $newTask['post_date'] = current_time( 'mysql' );
                    }
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
                        'date_update' => current_time( 'mysql' ),
                        'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                        'user_updated_by' => get_current_user_id(),
                    );
                    $post_id = @wp_update_post( $post );
                    $rt_pm_project->connect_post_to_entity($task_post_type,$newTask['post_project_id'],$post_id);
                    foreach ( $data as $key=>$value ) {
                        update_post_meta( $post_id, $key, $value );
                    }
                }else{
                    $data = array(
                        'post_project_id' => $newTask['post_project_id'],
                        'post_duedate' => $newTask['post_duedate'],
                        'date_update' => current_time( 'mysql' ),
                        'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                        'user_updated_by' => get_current_user_id(),
                        'user_created_by' => get_current_user_id(),
                    );
                    $post_id = @wp_insert_post($post);
                    $rt_pm_project->connect_post_to_entity($task_post_type,$newTask['post_project_id'],$post_id);
                    foreach ( $data as $key=>$value ) {
                        update_post_meta( $post_id, $key, $value );
                    }
                    $_REQUEST["new"]=true;
                    $newTask['post_id']= $post_id;
                }

                // Attachments
                $old_attachments = get_posts( array(
                    'post_parent' => $newTask['post_id'],
                    'post_type' => 'attachment',
                    'fields' => 'ids',
                    'posts_per_page' => -1,
                ));
                $new_attachments = array();
                if ( isset( $_POST['attachment'] ) ) {
                    $new_attachments = $_POST['attachment'];
                    foreach ( $new_attachments as $attachment ) {
                        if( !in_array( $attachment, $old_attachments ) ) {
                            $file = get_post($attachment);
                            $filepath = get_attached_file( $attachment );
                            $post_attachment_hashes = get_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash' );
                            if ( ! empty( $post_attachment_hashes ) && in_array( md5_file( $filepath ), $post_attachment_hashes ) ) {
                                continue;
                            }
                            if( !empty( $file->post_parent ) ) {
                                $args = array(
                                    'post_mime_type' => $file->post_mime_type,
                                    'guid' => $file->guid,
                                    'post_title' => $file->post_title,
                                    'post_content' => $file->post_content,
                                    'post_parent' => $newTask['post_id'],
                                    'post_author' => get_current_user_id(),
                                    'post_status' => 'inherit'
                                );
                                $new_attachments_id = wp_insert_attachment( $args, $file->guid, $newTask['post_id'] );
                                /*$new_attach_data=wp_generate_attachment_metadata($new_attachments_id,$filepath);
                                wp_update_attachment_metadata( $new_attachments_id, $new_attach_data );*/
                                add_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
                            } else {
                                wp_update_post( array( 'ID' => $attachment, 'post_parent' => $newTask['post_id'] ) );
                                $filepath = get_attached_file( $attachment );
                                add_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
                            }
                        }
                    }

                    foreach ( $old_attachments as $attachment ) {
                        if( !in_array( $attachment, $new_attachments ) ) {
                            wp_update_post( array( 'ID' => $attachment, 'post_parent' => '0' ) );
                            $filepath = get_attached_file( $attachment );
                            delete_post_meta($newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
                        }
                    }
                } else {
                    foreach ( $old_attachments as $attachment ) {
                        wp_update_post( array( 'ID' => $attachment, 'post_parent' => '0' ) );
                        $filepath = get_attached_file( $attachment );
                        delete_post_meta($newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
                    }
                    delete_post_meta($newTask['post_id'], '_rt_wp_pm_attachment_hash' );
                }

                /*$return = wp_redirect( admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-task") );
                if( !$return ) {
                    echo '<script> window.location="' . admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-task") . '"; </script> ';
                }*/
                $_REQUEST["{$task_post_type}_id"] = null;
                $action_complete= true;
            }

            //Check for wp error
            if ( is_wp_error( $post_id ) ) {
                wp_die( 'Error while creating new '. ucfirst( $rt_pm_project->labels['name'] ) );
            }

            $form_ulr = admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-task");
            ///alert Notification
            if ($action_complete){
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

            }else{
                $post=null;
            }

            // get project meta
            if (isset($post->ID)) {
                $post_author = $post->post_author;
                $due = new DateTime(get_post_meta($post->ID, 'post_duedate', true));
                $due_date = $due->format("M d, Y h:i A");
            } else {
                $post_author = get_current_user_id();
            }

            //assign to
            $results_member = Rt_PM_Utils::get_pm_rtcamp_user();
            ?>

            <?php if (isset($post->ID)){?>
                <script>
                    jQuery(document).ready(function($) {
                        setTimeout(function() {
                            $("#div-add-task").reveal({
                                opened: function(){
                                }
                            });
                        },10);
                    });
                </script>
            <?php } ?>

            <div id="wp-custom-list-table" class="row">
                <div style="max-width:none;" class="row">
                    <div style="padding:0" class="large-6 columns"></div>
                    <div style="padding:0;" class="large-6 columns rtcrm-sticky">
                        <?php if( $user_edit ) { ?>
                            <?php if (isset($_REQUEST["{$task_post_type}_id"])) {
                                $btntitle = 'Update Task';
                            }else{
                                $btntitle = 'Add Task';
                            } ?>
                            <button class="right mybutton add-task" type="button" ><?php _e($btntitle); ?></button>
                        <?php } ?>
                    </div>
                </div>

                <div class="row">
                    <?php
                    $rtpm_task_list= new Rt_PM_Task_List_View();
                    $rtpm_task_list->prepare_items($user_edit);
                    $rtpm_task_list->display();
                    ?>
                </div>
            </div>

                     <!--reveal-modal-add-task -->
            <div id="div-add-task" class="reveal-modal">
                <fieldset>
                    <legend><h4><i class="foundicon-address-book"></i> Add New Task</h4></legend>
                    <form method="post" id="form-add-post" data-posttype="<?php echo $task_post_type; ?>" action="<?php echo $form_ulr; ?>">
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
                        <div class="row collapse">
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
                        <div class="row collapse">
                            <div class="large-2 small-4 columns">
                                <span class="prefix" title="Due Date"><label>Due Date</label></span>
                            </div>
                            <div class="large-3 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                <?php if( $user_edit ) { ?>
                                    <input class="datetimepicker moment-from-now" type="text" placeholder="Select Create Date"
                                           value="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>"
                                           title="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>">
                                    <input name="post[post_duedate]" type="hidden" value="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>" />
                                <?php } else { ?>
                                    <span class="rtcrm_view_mode moment-from-now"><?php echo $duedate ?></span>
                                <?php } ?>
                            </div>
                            <div class="large-1 mobile-large-1 columns">
                                <span class="postfix datepicker-toggle" data-datepicker="closing-date"><label class="foundicon-calendar"></label></span>
                            </div>
                            <div class="large-3 mobile-large-1 columns">
                                <span class="prefix" title="Status">Status</span>
                            </div>
                            <div class="large-3 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                <?php
                                if (isset($post->ID))
                                    $pstatus = $post->post_status;
                                else
                                    $pstatus = "";
                                $post_status = $rt_pm_task->get_custom_statuses();
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
                        <?php $attachments = array();
                        if ( isset( $post->ID ) ) {
                            $attachments = get_posts( array(
                                'posts_per_page' => -1,
                                'post_parent' => $post->ID,
                                'post_type' => 'attachment',
                            ));
                        }
                        ?>
                        <div class="row collapse postbox">
                            <div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br /></div>
                            <h6 class="hndle"><span><i class="foundicon-paper-clip"></i> <?php _e('Attachments'); ?></span></h6>
                            <div class="inside">
                                <div class="row collapse" id="attachment-container">
                                    <?php if( $user_edit ) { ?>
                                        <a href="#" class="button" id="add_pm_attachment"><?php _e('Add'); ?></a>
                                    <?php } ?>
                                    <div class="scroll-height">
                                        <?php foreach ($attachments as $attachment) { ?>
                                            <?php $extn_array = explode('.', $attachment->guid); $extn = $extn_array[count($extn_array) - 1]; ?>
                                            <div class="large-12 mobile-large-3 columns attachment-item" data-attachment-id="<?php echo $attachment->ID; ?>">
                                                <a target="_blank" href="<?php echo wp_get_attachment_url($attachment->ID); ?>">
                                                    <img height="20px" width="20px" src="<?php echo RT_PM_URL . "app/assets/file-type/" . $extn . ".png"; ?>" />
                                                    <?php echo $attachment->post_title; ?>
                                                </a>
                                                <?php if( $user_edit ) { ?>
                                                    <a href="#" class="rtpm_delete_attachment right">x</a>
                                                <?php } ?>
                                                <input type="hidden" name="attachment[]" value="<?php echo $attachment->ID; ?>" />
                                            </div>
                                        <?php } ?>
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

            if( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] != $rt_pm_project->post_type ) {
                wp_die("Opsss!! You are in restricted area");
            }

            $post_type=$_REQUEST['post_type'];
            $task_post_type=$rt_pm_task->post_type;
            $timeentry_labels = $rt_pm_time_entries->labels;
            $timeentry_post_type = $rt_pm_time_entries->post_type;

            //Trash action
            if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' && isset( $_REQUEST[$timeentry_post_type.'_id'] ) ) {
                wp_trash_post( $_REQUEST[$timeentry_post_type.'_id'] );
                $return = wp_redirect( admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-add-'.$post_type.'&'.$post_type.'_id='.$_REQUEST[$post_type.'_id'].'&tab='.$post_type.'-timeentry') );
                if( !$return ) {
                    echo '<script> window.location="' . admin_url( 'edit.php?post_type='.$post_type.'&page=rtpm-add-'.$post_type.'&'.$post_type.'_id='.$_REQUEST[$post_type.'_id'].'&tab='.$post_type.'-timeentry') . '"; </script> ';
                }
            }

            //Check Post object is init or not
            if ( isset( $_POST['post'] ) ) {
                $action_complete = false;
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
                    'time_tracker' => $newTimeEntry['post_time_tracker'],
                );
                $updateFlag = false;
                //check post request is for Update or insert
                if ( isset($newTimeEntry['post_id'] ) ) {
                    $updateFlag = true;
                    $where = array( 'id' => $newTimeEntry['post_id'] );
                    $post_id = $rt_pm_time_entries_model->update_timeentry($post,$where);
                }else{
                    $post_id = $rt_pm_time_entries_model->add_timeentry($post);
                    $_REQUEST["new"]=true;
                }
                $_REQUEST["{$timeentry_post_type}_id"] = null;
                $action_complete= true;
            }

            //Check for wp error
            /*if ( is_wp_error( $post_id ) ) {
                wp_die( 'Error while creating new '. ucfirst( $rt_pm_project->labels['name'] ) );
            }*/

            if ( $action_complete ){
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

            $form_ulr = admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-timeentry");//&{$task_post_type}_id={$_REQUEST["{$task_post_type}_id"]}
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

                $create = new DateTime($post->timestamp);
                $createdate = $create->format("M d, Y h:i A");
            }

            // get project meta
            if (isset($post->id)) {
                $post_author = $post->author;
                $_REQUEST["{$post_type}_id"]=$post->project_id;
                $task_id=$post->task_id;
            } else {
                $post_author = get_current_user_id();
                if ( isset ( $_REQUEST["{$task_post_type}_id"] ) ) {
                    $task_id= $_REQUEST["{$task_post_type}_id"];
                }
            }
            ?>

            <?php if (isset($post->id) || ($_REQUEST["action"]=="timeentry")){?>
                <script>
                    jQuery(document).ready(function($) {
                        setTimeout(function() {
                            jQuery(".add-time-entry").trigger('click');
                        },10);
                    });
                </script>
            <?php } ?>

            <div id="wp-custom-list-table" class="row">
                <div style="max-width:none;" class="row">
                    <div style="padding:0" class="large-6 columns"></div>
                    <div style="padding:0;" class="large-6 columns rtcrm-sticky">
                        <?php if( $user_edit ) { ?>
                            <?php if (isset($_REQUEST["{$timeentry_post_type}_id"])) {
                                $btntitle = 'Update Time Entry';
                            }else{
                                $btntitle = 'Add Time Entry';
                            } ?>
                            <button class="right mybutton add-time-entry" type="button" ><?php _e($btntitle); ?></button>
                        <?php } ?>
                    </div>
                </div>

                <div class="row">
                    <?php
                    $rtpm_time_entry_list= new Rt_PM_Time_Entry_List_View();
                    $rtpm_time_entry_list->prepare_items();
                    $rtpm_time_entry_list->display();
                    ?>
                </div>
            </div>

            <!--reveal-modal-add-task -->
            <div id="div-add-time-entry" class="reveal-modal large">
                <fieldset>
                    <legend><h4><i class="foundicon-address-book"></i> Add New Time Entry</h4></legend>
                    <form method="post" id="form-add-post" data-posttype="<?php echo $timeentry_post_type; ?>" action="<?php echo $form_ulr; ?>">
                        <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $_REQUEST["{$post_type}_id"]; ?>" />
                        <?php if (isset($post->id) && $user_edit ) { ?>
                            <input type="hidden" name="post[post_id]" id='task_id' value="<?php echo $post->id; ?>" />
                        <?php } ?>
                        <div class="row collapse">
                            <?php
                            $rtpm_task_list= new Rt_PM_Task_List_View();
                            $rtpm_task_list->prepare_items($user_edit);
                            $rtpm_task_list->get_drop_down($task_id);
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
                                <span class="prefix" title="Duration"><label for="post[post_duration]"><strong>Duration</strong></label></span>
                            </div>
                            <div class="large-3 mobile-large-3 columns">
                                <?php if( $user_edit ) { ?>
                                    <select name="post[post_duration]" >
                                        <?php
                                        $arr_timer=$rt_pm_time_entries->get_timer_array();
                                        foreach( $arr_timer as $timer_key => $timer_val ){ ?>
                                            <option <?php echo isset($post) && $post->time_duration == $timer_key ? 'selected="selected"' :''; ?> value="<?php echo $timer_key; ?>" ><?php echo _e( $timer_val );  ?></option>
                                        <?php }?>
                                    </select>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="row collapse rtpm-post-author-wrapper">
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
                            <div class="large-3 mobile-large-1 columns">
                                <span class="prefix" title="Time Tracker"><label for="post[post_time_tracker]"><strong>Time Tracker</strong></label></span>
                            </div>
                            <div class="large-3 mobile-large-3 columns">
                                <?php if( $user_edit ) { ?>
                                <select name="post[post_time_tracker]" >
                                    <option <?php echo  isset($post) && $post->time_tracker == "office_time" ? 'selected="selectet"' : '';  ?> value="office_time" >Office Time</option>
                                    <option <?php echo  isset($post) && $post->time_tracker == "field_time" ? 'selected="selectet"' : '';  ?> value="field_time" >Field Time</option>
                                </select>
                                <?php } ?>
                             </div>

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
                        'project_client' => $newProject['project_client'],
                        'project_member' => $newProject['project_member'],
                        'date_update' => current_time( 'mysql' ),
                        'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                        'user_updated_by' => get_current_user_id(),
                    );
                    $post_id = @wp_update_post( $post );
                    $rt_pm_project_type->save_project_type($post_id,$newProject);
                    $data = apply_filters( 'rt_pm_project_detail_meta', $data);
                    foreach ( $data as $key=>$value ) {
                        update_post_meta( $post_id, $key, $value );
                    }
                }else{
                    $data = array(
                        'post_completiondate' => $newProject['post_completiondate'],
                        'project_client' => $newProject['project_client'],
                        'project_member' => $newProject['project_member'],
                        'date_update' => current_time( 'mysql' ),
                        'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                        'user_updated_by' => get_current_user_id(),
                        'user_created_by' => get_current_user_id(),
                    );
                    $post_id = @wp_insert_post($post);
                    $rt_pm_project_type->save_project_type($post_id,$newProject);
                    $data = apply_filters( 'rt_pm_project_detail_meta', $data);
                    foreach ( $data as $key=>$value ) {
                        update_post_meta( $post_id, $key, $value );
                    }
                }
                $_REQUEST[$post_type."_id"] = $post_id;
                $return = wp_redirect( admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-details") );
                if( !$return ) {
                    echo '<script> window.location="' . admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-details") . '"; </script> ';
                }
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
                <form method="post" id="form-add-post" data-posttype="<?php echo $post_type ?>" action="<?php echo $form_ulr; ?>">
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
                                                $status_html='';
                                                foreach ( $post_status as $status ) {
                                                    if($status['slug'] == $pstatus) {
                                                        $status_html = '<span class="rtcrm_view_mode">'.$status['name'].'</span>';
                                                        break;
                                                    }
                                                }
                                                if ( !isset( $status_html ) || empty( $status_html ) && ( isset( $pstatus ) && !empty( $pstatus ) ) ){
                                                    $status_html = '<span class="rtcrm_view_mode">'.$pstatus.'</span>';
                                                }
                                                echo $status_html;
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
                                        <div class="large-8 small-8 columns">
                                            <?php if( $user_edit ) {
                                                $rt_pm_project_type->get_project_types_dropdown( ( isset( $post->ID ) ) ? $post->ID : '', $user_edit );
                                            } else {
                                                $project_type = $rt_pm_project_type->get_project_type_id(( isset( $post->ID ) ) ? $post->ID : ''); ?>
                                                <span class="rtcrm_view_mode"><?php echo $project_type->name ?></span>
                                            <?php } ?>
                                        </div>
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
                            <?php do_action( 'rt_pm_other_details', $user_edit, $post ); ?>
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

        function get_project_file_tab($labels,$user_edit){
            $post_type=$_REQUEST['post_type'];
            $projectid = $_REQUEST["{$post_type}_id"];

            $attachments = array();
            $arg= array(
                'posts_per_page' => -1,
                'post_parent' => $projectid,
                'post_type' => 'attachment',
            );
            if ( isset($_REQUEST['attachment_tag']) && $_REQUEST['attachment_tag']!= -1 ){
                $arg=array_merge($arg, array('tax_query' => array(
                    array(
                        'taxonomy' => 'attachment_tag',
                        'field' => 'term_id',
                        'terms' => $_REQUEST['attachment_tag'])
                    ))
                );
            }
            if (isset($_POST['post'])){
                $new_attachment = $_POST['post'];
                $projectid = $new_attachment["post_project_id"];
                $args = array(
                    'guid' => $new_attachment["post_link"],
                    'post_title' => $new_attachment["post_title"],
                    'post_content' => $new_attachment["post_title"],
                    'post_parent' => $projectid,
                    'post_author' => get_current_user_id(),
                );
                $post_attachment_hashes = get_post_meta( $projectid, '_rt_wp_pm_external_link' );
                if ( empty( $post_attachment_hashes ) || $post_attachment_hashes != $new_attachment->post_link  ) {
                    $attachment_id = wp_insert_attachment( $args, $new_attachment["post_link"], $projectid );
                    add_post_meta( $projectid, '_rt_wp_pm_external_link', $new_attachment["post_link"] );
                    //convert string array to int array
                    $new_attachment["term"] = array_map( 'intval', $new_attachment["term"] );
                    $new_attachment["term"] = array_unique( $new_attachment["term"] );
                    //Set term to external link
                    wp_set_object_terms( $attachment_id, $new_attachment["term"], 'attachment_tag');
                    //Update flag for external link
                    update_post_meta( $attachment_id, '_wp_attached_external_file', '1');
                    /*update_post_meta($attachment_id, '_flagExternalLink', "true");*/
                }
            }
            delete_post_meta( $projectid, '_rt_wp_pm_external_link' );
            if ( isset( $projectid ) ) {
                $attachments = get_posts($arg );
            }
            $form_ulr = admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-files");?>
            <div id="add-new-attachment" class="row">
                <div id="attachment-error">

                </div>
                <div style="max-width:none;" class="row">
                    <div style="padding:0" class="large-6 columns"></div>
                    <div style="padding:0;" class="large-6 columns rtcrm-sticky">
                        <?php if( $user_edit ) { ?>
                            <button class="right mybutton add-external-link" type="button" ><?php _e("Add External link"); ?></button>
                            <button class="right mybutton add-project-file" data-projectid="<?php echo $projectid; ?>" id="add_project_attachment" type="button" ><?php _e('Add File'); ?></button>
                        <?php } ?>
                    </div>
                </div>
                 <div id="attachment-search-row" class="row collapse postbox">
                    <div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br /></div>
                    <h6 class="hndle"><span><i class="foundicon-paper-clip"></i> <?php _e('Attachments'); ?></span>
                        <form id ="attachment-search" method="post" action="<?php echo $form_ulr; ?>">
                            <button class="right mybutton success" type="submit" ><?php _e('Search'); ?></button> &nbsp;&nbsp;
                            <?php
                            wp_dropdown_categories('taxonomy=attachment_tag&hide_empty=0&orderby=name&name=attachment_tag&show_option_none=Select Media tag&selected='.$_REQUEST['attachment_tag']);
                            ?>
                        </form></h6>
                    <div class="inside">
                        <div class="row collapse" id="attachment-container">

                            <div class="scroll-height">
                                <?php if ( $attachments ){ ?>
                                    <?php foreach ($attachments as $attachment) { ?>
                                        <?php $extn_array = explode('.', $attachment->guid); $extn = $extn_array[count($extn_array) - 1];
                                            if ( get_post_meta( $attachment->ID, '_wp_attached_external_file', true) == 1){
                                                $extn ='unknown';
                                            }
                                        ?>
                                        <div class="large-12 mobile-large-3 columns attachment-item" data-attachment-id="<?php echo $attachment->ID; ?>">
                                            <a target="_blank" href="<?php echo wp_get_attachment_url($attachment->ID); ?>">
                                                <img height="20px" width="20px" src="<?php echo RT_PM_URL . "app/assets/file-type/" . $extn . ".png"; ?>" />
                                                <?php echo $attachment->post_title; ?>
                                            </a>
                                            <?php $taxonomies=get_attachment_taxonomies($attachment);
                                            $taxonomies=get_the_terms($attachment,$taxonomies);
                                            $term_html = '';
                                            if ( isset( $taxonomies ) && !empty( $taxonomies ) ){?>
                                                <div style="display:inline-flex;margin-left: 20px;" class="attachment-meta">[&nbsp;
                                                    <?php foreach( $taxonomies as $taxonomy){
                                                        if ( !empty($term_html) ){
                                                            $term_html.=',&nbsp;';
                                                        }
                                                        $term_html .= '<a href="'.admin_url("edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-files&attachment_tag={$taxonomy->term_id}").'" title="'. $taxonomy->name .'" >'.$taxonomy->name.'</a>';
                                                    }
                                                    echo $term_html;?>&nbsp;]
                                                </div>
                                            <?php } ?>
                                            <?php if( $user_edit ) { ?>
                                                <a href="#" data-attachmentid="<?php echo $attachment->ID; ?>"  class="rtpm_delete_project_attachment right">x</a>
                                            <?php } ?>
                                            <input type="hidden" name="attachment[]" value="<?php echo $attachment->ID; ?>" />
                                           <?php /*var_dump( get_post_meta($attachment, '_flagExternalLink') ) */?>
                                        </div>
                                    <?php } ?>
                                <?php }else{ ?>
                                    <?php if (  isset($_POST['attachment_tag']) && $_POST['attachment_tag']!= -1 ){ ?>
                                        <div class="large-12 mobile-large-3 columns no-attachment-item">
                                            <?php $term = get_term( $_POST['attachment_tag'], 'attachment_tag' ); ?>
                                            Not Found Attachment<?php echo isset( $term )? ' of ' . $term->name . ' Term!' :'!' ?>
                                        </div>
                                    <?php }else{ ?>
                                        <div class="large-12 mobile-large-3 columns no-attachment-item">
                                            <?php delete_post_meta($projectid, '_rt_wp_pm_attachment_hash'); ?>
                                            Attachment Not found!
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--reveal-modal-add-external file -->
            <div id="div-add-external-link" class="reveal-modal large">
                <fieldset>
                    <legend><h4><i class="foundicon-address-book"></i> Add External Link</h4></legend>
                    <form method="post" id="form-external-link" data-posttype="projec-attachment" action="<?php echo $form_ulr; ?>">
                        <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $_REQUEST["{$post_type}_id"]; ?>" />
                        <div class="row collapse">
                            <div class="large-3 small-4 columns">
                                <span class="prefix" title="Create Date"><label>Title</label></span>
                            </div>
                            <div class="large-9 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                <input name="post[post_title]" type="text" value="" />
                            </div>
                        </div>
                        <div class="row collapse">
                            <div class="large-3 small-4 columns">
                                <span class="prefix" title="Create Date"><label>External Link</label></span>
                            </div>
                            <div class="large-9 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                <input name="post[post_link]" type="text" value="" />
                            </div>
                        </div>
                        <div class="row collapse">
                            <div class="large-3 small-4 columns">
                                <span class="prefix" title="Create Date"><label>Terms</label></span>
                            </div>
                            <div class="large-9 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtcrm_attr_border' : ''; ?>">
                                <?php $media_terms= get_categories("taxonomy=attachment_tag&hide_empty=0&orderby=name");?>
                                <ul class="media-term-list">
                                    <?php foreach ( $media_terms as $term ){?>
                                    <li><label><input type="checkbox" name="post[term][]" value="<?php echo $term->term_id; ?>"><span><?php echo $term->name; ?></span></label></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row collapse">

                        </div>
                        <button class="mybutton right" type="submit" id="save-external-link">Save</button>
                    </form>
                </fieldset>
                <a class="close-reveal-modal">×</a>
            </div>
        <?php
        }

        public function attachment_added_ajax(){
            if ( isset( $_POST['project_id'] ) ) {
                $projectid = $_POST['project_id'];
                // Attachments
                $old_attachments = get_posts( array(
                    'post_parent' => $projectid,
                    'post_type' => 'attachment',
                    'fields' => 'ids',
                    'posts_per_page' => -1,
                ));
                if ( isset( $_POST['attachment_id'] ) ) {
                    $new_attachment = $_POST['attachment_id'];
                    if( !in_array( $new_attachment, $old_attachments ) ) {
                        $file = get_post($new_attachment);
                        $filepath = get_attached_file( $new_attachment );

                        $post_attachment_hashes = get_post_meta( $projectid, '_rt_wp_pm_attachment_hash' );
                        if ( ! empty( $post_attachment_hashes ) && in_array( md5_file( $filepath ), $post_attachment_hashes ) ) {
                            return '2';
                        }

                        if( !empty( $file->post_parent ) ) {
                            $args = array(
                                'post_mime_type' => $file->post_mime_type,
                                'guid' => $file->guid,
                                'post_title' => $file->post_title,
                                'post_content' => $file->post_content,
                                'post_parent' => $projectid,
                                'post_author' => get_current_user_id(),
                            );
                            wp_insert_attachment( $args, $file->guid, $projectid );

                            add_post_meta( $projectid, '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );

                        } else {
                            wp_update_post( array( 'ID' => $new_attachment, 'post_parent' => $_POST['project_id'] ) );
                            $file = get_attached_file( $new_attachment );
                            add_post_meta($projectid, '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
                        }
                        return '1';
                    }
                    return '2';
                }
            }
        }

        public function attachment_remove_ajax(){
            if ( isset( $_POST['project_id'] ) ) {
                $projectid = $_POST['project_id'];
                if ( isset( $_POST['attachment_id'] ) ) {
                    $new_attachment = $_POST['attachment_id'];
                    wp_update_post( array( 'ID' => $new_attachment, 'post_parent' => '0' ) );
                    $filepath = get_attached_file( $new_attachment );
                    delete_post_meta($_POST['project_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
                }
            }
        }
    }
}
