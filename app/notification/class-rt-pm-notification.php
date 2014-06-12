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
 * Description of RT_PM_Notification
 *
 * @author udit
 */
if ( ! class_exists( 'RT_PM_Notification' ) ) {

	class RT_PM_Notification {

		var $hooks = array();

		public function __construct() {
			$this->register_hooks();
		}

		function register_hooks() {
			$hooks = array(
				'rt_pm_time_entry_saved' => array(
					'contexts' => array(
						'project_budget' => __( 'Project Budget' ),
						'project_time' => __( 'Project Timesheet Cost' ),
					),
				),
			);
			$this->hooks = apply_filters( 'rt_pm_notification_hooks', $hooks );
		}

		function get_hooks() {
			return $this->hooks;
		}

		function get_contexts() {
			$contexts = array();
			foreach ( $this->hooks as $hook ) {
				$contexts = array_merge( $contexts, $hook['contexts'] );
			}
			return $contexts;
		}

		function get_context_label( $context ) {
			$contexts = $this->get_contexts();
			return $contexts[$context];
		}

		function get_operators() {
			$operators = array(
				'<',
				'<=',
				'=',
				'>=',
				'>',
				'!=',
			);

			return $operators;
		}

	}

}
