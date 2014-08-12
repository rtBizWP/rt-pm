<?php

/*
  Plugin Name: Voxxi PM
  Plugin URI: http://voxxi.me
  Description: Part of the Voxxi Business Suite
  Version: 0.0.4
  Author: Voxxi
  Author URI: http://voxxi.me
  License: GPL
  Text Domain: rt_pm
 */

if ( !defined( 'RT_PM_VERSION' ) ) {
	define( 'RT_PM_VERSION', '0.0.4' );
}
if ( !defined( 'RT_PM_TEXT_DOMAIN' ) ) {
	define( 'RT_PM_TEXT_DOMAIN', 'rt_pm' );
}
if ( !defined( 'RT_PM_PATH' ) ) {
	define( 'RT_PM_PATH', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'RT_PM_URL' ) ) {
	define( 'RT_PM_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'RT_PM_PATH_APP' ) ) {
	define( 'RT_PM_PATH_APP', plugin_dir_path( __FILE__ ) . 'app/' );
}
if ( !defined( 'RT_PM_PATH_ADMIN' ) ) {
	define( 'RT_PM_PATH_ADMIN', plugin_dir_path( __FILE__ ) . 'app/admin/' );
}
if ( !defined( 'RT_PM_PATH_MODELS' ) ) {
	define( 'RT_PM_PATH_MODELS', plugin_dir_path( __FILE__ ) . 'app/models/' );
}
if ( !defined( 'RT_PM_PATH_SCHEMA' ) ) {
	define( 'RT_PM_PATH_SCHEMA', plugin_dir_path( __FILE__ ) . 'app/schema/' );
}
if ( !defined( 'RT_PM_PATH_LIB' ) ) {
	define( 'RT_PM_PATH_LIB', plugin_dir_path( __FILE__ ) . 'app/lib/' );
}
if ( !defined( 'RT_PM_PATH_VENDOR' ) ) {
	define( 'RT_PM_PATH_VENDOR', plugin_dir_path( __FILE__ ) . 'app/vendor/' );
}
if ( !defined( 'RT_PM_PATH_HELPER' ) ) {
	define( 'RT_PM_PATH_HELPER', plugin_dir_path( __FILE__ ) . 'app/helper/' );
}
if ( !defined( 'RT_PM_PATH_NOTIFICATION' ) ) {
	define( 'RT_PM_PATH_NOTIFICATION', plugin_dir_path( __FILE__ ) . 'app/notification/' );
}
if ( !defined( 'RT_PM_PATH_TEMPLATES' ) ) {
	define( 'RT_PM_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'templates/' );
}

include_once RT_PM_PATH_LIB . 'wp-helpers.php';

function rt_pm_include() {

	include_once RT_PM_PATH_HELPER . 'rtpm-functions.php';

	global $rtpm_app_autoload, $rtpm_admin_autoload, $rtpm_models_autoload, $rtpm_helper_autoload, $rtpm_form_autoload, $rtpm_settings_autoload, $rtpm_notification_autoload;
	$rtpm_app_autoload = new RT_WP_Autoload( RT_PM_PATH_APP );
	$rtpm_admin_autoload = new RT_WP_Autoload( RT_PM_PATH_ADMIN );
	$rtpm_models_autoload = new RT_WP_Autoload( RT_PM_PATH_MODELS );
	$rtpm_helper_autoload = new RT_WP_Autoload( RT_PM_PATH_HELPER );
	$rtpm_notification_autoload = new RT_WP_Autoload( RT_PM_PATH_NOTIFICATION );
	$rtpm_form_autoload = new RT_WP_Autoload( RT_PM_PATH_LIB . 'rtformhelpers/' );
    $rtpm_settings_autoload = new RT_WP_Autoload( RT_PM_PATH . 'app/settings/' );
//	$rtpm_reports_autoload = new RT_WP_Autoload( RT_PM_PATH_LIB . 'rtreports/' );
}

function rt_pm_init() {

	rt_pm_include();

	global $rt_wp_pm;
	$rt_wp_pm = new RT_WP_PM();

}
add_action( 'rt_biz_init', 'rt_pm_init', 1 );

add_action( 'init', 'rt_pm_timely_notification_init' );
function rt_pm_timely_notification_init() {

	global $rt_biz_notification_rules_model, $rt_pm_notification;

	if ( ! isset( $rt_biz_notification_rules_model ) || ! isset( $rt_pm_notification ) ) {
		return;
	}

	$rules = $rt_biz_notification_rules_model->get( array( 'rule_type' => 'periodic' ) );

	foreach ( $rules as $r ) {
		add_action( 'rt_pm_timely_notification_'.$r->id, array( $rt_pm_notification, 'exec_timely_notification_actions' ) );
	}
}

register_deactivation_hook( __FILE__, 'rt_pm_deactivate' );
function rt_pm_deactivate() {

	if ( ! class_exists( 'RT_Biz_Notification_Rules_Model' ) ) {
		return;
	}
	$rt_biz_notification_rules_model = new RT_Biz_Notification_Rules_Model();
	$rules = $rt_biz_notification_rules_model->get( array( 'rule_type' => 'periodic' ) );

	foreach ( $rules as $r ) {
		wp_clear_scheduled_hook( 'rt_pm_timely_notification_'.$r->id, array( $r ) );
	}
}
