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
            add_action( 'p2p_init', array( $this, 'create_connection' ) );
           // add_action( 'save_post', 'update_project_meta' );
		}

		function register_custom_pages() {
            $author_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'author' );

            $filter = add_submenu_page( 'edit.php?post_type='.$this->post_type, __( 'Dashboard' ), __( 'Dashboard' ), $author_cap, 'rtpm-all-'.$this->post_type, array( $this, 'dashboard' ) );
            add_action( "load-$filter", array( $this, 'add_screen_options' ) );

           // $screen_id = add_submenu_page( 'edit.php?post_type='.$this->post_type, __('Add ' . ucfirst( $this->labels['name'] ) ), __('Add ' . ucfirst( $this->labels['name'] ) ), $author_cap, 'rtcrm-add-'.$this->post_type, array( $this, 'custom_page_ui' ) );
            //add_action( 'admin_footer-'.$screen_id, array( $this, 'footer_scripts' ) );
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

        /**
         * Custom list page for Projects
         */
		function dashboard() {
            $args = array(
                'post_type' => $this->post_type,
                'labels' => $this->labels,
            );
            rtpm_get_template( 'admin/dashboard.php', $args );
		}

        /**
         * Add list view on dashboard
         */
        function add_screen_options() {
            $option = 'per_page';
            $args = array(
                'label' => $this->labels['all_items'],
                'default' => 10,
                'option' => $this->post_type.'_per_page',
            );
            add_screen_option($option, $args);
            //new Rt_CRM_Leads_List_View();
        }

		function add_dashboard_widgets() {

		}

		function ui() {
		}

        /**
         * Register post relation between project with task
         */
        function create_connection() {
            global $rt_pm_task;
            p2p_register_connection_type( array(
                'name' => $this->post_type.'_to_'.$rt_pm_task->post_type,
                'from' => $this->post_type,
                'to' => $rt_pm_task->post_type,
            ) );
        }

        /**
         * Create relationship between project and task
         * @param $post_type
         * @param string $from
         * @param string $to
         * @param bool $clear_old
         */
        function connect_post_to_entity( $post_type, $from = '', $to = '', $clear_old = false ) {
            if ( $clear_old ) {
                p2p_delete_connections( $post_type.'_to_'.$this->post_type, array( 'from' => $from ) );
            }
            if ( ! p2p_connection_exists( $post_type.'_to_'.$this->post_type, array( 'from' => $from, 'to' => $to ) ) ) {
                p2p_create_connection( $post_type.'_to_'.$this->post_type, array( 'from' => $from, 'to' => $to ) );
            }
        }

        /**
         * get Project and task relationship
         * @param $post_id
         * @param $connection
         * @param string $term_seperator
         * @return string
         */
        static function connection_to_string( $post_id, $connection, $term_seperator = ' , ' ) {
            $post = get_post( $post_id );
            $termsArr = get_posts(array(
                'connected_type' => $post->post_type.'_to_'.$connection,
                'connected_items' => $post,
                'nopaging' => true,
                'suppress_filters' => false,
            ));
            $tmpStr = '';
            if( $termsArr ) {
                $sep = '';
                foreach ( $termsArr as $tObj ) {
                    $tmpStr .= $sep . $tObj->post_title;
                    $sep = $term_seperator;
                }
            }
            return $tmpStr;
        }

    }
}
