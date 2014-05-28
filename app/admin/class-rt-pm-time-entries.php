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
        var $post_type = 'rt_time_entry';
        var $name = 'PM';
        var $labels = array();

        public function __construct() {
            $this->get_custom_labels();
		}

        function get_custom_labels() {
            $this->labels = array(
                'name' => __( 'TimeEntry' ),
                'singular_name' => __( 'TimeEntry' ),
                'all_items' => __( 'TTimeEntrys' ),
                'add_new' => __( 'Add TimeEntry' ),
                'add_new_item' => __( 'Add TimeEntries' ),
                'new_item' => __( 'Add TimeEntry' ),
                'edit_item' => __( 'Edit TimeEntry' ),
                'view_item' => __( 'View TimeEntry' ),
                'search_items' => __( 'Search TimeEntries' ),
            );
            return $this->labels;
        }


	}

}
