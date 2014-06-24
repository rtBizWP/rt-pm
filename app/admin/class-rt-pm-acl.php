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
 * Description of Rt_PM_ACL
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_PM_ACL' ) ) {
	class Rt_PM_ACL {
		public function __construct() {
			add_filter( 'rt_biz_modules', array( $this, 'register_rt_pm_module' ) );
		}

		function register_rt_pm_module( $modules ) {
			global $rt_pm_project,$rt_pm_task;
			$module_key = ( function_exists( 'rt_biz_sanitize_module_key' ) ) ? rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) : '';
			$rt_pm_options = maybe_unserialize( get_option( RT_PM_TEXT_DOMAIN.'_options' ) );
			$menu_label = $rt_pm_options['menu_label'];
			$modules[ $module_key ] = array(
				'label' => $menu_label,
				'post_types' => array( $rt_pm_project->post_type,$rt_pm_task->post_type ),
			);
			return $modules;
		}
	}
}
