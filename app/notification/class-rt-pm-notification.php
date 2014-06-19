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
			$this->execute_hooks();
		}

		function register_hooks() {
			$hooks = array(
				'rt_pm_time_entry_saved' => array(
					'contexts' => array(
						'project_budget' => __( 'Project Budget' ),
						'project_time' => __( 'Project Timesheet Cost' ),
					),
					'callback_function' => array( $this, 'time_entry_saved_action' ),
					'priority' => 10,
					'args' => 3,
				),
			);
			$this->hooks = apply_filters( 'rt_pm_notification_hooks', $hooks );
		}

		function execute_hooks() {
			foreach ( $this->hooks as $hook => $args ) {
				add_action( $hook, $args['callback_function'], $args['priority'], $args['args'] );
			}
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
			$site_title = get_bloginfo( 'name' );
			$operators = array(
				'<' => array(
					'subject' => '['.$site_title.' - '.__('PM Notification').']',
					'message' => 'Hello {{user}}, {{context}} has gone below {{value}} for {{project_title}}. Please check here: {{link}}',
				),
				'<=' => array(
					'subject' => '['.$site_title.' - '.__('PM Notification').']',
					'message' => 'Hello {{user}}, {{context}} has gone below or equal to {{value}} for {{project_title}}. Please check here: {{link}}',
				),
				'=' => array(
					'subject' => '['.$site_title.' - '.__('PM Notification').']',
					'message' => 'Hello {{user}}, {{context}} has reached {{value}} for {{project_title}}. Please check here: {{link}}',
				),
				'>=' => array(
					'subject' => '['.$site_title.' - '.__('PM Notification').']',
					'message' => 'Hello {{user}}, {{context}} has gone above or equal to {{value}} for {{project_title}}. Please check here: {{link}}',
				),
				'>' => array(
					'subject' => '['.$site_title.' - '.__('PM Notification').']',
					'message' => 'Hello {{user}}, {{context}} has gone above {{value}} for {{project_title}}. Please check here: {{link}}',
				),
				'!=' => array(
					'subject' => '['.$site_title.' - '.__('PM Notification').']',
					'message' => 'Hello {{user}}, {{context}} is not equal to {{value}} for {{project_title}}. Please check here: {{link}}',
				),
			);

			return apply_filters( 'rtpm_notification_value_operators', $operators );
		}

		function time_entry_saved_action( $time_entry_post, $author, $rt_pm_project_object ) {
			global $rt_biz_notification_rules_model;
			$contexts = $this->hooks[current_filter()]['contexts'];
			$rules = $rt_biz_notification_rules_model->get( array( 'context' => array_keys( $contexts ) ) );
			$project_id = $time_entry_post['post_project_id'];

			foreach ( $rules as $r ) {
				switch( $r->context ) {
					case 'project_budget':
						$rule_budget = $this->calculate_rule_budget( $project_id, $r->value, $r->value_type );
						if ( $this->check_project_budget( $project_id, $rule_budget, $r->operator ) ) {
							$this->add_notification( $r->context, $r->value, $r->value_type, $r->subject, $r->message, $r->user, $project_id );
						}
						break;
					case 'project_time':
						$rule_time = $this->calculate_rule_time( $project_id, $r->value, $r->value_type );
						if ( $this->check_project_time( $project_id, $rule_time, $r->operator ) ) {
							$this->add_notification( $r->context, $r->value, $r->value_type, $r->subject, $r->message, $r->user, $project_id );
						}
						break;
					default:
						do_action( 'rtpm_time_entry_saved_action', $time_entry_post, $author, $rt_pm_project_object );
						break;
				}
			}
		}

		function calculate_rule_budget( $project_id, $value, $value_type ) {
			$budget = 0;
			switch( $value_type ) {
				case 'absolute':
					$budget = floatval( $value );
					break;
				case 'percentage':
					$project_budget = floatval( get_post_meta( $project_id, '_rtpm_project_budget', true ) );
					$budget = ( $project_budget * $value ) / 100;
					break;
				default:
					do_action( 'rtpm_calculate_rule_budget', $project_id, $value, $value_type );
					break;
			}
			return $budget;
		}

		function calculate_rule_time( $project_id, $value, $value_type ) {
			$time = 0;
			switch( $value_type ) {
				case 'absolute':
					$time = floatval( $value );
					break;
				case 'percentage':
					$project_time = floatval( get_post_meta( $project_id, 'project_estimated_time', true ) );
					$time = ( $project_time * $value ) / 100;
					break;
				default:
					do_action( 'rtpm_calculate_rule_time', $project_id, $value, $value_type );
					break;
			}
			return $time;
		}

		function check_project_budget( $project_id, $budget, $operator ) {
			$flag = false;
			global $rt_pm_time_entries_model;
			$time_entries = $rt_pm_time_entries_model->get_by_project_id( $project_id );
			$project_current_budget = 0;
			if ( $time_entries['total'] && ! empty( $time_entries['result'] ) ) {
				foreach ( $time_entries['result'] as $time_entry ) {
					$type = $time_entry['type'];
					$term = get_term_by( 'slug', $type, Rt_PM_Time_Entry_Type::$time_entry_type_tax );
					$project_current_budget += floatval( $time_entry['time_duration'] ) * Rt_PM_Time_Entry_Type::get_charge_rate_meta_field( $term->term_id );
				}
			}

			switch ( $operator ) {
				case '<':
					if ( $project_current_budget < $budget ) {
						$flag = true;
					}
					break;
				case '<=':
					if ( $project_current_budget <= $budget ) {
						$flag = true;
					}
					break;
				case '=':
					if ( $project_current_budget == $budget ) {
						$flag = true;
					}
					break;
				case '>=':
					if ( $project_current_budget >= $budget ) {
						$flag = true;
					}
					break;
				case '>':
					if ( $project_current_budget > $budget ) {
						$flag = true;
					}
					break;
				case '!=':
					if ( $project_current_budget != $budget ) {
						$flag = true;
					}
					break;
				default:
					$flag = apply_filters( 'rtpm_check_project_budget', $flag, $project_id, $budget, $operator );
					break;
			}
			return $flag;
		}

		function check_project_time( $project_id, $time, $operator ) {
			$flag = false;
			global $rt_pm_time_entries_model;
			$time_entries = $rt_pm_time_entries_model->get_by_project_id( $project_id );
			$project_current_time = 0;
			if ( $time_entries['total'] && ! empty( $time_entries['result'] ) ) {
				foreach ( $time_entries['result'] as $time_entry ) {
					$project_current_time += $time_entry['time_duration'];
				}
			}

			switch ( $operator ) {
				case '<':
					if ( $project_current_time < $time ) {
						$flag = true;
					}
					break;
				case '<=':
					if ( $project_current_time <= $time ) {
						$flag = true;
					}
					break;
				case '=':
					if ( $project_current_time == $time ) {
						$flag = true;
					}
					break;
				case '>=':
					if ( $project_current_time >= $time ) {
						$flag = true;
					}
					break;
				case '>':
					if ( $project_current_time > $time ) {
						$flag = true;
					}
					break;
				case '!=':
					if ( $project_current_time != $time ) {
						$flag = true;
					}
					break;
				default:
					$flag = apply_filters( 'rtpm_check_project_time', $flag, $project_id, $time, $operator );
					break;
			}
			return $flag;
		}

 		function add_notification( $context, $value, $value_type, $subject, $message, $user, $project_id ) {
			global $rt_biz_notification_queue_model;

			$user_id = '';
			switch ( $user ) {
				case '{{project_manager}}':
					$user_id = get_post_meta( $project_id, 'project_manager', true );
					break;
				case '{{business_manager}}':
					$user_id = get_post_meta( $project_id, 'business_manager', true );
					break;
				default:
					$user_id = $user;
					break;
			}

			$user = get_user_by( 'id', $user_id );
			$project = get_post( $project_id );

			$placeholders = array(
				'{{user}}' => $user->display_name,
				'{{context}}' => $this->get_context_label( $context ),
				'{{value}}' => ( $value_type == 'percentage' ) ? $value.'%' : $value,
				'{{project_title}}' => $project->post_title,
				'{{link}}' => add_query_arg( array( 'post_type' => $project->post_type, 'page' => 'rtpm-add-'.$project->post_type, $project->post_type.'_id' => $project_id, 'tab' => $project->post_type.'-timeentry' ), admin_url('edit.php') ),
			);

			foreach ( $placeholders as $key => $value ) {
				$message = str_replace( $key, $value, $message );
			}

			$data = array(
				'user' => $user_id,
				'subject' => $subject,
				'message' => $message,
				'module' => RT_PM_TEXT_DOMAIN,
				'entity' => get_post_type( $project_id ),
				'entity_id' => $project_id,
			);
			$rt_biz_notification_queue_model->insert( $data );
		}

	}

}
