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
 * Description of class-rt-pm-help
 *
 * @author udit
 */
if ( ! class_exists( 'RT_PM_Help' ) ) {

	class RT_PM_Help {

		var $tabs = array();
		var $help_sidebar_content;

		public function __construct() {
			add_action( 'init', array( $this, 'init_help' ) );
		}

		function init_help() {
			global $rt_pm_project;
			$this->tabs = apply_filters( 'rt_pm_help_tabs', array(
				'edit.php' => array(
					array(
						'id' => 'dashboard_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'post_type' => $rt_pm_project->post_type,
						'page' => Rt_PM_Project::$dashboard_slug,
					),
					array(
						'id' => 'dashboard_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'post_type' => $rt_pm_project->post_type,
						'page' => Rt_PM_Project::$dashboard_slug,
					),
					array(
						'id' => 'project_list_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'post_type' => $rt_pm_project->post_type,
					),
					array(
						'id' => 'project_list_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'post_type' => $rt_pm_project->post_type,
					),
					array(
						'id' => 'project_card_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => 'rtpm-all-'.$rt_pm_project->post_type,
						'post_type' => $rt_pm_project->post_type,
					),
					array(
						'id' => 'project_card_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'page' => 'rtpm-all-'.$rt_pm_project->post_type,
						'post_type' => $rt_pm_project->post_type,
					),
					array(
						'id' => 'add_edit_project_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => 'rtpm-add-'.$rt_pm_project->post_type,
						'post_type' => $rt_pm_project->post_type,
					),
					array(
						'id' => 'add_edit_project_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'page' => 'rtpm-add-'.$rt_pm_project->post_type,
						'post_type' => $rt_pm_project->post_type,
					),
					array(
						'id' => 'user_reports_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => Rt_PM_User_Reports::$user_reports_page_slug,
						'post_type' => $rt_pm_project->post_type,
					),
					array(
						'id' => 'settings_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => RT_WP_PM::$settings_page_slug,
						'post_type' => $rt_pm_project->post_type,
					),
				),
				'admin.php' => array(
					array(
						'id' => 'dashboard_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => Rt_PM_Project::$dashboard_slug,
					),
					array(
						'id' => 'dashboard_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'page' => Rt_PM_Project::$dashboard_slug,
					),
					array(
						'id' => 'project_card_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => 'rtpm-all-'.$rt_pm_project->post_type,
					),
					array(
						'id' => 'project_card_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'page' => 'rtpm-all-'.$rt_pm_project->post_type,
					),
				),
				'edit-tags.php' => array(
					array(
						'id' => 'project_types_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'taxonomy' => Rt_PM_Project_Type::$project_type_tax,
					),
					array(
						'id' => 'time_entry_types_screen_content',
						'title' => __( 'Overview' ),
						'content' => '',
						'taxonomy' => Rt_PM_Time_Entry_Type::$time_entry_type_tax,
					),
				),
					) );

			$documentation_link = apply_filters( 'rt_biz_help_documentation_link', '#' );
			$support_forum_link = apply_filters( 'rt_biz_help_support_forum_link', '#' );
			$this->help_sidebar_content = apply_filters( 'rt_biz_help_sidebar_content', '<p><strong>' . __( 'For More Information : ' ) . '</strong></p><p><a href="' . $documentation_link . '">' . __( 'Documentation' ) . '</a></p><p><a href="' . $support_forum_link . '">' . __( 'Support Forum' ) . '</a></p>' );

			add_action( 'current_screen', array( $this, 'check_tabs' ) );
		}

		function check_tabs() {
			if ( isset( $this->tabs[ $GLOBALS[ 'pagenow' ] ] ) ) {
				switch ( $GLOBALS[ 'pagenow' ] ) {
					case 'edit.php':
						if ( isset( $_GET[ 'post_type' ] ) ) {
							foreach ( $this->tabs[ $GLOBALS[ 'pagenow' ] ] as $args ) {
								if ( isset( $_GET[ 'page' ] ) && isset( $args[ 'page' ] ) && $args[ 'page' ] == $_GET[ 'page' ] ) {
									$this->add_tab( $args );
								} else if ( empty( $args[ 'page' ] ) && empty( $_GET[ 'page' ] ) && $args[ 'post_type' ] == $_GET[ 'post_type' ] ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
					case 'post.php':
						if ( isset( $_GET[ 'post' ] ) ) {
							$post_type = get_post_type( $_GET[ 'post' ] );
							foreach ( $this->tabs[ $GLOBALS[ 'pagenow' ] ] as $args ) {
								if ( $args[ 'post_type' ] == $post_type ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
					case 'admin.php':
						if ( isset( $_GET[ 'page' ] ) ) {
							foreach ( $this->tabs[ $GLOBALS[ 'pagenow' ] ] as $args ) {
								if ( $args[ 'page' ] == $_GET[ 'page' ] ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
					case 'edit-tags.php':
						if ( isset( $_GET[ 'taxonomy' ] ) ) {
							foreach ( $this->tabs[ $GLOBALS[ 'pagenow' ] ] as $args ) {
								if ( $args[ 'taxonomy' ] == $_GET[ 'taxonomy' ] ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
				}
			}
		}

		function add_tab( $args ) {
			get_current_screen()->add_help_tab( array(
				'id' => $args[ 'id' ],
				'title' => $args[ 'title' ],
				// You can directly set content as well.
//				'content' => $args[ 'content' ],
				// This is for some extra content & logic
				'callback' => array( $this, 'tab_content' ),
			) );
			get_current_screen()->set_help_sidebar( $this->help_sidebar_content );
		}

		function tab_content( $screen, $tab ) {
			// Some Extra content with logic
			switch ( $tab[ 'id' ] ) {
				case 'dashboard_overview':
					$menu_label = Rt_PM_Settings::$settings[ 'menu_label' ];
					?>
					<p>
						<?php echo sprintf( __( 'Welcome to your %s Dashboard!' ), $menu_label ); ?>
						<?php _e( 'You can get help for any screen by clicking the Help tab in the upper corner.' ); ?>
					</p>
					<?php
					break;
				case 'dashboard_screen_content':
					?>
					<p><?php _e( 'Dashboard Screen Content' ); ?></p>
					<?php
					break;
				case 'project_list_overview':
					?>
					<p>
						<?php _e( 'This screen provides access to a list view of all projects. You can customize the display of this screen to suit your workflow.' ); ?>
					</p>
					<?php
					break;
				case 'project_list_screen_content':
					?>
					<p><?php _e( 'You can customize the display of this screen’s contents in a number of ways :' ); ?></p>
					<ul>
						<li><?php _e( 'You can hide/display columns based on your needs and decide how many projects to list per screen using the Screen Options tab.' ); ?></li>
						<li>
							<?php _e( 'You can filter the list of projects by status using the text links in the upper left to show All, Approved, Rejected, or Pending Review projects.' ); ?>
							<?php _e( 'The default view is to show all projects.' ); ?>
						</li>
						<li>
							<?php _e( 'You can view projects in a simple title list or in a card view.' ); ?>
							<?php _e( 'Choose the view you prefer by clicking on the icons at the top of the list on the right.' ); ?>
						</li>
						<li>
							<?php _e( 'You can refine the list to show only projects in a specific category or from a specific month by using the dropdown menus above the projects list.' ); ?>
							<?php _e( 'Click the Filter button after making your selection.' ); ?>
							<?php _e( 'You also can refine the list by clicking on the author in the projects list.' ); ?>
						</li>
					</ul>
					<?php
					break;
				case 'project_card_overview':
					?>
					<p>
						<?php _e( 'This screen provides access to a card view of all projects. This page gives a basic overview of the projects.' ); ?>
					</p>
					<?php
					break;
				case 'project_card_screen_content':
					?>
					<p><?php _e( 'You can either add a new project from here or select a project and modify/view its details from the Edit Project Screen.' ); ?></p>
					<?php
					break;
				case 'add_edit_project_overview':
					?>
					<p><?php _e( 'This screen can be used to add/edit projects. You can configure all the project related settings from here such as Notifications, Time Entries, Project Tasks etc.' ); ?></p>
					<ul>
						<li>
							<strong><?php _e( 'Add Project :' ); ?></strong>
							<?php _e( 'While adding a new project, you would need to first save the initial data before you can have access to other tabs.' ); ?>
							<?php _e( 'A Project Manager and Business Manager for the project can also be assigned here.' ); ?>
							<?php _e( 'You can also subscribe other team members to the project using the Team Members section.' ); ?>
							<?php _e( 'To add an organisation involved in this project the Organisation section can be used and then to add the respective Organisation members involved in the project the Organisation Members section can be used.' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Edit Project :' ); ?></strong>
							<?php _e( 'Once the initial project data is added ans saved or once a lead has been converted into a project or while editing a project you would see many more options.' ); ?>
							<?php _e( 'You would have access to other tabs namely Details, Attachments, Time Entries, Tasks and Notifications tab.' ); ?>
						</li>
					</ul>
					<?php
					break;
				case 'add_edit_project_screen_content':
					?>
					<ul>
						<li>
							<strong><?php _e( 'Details :' ); ?></strong>
							<?php _e( 'This is the same screen that you see while adding a new project. You can edit any project related data from here.' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Attachments :' ); ?></strong>
							<?php _e( 'This section can be used to upload any project related files, media etc.' ); ?>
							<?php _e( 'You can also add an external link of files or references being used in the project.' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Time Entries :' ); ?></strong>
							<?php _e( 'This screen gives you access to the times entries done on the project and its tasks.'); ?>
							<?php _e( 'Here you can do time entries to the tasks that have been worked upon and based on the time entries and budget of the project you get an overview of the Budget and Time spent on the project till date.' ); ?>
							<?php _e( 'This depends on the values set for the project budget and the charges per hour for the project.' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Tasks :' ); ?></strong>
							<?php _e( 'Tasks can be managed here and assigned to the concerned person.' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Notifications :' ); ?></strong>
							<?php _e( 'Here you can setup notifications for this project. There are two types of notifications here.' ); ?>
							<ul>
								<li>
									<strong><?php _e( 'Triggered Notification :' ); ?></strong>
									<?php _e( 'These notifications are mostly budget and time entry based i.e if the project reaches a certain budget or crosses it or nears it this can be used.' ); ?>
									<?php _e( 'Same for the time spent on the project and the value be either absolute or in percentage.' ); ?>
									<?php _e( 'Ex: If you want to send a notification to the PM of the project when the budget nears 80% you will set the following values Context - Project Budget, Operators - =, Value - 80, Value Type - Percentage, Select User to Notify - {{project_manager}}' ); ?>
								</li>
								<li>
									<strong><?php _e( 'Periodic Notifications :' ); ?></strong>
									<?php _e( 'These notifications can be used to send a notifications or alert to a user based on a time period before or after the context for the alert.' ); ?>
									<?php _e( 'Ex: If you want to send a notification once daily to the BM when no one has been assigned to the project 5 hours after the project has been created you would use the following values Schedule - Once Daily, Contexts - Project Assignee, Operators - =, Value - (leave blank), Value Type - Absolute, Period - 5, Period Type - After, Select User to Notify - {{business_manager}}' ); ?>
								</li>
							</ul>
						</li>
					</ul>
					<?php
					break;
				case 'project_types_overview':
					?>
					<p><?php _e( 'This screen allows you to define the project types available to a project for categorization.' ); ?></p>
					<?php
					break;
				case 'time_entries_types_overview':
					?>
					<p><?php _e( 'This screen allows you to define the time entry types available to a user punching for a time entry.' ); ?></p>
					<?php
					break;
				case 'user_reports_overview':
					?>
					<p><?php _e( 'Here you can generate a report based on the filters i.e User, Time Entry Type and the concerned Project.' ); ?></p>
					<?php
					break;
				case 'settings_overview':
					$menu_label = Rt_PM_Settings::$settings[ 'menu_label' ];
					?>
					<p>
						<?php echo sprintf( __( 'This screen consists of all the %s settings.' ), $menu_label ); ?>
						<?php _e( 'The settings are divided into different tabs depending upon their functionality.' ); ?>
						<?php _e( 'You can configure & update them according to your choice from here.' ); ?>
						<?php _e( 'There\'s also a buttom named "Reset to Default" which will put all settings to its default values.' ); ?>
					</p>
					<?php
					break;
//				case 'settings_screen_content':
//					// Put Screen Content Option if required.
//					break;
				default:
					do_action( 'rt_biz_help_tab_content', $screen, $tab );
					break;
			}
		}

	}

}
