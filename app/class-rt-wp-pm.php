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
 * Description of RT_WP_PM
 *
 * @author udit
 */
if ( ! class_exists( 'RT_WP_PM' ) ) {
	class RT_WP_PM {

		public $templateURL;

		static $settings_page_slug = 'rtpm-settings';

		public function __construct() {

			$this->init_globals();

			add_action( 'init', array( $this, 'admin_init' ), 5 );
			add_action( 'init', array( $this, 'init' ), 6 );

            do_action( 'rt_pm_init' );
		}

		function admin_init() {
			$this->templateURL = apply_filters('rtpm_template_url', 'rtpm/');

			$this->update_database();

			global $rt_pm_admin;
			$rt_pm_admin = new Rt_PM_Admin();

			session_start();
		}


		function init_globals() {
			global $rtpm_form,
                   $rt_pm_time_entries_model,$rtpm_custom_media_fields,
                   $rt_pm_project_type,$rt_pm_project,$rt_pm_task,$rt_pm_time_entries,$rt_pm_time_entry_type,$rt_pm_acl,
                   $rt_pm_settings, $rt_pm_notification, $rt_pm_user_reports;

            $rtpm_form = new Rt_Form();

			$rt_pm_notification = new RT_PM_Notification();

			$rt_pm_time_entries_model = new Rt_PM_Time_Entries_Model();
            $rtpm_custom_media_fields = new Rt_Custom_Media_Fields();

            $rt_pm_project_type = new Rt_PM_Project_Type();
            $rt_pm_project = new Rt_PM_Project();
			$rt_pm_user_reports = new Rt_PM_User_Reports();
            $rt_pm_task = new Rt_PM_Task();
            $rt_pm_time_entries = new Rt_PM_Time_Entries();
			$rt_pm_time_entry_type = new Rt_PM_Time_Entry_Type();
            $rt_pm_acl = new Rt_PM_ACL();

            $rt_pm_settings = new Rt_PM_Settings();


		}

		function init() {

		}

		function update_database() {
			$updateDB = new RT_DB_Update( trailingslashit( RT_PM_PATH ) . 'index.php', trailingslashit( RT_PM_PATH_SCHEMA ) );
			$updateDB->do_upgrade();
		}
	}
}
