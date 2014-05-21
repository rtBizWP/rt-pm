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
 * Description of Rt_PM_Admin
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_PM_Admin' ) ) {

	class Rt_PM_Admin {

		public static $menu_position = 32;
		public static $pm_page_slug = 'rt-pm-dashboard';
		public $menu_order = array();

		public function __construct() {
			if ( is_admin() ) {
				$this->get_custom_menu_order();
				$this->hooks();
			}
		}

		function get_custom_menu_order() {

		}

		function hooks() {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
			add_action( 'admin_menu', array( $this, 'register_menu' ), 1 );
//			add_action( 'admin_bar_menu', array( $this, 'register_toolbar_menu' ), 100 );

			add_filter( 'custom_menu_order', array( $this, 'custom_pages_order' ) );
		}

		function load_styles_scripts() {

		}

		function register_menu() {
//			$admin_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'admin' );
//			$editor_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'editor' );
			$author_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'author' );



			add_menu_page( __( 'PM' ), __( 'PM' ), $author_cap, self::$pm_page_slug, 'icon-url', self::$menu_position );
		}

		function custom_pages_order( $menu_order ) {
			global $submenu;
			global $menu;
			if ( isset( $submenu[ self::$pm_page_slug ] ) && ! empty( $submenu[ self::$pm_page_slug ] ) ) {
				$menu = $submenu[ self::$pm_page_slug ];
				$new_menu = array();

				foreach ( $menu as $p_key => $item ) {
					foreach ( $this->menu_order as $slug => $order ) {
						if ( false !== array_search( $slug, $item ) ) {
							$new_menu[ $order ] = $item;
						}
					}
				}
				ksort( $new_menu );
				$submenu[ self::$pm_page_slug ] = $new_menu;
			}
			return $menu_order;
		}

	}

}
