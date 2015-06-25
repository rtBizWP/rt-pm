<?php

if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class Rt_PM_Task_Links_Model
 * @author paresh
 */
class Rt_PM_Task_Links_Model extends RT_DB_Model {


    /**
     * Placeholder method
     *
     */
    public function __construct() {
        parent::__construct( 'pm_task_links' );
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
     * @return Rt_PM_Task_Links_Model
     */
    public static function factory() {
        static $instance = false;
        if ( ! $instance ) {
            $instance = new self();
            $instance->setup();
        }
        return $instance;
    }

    public function rtpm_create_task_link( $data ) {
        return parent::insert( $data );
    }

    public function rtpm_update_task_link( $data, $where ) {
        return parent::update( $data, $where );
    }

    public function rtpm_delete_task_link( $where ) {
        return parent::delete( $where );
    }

    public function rtpm_get_task_links( $project_id = 0, $task_id = 0 ) {

        if( 0 !== $task_id &&
            0 !== $project_id ) {

            $args = array(
                'source_task_id' => $task_id,
                'project_id' => $project_id
            );

            return parent::get( $args );
        }

        __return_empty_array();
    }

}