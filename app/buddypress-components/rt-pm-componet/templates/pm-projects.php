<?php
	global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_bp_pm_project, $bp, $wpdb,  $wp_query,$rt_person;
	
	if (isset($_GET['rt_project_id']) || isset($_GET['post_type']) && ($_GET['action'] != 'archives')){
		$rt_pm_bp_pm_project->custom_page_ui();
	} else {
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$paged = $page = max( 1, get_query_var('paged') );
		
		$posts_per_page = 20;
		
		$order = 'DESC';
		$attr = 'startdate';
		
		$meta_key = 'post_duedate';

		

		$offset = ( $paged - 1 ) * $posts_per_page;
		if ($offset <=0) {
			$offset = 0;
		}
		$post_status = array( 'new', 'active', 'paused','complete', 'closed' );

		$archive_text = __('Archive');
		
		
		$args = array(
			'post_type' => $rt_pm_project->post_type,
			'post_status' => $post_status,
			'posts_per_page' => $posts_per_page,
			'offset' => $offset
		);



		if( isset( $_GET['orderby'] ) ) {

			$order_by = $_GET['orderby'];

			$args['orderby'] = $order_by;
			$args['order'] =  $_GET['order'];
			if( $order_by == 'meta_value' ){

				$args['meta_key'] = $_GET['meta_key'];
			}

		}


        if ( bp_is_current_action('projects') ) {
            $args['post_status'] = $post_status;
            $archive = 'archive';
        }elseif( bp_is_current_action('archives') ) {
            $archive_text = __('Unarchive');
            $archive = 'unarchive';
            $args['post_status'] = 'trash';
        }
		
		/*echo "<pre>";
		print_r($args);
		echo "</pre>";*/
		
		$columns = array(
            array(
                    'column_label' => __( 'Name', RT_PM_TEXT_DOMAIN ) ,
                    'sortable' => true,
                    'orderby' => 'title',
                    'order' => 'asc'
            ),
			array(
                    'column_label' => __( 'Job Number', RT_PM_TEXT_DOMAIN ) ,
                    'sortable' => true,
                    'orderby' => 'meta_value',
					'meta_key' => 'rt_pm_job_no',
                    'order' => 'asc'
            ),
            array(
                    'column_label' => __( 'Type', RT_PM_TEXT_DOMAIN ) ,
                    'sortable' => true,
                    'orderby' => 'rt_project-type',
                    'order' => 'asc'
            ),
            array(
                    'column_label' => __( 'PM', RT_PM_TEXT_DOMAIN ) ,
                    'sortable' => true,
                    'orderby' => 'project_manager',
                    'order' => 'asc'
                  
            ),
            array(
                    'column_label' => __( 'BDM', RT_PM_TEXT_DOMAIN ),
                    'sortable' => true,
                    'orderby' => 'business_manager',
                    'order' => 'asc'
            ),

    	);
		
		// The Query
		$the_query = new WP_Query( $args );
		$totalPage= $max_num_pages =  $the_query->max_num_pages;
        $editor_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'editor' );
		?>
        <div class="list-heading">
		    <div class="large-10 columns list-title">
				<?php if( bp_is_current_action('resources') ) { ?>
					<h4><?php _e( 'Resources', RT_PM_TEXT_DOMAIN ) ?></h4>
				<?php }else if( bp_is_current_action('all-resources') ){?>
					<h4><?php _e( 'All Resources', RT_PM_TEXT_DOMAIN ) ?></h4>
				<?php }else if( bp_is_current_action('my-tasks') ){?>
					<h4><?php _e( 'My Tasks', RT_PM_TEXT_DOMAIN ) ?></h4>
				<?php }else{ ?>
					<h4><?php _e( 'Projects', RT_PM_TEXT_DOMAIN ) ?></h4>
				<?php } ?>
		    </div>
		    <div class="large-2 columns">
		       
		    </div>
		</div>
		<?php if( !bp_is_current_action('resources') && !bp_is_current_action('all-resources') && !bp_is_current_action('my-tasks') ) { ?>
		<table class="responsive">
			<thead>
				<tr>
                  <?php foreach ( $columns as $column ) {
                  ?>
                        <th>
                            <?php
                            if(  $column['sortable']  ) {

								$query_sting = array( 'orderby' => $column['orderby'] );

								if( isset( $column['meta_key'] ) )
									$query_sting['meta_key'] = $column['meta_key'];

                                    if ( isset( $_GET['orderby'] ) && $column['orderby']  == $_GET['orderby'] ) {
                                       
                                        $current_order = $_GET['order'];
                                       
                                        $order = 'asc' == $current_order ? 'desc' : 'asc';

										$query_sting['order'] = $order;

                                        printf( __('<a href="%s">%s <i class="fa fa-sort-%s"></i> </a>'), esc_url( add_query_arg(  $query_sting  ) ), $column['column_label'], $order );
                                        
                                    }else{

											$query_sting['order'] = 'desc';
                                          printf( __('<a href="%s">%s <i class="fa fa-sort"></i> </a>'), esc_url( add_query_arg( $query_sting ) ), $column['column_label'] );
                                    }
                                  
                            }else{
                                    echo $column['column_label'];
                            }

                            ?>
                        </th>
                <?php  } ?>
                </tr>
                </thead>
				<tbody>
				<?php
				if ( $the_query->have_posts() ) {
					while ( $the_query->have_posts() ) { ?>
						<?php
						$the_query->the_post();
						$get_the_id =  get_the_ID();
						$get_user_meta = get_post_meta($get_the_id);
						$project_manager_id = get_post_meta( $get_the_id, 'project_manager', true );
						$business_manager_id = get_post_meta( $get_the_id, 'business_manager', true );
						
						$project_end_date_value = get_post_meta( $get_the_id, 'post_duedate', true );
						if (! empty($project_end_date_value)) {
							$project_end_date_value = strtotime( $project_end_date_value );
							$project_end_date_value = date( 'd-m-Y', (int) $project_end_date_value );
						}
						
						$project_manager_info = get_user_by( 'id', $project_manager_id );
						if ( ! empty( $project_manager_info->user_nicename ) ){							
							$project_manager_nicename = $project_manager_info->display_name;
						}
						
//						$business_manager_info = get_user_by( 'id', $business_manager_id );
//						if ( ! empty( $business_manager_info->user_nicename ) ){
//							$business_manager_nicename = $business_manager_info->display_name;
//						}
//
						//Returns Array of Term Names for "rt-leave-type"
						$rt_project_type_list = wp_get_post_terms( $get_the_id, 'rt_project-type', array("fields" => "names")); // tod0:need to call in correct way
						if ( bp_loggedin_user_id() == bp_displayed_user_id() ) {
						?>
						
						<tr>
							<td>
							<?php the_title();
                             if( current_user_can( $editor_cap )  || get_current_user_id() == intval( get_the_author_meta('ID') ) ) {
                            ?>
							<div class="row-actions">
								<?php
								if( bp_is_current_action('projects') )
									printf( __('<a href="%s">' . __( 'Edit | ', RT_PM_TEXT_DOMAIN ) . '</a>'), esc_url( add_query_arg( array( 'rt_project_id'=> $get_the_id, 'post_type' =>'rt_project','tab' => 'rt_project-details' ,'action'=>'edit' ), $rt_pm_bp_pm->get_component_root_url().'details' ) ) );
								printf( __('<a class="hidden-for-small-only" href="%s">' . __( 'View', RT_PM_TEXT_DOMAIN ) . '</a>'), esc_url( add_query_arg( array( 'rt_project_id'=> $get_the_id, 'post_type' =>'rt_project','tab' => 'rt_project-details' ,'action'=>'view' ), $rt_pm_bp_pm->get_component_root_url().'details' ) ) );
								printf( __('<span class="hidden-for-small-only"> | </span><a href="%s">' . __( $archive_text, RT_PM_TEXT_DOMAIN ).'</a>'), esc_url( add_query_arg( array( 'rt_project_id'=> $get_the_id, 'action'=> $archive ) ) ) );
								if( bp_is_current_action('projects') )
									//printf( __('<span class="hidden-for-small-only"> | </span><a class="hidden-for-small-only deletepostlink" href="%s">' . __( 'Delete', RT_PM_TEXT_DOMAIN ) . '</a>'), esc_url( add_query_arg( array( 'rt_project_id'=> $get_the_id, 'action'=>'trash' ) ) ) );
								?>
							</div>
                                 <?php } ?>
							</td>

							<td>
								<?php echo get_post_meta( $get_the_id, 'rt_pm_job_no', true ); ?>
							</td>

							<td>
								<?php if ( ! empty( $rt_project_type_list ) ) echo $rt_project_type_list[0]; ?>
							</td>
							<td><?php if ( ! empty( $project_manager_info->user_nicename ) ) echo $project_manager_nicename; ?></td>
							<td><?php if ( ! empty(  $business_manager_id ) ) echo rt_get_user_displayname( $business_manager_id ); ?></td>
							<!--<td><?php echo get_the_date('d-m-Y');?></td>
							<td><?php if ( ! empty( $project_end_date_value ) ) echo $project_end_date_value;?></td> -->
						</tr>
						<?php
						} 
					}
				} else {
					?>
					<tr><td colspan="6" align="center" scope="row"><?php _e( 'No Project Listing', RT_PM_TEXT_DOMAIN ); ?></td></tr>
					<?php
				}
				wp_reset_postdata();
				?>
			</tbody>
		</table>
	<?php }else if( bp_is_current_action('all-resources') ) {
		$current_date = date("Y-m-d");
		$dates = rt_get_next_dates( $current_date );
		
		// lets start with all projects
		// $the_query contains all projects
		if ( $the_query->have_posts() ) { ?>
			<div class="rt-main-resources-container rt-all-resources-container">
				<div><a href="#" id="export-csv" class="rt-export-button">Export CSV</a></div>
				<div><a href="#" id="export-pdf" class="rt-export-button">Export PDF</a></div>
				<div class="rt-left-container">
					<table>
						<thead>
							<tr>
								<td>
									<?php _e( 'Resources by project', RT_PM_TEXT_DOMAIN ); ?>
								</td>
							</tr>
						</thead>
						<tbody>
				<?php	while ( $the_query->have_posts() ) { ?>
						<?php
						$the_query->the_post();
						$get_the_id =  get_the_ID();
						//project manager & project members
						$team_member = get_post_meta($get_the_id, "project_member", true);
						if( $team_member != '' && !empty($team_member) ){
							?>
								<tr><td class="rt_project_resources_title"><?php the_title(); ?></td></tr>	
							<?php
					foreach ( $team_member as $key => $member_id) {
						$user = get_userdata( $member_id);
						$people = rt_biz_get_person_for_wp_user($member_id);
						if( !empty($people) ) {
						$people = $people[0];
						$employee_name = $people->post_title;
						if( $employee_name == '' ){
							$employee_name = $people->post_name;
						}
						?>
						<tr>
							<td class="rt_project_assignee">
								<div class="rtpm-show-user-tooltip">
							<?php echo $employee_name; ?>
							<div class="rtpm-task-info-tooltip">
								<div class="large-3 columns">
									<?php
										$val = Rt_Person::get_meta( $people->ID, $rt_person->user_id_key );  
										if (!empty($val) ){
											echo  get_avatar( $val[0], 32 );
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
										if($user_position != ''){
											echo "<span class='title'>Position : </span><span class='desc'>$user_position</span>";
										}
										?>
									</div>
									<div class="emp_timezone">
										<?php 
										$user_timezone = xprofile_get_field_data( 'Timezone', $person_wp_user_id );
										if( $user_timezone != '' )
										echo "<span class='title'>Timezone : </span><span class='desc'>$user_timezone</span>";
										?>
									</div>
									<div class="emp_country">
										<?php
										$emp_country = get_post_meta( $people->ID ,'rt_biz_contact_country',true);
										if( $emp_country != '' )
										echo "<span class='title'>Country : </span><span class='desc'>$emp_country</span>";
										?>
									</div>
									<div class="emp_phone">
										<?php
										$phone_number = get_post_meta( $people->ID ,'rt_biz_contact_phone',true);
										if( $phone_number != '' )
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
						}
					}?>
						</tbody></table></div>
			<div class="rt-right-container">
				<?php 
				$first_date = $dates[0];
				$last_date = $dates[count($dates)-1];
				?>
				<a id="rtpm-get-prev-calender" class="rtpm-get-calender" href="#" data-flag="prev" data-date="<?php echo $first_date; ?>" data-calender="all-resources"><?php if( wp_is_mobile() ){ echo "prev";}else{ echo "<"; } ?></a>
				<table id="rtpm-resources-calender">
					<?php echo rt_create_all_resources_calender( $dates ); ?>
				</table>
				<a id="rtpm-get-next-calender" class="rtpm-get-calender" href="#" data-flag="next" data-date="<?php echo $last_date; ?>" data-calender="all-resources"><?php if( wp_is_mobile() ){ echo "next";}else{ echo ">"; } ?></a>
			</div>
			</div>	
					<?php
					wp_reset_postdata();
		}
	} else if( bp_is_current_action('my-tasks') ) {
		global $rt_pm_task;

		$current_date = date("Y-m-d");
		$dates = rt_get_next_dates( $current_date );
		$project_array = rt_get_project_task_list();
		// lets start with all projects
		if ( !empty($project_array) ) { ?>
			<div class="rt-main-resources-container rt-my-tasks-container">
				<div><a href="#" id="export-csv" class="rt-export-button">Export CSV</a></div>
				<div><a href="#" id="export-pdf" class="rt-export-button">Export PDF</a></div>
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
				<?php foreach ( $project_array as $key => $value ) { ?>
						<?php
						$project_id = $key;
						$task_list = $value;
						?>
							<tr><td class="rt_project_resources_title"><?php echo get_the_title($project_id); ?></td></tr>	
						<?php
					foreach ( $task_list as $key => $task_data) {
						$createtimestamp = rt_convert_strdate_to_usertimestamp( $task_data->post_date );
						$createdate = $createtimestamp->format("M d, Y h:i A");
						$due = rt_convert_strdate_to_usertimestamp( get_post_meta($task_data->ID,'post_duedate',TRUE) );
						$due_date = $due->format("M d, Y h:i A");
						$progress = $rt_pm_task->rtpm_get_task_progress_percentage( $task_data->ID ) / 100;
						?>
						<tr>
							<td class="rt_project_tasks">
								<div class="rtpm-show-user-tooltip rtpm-task-icon">
							<?php echo $task_data->post_title; ?>
							<div class="rtpm-task-info-tooltip">
								<p><b>Task : </b><a href="?rt_task_id=<?php echo $task_data->ID ; ?>"><?php echo $task_data->post_title; ?></a></p>
								<p><b>Status : </b><?php echo $task_data->post_status; ?></p>
								<p><b>Progress : </b><?php echo $progress.' %'; ?></p>
								<p><b>Start Date : </b><?php echo $createdate; ?></p>
								<p><b>Due Date : </b><?php echo $due_date; ?></p>
							</div>
						</div>
							</td>
						</tr>		
						<?php
					}
					}?>
						</tbody></table></div>
			<div class="rt-right-container">
				<?php 
				$first_date = $dates[0];
				$last_date = $dates[count($dates)-1];
				?>
				<a id="rtpm-get-prev-calender" class="rtpm-get-calender" href="#" data-flag="prev" data-date="<?php echo $first_date; ?>" data-calender="my-tasks"><?php if( wp_is_mobile() ){ echo "prev";}else{ echo "<"; } ?></a>
				<table id="rtpm-resources-calender">
					<?php echo rt_create_my_task_calender( $dates ); ?>
				</table>
				<a id="rtpm-get-next-calender" class="rtpm-get-calender" href="#" data-flag="next" data-date="<?php echo $last_date; ?>" data-calender="my-tasks"><?php if( wp_is_mobile() ){ echo "next";}else{ echo ">"; } ?></a>
			</div>
			</div>	<?php
		}else{?>
		<div>No tasks to show</div>	
		<?php
		}
	}
	if(isset($_REQUEST['rt_task_id'])){
		global $rt_pm_task;
			$task_labels=$rt_pm_task->labels;
			$task_post_type=$rt_pm_task->post_type;
			$user_edit = true;
			$task_post = get_post( $_REQUEST['rt_task_id'], OBJECT );
			$task_post_type = $task_post->post_type;
			$form_ulr = "?update=true";
			$project_id = get_post_meta( $task_post->ID,'post_project_id', TRUE );
			$createtimestamp = rt_convert_strdate_to_usertimestamp( $task_post->post_date_gmt );
			$createdate = $createtimestamp->format("M d, Y h:i A");
			$due = rt_convert_strdate_to_usertimestamp( get_post_meta($task_post->ID,'post_duedate',TRUE) );
			$due_date = $due->format("M d, Y h:i A");
			$post_assignee = get_post_meta($task_post->ID, 'post_assignee', TRUE);
	?>
 <div id="div-add-task" class="reveal-modal">

	                <form method="post" id="form-add-post" data-posttype="<?php echo $task_post_type; ?>" action="<?php echo $form_ulr; ?>">
                        <?php wp_nonce_field('rt_pm_task_edit','rt_pm_task_edit') ?>
	                    <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $project_id; ?>" />
	                    <?php if (isset($task_post->ID) && $user_edit ) { ?>
	                        <input type="hidden" name="post[post_id]" id='task_id' value="<?php echo $task_post->ID; ?>" />
	                    <?php } ?>
	                    <h4> <?php  _e( 'Add New Task', RT_PM_TEXT_DOMAIN ) ; ?></h4>
	                    <div class="row">
	                        <div class="large-6 columns">
	                        	<label><?php _e(ucfirst($task_labels['name'])." Name"); ?><small class="required"> * </small></label>
	                            <?php if( $user_edit ) { ?>
	                                <input required="required" name="post[post_title]" id="new_<?php echo $task_post_type ?>_title" type="text" placeholder="<?php _e(ucfirst($task_labels['name'])." Name"); ?>" value="<?php echo ( isset($task_post->ID) ) ? $task_post->post_title : ""; ?>" />
	                            <?php } else { ?>
	                                <span><?php echo ( isset($task_post->ID) ) ? $task_post->post_title : ""; ?></span><br /><br />
	                            <?php } ?>
	                            <label><?php _e("Message"); ?><small class="required"> * </small></label>
	                            <?php
	                            if( $user_edit ) {
	                            	?>
	                            	<textarea required="required" name="post[post_content]" rows="5" type="text" placeholder="<?php _e("Message"); ?>" ><?php echo ( isset($task_post->ID ) ) ? trim($task_post->post_content) : ""; ?></textarea>
	                                <?php
	                                //wp_editor( ( isset( $post->ID ) ) ? $post->post_content : "", "post_content", array( 'textarea_name' => 'post[post_content]', 'media_buttons' => false, 'tinymce' => false, 'quicktags' => false, 'textarea_rows' => 5 ) );
	                            } else {
	                                echo  'Content : <br /><br /><span>'.(( isset($task_post->ID) ) ? trim($task_post->post_content) : '').'</span><br /><br />';
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
	                        		<div class="large-6 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
	                        			<span class="hidden" title="Status"><label>Status<small class="required"> * </small></label></span>
		                                <?php
		                                if (isset($task_post->ID))
		                                    $pstatus = $task_post->post_status;
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
		                                        <?php if ( $custom_status_flag && isset( $task_post->ID ) ) { echo '<option selected="selected" value="'.$pstatus.'">'.$pstatus.'</option>'; } ?>
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
		                            <div class="large-1 mobile-large-1 columns">&nbsp;&nbsp;&nbsp;&nbsp;</div>
		                            <div class="large-5 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
		                            	<span class="hidden" title="Create Date"><label>Create Date<small class="required"> * </small></label></span>
		                                <?php if( $user_edit ) { ?>
		                                    <input required="required" class="datetimepicker moment-from-now" name="post[post_date]" type="text" placeholder="Select Create Date"
		                                           value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
		                                           title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>" id="create_<?php echo $task_post_type ?>_date">

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
		                                <?php if( $user_edit ) {
                                            rt_pm_render_task_assignee_selectbox( $post_assignee );
                                        } ?>
		                            </div>
		                            <div class="large-1 mobile-large-1 columns">&nbsp;&nbsp;&nbsp;&nbsp;</div>
		                            <div class="large-5 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
		                                <span class="hidden" title="Due Date"><label>Due Date<small class="required"> * </small></label></span>
		                                <?php if( $user_edit ) { ?>
		                                    <input class="datetimepicker moment-from-now" type="text" name="post[post_duedate]" placeholder="Select Due Date"
		                                           value="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>"
		                                           title="<?php echo ( isset($due_date) ) ? $due_date : ''; ?>" id="due_<?php echo $task_post_type ?>_date">

		                                <?php } else { ?>
		                                    <span class="rtpm_view_mode moment-from-now"><?php echo $duedate ?></span>
		                                <?php } ?>
		                            </div>
	                        	</div>
	                        	</fieldset>
	                        </div>
	                        <div class="large-6 column">
	                        	<?php $attachments = array();
		                        if ( isset( $task_post->ID ) ) {
		                            $attachments = get_posts( array(
		                                'posts_per_page' => -1,
		                                'post_parent' => $task_post->ID,
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
		<?php }
       /*if ( $max_num_pages > 1 ) { ?>
		<ul id="projects-pagination"><li id="prev"><a class="page-link"> &laquo; Previous</a></li><li id="next"><a class="page-link next">Next &raquo;</a></li></ul>
		<?php } */
		pm_pagination($totalPage, $page);
	} 
	?>
