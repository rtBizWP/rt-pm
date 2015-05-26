<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 23/4/15
 * Time: 4:19 PM
 */

/**
 * Class Rt_Pm_Task_Resources_Model
 * @authot paresh
 */
class Rt_Pm_Task_Resources_Model extends RT_DB_Model {

	/**
	 * Placeholder method
	 *
	 */
	public function __construct() {
		parent::__construct( 'wp_pm_task_resources' );
	}

	/**
	 * Setup actions and filters
	 *
	 */
	public function setup() {
	}

	/**
	 * Return a singleton instance of the class.
	 *
	 * @return Rt_Pm_Task_Resources_Model
	 */
	public static function factory() {
		static $instance = false;
		if ( ! $instance ) {
			$instance = new self();
			$instance->setup();
		}
		return $instance;
	}

	/**
	 * Add new resource on task
	 * @param $data
	 */
	public function rtpm_add_task_resources( $data ) {
		parent::insert( $data );
	}

	/**
	 * Update resources from task
	 * @param $data
	 * @param $where
	 */
	public function rtpm_update_task_resources( $data, $where ) {
		parent::update( $data, $where );
	}

	/**
	 * Delete resources from task
	 * @param $where
	 */
	public function rtpm_delete_task_resources( $where ) {
		parent::delete( $where );
	}

	/**
	 * Get task estimated hours base on hours assigned to all resources
	 * @param $task_ids
	 *
	 * @return bool
	 */
	public function rtpm_get_tasks_estimated_hours( $task_ids ) {
		global $wpdb;

		if( empty( $task_ids ) )
			return false;

		if(  is_array( $task_ids ) ) {
			$tasks = implode(', ', $task_ids );
		} else {
			$tasks =  $task_ids ;
		}

		$query = "SELECT SUM( time_duration)  AS estimated_hours FROM {$this->table_name} WHERE task_id IN ( $tasks )";

		return $wpdb->get_var( $query );
	}

	/**
	 * Get all task id assigned to user
	 * @param $user_id
	 * @param string $project_id
	 *
	 * @return mixed
	 */
	public function rtpm_get_all_task_id_by_user( $user_id, $project_id = '' ) {
		global $wpdb;

		$query = "SELECT task_id  FROM {$this->table_name} WHERE user_id = {$user_id}";

		if( ! empty( $project_id ) )
			$query .= " AND project_id = {$project_id}";

		return $wpdb->get_col( $query );
	}

	/**
	 * Get all resources wp_user id assigned to task
	 * @param $task_id
	 *
	 * @return mixed
	 */
	public function rtpm_get_task_resources( $task_id ) {
		global $wpdb;

		$query = "SELECT user_id  FROM {$this->table_name} WHERE task_id = {$task_id}";

		return $wpdb->get_col( $query );
	}

	/**
	 * Get all resources involved in projects
	 * @param $project_id
	 *
	 * @return mixed
	 */
	public function rtpm_get_project_resources( $project_id ) {
		global $wpdb;

		$query = "SELECT DISTINCT user_id  FROM {$this->table_name} WHERE project_id = {$project_id}";

		return $wpdb->get_col( $query );
	}


	public function rtpm_get_estimated_hours( $args ) {
		global $wpdb;

		$select_statement = "SELECT COALESCE( SUM( time_duration ), 0 ) FROM {$this->table_name}";
		$where_clause   =   $this->rtpm_prepare_query_where_clause( $args );

		$query = $select_statement.$where_clause;

		//var_dump( $query );
		return $wpdb->get_var( $query );
	}

	public function rtpm_prepare_query_where_clause( $args ) {

		$where_clause = ' WHERE ';

		if( ! is_array( $args ) )
			return $where_clause;

		if( isset( $args['user_id'] ) )
			$where_clause .= " user_id = {$args['user_id']} ";

		if( isset( $args['task_id'] ) )
			$where_clause .= " AND task_id = {$args['task_id']} ";

		if( isset( $args['project_id'] ) )
			$where_clause .= " AND project_id = {$args['project_id']} ";

		if( isset( $args['timestamp'] ) )
			$where_clause .= " AND DATE(timestamp) = '{$args['timestamp']}'";

		return $where_clause;
	}
}