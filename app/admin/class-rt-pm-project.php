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
            $this->get_custom_menu_order();
			add_action( 'init', array( $this, 'init_project' ) );
			$this->hooks();
		}

		function init_project() {
			$menu_position = 31;
			$this->register_custom_post( $menu_position );
			$this->register_custom_statuses();

			/*$settings = get_site_option( 'rt_pm_settings', false );
			if ( isset( $settings['attach_contacts'] ) && $settings['attach_contacts'] == 'yes' && function_exists( 'rt_biz_register_person_connection' ) ) {
				rt_biz_register_person_connection( $this->post_type, $this->labels['name'] );
			}
			if ( isset( $settings['attach_accounts'] ) && $settings['attach_accounts'] == 'yes' && function_exists( 'rt_biz_register_organization_connection' ) ) {
				rt_biz_register_organization_connection( $this->post_type, $this->labels['name'] );
			}*/

            global $rt_pm_project_type;
            $rt_pm_project_type->project_type( rtpm_post_type_name( $this->labels['name'] ) );
		}

		function hooks() {
			add_action( 'admin_menu', array( $this, 'register_custom_pages' ), 1 );
            add_filter( 'custom_menu_order', array($this, 'custom_pages_order') );
            add_action( 'p2p_init', array( $this, 'create_connection' ) );
           // add_action( 'save_post', 'update_project_meta' );
		}

		function register_custom_pages() {
            $author_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'author' );

            $filter = add_submenu_page( 'edit.php?post_type='.$this->post_type, __( 'Dashboard' ), __( 'Dashboard' ), $author_cap, 'rtpm-all-'.$this->post_type, array( $this, 'dashboard' ) );
            add_action( "load-$filter", array( $this, 'add_screen_options' ) );
            add_action( 'admin_footer-'.$filter, array( $this, 'footer_scripts' ) );

            $screen_id = add_submenu_page( 'edit.php?post_type='.$this->post_type, __('Add ' . ucfirst( $this->labels['name'] ) ), __('Add ' . ucfirst( $this->labels['name'] ) ), $author_cap, 'rtpm-add-'.$this->post_type, array( $this, 'custom_page_ui' ) );
            add_action( 'admin_footer-'.$screen_id, array( $this, 'footer_scripts' ) );

			add_submenu_page( 'edit.php?post_type='.$this->post_type, __( 'Time Entry Types' ), __( 'Time Entry Types' ), $author_cap, 'edit-tags.php?taxonomy='.Rt_PM_Time_Entry_Type::$time_entry_type_tax.'&post_type='.Rt_PM_Time_Entries::$post_type );
			// $screen_id = add_submenu_page( 'edit.php?post_type='.$this->post_type, __('Add ' . ucfirst( $this->labels['name'] ) ), __('Add ' . ucfirst( $this->labels['name'] ) ), $author_cap, 'rtcrm-add-'.$this->post_type, array( $this, 'custom_page_ui' ) );
            //add_action( 'admin_footer-'.$screen_id, array( $this, 'footer_scripts' ) );
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
				'show_ui' => true, // Show the UI in admin panel
				'menu_icon' => $logo_url,
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
					'description' => __( 'New Project is created' ),
				),
				array(
					'slug' => 'active',
					'name' => __( 'Active' ),
					'description' => __( 'Project is activated' ),
				),
				array(
					'slug' => 'paused',
					'name' => __( 'Paused' ),
					'description' => __( 'Project is under Paused' ),
				),
				array(
					'slug' => 'complete',
					'name' => __( 'Complete' ),
					'description' => __( 'Project is Completed' ),
				),
				array(
					'slug' => 'closed',
					'name' => __( 'Closed' ),
					'description' => __( 'Project is closed' ),
				),
			);
			return $this->statuses;
		}

        function get_custom_menu_order(){
            $this->custom_menu_order = array(
                'rtpm-all-'.$this->post_type,
                'rtpm-add-'.$this->post_type,
                'edit-tags.php?taxonomy='.Rt_PM_Project_Type::$project_type_tax.'&amp;post_type='.$this->post_type,
                'edit-tags.php?taxonomy='.Rt_PM_Time_Entry_Type::$time_entry_type_tax.'&post_type='.  Rt_PM_Time_Entries::$post_type,
                'rtpm-settings',
            );
        }

        function custom_pages_order( $menu_order ) {
            global $submenu;
            global $menu;

			$dashed_menu_item = array(
				'edit-tags.php?taxonomy='.Rt_PM_Project_Type::$project_type_tax.'&amp;post_type='.$this->post_type,
				'edit-tags.php?taxonomy='.Rt_PM_Time_Entry_Type::$time_entry_type_tax.'&post_type='.  Rt_PM_Time_Entries::$post_type,
			);

            if ( isset( $submenu['edit.php?post_type='.$this->post_type] ) && !empty( $submenu['edit.php?post_type='.$this->post_type] ) ) {
                $module_menu = $submenu['edit.php?post_type='.$this->post_type];
                unset($submenu['edit.php?post_type='.$this->post_type]);
                unset($module_menu[5]);
                unset($module_menu[10]);
                $new_index = 5;
                foreach ( $this->custom_menu_order as $item ) {
                    foreach ( $module_menu as $p_key => $menu_item ) {
                        if ( in_array( $item, $menu_item ) ) {
                            if ( in_array( $item, $dashed_menu_item )  ) {
                                $menu_item[0]= '--- '.$menu_item[0];
                            }
                            $submenu['edit.php?post_type='.$this->post_type][$new_index] = $menu_item;
                            unset ( $module_menu[$p_key] );
                            $new_index += 5;
                            break;
                        }
                    }
                }
                foreach( $module_menu as $p_key => $menu_item ) {
                    $menu_item[0]= '--- '.$menu_item[0];
                    $submenu['edit.php?post_type='.$this->post_type][$new_index] = $menu_item;
                    unset ( $module_menu[$p_key] );
                    $new_index += 5;
                }
            }
            return $menu_order;
        }

        /**
         * Prints the jQuery script to initiliase the metaboxes
         * Called on admin_footer-*
         */
        function footer_scripts() { ?>
            <script> postboxes.add_postbox_toggles(pagenow);</script>
        <?php }

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

        /**
         * Custom page ui for Add Project
         */
		function custom_page_ui() {
            $args = array(
                'post_type' => $this->post_type,
                'labels' => $this->labels,
            );
            rtpm_get_template('admin/project.php', $args);
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
                p2p_delete_connections( $this->post_type.'_to_'.$post_type, array( 'from' => $from ) );
            }
            if ( ! p2p_connection_exists( $this->post_type.'_to_'.$post_type, array( 'from' => $from, 'to' => $to ) ) ) {
                p2p_create_connection( $this->post_type.'_to_'.$post_type, array( 'from' => $from, 'to' => $to ) );
            }
        }

        /**
         * Delete relationship between project and task
         * @param $post_type
         * @param string $from
         * @param string $to
         */
        function remove_connect_post_to_entity( $post_type, $to = '') {
                p2p_delete_connections( $this->post_type.'_to_'.$post_type, array( 'to' => $to ) );
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
