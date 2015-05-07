<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_bp_pm_project, $bp, $wpdb, $wp_query, $rt_person;
$page = max( 1, get_query_var( 'paged' ) );
$project_id = $_GET['rt_project_id'];
$current_date = date( "Y-m-d" );
$dates = rt_get_next_dates( $current_date );

if ( isset( $_REQUEST['user_id'] ) ) {
	// user edit popup
}
if ( isset( $_REQUEST['update'] ) ) {
	if ( $_REQUEST['update'] == "true" ) {
		$newTask = $_POST['post'];
		global $rt_pm_task;
		$task_post_type = $rt_pm_task->post_type;
		$creationdate = $newTask['post_date'];
		if ( isset( $creationdate ) && $creationdate != '' ) {
			try {
				$dr = date_create_from_format( 'M d, Y H:i A', $creationdate );
				//  $UTC = new DateTimeZone('UTC');
				//  $dr->setTimezone($UTC);
				$timeStamp = $dr->getTimestamp();
				$newTask['post_date'] = gmdate( 'Y-m-d H:i:s', intval( $timeStamp ) );
				$newTask['post_date_gmt'] = rt_set_date_to_utc( gmdate( 'Y-m-d H:i:s', (intval( $timeStamp ) ) ) );
			} catch ( Exception $e ) {
				$newTask['post_date'] = current_time( 'mysql' );
				$newTask['post_date_gmt'] = gmdate( 'Y-m-d H:i:s' );
			}
		} else {
			$newTask['post_date'] = current_time( 'mysql' );
			$newTask['post_date_gmt'] = gmdate( 'Y-m-d H:i:s' );
		}

		$duedate = $newTask['post_duedate'];
		if ( isset( $duedate ) && $duedate != '' ) {
			try {
				$dr = date_create_from_format( 'M d, Y H:i A', $duedate );
				//  $UTC = new DateTimeZone('UTC');
				// $dr->setTimezone($UTC);
				$timeStamp = $dr->getTimestamp();
				$newTask['post_duedate'] = rt_set_date_to_utc( gmdate( 'Y-m-d H:i:s', intval( $timeStamp ) ) );
			} catch ( Exception $e ) {
				$newTask['post_duedate'] = current_time( 'mysql' );
			}
		}

		$post = array(
			'post_content' => $newTask['post_content'],
			'post_status' => $newTask['post_status'],
			'post_title' => $newTask['post_title'],
			'post_date' => $newTask['post_date'],
			'post_date_gmt' => $newTask['post_date_gmt'],
			'post_type' => $task_post_type
		);


		$post = array_merge( $post, array( 'ID' => $newTask['post_id'] ) );
		$data = array(
			'post_assignee' => $newTask['post_assignee'],
			'post_project_id' => $newTask['post_project_id'],
			'post_duedate' => $newTask['post_duedate'],
			'date_update' => current_time( 'mysql' ),
			'date_update_gmt' => gmdate( 'Y-m-d H:i:s' ),
			'user_updated_by' => get_current_user_id(),
		);
		$post_id = @wp_update_post( $post );
		$rt_pm_project->connect_post_to_entity( $task_post_type, $newTask['post_project_id'], $post_id );
		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
		// link post to user

		$employee_id = rt_biz_get_person_for_wp_user( $newTask['post_assignee'] );
		// remove old data
		$rt_pm_project->remove_post_from_user( $task_post_type, $post_id );
		$rt_pm_project->connect_post_to_user( $task_post_type, $post_id, $employee_id[0]->ID );
	}
}
?>
<div class="list-heading">
	<div class="large-10 columns list-title">
		<h4><?php _e( 'Resources', RT_PM_TEXT_DOMAIN ) ?></h4>
	</div>
	<div class="large-2 columns">

	</div>
</div>
<div class="rt-main-resources-container">
	<div><a href="#" id="export-csv" class="rt-export-button">Export CSV</a></div>
	<div><a href="#" id="export-pdf" class="rt-export-button">Export PDF</a></div>
	<div class="rt-left-container">
		<table>
			<thead>
				<tr>
					<td>
						<?php _e( 'Project Resources', RT_PM_TEXT_DOMAIN ); ?>
					</td>
				</tr>
			</thead>
			<tbody>
				<?php
				$team_member = get_post_meta( $project_id, "project_member", true );
				if ( $team_member != '' && !empty( $team_member ) ) {
					?>
					<?php
					foreach ( $team_member as $key => $member_id ) {
						$user = get_userdata( $member_id );
						$people = rt_biz_get_person_for_wp_user( $member_id );
						$people = $people[0];
						?>
						<tr>
							<td>
								<?php
								if ( !empty( $people->post_title ) ) {
									$employee_name = $people->post_title;
									//printf( __('<a href="%s">'.$people->post_title.'</a>'), esc_url( add_query_arg( array( 'user_id'=> $people->ID, 'action'=>'edit' ) ) ) ); 
								} else {
									$person_wp_user_id = rt_biz_get_wp_user_for_person( $people->ID );
									$employee_name = rt_get_user_displayname( $person_wp_user_id );
									//   if( !empty( $person_wp_user_id ) ){
									//	printf( __('<a href="%s">'.rt_get_user_displayname( $person_wp_user_id ).'</a>'), esc_url( add_query_arg( array( 'user_id'=> $people->ID, 'action'=>'edit' ) ) ) );
									// }
								}
								?>
								<div class="rtpm-show-user-tooltip">
									<?php echo $employee_name; ?>
									<div class="rtpm-task-info-tooltip">
										<div class="large-3 columns">
											<?php
											$val = Rt_Person::get_meta( $people->ID, $rt_person->user_id_key );
											if ( !empty( $val ) ) {
												echo get_avatar( $val[0], 32 );
											}
											?>
										</div>
										<div class="large-9 columns">
											<div class="emp_name">
												<?php echo $employee_name; ?>
											</div>
											<div class="emp_position">
												<?php
												$person_wp_user_id = rt_biz_get_wp_user_for_person( $people->ID );
												$user_position = xprofile_get_field_data( 'Job Title', $person_wp_user_id );
												if ( $user_position != '' ) {
													echo "<span class='title'>Position : </span><span class='desc'>$user_position</span>";
												}
												?>
											</div>
											<div class="emp_timezone">
												<?php
												$user_timezone = xprofile_get_field_data( 'Timezone', $person_wp_user_id );
												if ( $user_timezone != '' )
													echo "<span class='title'>Timezone : </span><span class='desc'>$user_timezone</span>";
												?>
											</div>
											<div class="emp_country">
												<?php
												$emp_country = get_post_meta( $people->ID, 'rt_biz_contact_country', true );
												if ( $emp_country != '' )
													echo "<span class='title'>Country : </span><span class='desc'>$emp_country</span>";
												?>
											</div>
											<div class="emp_phone">
												<?php
												$phone_number = get_post_meta( $people->ID, 'rt_biz_contact_phone', true );
												if ( $phone_number != '' )
													echo "<span class='title'>Phone : </span><span class='desc'><a title='click to call' href='tel:$phone_number'>$phone_number</a></span>";
												?>
											</div>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<?php
					}
				}
				wp_reset_postdata();
				?>
			</tbody>
		</table>
	</div>
	<div class="rt-right-container">
		<?php
		$first_date = $dates[0];
		$last_date = $dates[count( $dates ) - 1];
		?>
		<a id="rtpm-get-prev-calender" class="rtpm-get-calender" href="#" data-flag="prev" data-date="<?php echo $first_date; ?>" data-calender="resources" data-project="<?php echo $project_id; ?>"><?php
			if ( wp_is_mobile() ) {
				echo "prev";
			} else {
				echo "<";
			}
			?></a>
		<table id="rtpm-resources-calender">
			<?php echo rt_create_resources_calender( $dates, $project_id ); ?>
		</table>
		<a id="rtpm-get-next-calender" class="rtpm-get-calender" href="#" data-flag="next" data-date="<?php echo $last_date; ?>" data-calender="resources" data-project="<?php echo $project_id; ?>"><?php
			if ( wp_is_mobile() ) {
				echo "next";
			} else {
				echo ">";
			}
			?></a>
	</div>
</div>
<?php
if ( isset( $_REQUEST['rt_task_id'] ) ) {
	global $rt_pm_task;
	$task_labels = $rt_pm_task->labels;
	$task_post_type = $rt_pm_task->post_type;
	$user_edit = true;
	$task_post = get_post( $_REQUEST['rt_task_id'], OBJECT );
	$task_post_type = $task_post->post_type;
	$form_ulr = "?update=true";
	$project_id = get_post_meta( $task_post->ID, 'post_project_id', TRUE );
	$createtimestamp = rt_convert_strdate_to_usertimestamp( $task_post->post_date_gmt );
	$createdate = $createtimestamp->format( "M d, Y h:i A" );
	$due = rt_convert_strdate_to_usertimestamp( get_post_meta( $task_post->ID, 'post_duedate', TRUE ) );
	$due_date = $due->format( "M d, Y h:i A" );
	$post_assignee = get_post_meta( $task_post->ID, 'post_assignee', TRUE );
	?>
	<div id="div-add-task" class="reveal-modal">

		<form method="post" id="form-add-post" data-posttype="<?php echo $task_post_type; ?>" action="<?php echo $form_ulr; ?>">
			<?php wp_nonce_field( 'rt_pm_task_edit', 'rt_pm_task_edit' ) ?>
			<input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $project_id; ?>" />
	<?php if ( isset( $task_post->ID ) && $user_edit ) { ?>
				<input type="hidden" name="post[post_id]" id='task_id' value="<?php echo $task_post->ID; ?>" />
					<?php } ?>
			<h4> <?php _e( 'Add New Task', RT_PM_TEXT_DOMAIN ); ?></h4>
			<div class="row">
				<div class="large-6 columns">
					<label><?php _e( ucfirst( $task_labels['name'] ) . " Name" ); ?><small class="required"> * </small></label>
					<?php if ( $user_edit ) { ?>
						<input required="required" name="post[post_title]" id="new_<?php echo $task_post_type ?>_title" type="text" placeholder="<?php _e( ucfirst( $task_labels['name'] ) . " Name" ); ?>" value="<?php echo ( isset( $task_post->ID ) ) ? $task_post->post_title : ""; ?>" />
					<?php } else { ?>
						<span><?php echo ( isset( $task_post->ID ) ) ? $task_post->post_title : ""; ?></span><br /><br />
					<?php } ?>
					<label><?php _e( "Message" ); ?><small class="required"> * </small></label>
					<?php
					if ( $user_edit ) {
						?>
						<textarea required="required" name="post[post_content]" rows="5" type="text" placeholder="<?php _e( "Message" ); ?>" ><?php echo ( isset( $task_post->ID ) ) ? trim( $task_post->post_content ) : ""; ?></textarea>
						<?php
						//wp_editor( ( isset( $post->ID ) ) ? $post->post_content : "", "post_content", array( 'textarea_name' => 'post[post_content]', 'media_buttons' => false, 'tinymce' => false, 'quicktags' => false, 'textarea_rows' => 5 ) );
					} else {
						echo 'Content : <br /><br /><span>' . (( isset( $task_post->ID ) ) ? trim( $task_post->post_content ) : '') . '</span><br /><br />';
					}
					?>
					<fieldset>
						<div class="row collapse">
							<div class="large-6 columns">
								<span class="status-hidden" title="Status"><label>Status<small class="required"> * </small></label></span>
							</div>
							<div class="large-6 columns push-1">
								<span class="create-date-hidden" title="Create Date"><label>Create Date<small class="required"> * </small></label></span>
							</div><hr />
						</div>

						<div class="row collapse">
							<div class="large-6 columns <?php echo (!$user_edit ) ? 'rtpm_attr_border' : ''; ?>">
								<span class="hidden" title="Status"><label>Status<small class="required"> * </small></label></span>
								<?php
								if ( isset( $task_post->ID ) )
									$pstatus = $task_post->post_status;
								else
									$pstatus = "";
								$post_status = $rt_pm_task->get_custom_statuses();
								$custom_status_flag = true;
								?>
									<?php if ( $user_edit ) { ?>
									<select required="required" id="rtpm_post_status" class="right" name="post[post_status]">
										<?php
										foreach ( $post_status as $status ) {
											if ( $status['slug'] == $pstatus ) {
												$selected = 'selected="selected"';
												$custom_status_flag = false;
											} else {
												$selected = '';
											}
											printf( '<option value="%s" %s >%s</option>', $status['slug'], $selected, $status['name'] );
										}
										?>
									<?php
									if ( $custom_status_flag && isset( $task_post->ID ) ) {
										echo '<option selected="selected" value="' . $pstatus . '">' . $pstatus . '</option>';
									}
									?>
									</select>
									<?php
								} else {
									foreach ( $post_status as $status ) {
										if ( $status['slug'] == $pstatus ) {
											echo '<span class="rtpm_view_mode">' . $status['name'] . '</span>';
											break;
										}
									}
								}
								?>
							</div>
							<div class="large-1 mobile-large-1 columns">&nbsp;&nbsp;&nbsp;&nbsp;</div>
							<div class="large-5 mobile-large-1 columns <?php echo (!$user_edit ) ? 'rtpm_attr_border' : ''; ?>">
								<span class="hidden" title="Create Date"><label>Create Date<small class="required"> * </small></label></span>
								<?php if ( $user_edit ) { ?>
									<input required="required" class="datetimepicker moment-from-now" name="post[post_date]" type="text" placeholder="Select Create Date"
										   value="<?php echo ( isset( $createdate ) ) ? $createdate : ''; ?>"
										   title="<?php echo ( isset( $createdate ) ) ? $createdate : ''; ?>" id="create_<?php echo $task_post_type ?>_date">

	<?php } else { ?>
									<span class="rtpm_view_mode moment-from-now"><?php echo $createdate ?></span>
	<?php } ?>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<div class="row collapse">
							<div class="large-6 columns push-1">
								<span class="due-date-hidden" title="Due Date"><label>Due Date<small class="required"> * </small></label></span>
							</div><hr />
						</div>
						<div class="row collapse">
							<div class="large-5 mobile-large-1 columns <?php echo (!$user_edit ) ? 'rtpm_attr_border' : ''; ?>">
								<span class="hidden" title="Due Date"><label>Due Date<small class="required"> * </small></label></span>
								<?php if ( $user_edit ) { ?>
									<input class="datetimepicker moment-from-now" type="text" name="post[post_duedate]" placeholder="Select Due Date"
										   value="<?php echo ( isset( $due_date ) ) ? $due_date : ''; ?>"
										   title="<?php echo ( isset( $due_date ) ) ? $due_date : ''; ?>" id="due_<?php echo $task_post_type ?>_date">

	<?php } else { ?>
									<span class="rtpm_view_mode moment-from-now"><?php echo $duedate ?></span>
					<?php } ?>
							</div>
						</div>
					</fieldset>
				</div>
				<div class="large-6 column">
					<?php
					$attachments = array();
					if ( isset( $task_post->ID ) ) {
						$attachments = get_posts( array(
							'posts_per_page' => -1,
							'post_parent' => $task_post->ID,
							'post_type' => 'attachment',
								) );
					}
					?>
					<div class="inside">
						<div class="row collapse" id="attachment-container">
	<?php if ( $user_edit ) { ?>
								<a href="#" class="button right" id="add_pm_attachment"><?php _e( 'Add Docs' ); ?></a>
	<?php } ?>
							<div class="scroll-height">
								<table>
									<?php if ( !empty( $attachments ) ) { ?>
										<tr>
											<th scope="column">Type</th>
											<th scope="column">Name</th>
											<th scope="column">Size</th>
											<th scope="column"></th>
										</tr>
													<?php foreach ( $attachments as $attachment ) { ?>
														<?php $extn_array = explode( '.', $attachment->guid );
														$extn = $extn_array[count( $extn_array ) - 1];
														?>
											<tr class="large-12 mobile-large-3 attachment-item" data-attachment-id="<?php echo $attachment->ID; ?>">
												<td scope="column"><img height="20px" width="20px" src="<?php echo RT_PM_URL . "app/assets/file-type/" . $extn . ".png"; ?>" /></td>
												<td scope="column">
													<a target="_blank" href="<?php echo wp_get_attachment_url( $attachment->ID ); ?>">
													<?php echo '<span>' . $attachment->post_title . "." . $extn . '</span>'; ?>
													</a>
												</td>
												<td scope="column">
													<?php
													$attached_file = get_attached_file( $attachment->ID );
													if ( file_exists( $attached_file ) ) {
														$bytes = filesize( $attached_file );
														$response['filesizeInBytes'] = $bytes;
														echo '<span>' . $response['filesizeHumanReadable'] = size_format( $bytes ) . '</span>';
													}
													?>
												</td>
												<td scope="column">
											<?php if ( $user_edit ) { ?>
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
	<script>
		jQuery( document ).ready( function( $ ) {
			setTimeout( function() {
				$( "#div-add-task" ).reveal( {
					opened: function() {
					}
				} );
			}, 10 );
		} );
	</script>
	<?php
}
