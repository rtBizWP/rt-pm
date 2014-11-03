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
 * Description of Rt_PM_Bp_PM_Frontend
 *
 * @author kishore
 */
if( !class_exists( 'Rt_PM_Bp_PM_Frontend' ) ) {
    /**
     * Class Rt_PM_Bp_PM_Frontend
     */
    class Rt_PM_Bp_PM_Frontend extends Rt_PM_Admin {

        /**
         * Object initialization
         */
        public function __construct() {
			if ( ! is_admin() ) {
				$this->hooks();
			}
		}
		
		function hooks() {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
		}

		function load_styles_scripts() {
            global $rt_pm_project;
            if ( bp_is_current_component( 'pm' ) ){
                wp_enqueue_script('jquery-ui-timepicker-addon', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/jquery-ui-timepicker-addon.js',array("jquery-ui-datepicker","jquery-ui-slider"), RT_PM_VERSION, true);

                wp_enqueue_script('foundation.zepto', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/vendor/zepto.js',array("jquery"), "", true);
                wp_enqueue_script('jquery.foundation.reveal', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/jquery.foundation.reveal.js',array("foundation-js"), "", true);
                //wp_enqueue_script('jquery.foundation.form', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/foundation/foundation.forms.js',array("foundation-js"), "", true);
                wp_enqueue_script('jquery.foundation.table', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/responsive-tables.js',array("foundation-js"), "", true);
                wp_enqueue_script('foundation-modernizr-js', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/vendor/custom.modernizr.js', array(), "", false);
                wp_enqueue_script('foundation-js', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/foundation/foundation.js',array("jquery","foundation.zepto"), RT_PM_VERSION, true);
                wp_enqueue_script('rtpm-admin-js', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/admin-frontend.js',array("foundation-js"), RT_PM_VERSION, true);
				wp_enqueue_script('rtpm-readmore', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/readmore.min.js',array("jquery"), "", true);
				// Code for front-end pagination
				wp_enqueue_script('rtpm-frontend-js', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/frontend.js','jquery', RT_PM_VERSION, true);
                wp_enqueue_script('moment-js', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/moment.js',array("jquery"), RT_PM_VERSION, true);

                if( !wp_script_is('jquery-ui-accordion') ) {
                    wp_enqueue_script( 'jquery-ui-accordion' );
                }

				wp_enqueue_media(); // Enqueues all scripts, styles, settings, and templates necessary to use all media JavaScript APIs.
				
                wp_enqueue_style('foundation-icon-general-css', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/general_foundicons.css', false, "", 'all');
                wp_enqueue_style('foundation-icon-general-ie-css', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/general_foundicons_ie7.css', false, "", 'all');
                wp_enqueue_style('foundation-icon-social-css', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/social_foundicons.css', false, "", 'all');
                wp_enqueue_style('foundation-icon-social-ie-css', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/social_foundicons_ie7.css', false, "", 'all');
                wp_enqueue_style('foundation-normalize', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/legacy_normalize.css', false, '', 'all');
                wp_enqueue_style('foundation-legacy-css', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/legacy_admin.css', false, '', 'all');
                wp_enqueue_style('foundation.table-css', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/responsive-tables.css', false, '', 'all');
				wp_enqueue_style( 'foundation-css', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/foundation.min.css' , array('buddyboss-wp-frontend') );
				wp_enqueue_style('rtpm-frontend-css', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/pm-frontend.css', false, RT_PM_VERSION, 'all');

                if( !wp_script_is('jquery-ui-autocomplete') ) {
                    wp_enqueue_script('jquery-ui-autocomplete', '', array('jquery-ui-widget', 'jquery-ui-position'), '1.9.2',true);
                }

                if( !wp_script_is('jquery-ui-datepicker') ) {
                    wp_enqueue_script( 'jquery-ui-datepicker' );
                }

				if ( !  wp_style_is( 'rt-jquery-ui-css' ) ) {
					wp_enqueue_style('rt-jquery-ui-css', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/css/jquery-ui-1.9.2.custom.css', false, RT_PM_VERSION, 'all');
				}

                wp_enqueue_script( 'postbox' );

            }
			wp_enqueue_script('rtpm-wp-menu-patch-js', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/wp-menu-patch.js',array("jquery"), RT_PM_VERSION, true);
            $this->localize_scripts();
		}

        function localize_scripts() {
            global $rt_pm_project, $rt_pm_bp_pm;
             $user_edit = false;
                if ( current_user_can( "edit_{$rt_pm_project->post_type}" ) ) {
                    $user_edit = true;
                }
                wp_localize_script( 'rtpm-admin-js', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
                wp_localize_script( 'rtpm-admin-js', 'rtpm_post_type', $_REQUEST['post_type'] );
                wp_localize_script( 'rtpm-admin-js', 'rtpm_user_edit', array($user_edit) );
				wp_localize_script( 'rtpm-frontend-js', 'rtpmurl', RT_PM_URL );

			// if (isset($_REQUEST['taxonomy']) && $_REQUEST['taxonomy'] == Rt_PM_Time_Entry_Type::$time_entry_type_tax ) {
				// wp_localize_script( 'rtpm-wp-menu-patch-js', 'rtpm_time_entry_type_screen', admin_url( 'edit-tags.php?taxonomy='.Rt_PM_Time_Entry_Type::$time_entry_type_tax ) );
			// }
        }
	}
}