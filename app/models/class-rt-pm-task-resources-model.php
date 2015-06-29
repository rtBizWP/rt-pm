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
		parent::__construct( 'pm_task_resources' );
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
	 * @return newly inserted resource id
	 */
	public function rtpm_add_task_resources( $data ) {
		return parent::insert( $data );
	}

	/**
	 * Update resources from task
	 * @param $data
	 * @param $where
	 */
	public function rtpm_update_task_resources( $data, $where ) {
		return parent::update( $data, $where );
	}

	/**
	 * Delete resources from task
	 * @param $where
	 * @return id
	 */
	public function rtpm_delete_task_resources( $where ) {
		return parent::delete( $where );
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

		$query = "SELECT SUM( time_duration)  AS estimated_hours FROM {$this->table_name} WHERE task_id IN ( $tasks ) AND post_status <> 'trash'";

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

		$query = "SELECT DISTINCT task_id  FROM {$this->table_name}  WHERE user_id = {$user_id} AND post_status <> 'trash'";

		if( ! empty( $project_id ) )
			$query .= " AND project_id = {$project_id}";

		//var_dump( $query );
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

		$query = "SELECT DISTINCT user_id  FROM {$this->table_name} WHERE task_id = {$task_id} AND post_status <> 'trash'";

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

		$query = "SELECT DISTINCT user_id  FROM {$this->table_name} WHERE project_id = {$project_id} AND post_status <> 'trash'";

		return $wpdb->get_col( $query );
	}

	/**
	 * Return task_ids
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function rtpm_get_resources_tasks( $args = array() ) {
		global $wpdb;

		$select_statment = "SELECT DISTINCT task_id  FROM {$this->table_name}";

		$where_columns   =   $this->rtpm_prepare_query_where_clause( $args );

		$where_clause   =   $this->rtpm_generate_where_clause_string( $where_columns );

		$query = $select_statment.$where_clause;

		return $wpdb->get_col( $query );
	}

	/**
	 * Return project_ids
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function rtpm_get_resources_projects( $args = array() ) {
		global $wpdb;

		$select_statment = "SELECT DISTINCT project_id  FROM {$this->table_name}";

		$where_columns   =   $this->rtpm_prepare_query_where_clause( $args );

		$where_columns[] = " post_status <> 'trash' ";

		$where_clause   =   $this->rtpm_generate_where_clause_string( $where_columns );

		$query = $select_statment.$where_clause;

		return $wpdb->get_col( $query );
	}

	/**
	 * Return user_ids
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function rtpm_get_resources_users( $args = array()) {
		global $wpdb;

		$select_statment = "SELECT DISTINCT user_id  FROM {$this->table_name}";

		$where_columns   =   $this->rtpm_prepare_query_where_clause( $args );

		$where_clause   =   $this->rtpm_generate_where_clause_string( $where_columns );

		$query = $select_statment.$where_clause;

		return $wpdb->get_col( $query );
	}

	public function rtpm_get_users_projects( $user_id ) {
		global $wpdb;

		$query = "SELECT DISTINCT project_id  FROM {$this->table_name} WHERE user_id = {$user_id} AND post_status <> 'trash' ORDER BY project_id DESC";

		return $wpdb->get_col( $query );
	}

	/**
	 * Return sum of estimated hours
	 * @param $args
	 *
	 * @return mixed
	 */
	public function rtpm_get_estimated_hours( $args ) {
		global $wpdb;

		$select_statement = "SELECT COALESCE( SUM( time_duration ), 0 ) FROM {$this->table_name}";

		$where_columns   =   $this->rtpm_prepare_query_where_clause( $args );

		$where_clause   =   $this->rtpm_generate_where_clause_string( $where_columns );

		$query = $select_statement.$where_clause;

		//var_dump( $query );
		return $wpdb->get_var( $query );
	}

	/**
	 * Prepare where clause for resources query
	 * @param $args
	 *
	 * @return string
	 */
	public function rtpm_prepare_query_where_clause( $args ) {

		if( ! is_array( $args ) )
			return false;

		$where_columns = array();

		if( isset( $args['user_id'] ) )
			$where_columns[] = " {$this->table_name}.user_id = {$args['user_id']} ";

		if( isset( $args['task_id'] ) )
			$where_columns[] = " {$this->table_name}.task_id = {$args['task_id']} ";

		if( isset( $args['project_id'] ) )
			$where_columns[] = " {$this->table_name}.project_id = {$args['project_id']} ";

		if( isset( $args['timestamp'] ) )
			$where_columns[] = " DATE({$this->table_name}.timestamp) = '{$args['timestamp']}'";

		if( isset( $args['task__in'] )  && is_array( $args['task__in'] ) )
			$where_columns[] =  $this->table_name.'.task_id IN( '.implode(', ', $args['task__in'] ) .') ';

		if( isset( $args['project__in'] )  && is_array( $args['project__in'] ) )
			$where_columns[] =  $this->table_name.'.project_id IN( '.implode(', ', $args['project__in'] ) .') ';

		if( isset( $args['user__in'] )  && is_array( $args['user__in'] ) )
			$where_columns[] =  $this->table_name.'user_id IN( '.implode(', ', $args['user__in'] ) .') ';

		if( ! isset( $args['post_status'] ) )
			$where_columns[] = " {$this->table_name}.post_status <> 'trash' ";

		return $where_columns;
	}

	/**
	 * Join all where clause column with am AND
	 * @param $where_columns
	 *
	 * @return string
	 */
	public function rtpm_generate_where_clause_string( $where_columns ) {

		if( empty( $where_columns ) )
			return '';

		$where_clause = ' WHERE ';
		$where_clause .= implode( ' AND ', $where_columns );
		return $where_clause;
	}
}