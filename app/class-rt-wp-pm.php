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

			if ( ! $this->check_rt_biz_dependecy() ) {
				return false;
			}

			$this->init_globals();

			add_action( 'init', array( $this, 'admin_init' ), 5 );
			add_action( 'init', array( $this, 'init' ), 6 );

            do_action( 'rt_pm_init' );

            add_action( 'after_setup_theme', array( $this, 'init_pm_bp_component' ) );

            add_action( 'shutdown', array( $this, 'rt_pm_remove_activation_option' ) );
			add_action( "remove_lead", array( $this, 'lead_remove_bp_activity' ), 10, 2 );
		}
		
		function lead_remove_bp_activity( $post_id ){
			global $wpdb;
			$bp  = buddypress();
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->activity->table_name} WHERE item_id = %d", $post_id ) );
			if($row != NULL){
				$activity_id = (int) $row->id;
				$activity_userid = (int) $row->user_id;
				do_action( 'bp_activity_before_action_delete_activity', $activity_id, $activity_userid );
				if( !bp_activity_delete( array( 'id' => $activity_id, 'user_id' => $activity_userid ) ) ){
					return;
				}
			}
			return;
		}
		
        function init_pm_bp_component(){

            if( function_exists('bp_is_active') ) {

               $author_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'author' );

                if (  current_user_can( $author_cap ) ) {

                    global $rt_pm_bp_pm, $rt_pm_bp_pm_frontend, $rt_pm_bp_pm_project;

                    $rt_pm_bp_pm = new RT_PM_Bp_PM();
                    $rt_pm_bp_pm_frontend = new Rt_PM_Bp_PM_Frontend();
                    if ( ! is_admin() ){
                        $rt_pm_bp_pm_project = new Rt_PM_Bp_PM_Project();
                    }
                }
            }
        }


		function admin_init() {
			$this->templateURL = apply_filters('rtpm_template_url', 'rtpm/');

			$this->update_database();

			global $rt_pm_admin;
			$rt_pm_admin = new Rt_PM_Admin();

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
		}

		function check_rt_biz_dependecy() {
            $flag = true;
            $used_function = array(
                'rt_biz_get_access_role_cap',
                'rt_biz_sanitize_module_key'
            );

            foreach ( $used_function as $fn ) {
                if ( ! function_exists( $fn ) ) {
                    $flag = false;
                }
            }

            if ( ! $flag ) {
                add_action( 'admin_notices', array( $this, 'rt_biz_admin_notice' ) );
            }

            return $flag;
		}

		function rt_biz_admin_notice() { ?>
			<div class="updated">
				<p><?php _e( sprintf( 'Voxxi PM : It seems that rtBiz plugin is not installed or activated. Please %s / %s it.', '<a href="'.admin_url( 'plugin-install.php?tab=search&s=rt-contacts' ).'">'.__( 'install' ).'</a>', '<a href="'.admin_url( 'plugins.php' ).'">'.__( 'activate' ).'</a>' ) ); ?></p>
			</div>
		<?php }

		function init_globals() {
			global $rtpm_form,
                   $rt_pm_time_entries_model,$rtpm_custom_media_fields,
                   $rt_pm_project_type,$rt_pm_project,$rt_pm_task,$rt_pm_time_entries,$rt_pm_time_entry_type,$rt_pm_acl,
                   $rt_pm_settings, $rt_pm_notification, $rt_pm_user_reports, $rt_pm_help;

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

			$rt_pm_help = new RT_PM_Help();

		}

		function init() {

            $rt_pm_activated = get_option('rt_pm_activated');

            if( !$rt_pm_activated )
                return;

            update_option('rt_pm_job_last_index', '3200');
        }

		function update_database() {
			$updateDB = new RT_DB_Update( trailingslashit( RT_PM_PATH ) . 'index.php', trailingslashit( RT_PM_PATH_SCHEMA ) );
			$updateDB->do_upgrade();
		}

        /**
         * Remove flag option for plugin activation
         */
        function rt_pm_remove_activation_option(){

            $rt_pm_activated = get_option('rt_pm_activated');

            if( !$rt_pm_activated )
                return;

            delete_option('rt_pm_activated');

        }

    }
}
