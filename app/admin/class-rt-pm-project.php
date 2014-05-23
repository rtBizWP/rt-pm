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
 * Description of Rt_PM_Project
 *
 * @author udit
 */
if( !class_exists( 'Rt_PM_Project' ) ) {
	class Rt_PM_Project {

		var $post_type = 'rt_project';
		// used in mail subject title - to detect whether it's a PM mail or not. So no translation
		var $name = 'PM';
		var $labels = array();
		var $statuses = array();
		var $custom_menu_order = array();

		public function __construct() {
			$this->get_custom_labels();
			$this->get_custom_statuses();
			add_action( 'init', array( $this, 'init_project' ) );
			$this->hooks();
		}

		function init_project() {
			$menu_position = 31;
			$this->register_custom_post( $menu_position );
			$this->register_custom_statuses();

			$settings = get_site_option( 'rt_pm_settings', false );
			if ( isset( $settings['attach_contacts'] ) && $settings['attach_contacts'] == 'yes' && function_exists( 'rt_biz_register_person_connection' ) ) {
				rt_biz_register_person_connection( $this->post_type, $this->labels['name'] );
			}
			if ( isset( $settings['attach_accounts'] ) && $settings['attach_accounts'] == 'yes' && function_exists( 'rt_biz_register_organization_connection' ) ) {
				rt_biz_register_organization_connection( $this->post_type, $this->labels['name'] );
			}
		}

		function hooks() {
			add_action( 'admin_menu', array( $this, 'register_custom_pages' ), 1 );
		}

		function register_custom_pages() {

		}

		function register_custom_post( $menu_position ) {
			$pm_logo_url = get_site_option( 'rtpm_logo_url' );

			if ( empty( $pm_logo_url ) ) {
                $pm_logo_url = RT_PM_URL.'app/assets/img/pm-16X16.png';
			}

			$args = array(
				'labels' => $this->labels,
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true, // Show the UI in admin panel
				'menu_icon' => $pm_logo_url,
				'menu_position' => $menu_position,
				'supports' => array('title', 'editor', 'comments', 'custom-fields'),
				'capability_type' => $this->post_type,
			);
			register_post_type( $this->post_type, $args );
		}

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
				'name' => __( 'Project' ),
				'singular_name' => __( 'Project' ),
				'menu_name' => __( 'PM' ),
				'all_items' => __( 'Projects' ),
				'add_new' => __( 'Add Project' ),
				'add_new_item' => __( 'Add Project' ),
				'new_item' => __( 'Add Project' ),
				'edit_item' => __( 'Edit Project' ),
				'view_item' => __( 'View Project' ),
				'search_items' => __( 'Search Projects' ),
			);
			return $this->labels;
		}

		function get_custom_statuses() {
			$this->statuses = array(
				array(
					'slug' => 'new',
					'name' => __( 'New' ),
					'description' => __( 'New lead is created' ),
				),
				array(
					'slug' => 'assigned',
					'name' => __( 'Assigned' ),
					'description' => __( 'Lead is assigned' ),
				),
				array(
					'slug' => 'requirement-analysis',
					'name' => __( 'Requirement Analysis' ),
					'description' => __( 'Lead is under requirement analysis' ),
				),
				array(
					'slug' => 'quotation',
					'name' => __( 'Quotation' ),
					'description' => __( 'Lead is in quotation phase' ),
				),
				array(
					'slug' => 'negotiation',
					'name' => __( 'Negotiation' ),
					'description' => __( 'Lead is in negotiation phase' ),
				),
				array(
					'slug' => 'closed',
					'name' => __( 'Closed' ),
					'description' => __( 'Lead is closed' ),
				),
			);
			return $this->statuses;
		}

		function dashboard() {
			/*global $rt_pm_dashboard;
			$rt_pm_dashboard->ui( $this->post_type );*/
		}

		function add_dashboard_widgets() {

		}

		function ui() {
			/*global $rt_attributes_model;
			rtpm_get_template('admin/add-module.php', array( 'rt_attributes_model' => $rt_attributes_model ) );*/
		}

	}
}
