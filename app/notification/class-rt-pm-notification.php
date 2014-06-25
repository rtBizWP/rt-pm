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
		var $timely_context = array();

		public function __construct() {
			$this->register_hooks();
			$this->register_timely_contexts();
			$this->execute_hooks();

			add_action( 'init', array( $this, 'schedule_timely_notification_actions' ) );
		}

		function register_timely_contexts() {
			$contexts = array(
				'project_assignee' => __( 'Project Assignee' ),
				'task_due_date' => __( 'Task Due Date' ),
			);
			$this->timely_context = apply_filters( 'rt_pm_notification_timely_context', $contexts );
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
				add_action( $hook, $args[ 'callback_function' ], $args[ 'priority' ], $args[ 'args' ] );
			}
		}

		function get_hooks() {
			return $this->hooks;
		}

		function get_contexts( $type = 'triggered' ) {
			$contexts = array();

			switch ( $type ) {
				case 'triggered':
					foreach ( $this->hooks as $hook ) {
						$contexts = array_merge( $contexts, $hook[ 'contexts' ] );
					}
					break;
				case 'periodic':
					$contexts = $this->timely_context;
					break;
				default:
					$contexts = apply_filters( 'rtpm_notification_get_contexts', $contexts );
					break;
			}
			return $contexts;
		}

		function get_context_label( $context, $type = 'triggered' ) {
			$contexts = $this->get_contexts( $type );
			return $contexts[ $context ];
		}

		function get_operators() {
			$site_title = get_bloginfo( 'name' );
			$operators = array(
				'<' => array(
					'subject' => '[' . $site_title . ' - ' . __( 'PM Notification' ) . ']',
					'message' => 'Hello {{user}}, {{context}} has gone below {{value}} for {{entity_title}}. Please check here: {{link}}',
				),
				'<=' => array(
					'subject' => '[' . $site_title . ' - ' . __( 'PM Notification' ) . ']',
					'message' => 'Hello {{user}}, {{context}} has gone below or equal to {{value}} for {{entity_title}}. Please check here: {{link}}',
				),
				'=' => array(
					'subject' => '[' . $site_title . ' - ' . __( 'PM Notification' ) . ']',
					'message' => 'Hello {{user}}, {{context}} has reached {{value}} for {{entity_title}}. Please check here: {{link}}',
				),
				'>=' => array(
					'subject' => '[' . $site_title . ' - ' . __( 'PM Notification' ) . ']',
					'message' => 'Hello {{user}}, {{context}} has gone above or equal to {{value}} for {{entity_title}}. Please check here: {{link}}',
				),
				'>' => array(
					'subject' => '[' . $site_title . ' - ' . __( 'PM Notification' ) . ']',
					'message' => 'Hello {{user}}, {{context}} has gone above {{value}} for {{entity_title}}. Please check here: {{link}}',
				),
				'!=' => array(
					'subject' => '[' . $site_title . ' - ' . __( 'PM Notification' ) . ']',
					'message' => 'Hello {{user}}, {{context}} is not equal to {{value}} for {{entity_title}}. Please check here: {{link}}',
				),
			);

			return apply_filters( 'rtpm_notification_value_operators', $operators );
		}

		function time_entry_saved_action( $time_entry_post, $author, $rt_pm_project_object ) {
			global $rt_biz_notification_rules_model;
			$contexts = $this->hooks[ current_filter() ][ 'contexts' ];
			$rules = $rt_biz_notification_rules_model->get( array( 'context' => array_keys( $contexts ), 'rule_type' => 'triggered' ) );
			$project_id = $time_entry_post[ 'post_project_id' ];
			$project = get_post( $project );
			$link = add_query_arg( array( 'post_type' => $project->post_type, 'page' => 'rtpm-add-' . $project->post_type, $project->post_type . '_id' => $project_id, 'tab' => $project->post_type . '-timeentry' ), admin_url( 'edit.php' ) );

			foreach ( $rules as $r ) {
				switch ( $r->context ) {
					case 'project_budget':
						$rule_budget = $this->calculate_rule_budget( $project_id, $r->value, $r->value_type );
						if ( $this->check_project_budget( $project_id, $rule_budget, $r->operator ) ) {
							$this->add_notification( $this->get_context_label( $r->context, $r->rule_type ), $r->value, $r->value_type, $r->subject, $r->message, $r->user, $project_id, $link );
						}
						break;
					case 'project_time':
						$rule_time = $this->calculate_rule_time( $project_id, $r->value, $r->value_type );
						if ( $this->check_project_time( $project_id, $rule_time, $r->operator ) ) {
							$this->add_notification( $this->get_context_label( $r->context, $r->rule_type ), $r->value, $r->value_type, $r->subject, $r->message, $r->user, $project_id, $link );
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
			switch ( $value_type ) {
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
			switch ( $value_type ) {
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
			if ( $time_entries[ 'total' ] && ! empty( $time_entries[ 'result' ] ) ) {
				foreach ( $time_entries[ 'result' ] as $time_entry ) {
					$type = $time_entry[ 'type' ];
					$term = get_term_by( 'slug', $type, Rt_PM_Time_Entry_Type::$time_entry_type_tax );
					$project_current_budget += floatval( $time_entry[ 'time_duration' ] ) * Rt_PM_Time_Entry_Type::get_charge_rate_meta_field( $term->term_id );
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
			if ( $time_entries[ 'total' ] && ! empty( $time_entries[ 'result' ] ) ) {
				foreach ( $time_entries[ 'result' ] as $time_entry ) {
					$project_current_time += $time_entry[ 'time_duration' ];
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

		function add_notification( $context, $value, $value_type, $subject, $message, $user, $entity_id, $link ) {
			global $rt_biz_notification_queue_model;

			$user_id = '';
			switch ( $user ) {
				case '{{project_manager}}':
					$user_id = get_post_meta( $entity_id, 'project_manager', true );
					break;
				case '{{business_manager}}':
					$user_id = get_post_meta( $entity_id, 'business_manager', true );
					break;
				default:
					$user_id = $user;
					break;
			}

			$user = get_user_by( 'id', $user_id );
			$entity = get_post( $entity_id );

			$placeholders = array(
				'{{user}}' => $user->display_name,
				'{{context}}' => $context,
				'{{value}}' => ( $value_type == 'percentage' ) ? $value . '%' : ( ! empty( $value ) ) ? $value : __( 'Nill' ),
				'{{entity_title}}' => $entity->post_title,
				'{{link}}' => $link,
			);

			foreach ( $placeholders as $key => $value ) {
				$message = str_replace( $key, $value, $message );
			}

			$data = array(
				'user' => $user_id,
				'subject' => $subject,
				'message' => $message,
				'module' => RT_PM_TEXT_DOMAIN,
				'entity' => $entity->post_type,
				'entity_id' => $entity_id,
			);
			$rt_biz_notification_queue_model->insert( $data );
		}

		function schedule_timely_notification_actions() {

			global $rt_biz_notification_rules_model;
			$rules = $rt_biz_notification_rules_model->get( array( 'rule_type' => 'periodic' ) );

			foreach ( $rules as $r ) {
				if ( ! wp_next_scheduled( 'rt_pm_timely_notification_' . $r->id, array( $r ) ) ) {
					wp_schedule_event( time(), $r->schedule, 'rt_pm_timely_notification_' . $r->id, array( $r ) );
				}
			}
		}

		function exec_timely_notification_actions( $rule ) {
			global $rt_pm_project, $rt_pm_task;
			$rule = (array) $rule;
			switch ( $rule['context'] ) {
				case 'project_assignee':
					$args = array(
						'post_type' =>  $rt_pm_project->post_type,
						'post_status' => array_diff( wp_list_pluck( $rt_pm_project->get_custom_statuses(), 'slug' ), array( 'closed' ) ),
					);
					if ( ! empty( $rule['entity_id'] ) ) {
						$args['post__in'] = array( $rule['entity_id'] );
					}
					$projects = get_posts( $args );
					$projects = array_unique( $projects, SORT_REGULAR );
					foreach ( $projects as $p ) {
						if ( $this->check_project_assignee( $p->ID, $rule ) ) {
							$context = $this->get_context_label( $rule['context'], 'periodic' );
							$value = $rule['value'];
							$value_type = $rule['value_type'];
							$subject = $rule['subject'];
							$message = $rule['message'];
							$user = $rule['user'];
							$project_id = $p->ID;
							$project = $p;
							$link = add_query_arg( array( 'post_type' => $project->post_type, 'page' => 'rtpm-add-' . $project->post_type, $project->post_type . '_id' => $project_id, 'tab' => $project->post_type . '-details' ), admin_url( 'edit.php' ) );
							$this->add_notification( $context, $value, $value_type, $subject, $message, $user, $project_id, $link );
						}
					}
					break;
				case 'task_due_date':
					$project_id_key = Rt_PM_Task_List_View::$project_id_key;
					$args = array(
						'post_type' => $rt_pm_project->post_type,
						'fields' => 'ids',
						'post_status' => array_diff( wp_list_pluck( $rt_pm_project->get_custom_statuses(), 'slug' ), array( 'closed' ) ),
					);
					$projects = get_posts( $args );
					$projects = array_unique( $projects, SORT_REGULAR );
					$args = array(
						'post_type' =>  $rt_pm_task->post_type,
						'post_status' => array_diff( wp_list_pluck( $rt_pm_task->get_custom_statuses(), 'slug' ), array( 'completed' ) ),
						'meta_query' => array(
							'key' => $project_id_key,
							'value' => $projects,
						),
					);
					if ( ! empty( $rule['entity_id'] ) ) {
						$args['meta_query'] = array(
							'key' => $project_id_key,
							'value' => $rule['entity_id'],
						);
					}
					$tasks = get_posts( $args );
					$tasks = array_unique( $tasks, SORT_REGULAR );
					foreach ( $tasks as $t ) {
						if ( $this->check_task_due_date( $t->ID, $rule ) ) {
							$context = $this->get_context_label( $rule['context'], 'periodic' );
							$value = $rule['value'];
							$value_type = $rule['value_type'];
							$subject = $rule['subject'];
							if ( empty( $rule['value'] ) ) {
								$post_type = get_post_type_object( $t->post_type );
								$message = sprintf( __( 'Hello {{user}}, It has been %s hours %s {{entity_title}} %s is due. Please check here: {{link}}' ), $rule['period'], $rule['period_type'], $post_type->labels->singular_name );
							} else {
								$message = $rule['message'];
							}
							$task_id = $t->ID;
							$project_id = get_post_meta( $task_id, $project_id_key, true );
							$project = get_post( $project_id );
							$link = add_query_arg( array( 'post_type' => $project->post_type, 'page' => 'rtpm-add-' . $project->post_type, $project->post_type . '_id' => $project_id, 'tab' => $project->post_type . '-task', $t->post_type.'_id' => $t->ID ), admin_url( 'edit.php' ) );
							$user_id = '';
							switch ( $rule['user'] ) {
								case '{{project_manager}}':
									$user_id = get_post_meta( $project_id, 'project_manager', true );
									break;
								case '{{business_manager}}':
									$user_id = get_post_meta( $project_id, 'business_manager', true );
									break;
								default:
									$user_id = $rule['user'];
									break;
							}
							$this->add_notification( $context, $value, $value_type, $subject, $message, $user_id, $task_id, $link );
						}
					}
					break;
				default:
					do_action( 'rt_pm_exec_timely_notification_actions', $rule );
					break;
			}
		}

		function check_task_due_date( $task_id, $rule ) {
			$flag = false;
			$task = get_post( $task_id );
			$task_due_date = new DateTime( get_post_meta( $task_id, 'post_duedate', true ) );
			$task_create_date = new DateTime( $task->post_date );
			$current_date = new DateTime( current_time( 'mysql' ) );

			$value = '';
			switch( $rule['value_type'] ) {
				case 'absolute':
					$value = $rule['value'];
					break;
				case 'percentage':
					$diff = $task_due_date->diff( $task_create_date );
					$hours = ( $diff->d * 24 ) + $diff->h;
					if ( $diff->invert ) {
						$hours *= -1;
					}
					$value = $hours * $rule['value'] / 100;
					break;
				default:
					$value = apply_filters( 'rt_pm_calculate_task_due_date_value', $task_id, $rule );
					break;
			}

			if ( ! empty( $value ) ) {
				$diff = $current_date->diff( $task_create_date );
				$project_current_hours = ( $diff->d * 24 ) + $diff->h;
				if ( $diff->invert ) {
					$project_current_hours *= -1;
				}
				switch ( $rule['operator'] ) {
					case '<':
						if ( $project_current_hours < $value ) {
							$flag = true;
						}
						break;
					case '<=':
						if ( $project_current_hours <= $value ) {
							$flag = true;
						}
						break;
					case '=':
						if ( $project_current_hours == $value ) {
							$flag = true;
						}
						break;
					case '>=':
						if ( $project_current_hours >= $value ) {
							$flag = true;
						}
						break;
					case '>':
						if ( $project_current_hours > $value ) {
							$flag = true;
						}
						break;
					case '!=':
						if ( $project_current_hours != $value ) {
							$flag = true;
						}
						break;
					default:
						$flag = apply_filters( 'rtpm_check_task_due_date', $flag, $task_id, $rule );
						break;
				}
			} else {
				switch ( $rule['period_type'] ) {
					case 'before':
						$diff = $current_date->diff( $task_due_date );
						$hours = ( $diff->d * 24 ) + $diff->h;
						if ( $diff->invert ) {
							$hours *= -1;
						}
						if ( $hours <= $rule['period'] ) {
							$flag = true;
						}
						break;
					case 'after':
						$diff = $task_due_date->diff( $current_date );
						$hours = ( $diff->d * 24 ) + $diff->h;
						if ( $diff->invert ) {
							$hours *= -1;
						}
						if ( $hours >= $rule['period'] ) {
							$flag = true;
						}
						break;
				}
			}
			return $flag;
		}

		function check_project_assignee( $project_id, $rule ) {
			$flag = false;
			$project_assignee = get_post_meta( $project_id, 'project_manager', true );
			$project_last_update_date = new DateTime( get_post_meta( $project_id, 'date_update', true ) );
			$current_date = new DateTime( current_time( 'mysql' ) );

			switch ( $rule['period_type'] ) {
				case 'before':
					$diff = $current_date->diff( $project_last_update_date );
					$hours = ( $diff->d * 24 ) + $diff->h;
					if ( $diff->invert ) {
						$hours *= -1;
					}
					if ( ( ( empty( $rule['value'] ) && empty( $project_assignee ) ) || ( $project_assignee == $rule['value'] ) ) && $hours <= $rule['period'] ) {
						$flag = true;
					}
					break;
				case 'after':
					$diff = $project_last_update_date->diff( $current_date );
					$hours = ( $diff->d * 24 ) + $diff->h;
					if ( $diff->invert ) {
						$hours *= -1;
					}
					if ( ( ( empty( $rule['value'] ) && empty( $project_assignee ) ) || ( $project_assignee == $rule['value'] ) ) && $hours >= $rule['period'] ) {
						$flag = true;
					}
					break;
			}
			return $flag;
		}

	}

}
