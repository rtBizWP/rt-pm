<?php
/**
 * Installation related functions and actions.
 *
 * @author   paresh
 * @category Admin
 * @package  rtpm/Classes
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Rt_PM_Install Class
 */
class Rt_PM_Install {

    /**
     * Hook in tabs.
     */
    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
        add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );

    }

    /**
     * check_version function.
     */
    public static function check_version() {
        if ( ! defined( 'IFRAME_REQUEST' ) && ( get_option( 'rtpm_version' ) != RT_PM_VERSION ) ) {
            self::install();
            do_action( 'rtpm_updated' );
        }
    }

    /**
     * Install actions such as installing pages when a button is clicked.
     */
    public static function install_actions() {
        if ( ! empty( $_GET['do_update_rtpm'] ) ) {
            self::update();
        }
    }

    /**
     * Install RP
     */
    public static function install() {

        if ( ! defined( 'RP_INSTALLING' ) ) {
            define( 'RP_INSTALLING', true );
        }

        self::create_roles();

        self::update_rb_version();

         // Trigger action
        do_action( 'rtpm_installed' );
    }

    /**
     * Update RP version to current
     */
    private static function update_rb_version() {
        delete_option( 'rtpm_version' );
        add_option( 'rtpm_version', RT_PM_VERSION );
    }

    /**
     * Update DB version to current
     */
    private static function update_db_version( $version = null ) {
        delete_option( 'rtpm_db_version' );
        add_option( 'rtpm_db_version', is_null( $version ) ? RT_PM_VERSION : $version );
    }

    /**
     * Handle updates
     */
    private static function update() {
        $current_db_version = get_option( 'rtpm_db_version' );

        foreach ( self::$db_updates as $version => $updater ) {
            if ( version_compare( $current_db_version, $version, '<' ) ) {
                include( $updater );
                self::update_db_version( $version );
            }
        }

        self::update_db_version();
    }

    /**
     * Create roles and capabilities
     */
    public static function create_roles() {
        global $wp_roles;

        if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        $capabilities = self::get_core_capabilities();

        foreach ( $capabilities as $cap_group ) {
            foreach ( $cap_group as $cap ) {
                $wp_roles->add_cap( 'administrator', $cap );
            }
        }
    }

    /**
     * Get capabilities for rtpm - these are assigned to admin/shop manager during installation or reset
     *
     * @return array
     */
    private static function get_core_capabilities() {
        $capabilities = array();

        $capabilities['core'] = array(
            'manage_project',
            'manage_time_entry',
            'add_time_entry',
            'manage_project_notification',
            'manage_project_attachment',
            'run_user_reports'
        );

        $capability_types = array( 'rt_project', 'rt_task' );

        foreach ( $capability_types as $capability_type ) {

            $capabilities[ $capability_type ] = array(
                // Post type
                "edit_{$capability_type}",
                "read_{$capability_type}",
                "delete_{$capability_type}",
                "edit_{$capability_type}s",
                "edit_others_{$capability_type}s",
                "publish_{$capability_type}s",
                "read_private_{$capability_type}s",
                "delete_{$capability_type}s",
                "delete_private_{$capability_type}s",
                "delete_published_{$capability_type}s",
                "delete_others_{$capability_type}s",
                "edit_private_{$capability_type}s",
                "edit_published_{$capability_type}s",
            );
        }

        return $capabilities;
    }

    /**
     * rtpm_remove_roles function.
     */
    public static function remove_roles() {
        global $wp_roles;

        if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        $capabilities = self::get_core_capabilities();

        foreach ( $capabilities as $cap_group ) {
            foreach ( $cap_group as $cap ) {
                $wp_roles->remove_cap( 'administrator', $cap );
            }
        }

    }

}

Rt_PM_Install::init();
