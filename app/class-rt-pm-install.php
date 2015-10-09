<?php
/**
 * Installation related functions and actions.
 *
 * @author   paresh
 * @category Admin
 * @package  rtpm/Classes
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rt_PM_Install Class
 */
class Rt_PM_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );

	}

	/**
	 * check_version function.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && ( get_option( 'rtpm_version' ) != RT_PM_VERSION ) ) {
			self::install();
			do_action( 'rtpm_updated' );
		}
	}

	/**
	 * Install actions such as installing pages when a button is clicked.
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['do_update_rtpm'] ) ) {
			self::update();
		}
	}

	/**
	 * Install RP
	 */
	public static function install() {

		if ( ! defined( 'RP_INSTALLING' ) ) {
			define( 'RP_INSTALLING', true );
		}

		self::create_roles();

		self::update_rb_version();

		// Trigger action
		do_action( 'rtpm_installed' );
	}

	/**
	 * Update RP version to current
	 */
	private static function update_rb_version() {
		delete_option( 'rtpm_version' );
		add_option( 'rtpm_version', RT_PM_VERSION );
	}

	/**
	 * Update DB version to current
	 */
	private static function update_db_version( $version = null ) {
		delete_option( 'rtpm_db_version' );
		add_option( 'rtpm_db_version', is_null( $version ) ? RT_PM_VERSION : $version );
	}

	/**
	 * Handle updates
	 */
	private static function update() {
		$current_db_version = get_option( 'rtpm_db_version' );

		foreach ( self::$db_updates as $version => $updater ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				include( $updater );
				self::update_db_version( $version );
			}
		}

		self::update_db_version();
	}

	/**
	 * Create roles and capabilities
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Customer role
		add_role( 'voxxi_projects_no_roles', __( 'Voxxi Projects No Roles', 'rtbiz' ), array(
			'read' => true,
		) );

		add_role( 'voxxi_projects_author', __( 'Voxxi Projects Author', 'rtbiz' ), array(

			'projects_edit_projects'             => true,
			'projects_read_projects'              => true,
			'voxxi_projects'                     => true,
			'projects_my_tasks'                  => true,
			'projects_edit_time_entries'         => true,
			"projects_delete_task"               => true,
			'projects_delete_published_tasks'    => true,
			'projects_edit_tasks'                => true,
			'projects_edit_published_tasks'      => true,
			'projects_publish_tasks'             => true,
			'projects_read_task'                 => true,
		) );

		add_role( 'voxxi_projects_editor', __( 'Voxxi Projects Editor', 'rtbiz' ), array(

			'projects_create_projects' => true,
			'projects_delete_others_projects'    => true,
			'projects_delete_private_projects'   => true,
			'projects_delete_published_projects' => true,
			'projects_edit_others_projects'      => true,
			'voxxi_projects'             => true,
			'projects_edit_project'              => true,
			'projects_edit_projects'              => true,
			'projects_edit_private_projects'     => true,
			'projects_edit_published_projects'   => true,
			'projects_publish_projects'          => true,
			'projects_read_projects'             => true,
			'projects_read_private_projects'     => true,
			'projects_unfiltered_html'           => true,
			'projects_upload_files'              => true,
			'voxxi_projects'                     => true,
			'projects_my_tasks'                  => true,
			'projects_ganttchart'               => true,
			'projects_edit_time_entries'         => true,
			'projects_delete_others_tasks'       => true,
			'projects_delete_private_tasks'      => true,
			'projects_delete_published_tasks'    => true,
			'projects_edit_others_tasks'         => true,
			'projects_edit_tasks'                => true,
			'projects_edit_task'                 => true,
			'projects_edit_private_tasks'        => true,
			'projects_edit_published_tasks'      => true,
			'projects_publish_tasks'             => true,
			'projects_read_tasks'                => true,
			'projects_read_private_tasks'        => true,

			'projects_user_reports'              => true,

			'projects_manage_project_types'       => true,
			'projects_edit_project_types'        => true,
			'projects_delete_project_types'      => true,
			'projects_assign_project_types'      => true,


		) );

		$capabilities = array(

			'projects_create_projects' => true,
			'projects_delete_others_projects'    => true,
			'projects_delete_private_projects'   => true,
			'projects_delete_published_projects' => true,
			'projects_edit_others_projects'      => true,
			'voxxi_projects'             => true,
			'projects_edit_project'              => true,
			'projects_edit_projects'              => true,
			'projects_edit_private_projects'     => true,
			'projects_edit_published_projects'   => true,
			'projects_publish_projects'          => true,
			'projects_read_projects'             => true,
			'projects_read_private_projects'     => true,
			'projects_unfiltered_html'           => true,
			'projects_upload_files'              => true,
			'projects_manage_time_entry_types'    => true,
			'projects_edit_time_entry_types'      => true,
			'projects_delete_time_entry_types'    => true,
			'projects_assign_time_entry_types'    => true,
			'projects_manage_project_types'       => true,
			'projects_edit_project_types'        => true,
			'projects_delete_project_types'      => true,
			'projects_assign_project_types'      => true,
			'projects_settings'                  => true,
			'projects_notifications'                  => true,
			'voxxi_projects'                     => true,
			'projects_project_overview' => true,
			'projects_user_reports'              => true,
			'projects_resources'                 => true,
			'projects_my_tasks'                  => true,
			'projects_overview'                  => true,
			'projects_ganttadmin'               => true,
			'projects_ganttchart'               => true,
			'projects_delete_others_tasks'       => true,
			'projects_delete_private_tasks'      => true,
			'projects_delete_published_tasks'    => true,
			'projects_edit_others_tasks'         => true,
			'projects_edit_tasks'                => true,
			'projects_edit_task'                 => true,
			'projects_edit_private_tasks'        => true,
			'projects_edit_published_tasks'      => true,
			'projects_publish_tasks'             => true,
			'projects_read_tasks'                => true,
			'projects_read_private_tasks'        => true,
		);

		// Shop manager role
		add_role( 'voxxi_projects_administrator', __( 'Voxxi Projects Administrator', 'rtbiz' ), $capabilities );


		foreach ( $capabilities as $cap ) {

			$wp_roles->add_cap( 'administrator', $cap );
		}
	}

}

Rt_PM_Install::init();
