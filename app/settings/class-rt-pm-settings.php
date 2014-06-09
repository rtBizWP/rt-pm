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
 * Description of Rt_PM_Settings
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_PM_Settings' ) ) {

	/**
	 * Class Rt_PM_Settings
	 */
	class Rt_PM_Settings {

		/**
		 * @var TitanFramework
		 */
		public static $titan_obj;

		/**
		 * @var - saved Settings
		 */
		public static $settings;

		/**
		 *
		 */
		public function __construct() {

			// Proceed only if Titan Framework Found
			if ( ! $this->embedd_titan_framework() ) {
				return;
			}
			// Init Titan Instance
			self::$titan_obj = $this->get_settings_instance();

			// Init Titan Settings
			add_action( 'plugins_loaded', array( $this, 'init_settings' ), 20 );
			// Load Saved Settings Values
			add_action( 'init', array( $this, 'load_settings' ), 5 );
		}

		/**
		 *  Load Settings
		 */
		function load_settings() {
			self::$settings['logo_url'] = ( isset( self::$titan_obj ) && ! empty( self::$titan_obj ) ) ? self::$titan_obj->getOption( 'logo_url' ) : '';
		}

		/**
		 *  Init Settings
		 */
		function init_settings() {

			global $rt_pm_admin,$rt_pm_project;

			if ( ! isset( self::$titan_obj ) || empty( self::$titan_obj ) ) {
				return;
			}

			$admin_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'admin' );

			$settings_page = self::$titan_obj->createAdminPanel( array(
				'name' => __( 'Settings' ), // Name of the menu item
				'title' => __( 'Settings' ), // Title displayed on the top of the admin panel
				'parent' => 'edit.php?post_type='.$rt_pm_project->post_type,//Rt_PM_Admin::$pm_page_slug, // id of parent, if blank, then this is a top level menu
				'id' => RT_WP_PM::$settings_page_slug, // Unique ID of the menu item
				'capability' => $admin_cap, // User role
				'position' => 10, // Menu position. Can be used for both top and sub level menus
				'use_form' => true, // If false, options will not be wrapped in a form
			) );
			$general_tab = $settings_page->createTab( array(
				'name' => __( 'General' ), // Name of the tab
				'id' => 'general', // Unique ID of the tab
				'title' => __( 'General' ), // Title to display in the admin panel when tab is active
			) );
			$general_tab->createOption( array(
				'name' => __( 'Icon (Logo) URL' ), // Name of the option
				'desc' => 'This logo will be used for all the Menu, Submenu, Post Types Menu Icons in WordPress HRM', // Description of the option
				'id' => 'logo_url', // Unique ID of the option
				'type' => 'text', //
				'default' => RT_PM_URL . 'app/assets/img/pm-16X16.png', // Menu icon for top level menus only
				'example' => 'http://google.com/icon.png', // An example value for this field, will be displayed in a <code>
				'livepreview' => '', // jQuery script to update something in the site. For theme customizer only
			) );
			$general_tab->createOption( array(
				'type' => 'save'
			) );
		}

		/**
		 * @return TitanFramework
		 */
		function get_settings_instance() {
			return TitanFramework::getInstance( RT_PM_TEXT_DOMAIN );
		}

		/**
		 * @return bool
		 */
		function is_plugin_activation_action() {
			// Don't do anything when we're activating a plugin to prevent errors
			// on redeclaring Titan classes
			if ( ! empty( $_GET[ 'action' ] ) && ! empty( $_GET[ 'plugin' ] ) ) {
				if ( $_GET[ 'action' ] == 'activate' ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * @return bool
		 */
		function is_titan_activated() {
			// Check if the framework plugin is activated
			$activePlugins = get_option( 'active_plugins' );
			if ( is_array( $activePlugins ) ) {
				foreach ( $activePlugins as $plugin ) {
					if ( is_string( $plugin ) ) {
						if ( stripos( $plugin, '/titan-framework.php' ) !== false ) {
							return true;
						}
					}
				}
			}
			return false;
		}

		/**
		 * @return bool
		 */
		function embedd_titan_framework() {
			/*
			 * When using the embedded framework, use it only if the framework
			 * plugin isn't activated.
			 */

			if ( $this->is_plugin_activation_action() ) {
				return false;
			}

			// Titan Already available as Plugin
			if ( $this->is_titan_activated() ) {
				return true;
			}

			// Use the embedded Titan Framework
			if ( ! class_exists( 'TitanFramework' ) ) {
				require_once( RT_HRM_PATH . 'app/vendor/titan-framework/titan-framework.php' );
			}
			return true;
		}
	}
}
