<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-pm-time-entries
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_PM_Time_Entries' ) ) {

	class Rt_PM_Time_Entries {
		//put your code here
        static $post_type = 'rt_time_entry';
        var $name = 'PM';
        var $labels = array();
        var $arr_timer;

        public function __construct() {
            $this->get_custom_labels();
		}

        function get_timer( $key ){
			$hours = floor( $key );
			$minutes = round( 60 * ( $key - $hours ) );

			return str_pad( $hours, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $minutes, 2, '0', STR_PAD_LEFT );
        }

        function get_custom_labels() {
            $this->labels = array(
                'name' => __( 'TimeEntry' ),
                'singular_name' => __( 'Time Entry' ),
                'all_items' => __( 'Time Entries' ),
                'add_new' => __( 'Add TimeEntry' ),
                'add_new_item' => __( 'Add Time Entry' ),
                'new_item' => __( 'Add Time Entry' ),
                'edit_item' => __( 'Edit Time Entry' ),
                'view_item' => __( 'View Time Entry' ),
                'search_items' => __( 'Search Time Entries' ),
            );
            return $this->labels;
        }

        public function rtpm_get_task_total_billed_hours( $task_id, $author_id = 0 ) {
            global $wpdb, $rt_pm_time_entries_model;

            $query = "SELECT COALESCE( SUM(time_duration), 0 ) FROM {$rt_pm_time_entries_model->table_name} WHERE task_id = {$task_id}";

            if( 0 !== $author_id )
                $query .= " AND author = {$author_id}";

            $time_billed = $wpdb->get_var( $query );

            return $time_billed;
        }


	}

}
