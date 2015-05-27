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
		//$this->setup();
	}

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

				$table_html .= '<tr>';

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
						$table_html .= '<div class="rtpm-show-tooltip">' . $estimated_hours;
						$table_html .= '</div></div>';
					}
					$table_html .= '</div>';
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

			$task_ids = $rt_pm_task_resources_model->rtpm_get_all_task_id_by_user( bp_displayed_user_id(), $project_id );

			if( empty( $task_ids ) )
				continue;

			if ( $old_project_id !== $project_id ) {

				$old_project_id = $project_id;
				$table_html .= '<tr><td colspan="10">' . get_post_field( 'post_title', $project_id ) . '</td></tr>';
			}

			$all_task_ids = array_merge( $all_task_ids, $task_ids );

			foreach ( $task_ids as $task_id ) {
				$table_html .= '<tr>';
				// for each task travel through each date

				//$table_html .= '<td>'.get_post_field( 'post_title', $task_id ).'</td>';
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

			$table_html .= '<tr>';
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

		$args = array(
			'project_id' => $project_id,
			'user_id'    => $user_id,
		);

		$table_html .= $this->rtpm_prepare_calender_footer( $dates, $args );

		return $table_html;
	}

	public function rtpm_prepare_resources_calender( $dates ) {

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

				$table_html .= '<div>' . $rt_pm_task_resources_model->rtpm_get_estimated_hours( $args );
				$table_html .= '</div>';
			}
			$table_html .= '</td>';
		}

		$table_html .= '</tr></tfoot>';

		return $table_html;
	}

}