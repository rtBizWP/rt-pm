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
         * Save time entries
         */
        public function rtpm_save_timeentry() {
            global $rt_pm_time_entries_model, $rt_pm_task_resources_model;

            if ( ! isset( $_POST['rtpm_save_timeentry_nonce'] ) || ! wp_verify_nonce( $_POST['rtpm_save_timeentry_nonce'], 'rtpm_save_timeentry' ) )
                return;

            $newTimeEntry = $_POST['post'];

            //Switch to blog in MU site while editing from other site
            if( isset( $newTimeEntry['rt_voxxi_blog_id'] ) )
                switch_to_blog( $newTimeEntry['rt_voxxi_blog_id'] );

            $creationdate = $newTimeEntry['post_date'];
            if ( isset( $creationdate ) && $creationdate != '' ) {

                $dr = date_create_from_format( 'M d, Y H:i A', $creationdate );

                $estimated_hours = (float)$rt_pm_task_resources_model->rtpm_get_estimated_hours( array(
                    'user_id' => get_current_user_id(),
                    'task_id' => $newTimeEntry['post_task_id'],
                    'timestamp' => $dr->format('Y-m-d'),
                    'project_id' => $newTimeEntry['post_project_id'],
                ) );

                $args = array(
                    'user_id'   =>  get_current_user_id(),
                    'task_id'   =>  $newTimeEntry['post_task_id'],
                    'timestamp' =>  $dr->format('Y-m-d'),
                );

                $billed_hours = (float)$rt_pm_time_entries_model->rtpm_get_billed_hours( $args );

                $new_billed_hours = (float)$newTimeEntry['post_duration'] + $billed_hours;

                if( empty( $estimated_hours) ||
                    $estimated_hours < $new_billed_hours ) {
                    if( !is_admin() ) {

                        $remain_billable_hours = $estimated_hours - $billed_hours;
                        $message = 'Assign hours limit has been exceeded';

                        if( $remain_billable_hours > 0 ) {
                            $message = sprintf( _n( 'Max remain allowed hour %s ', 'Max remain allowed hours %s', $remain_billable_hours, RT_PM_TEXT_DOMAIN ), $remain_billable_hours );
                        }
                        bp_core_add_message( $message, 'error' );
                    }
                    return false;
                }


                try {
//                   // $dr = date_create_from_format( 'M d, Y H:i A', $creationdate );
//                    //  $UTC = new DateTimeZone('UTC');
//                    //  $dr->setTimezone($UTC);
//                    $timeStamp = $dr->getTimestamp();
                    $newTimeEntry['post_date'] = $dr->format( 'Y-m-d H:i:s' );
                } catch ( Exception $e ) {
                    $newTimeEntry['post_date'] = current_time( 'mysql' );
                }
            } else {
                $newTimeEntry['post_date'] = current_time( 'mysql' );
            }


           if( ! empty( $estimated_hours ) )

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

        public function rtpm_project_summary_markup( $project_id ) {
            global $rt_pm_time_entries_model, $rt_pm_task_resources_model;

            $project_current_budget_cost = 0;
            $project_current_time_cost = 0;
            $time_entries = $rt_pm_time_entries_model->get_by_project_id( $project_id );
            if ( $time_entries['total'] && ! empty( $time_entries['result'] ) ) {
                foreach ( $time_entries['result'] as $time_entry ) {
                    $type = $time_entry['type'];
                    $term = get_term_by( 'slug', $type, Rt_PM_Time_Entry_Type::$time_entry_type_tax );

                    if( $term !=NULL )
                        $project_current_budget_cost += floatval( $time_entry['time_duration'] ) * Rt_PM_Time_Entry_Type::get_charge_rate_meta_field( $term->term_id );

                    $project_current_time_cost += $time_entry['time_duration'];
                }
            }

            $estimated_hours = $rt_pm_task_resources_model->rtpm_get_estimated_hours( array( 'project_id' => $project_id ) );

            ?>

            <div class="large-3 columns">
                <strong><?php _e( 'Project Cost:'); ?></strong>
                <span><?php echo '$ '.$project_current_budget_cost; ?></span>
            </div>
            <div class="large-3 columns">
                <strong><?php _e( 'Budget:'); ?></strong>
                <span><?php echo '$ '.floatval( get_post_meta( $project_id, '_rtpm_project_budget', true ) ); ?></span>
            </div>
            <div class="large-3 columns">
                <strong><?php _e( 'Time spent:'); ?></strong>
                <span><?php echo $project_current_time_cost.__(' hours'); ?></span>
            </div>
            <div class="large-3 columns">
                <strong><?php _e( 'Estimated Time:'); ?></strong>
                <span><?php echo floatval( $estimated_hours ).__(' hours'); ?></span>
            </div>
        <?php
        }


	}

}
