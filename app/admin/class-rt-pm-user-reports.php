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
 * Description of Rt_PM_User_Reports
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_PM_User_Reports' ) ) {

	class Rt_PM_User_Reports {

		static $user_reports_page_slug = 'rtpm-user-reports';

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_custom_pages' ) );
		}

		function register_custom_pages() {
			$admin_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'admin' );
			global $rt_pm_project;
			add_submenu_page( 'edit.php?post_type='.$rt_pm_project->post_type, __( 'User Reports' ), __( 'User Reports' ), 'projects_user_reports', self::$user_reports_page_slug, array( $this, 'ui' ) );
		}

		function ui() {
			$args = array();
			rtpm_get_template( 'admin/user-reports.php', $args );
		}
	}

}