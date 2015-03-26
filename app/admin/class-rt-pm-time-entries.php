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
            $this->setup();
		}

        private function setup() {

            add_action( 'init', array( $this, 'rtpm_save_timeentry' ) );
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

        /**
         * Return total billed hours
         * @param $task_id
         * @param int $author_id
         * @return mixed
         */
        public function rtpm_get_task_total_billed_hours( $task_id, $author_id = 0 ) {
            global $wpdb, $rt_pm_time_entries_model;

            $query = "SELECT COALESCE( SUM(time_duration), 0 ) FROM {$rt_pm_time_entries_model->table_name} WHERE task_id = {$task_id}";

            if( 0 !== $author_id )
                $query .= " AND author = {$author_id}";

            $time_billed = $wpdb->get_var( $query );

            return $time_billed;
        }

        /**
         * Save time entries
         */
        public function rtpm_save_timeentry() {
            global $rt_pm_project,$rt_pm_task,$rt_pm_time_entries,$rt_pm_time_entries_model;

            if ( ! isset( $_POST['rtpm_save_timeentry_nonce'] ) || ! wp_verify_nonce( $_POST['rtpm_save_timeentry_nonce'], 'rtpm_save_timeentry' ) )
                return;

            $newTimeEntry = $_POST['post'];

            //Switch to blog in MU site while editing from other site
            if( isset( $newTimeEntry['rt_voxxi_blog_id'] ) )
                switch_to_blog( $newTimeEntry['rt_voxxi_blog_id'] );

            $creationdate = $newTimeEntry['post_date'];
            if ( isset( $creationdate ) && $creationdate != '' ) {
                try {
                    $dr = date_create_from_format( 'M d, Y H:i A', $creationdate );
                    //  $UTC = new DateTimeZone('UTC');
                    //  $dr->setTimezone($UTC);
                    $timeStamp = $dr->getTimestamp();
                    $newTimeEntry['post_date'] = rt_set_date_to_utc( gmdate('Y-m-d H:i:s', intval($timeStamp) ) );
                } catch ( Exception $e ) {
                    $newTimeEntry['post_date'] = current_time( 'mysql' );
                }
            } else {
                $newTimeEntry['post_date'] = current_time( 'mysql' );
            }

            // Post Data to be saved.
            $post = array(
                'project_id' => $newTimeEntry['post_project_id'],
                'task_id' => $newTimeEntry['post_task_id'],
                'type' => $newTimeEntry['post_timeentry_type'],
                'message' => $newTimeEntry['post_title'],
                'time_duration' => $newTimeEntry['post_duration'],
                'timestamp' => $newTimeEntry['post_date'],
                'author' => get_current_user_id(),
            );
            $updateFlag = false;
            //check post request is for Update or insert
            if ( isset($newTimeEntry['post_id'] ) ) {
                $updateFlag = true;
                $where = array( 'id' => $newTimeEntry['post_id'] );
                $post_id = $rt_pm_time_entries_model->update_timeentry($post,$where);
            }else{
                $post_id = $rt_pm_time_entries_model->add_timeentry($post);
                $_REQUEST["new"]=true;
            }

            // Used for notification -- Regeistered in RT_PM_Notification
            do_action( 'rt_pm_time_entry_saved', $newTimeEntry, $author = get_current_user_id(), $this );


            if( !is_admin() ) {

                bp_core_add_message( 'Time entry saved successfully', 'success' );
                if( isset( $newTimeEntry['rt_voxxi_blog_id'] ) ){
                    restore_current_blog();
                    add_action ( 'wp_head', 'rt_voxxi_js_variables' );
                }
            }

        }


	}

}
