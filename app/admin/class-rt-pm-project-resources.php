<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 27/5/15
 * Time: 6:29 PM
 */

/**
 * @aurhor paresh
 */
class Rt_PM_Project_Resources {

	/**
	 * Return singleton instance of class
	 *
	 * @return Rt_Pm_Project_Resources
	 */
	public static function factory() {
		static $instance = false;
		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Placeholder method
	 */
	public function __construct() {
		$this->setup();
	}

	public function setup() {
		add_action('wp_enqueue_scripts', array( $this, 'rtpm_resources_load_style_script' ) );
		add_action('buddyboss_after_header', array( $this, 'buddyboss_after_header_rt_wrapper' ) );

		add_action( 'wp_ajax_rtpm_save_resources', array( $this, 'rtpm_save_resources' ) );
		add_action( 'wp_ajax_rtpm_remove_resources', array( $this, 'rtpm_remove_resources' ) );
		add_action( 'wp_ajax_rtpm_validate_user_assigned_hours', array( $this, 'rtpm_validate_user_assigned_hours'), 10 );
	}

	public function rtpm_resources_load_style_script() {
		$white_action_lists = array(
			'all-resources',
			'my-tasks',
			'resources'
		);

		if ( function_exists('bp_is_active') && in_array( bp_current_action(), $white_action_lists ) ) {

			wp_enqueue_style( 'rt-biz-sidr-style', get_stylesheet_directory_uri() . '/css/jquery.sidr.light.css', array() );
			wp_enqueue_script( 'rt-biz-sidr-script', get_stylesheet_directory_uri() . '/assets/js/jquery.sidr.min.js', array( 'jquery' ) );

			wp_enqueue_script( 'rtbiz-common-script', get_stylesheet_directory_uri() . '/assets/js/rtbiz-common.js', array(), BUDDYBOSS_CHILD_THEME_VERS );
			wp_enqueue_script( 'rtbiz-side-panel-script', get_stylesheet_directory_uri() . '/assets/js/rtbiz-fetch-side-panel.js', array(), BUDDYBOSS_CHILD_THEME_VERS );

			//Sidebar css
			wp_enqueue_style( 'rt-bp-hrm-calender-css', RT_HRM_BP_HRM_URL . 'assets/css/calender.css', false );

			wp_enqueue_script( 'rtbiz-attachment-script', RT_BP_PEOPLE_URL . 'assets/js/rtbiz-attachment-section.js', array( 'jquery' ), RT_BIZ_VERSION, false );
			wp_enqueue_media();

			wp_localize_script( 'rtbiz-side-panel-script', 'pm_script_url', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/rt-bp-pm.js' );
		}
	}

	/**
	 * Add div for sidr side panel
	 */
	public function buddyboss_after_header_rt_wrapper() { ?>
		<div id="rt-action-panel" class="sidr right"></div>
	<?php }

	/**
	 *  Creates All Resources calender
	 * @global type $rt_pm_project
	 *
	 * @param array $dates
	 *
	 * @return string
	 */

	public function rt_create_all_resources_calender( $dates ) {
		global $rt_pm_task_resources_model;

		// Print dates in head

		$table_html = $this->rtpm_prepare_calender_header( $dates );
		$table_html .= '<tbody>';

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

				$table_html .= "<tr data-project-id='{$project_id}' data-user-id='{$user_id}'>";

				// get the data for each date

				foreach ( $dates as $key => $value ) {

					// No data to show on weekends

					$is_weekend = $this->rt_isWeekend( $value );
					$table_html .= sprintf('<td class="%s">', $this->rtpm_highlight_weekend_cells( $value) );

					if ( ! $is_weekend ) {

						$args = array(
							'user_id'    => $user_id,
							'timestamp'  => $value,
							'project_id' => $project_id,
						);

						$estimated_hours = $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );

						// get task list for user by project id on this date
						$table_html .= "<a data-timestamp='{$value}' class='rtpm_user_task_estimated_hours'>" . $estimated_hours;
						$table_html .= '</a>';
					}
				}

				$table_html .= '</td>';
			}
			$table_html .= '</tr>';
		}

		$table_html .= '</tbody>';

		$args = array(
			'user__in'  => $all_user_ids,
		);

		$table_html .= $this->rtpm_prepare_calender_footer( $dates, $args );

		return $table_html;
	}

	/**
	 *  Creates my task calender
	 * @global type $rt_pm_project
	 *
	 * @param array $dates
	 *
	 * @return string
	 */
	public function rt_create_my_task_calender( $dates ) {
		global $rt_pm_task_resources_model;

		$project_ids = $rt_pm_task_resources_model->rtpm_get_users_projects( bp_displayed_user_id() );

		$table_html = $this->rtpm_prepare_calender_header( $dates );
		$table_html .= '<tbody>';

		// for project no data to show so lets create a blank row

		// travel through all task list

		$all_task_ids   = array();
		$old_project_id = '0';
		foreach ( $project_ids as $project_id ) {

			$task_ids = $rt_pm_task_resources_model->rtpm_get_resources_tasks( array( 'user_id' => bp_displayed_user_id(), 'project_id' => $project_id ) );

			if( empty( $task_ids ) )
				continue;

			if ( $old_project_id !== $project_id ) {

				$old_project_id = $project_id;
				$table_html .= '<tr>';
				foreach ( $dates as $date_key => $date_value ) {
					$table_html .= '<td>&nbsp</td>';
				}
				$table_html .= '</tr>';
				//$table_html .= '<tr><td colspan="10">' . get_post_field( 'post_title', $project_id ) . '</td></tr>';
			}

			$all_task_ids = array_merge( $all_task_ids, $task_ids );

			foreach ( $task_ids as $task_id ) {
				
				$task_info = get_post( $task_id );

				if ( !$task_info )
					continue;
				
				$table_html .= '<tr>';
				// for each task travel through each date

				foreach ( $dates as $date_key => $date_value ) {
					// if weekend no data to show

					$is_weekend = $this->rt_isWeekend( $date_value );
					$table_html .= sprintf('<td class="%s">', $this->rtpm_highlight_weekend_cells( $date_value ) );

					if ( ! $is_weekend ) {

						$args = array(
							'user_id'   => bp_displayed_user_id(),
							'task_id'   => $task_id,
							'timestamp' => $date_value
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

		$table_html .= '</tbody>';

		$args = array(
			'user_id'   => bp_displayed_user_id(),
			'task__in'  => $all_task_ids,
		);

		$table_html .= $this->rtpm_prepare_calender_footer( $dates, $args );

		return $table_html;
	}

	/**
	 *  Creates Resources calender
	 * @global type $rt_person
	 *
	 * @param array $dates
	 *
	 * @return string
	 */
	public function rt_create_resources_calender( $dates, $project_id ) {
		global $rt_pm_task_resources_model;

		// print all dates in head
		$table_html = $this->rtpm_prepare_calender_header( $dates );
		$table_html .= '<tbody>';

		// travel through all users
		$user_ids = $rt_pm_task_resources_model->rtpm_get_resources_users( array( 'project_id' => $project_id ) );

		foreach ( $user_ids as $user_id ) {

			$table_html .= "<tr data-user-id='{$user_id}'>";
			foreach ( $dates as $key => $value ) {

				$is_weekend = $this->rt_isWeekend( $value );
				$table_html .= sprintf('<td class="%s">', $this->rtpm_highlight_weekend_cells( $value) );

				// no data to show on weekend
				if ( ! $is_weekend ) {

					$args = array(
					'project_id' => $project_id,
					'user_id'    => $user_id,
					'timestamp'  => $value
					);

					$table_html .= "<a data-timestamp='{$value}' class='rtpm_user_task_estimated_hours'>" . $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );
					$table_html .= '</a>';
				}
				$table_html .= '</td>';
			}

			$table_html .= '</tr>';
		}

		$table_html .= '</tbody>';

		$args = array(
			'project_id' => $project_id,
			'user__in'    => $user_ids,
		);

		$table_html .= $this->rtpm_prepare_calender_footer( $dates, $args );

		return $table_html;
	}

	/**
	 * Prepare header for calender
	 * @param $dates
	 *
	 * @return string
	 */
	private function rtpm_prepare_calender_header( $dates ) {
		$table_html = '<thead><tr>';

		foreach ( $dates as $key => $value ) {
			$table_html .= '<th>' . date_format( date_create( $value ), "d M" ) . '</th>';
		}
		$table_html .= '</tr></thead>';

		return $table_html;
	}

	/**
	 * Highlight weekend day
	 * @param $date
	 *
	 * @return string
	 */
	private function rtpm_highlight_weekend_cells( $date ) {

		$is_weekend = $this->rt_isWeekend( $date );

		if ( $is_weekend ) {
			$weekend_class = "rt-weekend";
		} else {
			$weekend_class = "";
		}

		return $weekend_class;
	}

	/**
	 *  Checks if the date is weekend
	 * @param type $date
	 * @return type boolean
	 */

	public function rt_isWeekend($date) {
		return (date('N', strtotime($date)) >= 6);
	}

	/**
	 * Prepare footer row for calender
	 * @param $dates
	 * @param array $args
	 *
	 * @return string
	 */
	private function rtpm_prepare_calender_footer( $dates, $args = array() ) {
		global $rt_pm_task_resources_model;

		$table_html = '<tfoot><tr>';
		foreach ( $dates as $key => $value ) {

			$is_weekend = $this->rt_isWeekend( $value );
			$table_html .= sprintf('<td class="%s">', $this->rtpm_highlight_weekend_cells( $value) );

			// no data to show on weekend
			if ( ! $is_weekend ) {

				$args['timestamp'] =  $value;

				$table_html .= $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );
				//$table_html .= '</div>';
			}
			$table_html .= '</td>';
		}

		$table_html .= '</tr></tfoot>';

		return $table_html;
	}

	/**
	 * Save resources via ajax
	 */
	public function rtpm_save_resources() {
		global $rt_pm_task_resources_model;

		check_ajax_referer( 'rtpm-save-resources', 'security' );

		$post = $_REQUEST['post'];

		//Send json success while adding new task
		if( ! isset( $post['task_id'] ) )
			wp_send_json_success();

		$user_id = $post['user_id'];
		$project_id =  $post['project_id'];
		$task_id = $post['task_id'];
		$timstamp = $post['timestamp'];
		$time_duration = $post['time_duration'];

		$project_members = get_post_meta( $project_id, "project_member", true );
		$team            = array( $user_id );

		if ( ! empty( $project_members ) ) {
			$team = array_unique( array_merge( $team, $project_members ) );
		}

		update_post_meta( $project_id, 'project_member', $team );

		$insert_rows = array(
			'project_id' => $project_id,
			'task_id' => $task_id,
			'user_id' => $user_id,
			'time_duration' => $time_duration,
			'timestamp' => $timstamp,
		);

		$data = $this->rtpm_validate_assigned_hours( $post );

		if( $data['success'] ) {
			$resources_id = $rt_pm_task_resources_model->rtpm_add_task_resources( $insert_rows );

			if( $resources_id )
				wp_send_json_success( array( 'resource_id' => $resources_id ) );

		}

		wp_send_json_error();
	}

	/**
	 * Remove resources via ajax
	 */
	public function rtpm_remove_resources() {
		global $rt_pm_task_resources_model;

		check_ajax_referer( 'rtpm-remove-resources', 'security' );

		$post = $_REQUEST['post'];

		$where = array(
			'id' => $post['resource_id'],
		);

		$resource_id = $rt_pm_task_resources_model->rtpm_delete_task_resources( $where );

		if( $resource_id )
			wp_send_json_success();
		else
			wp_send_json_error();

	}

	/**
	 * Validate user assigned hours
	 * @param $post
	 *
	 * @return array
	 */
	public function rtpm_validate_assigned_hours( $post ) {
		global $rt_pm_task_resources_model, $rt_hrm_leave;

		$success = false;

		$project_id = $post['project_id'];


		/**
		 * Check user is not on leave before
		 */
		$data = $rt_hrm_leave->rthrm_check_user_on_leave( $post['user_id'], $post['timestamp'] );
		if( ! empty( $data ) ) {
			$validate_data = array(
				'message' => 'Employee on leave',
				'success'   => false,
			);
			return $validate_data;
		}


		$project_working_hours = (float)get_post_meta( $project_id, 'working_hours' , true );

		$time_duration = (float)$post['time_duration'];

		$args = array(
			'user_id'   =>  $post['user_id'],
			'project_id' =>  $post['project_id'],
			'timestamp' =>  $post['timestamp'],
		);

		$estimated_hours_in_project = $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );

		/**
		 * Check project working hours is not exceeding
		 */
		if ( $estimated_hours_in_project <= $project_working_hours ) {

			//New assigned hours after adding new value to old assigned value
			$new_estimated_hours_in_project =  $estimated_hours_in_project + $time_duration;

			$person = rt_biz_get_person_for_wp_user( $post['user_id'] );
			$person_working_hours = (float)Rt_Person::get_meta( $person[0]->ID, 'contact_working_hours', true );

			unset( $args['project_id'] );
			$estimated_hours_in_all_projects = $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );
			$new_estimated_hours_in_all_projects =  $estimated_hours_in_all_projects + $time_duration;

			if( $new_estimated_hours_in_project > $project_working_hours ) {
				$user_remain_hours = $project_working_hours - $estimated_hours_in_project;
				$message = sprintf( _n( 'You can not assign more than %s hour', 'You can not assign more than %s hours', $user_remain_hours, RT_PM_TEXT_DOMAIN ), $user_remain_hours );
			} else if(  ! empty( $person_working_hours ) &&
			            $new_estimated_hours_in_all_projects > $person_working_hours ) {
				$user_remain_hours = $person_working_hours - $estimated_hours_in_all_projects;
				$message = sprintf( _n( 'You can not assign more than %s hour', 'You can not assign more than %s hours', $user_remain_hours, RT_PM_TEXT_DOMAIN ), $user_remain_hours );
				//$message = 'You can not assign more than ' . $user_remain_hours;
			} else {
				$success = true;
				//$user_remain_hours = $project_working_hours - $new_estimated_hours;
			}
		} else {
			$user_remain_hours = 0;
			$message  = 'Project working hours limit has been exceeded';
		}

		$validate_data = array(
			'message' => $message,
			'success'   => $success
		);

		return $validate_data;
	}

	/**
	 * Validate user working hours ajax
	 */
	public function rtpm_validate_user_assigned_hours() {
		global $rt_pm_task_resources_model;
		check_ajax_referer( 'rtpm-validate-hours', 'security' );

		$post = $_REQUEST['post'];;

		$where = array(
			'id' => $post['resource_id']
		);

		//Temp change post_status to trash to exclude it from search
		$rt_pm_task_resources_model->rtpm_update_task_resources( array( 'post_status' => 'trash' ), $where );

		$data = $this->rtpm_validate_assigned_hours( $post );

		if( ! empty( $post['resource_id'] ) &&
		    true === $data['success'] ) {
			$data = array(
				'user_id'   =>  $post['user_id'],
				'timestamp' =>  $post['timestamp'],
				'time_duration' =>  $post['time_duration'],
				'post_status'   =>  'new'
			);

			$where = array(
				'id' => $post['resource_id']
			);
			$rt_pm_task_resources_model->rtpm_update_task_resources( $data, $where );
		} else {
			$rt_pm_task_resources_model->rtpm_update_task_resources( array( 'post_status' => 'new' ), $where );
		}
		wp_send_json_success( $data );
	}


	/**
	 * Save resource data
	 * @param $task_id
	 * @param $project_id
	 * @param $post_data
	 */
	public function rtpm_save_task_resources( $task_id, $project_id, $post_data ) {
		global $rt_pm_task_resources_model;

		if( ! isset( $post_data['resource_wp_user_id'] ) )
			return;


		foreach( $post_data['resource_wp_user_id'] as $key => $value ) {

			if( empty( $post_data['resource_wp_user_id'] ) ||
			    empty( $post_data['resource_wp_user_id'][$key] ) ||
			    empty( $post_data['time_duration'][$key] )
			)
				continue;

			$dr = date_create_from_format( 'M d, Y H:i A', $post_data['timestamp'][$key] );

			$args = array(
				'user_id'   =>  $post_data['resource_wp_user_id'][$key],
				'project_id' =>  $post_data['post_project_id'],
				'time_duration' => $post_data['time_duration'][$key],
				'timestamp' => $dr->format('Y-m-d H:i:s'),
			);


			$data = $this->rtpm_validate_assigned_hours( $args );

			if( $data['success'] ) {

				$insert_rows = array(
					'project_id' => $project_id,
					'task_id' => $task_id,
					'user_id' => $post_data['resource_wp_user_id'][$key],
					'time_duration' => $post_data['time_duration'][$key],
					'timestamp' => $dr->format('Y-m-d H:i:s'),
				);

				$rt_pm_task_resources_model->rtpm_add_task_resources( $insert_rows );
			}

		}
	}




}
