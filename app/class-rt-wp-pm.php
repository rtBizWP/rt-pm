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

			if( ! $this->check_rt_biz_dependecy() ) {
				return false;
			}

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
		}

		function check_rt_biz_dependecy() {
            $flag = true;
            $used_function = array(
                'rt_biz_get_access_role_cap'
            );

            foreach ( $used_function as $fn ) {
                if ( ! function_exists( $fn ) ) {
                    $flag = false;
                }
            }

            if ( ! class_exists( 'Rt_Biz' ) ) {
                $flag = false;
            }

            if ( ! $flag ) {
                add_action( 'admin_notices', array( $this, 'rt_biz_admin_notice' ) );
            }

            return $flag;
		}

		function rt_biz_admin_notice() { ?>
			<div class="updated">
				<p><?php _e( sprintf( 'WordPress PM : It seems that rtBiz plugin is not installed or activated. Please %s / %s it.', '<a href="'.admin_url( 'plugin-install.php?tab=search&s=rt-contacts' ).'">'.__( 'install' ).'</a>', '<a href="'.admin_url( 'plugins.php' ).'">'.__( 'activate' ).'</a>' ) ); ?></p>
			</div>
		<?php }

		function init_globals() {
			global $rtpm_form,
                   $rt_pm_time_entries_model,$rtpm_custom_media_fields,
                   $rt_pm_project_type,$rt_pm_add_project,$rt_pm_project,$rt_pm_task,$rt_pm_time_entries,$rt_pm_acl,
                   $rt_pm_settings;

//					$rt_crm_gravity_form_importer, $rt_crm_settings, $rt_crm_logs,$taxonomy_metadata,
//					$rt_crm_mail_acl_model, $rt_crm_mail_thread_importer_model,
//					$rt_crm_mail_message_model, $rt_crm_mail_outbound_model,
//					$rt_crm_gravity_fields_mapping_model, $rt_crm_user_settings,
//					$rt_crm_dashboard, $rt_crm_lead_history_model, $rt_reports,
//					$rt_crm_accounts, $rt_crm_contacts, $rt_crm_closing_reason,
//					$rt_pm_imap_server_model, $rt_pm_gravity_form_mapper;

            $rtpm_form = new Rt_Form();

			$rt_pm_time_entries_model = new Rt_PM_Time_Entries_Model();
            $rtpm_custom_media_fields = new Rt_Custom_Media_Fields();

            $rt_pm_project_type = new Rt_PM_Project_Type();
            $rt_pm_add_project = new Rt_PM_Add_Project();
            $rt_pm_project = new Rt_PM_Project();
            $rt_pm_task = new Rt_PM_Task();
            $rt_pm_time_entries = new Rt_PM_Time_Entries();
            $rt_pm_acl = new Rt_PM_ACL();

            $rt_pm_settings = new Rt_PM_Settings();


//			$rt_crm_mail_accounts_model = new Rt_CRM_Mail_Accounts_Model();
//			$rt_crm_mail_acl_model = new Rt_CRM_Mail_ACL_Model();
//			$rt_crm_mail_thread_importer_model = new Rt_CRM_Mail_Thread_Importer_Model();
//			$rt_crm_mail_message_model = new Rt_CRM_Mail_Message_Model();
//			$rt_crm_mail_outbound_model = new Rt_CRM_Mail_Outbound_Model();
//			$rt_crm_gravity_fields_mapping_model = new Rt_CRM_Gravity_Fields_Mapping_Model();
//			$rt_crm_lead_history_model = new Rt_CRM_Lead_History_Model();
//			$rt_crm_imap_server_model = new Rt_CRM_IMAP_Server_Model();
//			$taxonomy_metadata = new Taxonomy_Metadata();
//			$taxonomy_metadata->activate();
//			$rt_crm_closing_reason = new Rt_CRM_Closing_Reason();
//			$rt_crm_attributes = new Rt_CRM_Attributes();
//			$rt_crm_roles = new Rt_CRM_Roles();
//			$rt_crm_accounts = new Rt_CRM_Accounts();
//			$rt_crm_contacts = new Rt_CRM_Contacts();
//			$rt_crm_leads = new Rt_CRM_Leads();
//
//			$rt_crm_dashboard = new Rt_CRM_Dashboard();
//
//			$rt_crm_gravity_form_importer = new Rt_CRM_Gravity_Form_Importer();
//			$rt_crm_gravity_form_mapper= new Rt_CRM_Gravity_Form_Mapper();
//			$rt_crm_settings = new Rt_CRM_Settings();
//			$rt_crm_user_settings = new Rt_CRM_User_Settings();
//			$rt_crm_logs = new Rt_CRM_Logs();
//
//			$page_slugs = array(
//				'rtcrm-'.$rt_crm_module->post_type.'-dashboard',
//			);
//			$rt_reports = new Rt_Reports( $page_slugs );
		}

		function init() {

		}

		function update_database() {
			$updateDB = new RT_DB_Update( trailingslashit( RT_PM_PATH ) . 'index.php', trailingslashit( RT_PM_PATH_SCHEMA ) );
			$updateDB->do_upgrade();
		}
	}
}
