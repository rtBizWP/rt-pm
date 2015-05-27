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
	global $rt_pm_task_resources_model;

	// Print dates in head

	$table_html = '<thead><tr>';
	foreach ( $dates as $key => $value ) {
		$table_html .= '<td>' . date_format( date_create( $value ), "d M" ) . '</td>';
	}
	$table_html .= '</tr></thead><tbody>';

	$project_ids = $rt_pm_task_resources_model->rtpm_get_resources_projects();

	$all_user_ids = array();

	foreach ( $project_ids as $project_id ) {

		$user_ids = $rt_pm_task_resources_model->rtpm_get_resources_users( array( 'project_id' => $project_id ) );

		//Hold all user ids
		$all_user_ids = array_merge( $all_user_ids, $user_ids );

		$table_html .= '<tr>';
		foreach ( $dates as $key => $value ) {
			$table_html .= '<td style="height: 32px;"></td>';
		}
		$table_html .= '</tr>';

		// travel through all team members and print resources for each member

		foreach ( $user_ids as $user_id ) {

			$table_html .= '<tr>';

			// get the data for each date

			foreach ( $dates as $key => $value ) {

				// No data to show on weekends

				$is_weekend = rt_isWeekend( $value );
				if ( $is_weekend ) {
					$weekend_class = "rt-weekend";
				} else {
					$weekend_class = "";
				}
				$table_html .= '<td class="' . $weekend_class . '">';
				if ( ! $is_weekend ) {

					$args = array(
						'user_id'    => $user_id,
						'timestamp'  => $value,
						'project_id' => $project_id,
					);

					$estimated_hours = $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );

					// get task list for user by project id on this date
					$table_html .= '<div class="rtpm-show-tooltip">' . $estimated_hours;
					$table_html .= '</div></div>';
				}
				$table_html .= '</div>';
			}

			$table_html .= '</td>';
		}
		$table_html .= '</tr>';
	}

	//Print total hours in tfooter
	$table_html .= '<tr>';
	foreach ( $dates as $key => $value ) {

		$is_weekend = rt_isWeekend( $value );
		if ( $is_weekend ) {
			$weekend_class = "rt-weekend";
		} else {
			$weekend_class = "";
		}
		$table_html .= '<td class="' . $weekend_class . '">';
		if ( ! $is_weekend ) {

			$args = array(
				'user__in'  => $all_user_ids,
				'timestamp' => $value,
				//'project_id'    =>  $project_id,
			);

			$estimated_hours = $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );

			// get task list for user by project id on this date
			$table_html .= '<div class="rtpm-show-tooltip">' . $estimated_hours;
			$table_html .= '</div></div>';
		}
		$table_html .= '</div>';
	}

	$table_html .= '</td>';

	$table_html .= '</tbody>';

	return $table_html;
}

/**
 *  Creates my task calender
 * @global type $rt_pm_project
 * @param array $dates
 * @return string
 */
function rt_create_my_task_calender( $dates ) {
	global $rt_pm_project, $rt_pm_task_resources_model;


	$project_ids = $rt_pm_task_resources_model->rtpm_get_users_projects( bp_displayed_user_id() );
	// print dates in head
	$table_html = '<thead><tr>';
	foreach ( $dates as $key => $value ) {
		$table_html .= '<th>' . date_format( date_create( $value ), "d M" ) . '</th>';
	}
	$table_html .= '</tr></thead><tbody>';

	// for project no data to show so lets create a blank row

	// travel through all task list

	$all_task_ids = array();
	$old_project_id = '0';
	foreach( $project_ids as $project_id ) {


		if( $old_project_id !== $project_id ) {

			$old_project_id = $project_id;
			$table_html .= '<tr><td colspan="10">'. get_post_field( 'post_title', $project_id ) .'</td></tr>';
		}

		$task_ids = $rt_pm_task_resources_model->rtpm_get_all_task_id_by_user( bp_displayed_user_id(), $project_id );

		$all_task_ids = array_merge( $all_task_ids, $task_ids );
		foreach ( $task_ids as  $task_id ) {
			$table_html .= '<tr>';
			// for each task travel through each date

			//$table_html .= '<td>'.get_post_field( 'post_title', $task_id ).'</td>';
			foreach ( $dates as $date_key => $date_value ) {
				// if weekend no data to show

				$is_weekend = rt_isWeekend( $date_value );
				if ( $is_weekend ) {
					$weekend_class = "rt-weekend";
				} else {
					$weekend_class = "";
				}
				$table_html .= '<td class="' . $weekend_class . '">';
				if ( ! $is_weekend ) {

					$args = array(
						'user_id'   =>  bp_displayed_user_id(),
						'task_id'   =>  $task_id,
						'timestamp' =>  $date_value
					);

					$estimated_hours = $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );
					$table_html .= '<div class="rtpm-show-tooltip">' . $estimated_hours;
					$table_html .= '</div>';
				}
				$table_html .= '</td>';
			}
			$table_html .= '</tr>';
		}
	}

	$table_html .= '<tr>';
	foreach ( $dates as $date_key => $date_value ) {

		$is_weekend = rt_isWeekend( $date_value );
		if ( $is_weekend ) {
			$weekend_class = "rt-weekend";
		} else {
			$weekend_class = "";
		}

		$table_html .= '<td class="' . $weekend_class . '">';

		if ( ! $is_weekend ) {

			$args = array(
				'user_id'   =>  bp_displayed_user_id(),
				'task__in'   =>  $all_task_ids,
				'timestamp' =>  $date_value
			);

			$estimated_hours = $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );
			$table_html .= '<div class="rtpm-show-tooltip">' . $estimated_hours;
			$table_html .= '</div>';
		}

		$table_html .= '</td>';
	}

	$table_html .= '</tr>';

	$table_html .= '</tbody>';

	return $table_html;
}

/**
 *  Creates Resources calender
 * @global type $rt_person
 * @param array $dates 
 * @return string
 */
function rt_create_resources_calender( $dates, $project_id ) {
	global $rt_pm_task_resources_model;

	// print all dates in head

	$table_html = '<thead><tr>';
	foreach ( $dates as $key => $value ) {
		$table_html .= '<td>' . date_format( date_create( $value ), "d M" ) . '</td>';
	}
	$table_html .= '</tr></thead><tbody>';

	// travel through all users
	$user_ids = $rt_pm_task_resources_model->rtpm_get_resources_users( array( 'project_id' => $project_id ) );

	foreach ( $user_ids as $user_id ) {

		$table_html .= '<tr>';
		foreach ( $dates as $key => $value ) {

			$is_weekend = rt_isWeekend( $value );
			if ( $is_weekend ) {
				$weekend_class = "rt-weekend";
			} else {
				$weekend_class = "";
			}

			$table_html .= '<td class="' . $weekend_class . '">';

			// no data to show on weekend
			if ( ! $is_weekend ) {

				$args = array(
					'project_id' => $project_id,
					'user_id'    => $user_id,
					'timestamp'  => $value
				);

				$table_html .= '<div class="rtpm-show-tooltip">' . $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );
				if ( ! empty( $tasks_array ) ) {
					$table_html .= '<span class="rtpm-task-info-tooltip"><ul>';
					foreach ( $tasks_array as $key => $task ) {
						$table_html .= '<li><a href="?post_type=rt_project&rt_project_id=' . $project_id . '&tab=rt_resources-details&rt_task_id=' . $task->ID . '">' . $task->post_title . '</a></li>';
					}
					$table_html .= '</ul></span></div>';
				}
				$table_html .= '</div>';
			}
			$table_html .= '</td>';
		}

		$table_html .= '</tr>';
	}

	$table_html .= '</tbody>';

	//Table footer show total estimated hours by dates
	$table_html .= '<tfoot><tr>';
	foreach ( $dates as $key => $value ) {

		$is_weekend = rt_isWeekend( $value );
		if ( $is_weekend ) {
			$weekend_class = "rt-weekend";
		} else {
			$weekend_class = "";
		}

		$table_html .= '<td class="' . $weekend_class . '">';

		// no data to show on weekend
		if ( ! $is_weekend ) {

			$args = array(
				'project_id' => $project_id,
				'user__in'    => $user_ids,
				'timestamp'  => $value
			);

			$table_html .= '<div>' . $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );
			$table_html .= '</div>';
		}
		$table_html .= '</td>';
	}

	$table_html .= '</tr></tfoot>';

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
 * Get project edit url
 * @param $project_id
 * @return string
 */
function rtpm_bp_get_project_details_url( $project_id ) {
    global $rt_pm_bp_pm, $rt_pm_project;

    $project_edit_link = add_query_arg( array( 'rt_project_id' => $project_id, 'action' => 'edit', 'post_type' => $rt_pm_project->post_type, 'tab' => "{$rt_pm_project->post_type}-details" ), $rt_pm_bp_pm->get_component_root_url().'/details' );
    return $project_edit_link;
}

