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
//			add_action( 'admin_menu', array( $this, 'register_menu' ), 1 );
//			add_action( 'admin_bar_menu', array( $this, 'register_toolbar_menu' ), 100 );
//			add_filter( 'custom_menu_order', array( $this, 'custom_pages_order' ) );
		}

		function load_styles_scripts() {
            global $rt_pm_project;
            $pagearray = array( 'rtpm-all-'.$rt_pm_project->post_type,'rtpm-add-'.$rt_pm_project->post_type );
            if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $pagearray ) ) {
                wp_enqueue_script('jquery-ui-timepicker-addon', RT_PM_URL . 'app/assets/javascripts/jquery-ui-timepicker-addon.js',array("jquery-ui-datepicker","jquery-ui-slider"), RT_PM_VERSION, true);

                wp_enqueue_script('foundation.zepto', RT_PM_URL . 'app/assets/javascripts/vendor/zepto.js',array("jquery"), "", true);
                wp_enqueue_script('jquery.foundation.reveal', RT_PM_URL . 'app/assets/javascripts/jquery.foundation.reveal.js',array("foundation-js"), "", true);
                wp_enqueue_script('jquery.foundation.form', RT_PM_URL . 'app/assets/javascripts/foundation/foundation.forms.js',array("foundation-js"), "", true);
                wp_enqueue_script('jquery.foundation.tabs', RT_PM_URL . 'app/assets/javascripts/foundation/foundation.section.js',array("foundation-js"), "", true);
                wp_enqueue_script('foundation-modernizr-js', RT_PM_URL . 'app/assets/javascripts/vendor/custom.modernizr.js', array(), "", false);
                wp_enqueue_script('foundation-js', RT_PM_URL . 'app/assets/javascripts/foundation/foundation.js',array("jquery","foundation.zepto"), RT_PM_VERSION, true);
                wp_enqueue_script('sticky-kit', RT_PM_URL . 'app/assets/javascripts/stickyfloat.js', array('jquery'), RT_PM_VERSION, true);
                wp_enqueue_script('rtpm-admin-js', RT_PM_URL . 'app/assets/javascripts/admin.js',array("foundation-js"), RT_PM_VERSION, true);
                wp_enqueue_script('moment-js', RT_PM_URL . 'app/assets/javascripts/moment.js',array("jquery"), RT_PM_VERSION, true);

                if( !wp_script_is('jquery-ui-accordion') ) {
                    wp_enqueue_script( 'jquery-ui-accordion' );
                }

                wp_enqueue_style('foundation-icon-general-css', RT_PM_URL . 'app/assets/css/general_foundicons.css', false, "", 'all');
                wp_enqueue_style('foundation-icon-general-ie-css', RT_PM_URL . 'app/assets/css/general_foundicons_ie7.css', false, "", 'all');
                wp_enqueue_style('foundation-icon-social-css', RT_PM_URL . 'app/assets/css/social_foundicons.css', false, "", 'all');
                wp_enqueue_style('foundation-icon-social-ie-css', RT_PM_URL . 'app/assets/css/social_foundicons_ie7.css', false, "", 'all');
                wp_enqueue_style('foundation-normalize', RT_PM_URL . 'app/assets/css/legacy_normalize.css', false, '', 'all');
                wp_enqueue_style('foundation-legacy-css', RT_PM_URL . 'app/assets/css/legacy_admin.css', false, '', 'all');
                wp_enqueue_style('rtpm-admin-css', RT_PM_URL . 'app/assets/css/admin.css', false, '', 'all');

                if( !wp_script_is('jquery-ui-autocomplete') ) {
                    wp_enqueue_script('jquery-ui-autocomplete', '', array('jquery-ui-widget', 'jquery-ui-position'), '1.9.2',true);
                }

                if( !wp_script_is('jquery-ui-datepicker') ) {
                    wp_enqueue_script( 'jquery-ui-datepicker' );
                }

				wp_enqueue_style('jquery-ui-css', RT_HRM_URL . 'app/assets/css/jquery-ui-1.9.2.custom.css', false, RT_PM_VERSION, 'all');

                wp_enqueue_script( 'postbox' );

            }
			wp_enqueue_script('rtpm-wp-menu-patch-js', RT_PM_URL . 'app/assets/javascripts/wp-menu-patch.js',array("jquery"), RT_PM_VERSION, true);
            $this->localize_scripts();
		}

        function localize_scripts() {
            global $rt_pm_project;
            $pagearray = array( 'rtpm-all-'.$rt_pm_project->post_type,'rtpm-add-'.$rt_pm_project->post_type );
            if( wp_script_is( 'rtpm-admin-js' ) && isset( $_REQUEST['post_type'] ) && isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $pagearray ) ) {
                $user_edit = false;
                if ( current_user_can( "edit_{$rt_pm_project->post_type}" ) ) {
                    $user_edit = true;
                }
                wp_localize_script( 'rtpm-admin-js', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
                wp_localize_script( 'rtpm-admin-js', 'rtcrm_post_type', $_REQUEST['post_type'] );
                wp_localize_script( 'rtpm-admin-js', 'rtcrm_user_edit', array($user_edit) );
            } else {
                wp_localize_script( 'rtpm-admin-js', 'rtcrm_user_edit', array('') );
            }

			if ( $_REQUEST['taxonomy'] == Rt_PM_Time_Entry_Type::$time_entry_type_tax && $_REQUEST['post_type'] == Rt_PM_Time_Entries::$post_type ) {
				wp_localize_script( 'rtpm-wp-menu-patch-js', 'rtpm_time_entry_type_screen', admin_url( 'edit-tags.php?taxonomy='.Rt_PM_Time_Entry_Type::$time_entry_type_tax.'&post_type='.  Rt_PM_Time_Entries::$post_type ) );
			}
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
