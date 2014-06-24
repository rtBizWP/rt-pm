<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rt_PM_Task
 *
 * @author udit
 */
if( !class_exists( 'Rt_PM_Task' ) ) {
    class Rt_PM_Task {
        var $post_type = 'rt_task';
        // used in mail subject title - to detect whether it's a PM mail or not. So no translation
        var $name = 'PM';
        var $labels = array();
        var $statuses = array();
        var $custom_menu_order = array();

        public function __construct() {
            $this->get_custom_labels();
            $this->get_custom_statuses();
            add_action( 'init', array( $this, 'init_task' ) );
            $this->hooks();
        }

        function init_task() {
            $menu_position = 31;
            $this->register_custom_post( $menu_position );
            $this->register_custom_statuses();

            $settings = get_site_option( 'rt_pm_settings', false );
            if ( isset( $settings['attach_contacts'] ) && $settings['attach_contacts'] == 'yes' ) {
                rt_biz_register_person_connection( $this->post_type, $this->labels['name'] );
            }
            if ( isset( $settings['attach_accounts'] ) && $settings['attach_accounts'] == 'yes' ) {
                rt_biz_register_organization_connection( $this->post_type, $this->labels['name'] );
            }
        }

        function hooks() {
            add_action( 'admin_menu', array( $this, 'register_custom_pages' ), 1 );
        }

        function register_custom_pages() {

        }

        function register_custom_post( $menu_position ) {
            $logo_url = Rt_PM_Settings::$settings['logo_url'];

            if ( empty( $pm_logo_url ) ) {
                $pm_logo_url = RT_PM_URL.'app/assets/img/pm-16X16.png';
            }

            $args = array(
                'labels' => $this->labels,
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => false, // Show the UI in admin panel
                'menu_icon' => $logo_url,
                'menu_position' => $menu_position,
                'supports' => array('title', 'editor', 'comments', 'custom-fields'),
                'capability_type' => $this->post_type,
            );
            register_post_type( $this->post_type, $args );
        }

        function footer_scripts() { ?>
            <script>postboxes.add_postbox_toggles(pagenow);</script>
        <?php }

        function register_custom_statuses() {
            foreach ($this->statuses as $status) {

                register_post_status($status['slug'], array(
                    'label' => $status['slug']
                , 'protected' => true
                , '_builtin' => false
                , 'label_count' => _n_noop("{$status['name']} <span class='count'>(%s)</span>", "{$status['name']} <span class='count'>(%s)</span>"),
                ));
            }
        }

        function get_custom_labels() {
            $this->labels = array(
                'name' => __( 'Task' ),
                'singular_name' => __( 'Task' ),
                'menu_name' => __( 'PM-Task' ),
                'all_items' => __( 'Tasks' ),
                'add_new' => __( 'Add Task' ),
                'add_new_item' => __( 'Add Task' ),
                'new_item' => __( 'Add Task' ),
                'edit_item' => __( 'Edit Task' ),
                'view_item' => __( 'View Task' ),
                'search_items' => __( 'Search Tasks' ),
            );
            return $this->labels;
        }

        function get_custom_statuses() {
            $this->statuses = array(
                array(
                    'slug' => 'new',
                    'name' => __( 'New' ),
                    'description' => __( 'New Task is Created' ),
                ),
                array(
                    'slug' => 'assigned',
                    'name' => __( 'Assigned' ),
                    'description' => __( 'Task is assigned' ),
                ),
                array(
                    'slug' => 'inprogress',
                    'name' => __( 'Inprogress' ),
                    'description' => __( 'Task is Inprogress' ),
                ),
                array(
                    'slug' => 'ask_client',
                    'name' => __( 'Ask-Client' ),
                    'description' => __( 'Task is for client' ),
                ),
                array(
                    'slug' => 'confirmed',
                    'name' => __( 'Confirmed' ),
                    'description' => __( 'Task is Confirmed' ),
                ),
                array(
                    'slug' => 'duplicate',
                    'name' => __( 'Duplicate' ),
                    'description' => __( 'Task is Duplicate' ),
                ),
                array(
                    'slug' => 'blocked',
                    'name' => __( 'Blocked' ),
                    'description' => __( 'Task is Blocked' ),
                ),
                array(
                    'slug' => 'fixed',
                    'name' => __( 'Fixed' ),
                    'description' => __( 'Task is Fixed' ),
                ),
                array(
                    'slug' => 'reopened',
                    'name' => __( 'Reopened' ),
                    'description' => __( 'Task is Reopened' ),
                ),
                array(
                    'slug' => 'verified',
                    'name' => __( 'Verified' ),
                    'description' => __( 'Task is verified' ),
                ),
                array(
                    'slug' => 'completed',
                    'name' => __( 'Completed' ),
                    'description' => __( 'Task is completed' ),
                ),
            );
            return $this->statuses;
        }

        function dashboard() {
        }

        function add_dashboard_widgets() {
        }

        function ui() {
        }
    }
}