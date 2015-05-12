<?php

/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Description of BP HRM functions.
 *
 * @author kishore
 */


function pm_pagination( $totalPage, $page ){
    global $rt_pm_bp_pm;
   
    if( $totalPage > 1 ){
                                            
        $base = $rt_pm_bp_pm->get_component_root_url().bp_current_action().'/%_%';
        $formate = 'page/%#%';
        if( isset( $_GET['orderby'] ) ) {

                $arr_params = array( 'orderby' => $_GET['orderby'], 'order' => $_GET['order'] );
                $base = add_query_arg( $arr_params, $rt_pm_bp_pm->get_component_root_url().bp_current_action() ) .'%_%' ; 
                $formate = '&paged=%#%';
        }

        $customPagHTML     =  '<div class="projects-lists pagination" role="menubar" aria-label="Pagination"><span class="current">Page '.$page.' of '.$totalPage.'</span>'.paginate_links( array(
        'base' => $base,
        'format' => $formate,
        'total' => $totalPage,
        'current' => $page
        )).'</div>';
        echo $customPagHTML;
    }
}

function render_project_summary_buttons( $post_id ){
	global $rt_pm_bp_pm;
	if (isset($post_id)) {
				$save_button = __( 'Update' );
			} else {
				$save_button = __( 'Add Project' );
			}
	?>
	<button class="mybutton" type="submit" ><?php _e($save_button); ?></button>
				<?php 
				if(isset($post_id)) { 
					$get_post_status = get_post_status( $post_id );
					if ( isset( $get_post_status ) && $get_post_status == 'trash' ){
						$archive_action = 'unarchive';
						$archive_button = __( 'Unarchive' );
						$button_archive_id = 'button-unarchive';
						$redirect = $rt_pm_bp_pm->get_component_root_url(). 'archives';
					} else {
						$archive_action = 'archive';
						$archive_button = __( 'Archive' );
						$button_archive_id = 'button-archive';
						$redirect = $rt_pm_bp_pm->get_component_root_url();
					}
					
				?>
				<button id="top-<?php echo $button_archive_id; ?>" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => $archive_action, 'rt_project_id' => $post_id ), $redirect ); ?>" class=""><?php _e($archive_button); ?></button>
				<button id="top-button-trash" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => 'trash', 'rt_project_id' => $post_id ), $redirect ); ?>" class=""><?php _e( 'Delete' ); ?></button>
				<?php  }
}

/**
 *  returns next dates array
 * @param string $date
 * @return array
 */

function rt_get_next_dates( $date ){
		$date_object = date_create( $date );
		$start = date_timestamp_get( $date_object );
		$dates=array();
		
		// if mobile show only 3 columns
		
		if( wp_is_mobile() ){
			$table_cols = 3;	
		}else{
			$table_cols = 9;
		}
		for($i = 0; $i<=$table_cols; $i++)
		{
			array_push($dates,date('Y-m-d', strtotime("+$i day", $start)));
		}
		return $dates;
}

/**
 *  Creates All Resources calender
 * @global type $rt_pm_project
 * @param array $dates
 * @return string
 */

function rt_create_all_resources_calender( $dates ){
	global $rt_pm_project,$rt_pm_task;
	if( isset($_SESSION['rt_project_post_per_page']) ){
			$posts_per_page = $_SESSION['rt_project_post_per_page'];
		}else{
			$posts_per_page = 25;
		}
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	$paged = $page = max( 1, get_query_var('paged') );
	$offset = ( $paged - 1 ) * $posts_per_page;
		if ($offset <=0) {
			$offset = 0;
		}
	$post_status = array( 'new', 'active', 'paused','complete', 'closed' );
	$args = array(
			'post_type' => $rt_pm_project->post_type,
			'post_status' => $post_status,
			'posts_per_page' => $posts_per_page,
			'offset' => $offset,
		);
	
	// Print dates in head
	
	$table_html = '<thead><tr>';
					foreach ( $dates as $key => $value ) {
					$table_html .= '<td>'.date_format(date_create($value),"d M").'</td>';
					}
	$table_html .= '</tr></thead><tbody>';
	$projects_query = new WP_Query( $args );
	
	// lets travel through all projects
	
	while ( $projects_query->have_posts() ) {
		$projects_query->the_post();
		$project_id = get_the_ID();
		
		// get team members for project
		
		$team_member = get_post_meta(  $project_id, "project_member", true);
			if( $team_member != '' && !empty($team_member) ){
				
				// No data to show for project so lets create a blank row for all dates
				
				$table_html .= '<tr>';
					foreach ( $dates as $key => $value ) {
						$table_html .= '<td style="height: 32px;"></td>';
					}
				$table_html .= '</tr>';
				
				// travel through all team members and print resources for each member
				
				foreach ( $team_member as $member_key => $member_id ) {
					$member_data = get_userdata($member_id);
					$person = rt_biz_get_person_for_wp_user($member_id);
					if( !empty($person) ){
					$table_html .= '<tr>';
					
					// get the data for each date
					
						foreach ( $dates as $key => $value ) {
							
							// No data to show on weekends
							
							$is_weekend = rt_isWeekend( $value);
							if($is_weekend){
								$weekend_class = "rt-weekend";
							}else{
								$weekend_class = "";
							}
							$table_html .= '<td class="'.$weekend_class.'">';
								if( !$is_weekend ){
									$person_id = $person[0]->ID;
									
									// get task list for user by project id on this date
									
									$tasks_array = rt_get_person_task_by_project($person_id,$value,$project_id);
									$table_html .= '<div class="rtpm-show-tooltip">'.count($tasks_array);
									if(!empty($tasks_array)){
										$table_html .= '<div class="rtpm-task-info-tooltip">';
										foreach ( $tasks_array as $key => $task ) {
											$progress = $rt_pm_task->rtpm_get_task_progress_percentage( $task->ID ) / 100;
											$createtimestamp = rt_convert_strdate_to_usertimestamp( $task->post_date );
											$createdate = $createtimestamp->format("M d, Y h:i A");
											$due = rt_convert_strdate_to_usertimestamp( get_post_meta($task->ID,'post_duedate',TRUE) );
											$due_date = $due->format("M d, Y h:i A");
											$table_html .= '<p><b>Task : </b><a href="?rt_task_id='.$task->ID.'">'.$task->post_title.'</a></p>';
											$table_html .= '<p><b>Status : </b>'.$task->post_status.'</p>';
											$table_html .= '<p><b>Progress : </b>'.$progress.' %</p>';
											$table_html .= '<p><b>Start date : </b>'.$createdate.'</p>';
											$table_html .= '<p><b>Due date : </b>'.$due_date.'</p>';
										}
										$table_html .= '</div></div>';
									}
									$table_html .= '</div>';
							}
							$table_html .= '</td>';
						}
					$table_html .= '</tr>';
				}
			}
		}
	}
	$table_html .= '</tbody>';
	return $table_html;
}

/**
 *  Creates my task calender
 * @global type $rt_pm_project
 * @param array $dates
 * @return string
 */

function rt_create_my_task_calender( $dates ){
	global $rt_pm_project;
	$project_array = rt_get_project_task_list();
	
	// print dates in head
	
	$table_html = '<thead><tr>';
					foreach ( $dates as $key => $value ) {
					$table_html .= '<td>'.date_format(date_create($value),"d M").'</td>';
					}
	$table_html .= '</tr></thead><tbody>';
	
	// lets travel through all projects
	
	foreach ( $project_array as $key => $value ) {
		$project_id = $key;
		$max_working_hours = get_post_meta($project_id,'working_hours',true);
		$flag_remaining = FALSE;
		$task_array = $value;
		
		// for project no data to show so lets create a blank row 
		
		$table_html .= '<tr>';
		foreach ( $dates as $date_key => $date_value ) {
			$table_html .= '<td style="height: 32px;"></td>';
		}
		$table_html .= '</tr>';
		
		// travel through all task list 
		
			foreach ( $task_array as $task_key => $task_data ) {
				$table_html .= '<tr>';
				$estimated_hours = get_post_meta($task_data->ID,'post_estimated_hours',true);
				
				// for each task travel through each date
				
					foreach ( $dates as $date_key => $date_value ) {
						
						// if weekend no data to show
						
						$is_weekend = rt_isWeekend( $date_value);
						if($is_weekend){
							$weekend_class = "rt-weekend";
						}else{
							$weekend_class = "";
						}
						$table_html .= '<td class="'.$weekend_class.'">';
							if( !$is_weekend ){
								$createtimestamp = rt_convert_strdate_to_usertimestamp( $task_data->post_date );
								$createdate = $createtimestamp->format("Y-m-d");
								
								// as we are only showing max working hours for each day
								// check if there are any remaining hours left from previous date
								
									if( $createdate == $date_value || $flag_remaining ){
										if( $flag_remaining ){
											$estimated_hours = $remaining_hours;
										}
										
										// if estimated hours for task are greater than max working hours
										// create remaining hours for the task to add in next day
										
										if( $estimated_hours > $max_working_hours ){
											$flag_remaining = TRUE;
											$remaining_hours = $estimated_hours - $max_working_hours;
											$estimated_hours = $max_working_hours;
										}else{
											$flag_remaining = FALSE;
										}
										$table_html .= '<div class="rtpm-show-tooltip">'.$estimated_hours;
										//$table_html .= '<div class="rtpm-task-info-tooltip"></div>';
										$table_html .= '</div>';
									}else{
										$table_html .= '<div class="rtpm-show-tooltip">0</div>';
									}
						} 
						$table_html .= '</td>';
				}
				$table_html .= '</tr>';
		}
	}
	$table_html .= '</tbody>';
	return $table_html;
}

/**
 *  Creates Resources calender
 * @global type $rt_person
 * @param array $dates 
 * @return string
 */

function rt_create_resources_calender( $dates, $project_id ){
	global $rt_person;
	$page = max( 1, get_query_var('paged') );		
		// print all dates in head
		
		$table_html = '<thead><tr>';
					foreach ( $dates as $key => $value ) {
					$table_html .= '<td>'.date_format(date_create($value),"d M").'</td>';
					}
		$table_html .= '</tr></thead><tbody>';
		
		// travel through all users
		$team_member = get_post_meta( $project_id, "project_member", true );
				if ( $team_member != '' && !empty( $team_member ) ) {
					?>
					<?php
					foreach ( $team_member as $key => $member_id ) {
						$user = get_userdata( $member_id );
						$people = rt_biz_get_person_for_wp_user( $member_id );
						$people = $people[0];
						$table_html .= '<tr>';
							foreach ( $dates as $key => $value ) {
								$is_weekend = rt_isWeekend( $value);
								if($is_weekend){
									$weekend_class = "rt-weekend";
								}else{
									$weekend_class = "";
								}
									$table_html .= '<td class="'.$weekend_class.'">';

									// no data to show on weekend

												if( !$is_weekend ){
													$tasks_array = rt_get_person_task($people->ID,$value);
													$table_html .= '<div class="rtpm-show-tooltip">'.count($tasks_array);
													if(!empty($tasks_array)){
														$table_html .= '<span class="rtpm-task-info-tooltip"><ul>';
														foreach ( $tasks_array as $key => $task ) {
															$table_html .= '<li><a href="?post_type=rt_project&rt_project_id='.$project_id.'&tab=rt_resources-details&rt_task_id='.$task->ID.'">'.$task->post_title.'</a></li>';
														}
														$table_html .= '</ul></span></div>';
													}
													$table_html .= '</div>';
												} 
									$table_html .= '</td>';
										}
						$table_html .=	'</tr>';
					}
				}
		$table_html .=	'</tbody>';
		return $table_html;
}

/**
 *  Returns task list by person for specific date
 * @global type $rt_pm_task
 * @global type $rt_person
 * @param type $person_id
 * @param type $date
 * @return type array
 */

function rt_get_person_task($person_id,$date){
	global $rt_pm_task, $rt_person;
	// get month, year, day by exploading
	$date_array = explode("-",$date);
	$args = array(
                'post_type' => $rt_pm_task->post_type,
                'post_status' => 'any',
                'connected_type' => $rt_pm_task->post_type . '_to_' . $rt_person->post_type,
                'connected_items' => $person_id,
                'nopaging' => true,
                'suppress_filters' => false,
				'year' => $date_array[0],
				'monthnum' => $date_array[1],
				'day' => $date_array[2],
                );
	$wp_query = new WP_Query();
    $wp_query->query( $args );
	wp_reset_postdata();
	return $wp_query->posts;
}

/**
 *  Returns task list by project id
 * @global type $rt_pm_task
 * @param type $project_id
 * @return type array
 */

function rt_get_task_by_project( $project_id ){
	
	global $rt_pm_task;
	// get month, year, day by exploading
	$args = array(
                'post_type' => $rt_pm_task->post_type,
                'post_status' => 'any',
                'nopaging' => true,
                'suppress_filters' => false,
				'meta_query'             => array(
						array(
							'key'       => 'post_project_id',
							'value'     => $project_id,
						),
					),
                );
	$wp_query = new WP_Query();
    $wp_query->query( $args );
	return $wp_query->posts;
}

add_action( 'wp_ajax_rtpm_validate_estimated_date', 'rtpm_validate_estimated_date_ajax' );
add_action( 'wp_ajax_nopriv_rtpm_validate_estimated_date', 'rtpm_validate_estimated_date_ajax' );

/**
 *  Function to check if estimated hours are less than max working hours
 */

function rtpm_validate_estimated_date_ajax(){
	
	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];
	$est_time = $_POST['est_time'];
	$project_id = $_POST['project_id'];
	$max_working_hours = get_post_meta($project_id,'working_hours',true);
	$first_date = date_create_from_format( 'M d, Y H:i A', $start_date );
	$second_date = date_create_from_format( 'M d, Y H:i A', $end_date );
	$diff = $second_date->diff($first_date);
	$diff_hours = $diff->h;
	
	// hours between start date and end date
	
	$diff_hours = $diff_hours + ($diff->days*(int)$max_working_hours);

	if( (int)$diff_hours < (int)$est_time ){
		echo json_encode( array( 'fetched' => false,'diff'=>$diff_hours ) );
	}else{
		echo json_encode( array( 'fetched' => true ) );
	}
	die;
}
		
/*
 * function to create a project_id => task list array
 */

function rt_get_project_task_list(){
	$task_list = rt_get_task_by_user( get_current_user_id() );
		$project_array = array();
		if( !empty($task_list) ){
			foreach ( $task_list as $key => $value ) {
				$project_id = get_post_meta( $value->ID, 'post_project_id', true );
				if( !array_key_exists( $project_id, $project_array ) ){
				$project_array[ $project_id ] = array($value);
				}else{
					$project_list_array = $project_array[ $project_id ];
					array_push($project_list_array,$value);
					$project_array[ $project_id ] = $project_list_array;
				}
			}
		}
		return $project_array;
}

/*
 *  Returns task list for user
 */

function rt_get_task_by_user( $user_id ){
	
	global $rt_pm_task;
	// get month, year, day by exploading
	$args = array(
                'post_type' => $rt_pm_task->post_type,
                'post_status' => 'any',
                'nopaging' => true,
                'suppress_filters' => false,
				'meta_query'             => array(
						array(
							'key'       => 'post_assignee',
							'value'     => $user_id,
						),
					),
                );
	$wp_query = new WP_Query();
    $wp_query->query( $args );
	return $wp_query->posts;
	
}

/*
 *  Returns task list for user by date
 */

function rt_get_task_by_date( $user_id, $date ){
	
	global $rt_pm_task;
	// get month, year, day by exploading
	$date_array = explode("-",$date);
	$args = array(
                'post_type' => $rt_pm_task->post_type,
                'post_status' => 'any',
                'nopaging' => true,
                'suppress_filters' => false,
				'year' => $date_array[0],
				'monthnum' => $date_array[1],
				'day' => $date_array[2],
				'meta_query'             => array(
						array(
							'key'       => 'post_assignee',
							'value'     => $user_id,
						),
					),
                );
	$wp_query = new WP_Query();
    $wp_query->query( $args );
	return $wp_query->posts;
	
}

/**
 *  Returns task list by project id for person
 * @global type $rt_pm_task
 * @global type $rt_person
 * @param type $person_id
 * @param type $date
 * @param type $project_id
 * @return type array
 */

function rt_get_person_task_by_project($person_id,$date,$project_id){
	global $rt_pm_task, $rt_person;
	// get month, year, day by exploading
	$date_array = explode("-",$date);
	$args = array(
                'post_type' => $rt_pm_task->post_type,
                'post_status' => 'any',
                'connected_type' => $rt_pm_task->post_type . '_to_' . $rt_person->post_type,
                'connected_items' => $person_id,
                'nopaging' => true,
                'suppress_filters' => false,
				'year' => $date_array[0],
				'monthnum' => $date_array[1],
				'day' => $date_array[2],
				'meta_query'             => array(
						array(
							'key'       => 'post_project_id',
							'value'     => $project_id,
						),
					),
                );
	$wp_query = new WP_Query();
    $wp_query->query( $args );
	wp_reset_postdata();
	return $wp_query->posts;
}

/**
 *  Checks if the date is weekend
 * @param type $date
 * @return type boolean
 */

function rt_isWeekend($date) {
    return (date('N', strtotime($date)) >= 6);
}

function pm_get_attachment_data(){
  
    $meta = get_post( $_POST['attachment_id'] );
   
    echo json_encode( $meta );
    
    die(0);
}

add_action( 'wp_ajax_rtpmattachment_metadata', 'pm_get_attachment_data' );

function pm_save_attachment_data(){
  
  
    $args = array(
        'ID' => $_POST['ID'],
        'post_title' => $_POST['post_title'],
        'post_excerpt' => $_POST['post_excerpt'],
        'post_content' => $_POST['post_content'],
    );
   $post_id = wp_update_post( $args );
   echo $post_id;
   die();
  
}

add_action( 'wp_ajax_rtpmattachment_save_data', 'pm_save_attachment_data' );


function pm_add_new_documents(){

    
    $parent_post_id = $_POST['post_id'];
    $filename = $_POST['filename'];
    //var_dump($filenames);
    //foreach ( $filenames as $filename ) {
        
    
    // $filename should be the path to a file in the upload directory.
   

    // The ID of the post this attachment is for.
    //$parent_post_id = 37;

    // Check the type of tile. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype( basename( $filename ), null );

    // Get the path to the upload directory.
    $wp_upload_dir = wp_upload_dir();

    // Prepare an array of post data for the attachment.
    $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
    );

    // Insert the attachment.
    $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

   $data = array(
           'attachment_id'=>$attach_id
    );
    echo json_encode( $data );
    
    die();
}
add_action( 'wp_ajax_rtpm_add_new_documents', 'pm_add_new_documents'  );


function pm_remove_document(){
    
    $attachment_id = $_POST[ 'attachment_id' ];
    wp_delete_attachment( $attachment_id );
}
add_action( 'wp_ajax_rtpm_remove_document', 'pm_remove_document'  );


/**
 * Render BDM selectbox
 * @param $business_manager
 */
function rt_pm_render_bdm_selectbox( $business_manager ){ ?>

    <select name="post[business_manager]" >
        <option value=""><?php _e( 'Select BM' ); ?></option>
        <?php

        $employees = rt_biz_get_employees();

        if (!empty( $employees )) {
            foreach ($employees as $bm) {

                $employee_wp_user_id = rt_biz_get_wp_user_for_person( $bm->ID );

                if ( $employee_wp_user_id == $business_manager) {
                    $selected = " selected";
                } else {
                    $selected = " ";
                }
                echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rt_get_user_displayname( $employee_wp_user_id ) . '</option>';
            }
        }
        ?>
    </select>

<?php }

/**
 * Render BDM selectbox
 * @param $business_manager
 */
function rtpm_render_manager_selectbox( $project_manager ){ ?>
    <select name="post[project_manager]" >
        <option value=""><?php _e( 'Select PM' ); ?></option>
        <?php
        $employees = rt_biz_get_employees();

        if (!empty( $employees )) {
            foreach ($employees as $bm) {

                $employee_wp_user_id = rt_biz_get_wp_user_for_person( $bm->ID );

                if ( $employee_wp_user_id == $project_manager ) {
                    $selected = " selected";
                } else {
                    $selected = " ";
                }
                echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rt_get_user_displayname( $employee_wp_user_id ) . '</option>';
            }
        }
        ?>
    </select>
<?php }

/**
 * Get project edit url
 * @param $project_id
 * @return string
 */
function rtpm_bp_get_project_details_url( $project_id ) {
    global $rt_pm_bp_pm, $rt_pm_project;

    $project_edit_link = add_query_arg( array( 'rt_project_id' => $project_id, 'action' => 'edit', 'post_type' => $rt_pm_project->post_type, 'tab' => "{$rt_pm_project->post_type}-details" ), $rt_pm_bp_pm->get_component_root_url().'/details' );
    return $project_edit_link;
}

