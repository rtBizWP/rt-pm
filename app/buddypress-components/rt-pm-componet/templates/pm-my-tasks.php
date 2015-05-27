<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

global $rt_pm_task, $rt_pm_task_resources_model;

$current_date = date( "Y-m-d" );
$dates = rt_get_next_dates( $current_date );
$project_ids = $rt_pm_task_resources_model->rtpm_get_users_projects( bp_displayed_user_id() );

// lets start with all projects
if ( !empty( $project_ids ) ) {
	?>
	<div class="list-heading">
		<div class="large-10 columns list-title">
			<h4><?php _e( 'My Tasks', RT_PM_TEXT_DOMAIN ) ?></h4>
		</div>
		<div class="large-2 columns">

		</div>
	</div>

	<div class="rt-main-resources-container rt-my-tasks-container">
		<div class="rt-export-button-container"><a href="#" class="rt-export-button export-csv">Export CSV</a></div>
		<div class="rt-export-button-container"><a href="#" class="rt-export-button export-pdf">Export PDF</a></div>
		<div class="rt-left-container">
			<table>
				<thead>
					<tr>
						<td>
							<?php _e( 'My Tasks by Project', RT_PM_TEXT_DOMAIN ); ?>
						</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $project_ids as $key => $project_id ) { ?>
						<?php

						$task_ids = $rt_pm_task_resources_model->rtpm_get_all_task_id_by_user( bp_displayed_user_id(), $project_id );

						$task_list = $rt_pm_task->rtpm_get_task_data( array( 'post__in' => $task_ids ) );
						?>
						<tr><td class="rt_project_resources_title"><?php echo mb_strimwidth( get_post_field( 'post_title', $project_id ), 0, 25, '..' ); ?></td></tr>
						<?php
						foreach ( $task_list as $key => $task_data ) {
							$createtimestamp = rt_convert_strdate_to_usertimestamp( $task_data->post_date );
							$createdate = $createtimestamp->format( "M d, Y h:i A" );
							$due = rt_convert_strdate_to_usertimestamp( get_post_meta( $task_data->ID, 'post_duedate', TRUE ) );
							$due_date = $due->format( "M d, Y h:i A" );
							$progress = $rt_pm_task->rtpm_get_task_progress_percentage( $task_data->ID ) / 100;
							?>
							<tr>
								<td class="rt_project_tasks">
									<div class="rtpm-show-user-tooltip rtpm-task-icon">
										<?php echo mb_strimwidth( $task_data->post_title, 0, 20 , '..' ); ?>
										<div class="rtpm-task-info-tooltip">
											<p><b>Task : </b><a href="?rt_task_id=<?php echo $task_data->ID; ?>"><?php echo $task_data->post_title; ?></a></p>
											<p><b>Status : </b><?php echo $task_data->post_status; ?></p>
											<p><b>Progress : </b><?php echo $progress . ' %'; ?></p>
											<p><b>Start Date : </b><?php echo $createdate; ?></p>
											<p><b>Due Date : </b><?php echo $due_date; ?></p>
										</div>
									</div>
								</td>
							</tr>		
							<?php
						}
					}
					?>
				<tr>
					<th>TOTAL HOURS</th>
				</tr>
				</tbody></table></div>
		<div class="rt-right-container">
			<?php
			$first_date = $dates[0];
			$last_date = $dates[count( $dates ) - 1];
			?>
			<a id="rtpm-get-prev-calender" class="rtpm-get-calender" data-flag="prev" data-date="<?php echo $first_date; ?>" data-calender="my-tasks"><?php
				if ( wp_is_mobile() ) {
					echo "prev";
				} else {
					echo "<";
				}
				?></a>
			<table id="rtpm-resources-calender">
				<?php echo rt_create_my_task_calender( $dates ); ?>
			</table>
			<a id="rtpm-get-next-calender" class="rtpm-get-calender" data-flag="next" data-date="<?php echo $last_date; ?>" data-calender="my-tasks"><?php
				if ( wp_is_mobile() ) {
					echo "next";
				} else {
					echo ">";
				}
				?></a>
		</div>
		<div class="rt-export-button-container"><a href="#" class="rt-export-button export-csv export-bottom">Export CSV</a></div>
		<div class="rt-export-button-container"><a href="#" class="rt-export-button export-pdf export-bottom">Export PDF</a></div>
	</div>	<?php } else {
				?>
	<div>No tasks to show</div>	
	<?php
}
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
									<?php if ( $custom_status_flag && isset( $task_post->ID ) ) {
										echo '<option selected="selected" value="' . $pstatus . '">' . $pstatus . '</option>';
									} ?>
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
							<div class="large-6 columns">		                                
								<span class="assigned-to-hidden" title="Assigned To"><label for="post[post_assignee]">Assigned To<small class="required"> * </small></label></span>
							</div>
							<div class="large-6 columns push-1">
								<span class="due-date-hidden" title="Due Date"><label>Due Date<small class="required"> * </small></label></span>
							</div><hr />
						</div>
						<div class="row collapse">
							<div class="large-6 columns">
								<span class="hidden" title="Assigned To"><label for="post[post_assignee]">Assigned To</label></span>
								<?php
								if ( $user_edit ) {
									//rt_pm_render_task_assignee_selectbox( $post_assignee );
								}
								?>
							</div>
							<div class="large-1 mobile-large-1 columns">&nbsp;&nbsp;&nbsp;&nbsp;</div>
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
													$extn = $extn_array[count( $extn_array ) - 1]; ?>
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