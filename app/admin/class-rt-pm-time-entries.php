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
            global $rt_pm_time_entries_model, $rt_pm_project_resources, $rt_pm_task_resources_model, $table_prefix, $rt_pm_task;

            if ( ! isset( $_POST['rtpm_save_timeentry_nonce'] ) || ! wp_verify_nonce( $_POST['rtpm_save_timeentry_nonce'], 'rtpm_save_timeentry' ) )
                return;

            $newTimeEntry = $_POST['post'];

            //Switch to blog in MU site while editing from other site
            if( isset( $newTimeEntry['rt_voxxi_blog_id'] ) ) {


                $rt_pm_task_resources_table_name = $rt_pm_task_resources_model->table_name;
                $rt_pm_time_entries_table_name = $rt_pm_time_entries_model->table_name;
                $old_table_prefix = $table_prefix;

                $blog_id = $newTimeEntry['rt_voxxi_blog_id'];

                switch_to_blog( $blog_id );
                $blog_table_prefix = $table_prefix;
                $rt_pm_task_resources_model->table_name  = str_replace( $old_table_prefix, $blog_table_prefix, $rt_pm_task_resources_table_name );
                $rt_pm_time_entries_model->table_name = str_replace( $old_table_prefix, $blog_table_prefix, $rt_pm_time_entries_table_name );
            }


            $creationdate   = $newTimeEntry['post_date'];
            $add_time       = $newTimeEntry['post_task_id'];

            if ( isset( $creationdate ) && $creationdate != '' ) {

                $dr = date_create_from_format( 'M d, Y H:i A', $creationdate );

                //Set post_status to trash while editing time entry
                if( isset( $newTimeEntry['post_id'] ) ) {
                    $where = array( 'id' => $newTimeEntry['post_id'] );
                    $rt_pm_time_entries_model->update_timeentry( array( 'post_status' => 'trash' ), $where );
                }

                if( 'add-time' !== $add_time ) {
                    /**
                     * Validate assigned hours
                     */
                    $estimated_hours = (float) $rt_pm_task_resources_model->rtpm_get_estimated_hours( array(
                        'user_id' => get_current_user_id(),
                        'task_id' => $newTimeEntry['post_task_id'],
                        'timestamp' => $dr->format( 'Y-m-d' ),
                        'project_id' => $newTimeEntry['post_project_id'],
                    ) );

                    $args = array(
                        'user_id' => get_current_user_id(),
                        'task_id' => $newTimeEntry['post_task_id'],
                        'timestamp' => $dr->format( 'Y-m-d' ),
                    );

                    $billed_hours = (float) $rt_pm_time_entries_model->rtpm_get_billed_hours( $args );

                    $new_billed_hours = (float) $newTimeEntry['post_duration'] + $billed_hours;

                    if ( empty( $estimated_hours ) ||
                         $estimated_hours < $new_billed_hours
                    ) {

                        $remain_billable_hours = $estimated_hours - $billed_hours;
                        $message = 'Assign hours limit has been exceeded';

                        if ( $remain_billable_hours > 0 ) {
                            $message = sprintf( _n( 'Max remain allowed hour %s ', 'Max remain allowed hours %s', $remain_billable_hours, RT_PM_TEXT_DOMAIN ), $remain_billable_hours );
                        }

                        if ( !is_admin() ) {
                            bp_core_add_message( $message, 'error' );
                        } else {
                            add_notice( $message, 'error' );
                        }

                        /**
                         * Restore blog if time entry is fail
                         */
                        if ( isset( $newTimeEntry['rt_voxxi_blog_id'] ) ) {
                            restore_current_blog();
                            $rt_pm_task_resources_model->table_name = $rt_pm_task_resources_table_name;
                            $rt_pm_time_entries_model->table_name = $rt_pm_time_entries_table_name;
                            add_action( 'wp_head', 'rt_voxxi_js_variables' );
                        }

                        return false;
                    }
                } else {

                    //Validate resource -> Personal and Project level validation
                    $person = rt_biz_get_person_for_wp_user( get_current_user_id() );
                    $person_working_hours = (float)Rt_Person::get_meta( $person[0]->ID, 'contact_working_hours', true );
                    $project_working_hours = (float)get_post_meta( $newTimeEntry['post_project_id'], 'working_hours' , true );

                    $args = array(
                        'user_id'   =>  get_current_user_id(),
                        'project_id' =>   $newTimeEntry['post_project_id'],
                        'timestamp' =>  $dr->format( 'Y-m-d H:i:s' ),
                    );

                    $resource_validate = $rt_pm_project_resources->rtpm_validate_assigned_hours( $args );

                    //Check resource is not occupied on assigned date
                    if ( empty( $resource_validate['success'] ) ) {
                        bp_core_add_message( $resource_validate['message'] , 'error' );
                        return false;

                        //Project level validate
                    } else if ( floatval( $newTimeEntry['post_duration'] ) > $person_working_hours ) {
                        bp_core_add_message( "Personal working hours limit has been exceeded", 'error' );
                        return false;
                        //Personal level validate
                    } else if ( floatval( $newTimeEntry['post_duration'] ) > $project_working_hours ) {
                        bp_core_add_message( "Project working hours limit has been exceeded", 'error' );
                        return false;
                    }

                    /*
                     * Create new task
                     */

                    $task_start_dr  = date_create_from_format( 'M d, Y H:i A', $newTimeEntry['task_start_date'] );
                    $end_start_dr   = date_create_from_format( 'M d, Y H:i A', $newTimeEntry['task_end_date'] );

                    //Task post data
                    $args = array(
                        'post_title'  => $newTimeEntry['post_title'],
                        'post_date'   => $task_start_dr->format('Y-m-d H:i:s'),
                        'post_date_gmt' => $task_start_dr->format('Y-m-d H:i:s'),
                        'post_parent' => $newTimeEntry['post_project_id'],
                        'post_status' => 'new',
                    );

                    //Task post meta values
                    $meta_values = array(
                        'post_duedate'         => $end_start_dr->format( 'Y-m-d H:i:s'),
                        'post_project_id'      => $newTimeEntry['post_project_id'],
                        'rtpm_parent_task'     => 0,
                    );

                    //Create task
                    $task_id = $rt_pm_task->rtpm_save_task_data( $args, $meta_values );

                    //Set task id for time entry
                    $newTimeEntry['post_task_id'] = $task_id;

                    //Insert resource entry
                    $insert_rows = array(
                        'project_id' => $newTimeEntry['post_project_id'],
                        'task_id' => $task_id,
                        'user_id' => get_current_user_id(),
                        'time_duration' => $newTimeEntry['post_duration'],
                        'timestamp' => $dr->format( 'Y-m-d H:i:s' ),
                    );

                    $rt_pm_task_resources_model->rtpm_add_task_resources( $insert_rows );
                }


                try {
                    $newTimeEntry['post_date'] = $dr->format( 'Y-m-d H:i:s' );
                } catch ( Exception $e ) {
                    $newTimeEntry['post_date'] = current_time( 'mysql' );
                }
            } else {
                $newTimeEntry['post_date'] = current_time( 'mysql' );
            }


          // if( ! empty( $estimated_hours ) )

            // Post Data to be saved.
            $post = array(
                'project_id' => $newTimeEntry['post_project_id'],
                'task_id' => $newTimeEntry['post_task_id'],
                'type' => $newTimeEntry['post_timeentry_type'],
                'message' => $newTimeEntry['post_title'],
                'time_duration' => $newTimeEntry['post_duration'],
                'timestamp' => $newTimeEntry['post_date'],
                'author' => get_current_user_id(),
                'post_status' => 'new'
            );


            if ( isset($newTimeEntry['post_id'] ) ) {

                $where = array( 'id' => $newTimeEntry['post_id'] );
                $rt_pm_time_entries_model->update_timeentry( $post, $where );
            }else{
                $rt_pm_time_entries_model->add_timeentry( $post );
                $_REQUEST["new"]=true;
            }

            // Used for notification -- Regeistered in RT_PM_Notification
            do_action( 'rt_pm_time_entry_saved', $newTimeEntry, $author = get_current_user_id(), $this );


            /**
             * Update message and blog restore
             */
            $message = __( 'Time recorded successfully', RT_PM_TEXT_DOMAIN );
            if( ! is_admin() ) {

                bp_core_add_message( $message , 'success' );
                if( isset( $newTimeEntry['rt_voxxi_blog_id'] ) ){
                    restore_current_blog();
                    $rt_pm_task_resources_model->table_name  = $rt_pm_task_resources_table_name;
                    $rt_pm_time_entries_model->table_name = $rt_pm_time_entries_table_name;
                    add_action ( 'wp_head', 'rt_voxxi_js_variables' );
                }
            } else {
                add_notice( $message );
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
