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

	public function rtpm_add_task_resources( $data ) {
		parent::insert( $data );
	}

	public function rtpm_update_task_resources( $data, $where ) {
		parent::update( $data, $where );
	}

	public function rtpm_delete_task_resources( $where ){
		parent::delete( $where );
	}
}