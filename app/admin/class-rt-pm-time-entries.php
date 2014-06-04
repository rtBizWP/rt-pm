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
        var $arr_timer;

        public function __construct() {
            $this->get_custom_labels();
            $this->set_array_timer();
		}

        function set_array_timer(){
            $this->arr_timer=array(
                '0.2'=>'15 min',
                '0.5'=>'30 min',
                '0.7'=>'45 min',
                '1.0'=>'1 hour',
                '1.2'=>'1 hour 15 min',
                '1.5'=>'1 hour 30 min',
                '1.7'=>'1 hour 45 min',
                '2.0'=>'2 hours',
            );
        }

        function get_timer_array(){
            return $this->arr_timer;
        }

        function get_timer($key){
            return $this->arr_timer[$key];
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


	}

}
