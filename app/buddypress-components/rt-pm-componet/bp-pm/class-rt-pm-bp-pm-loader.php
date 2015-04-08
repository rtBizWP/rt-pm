<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implementation of RT_PM_Bp_PM_Loader
 *
 * @package BuddyPress_PM_Component
 * @author kishore
 */
if ( !class_exists( 'RT_PM_Bp_PM_Loader' ) ) {
	class RT_PM_Bp_PM_Loader extends BP_Component {
            
                /**
                 * Start the messages component creation process
                 *
                 * @since BuddyPress (1.5)
                 */
                private $sub_nav_items;
                static $projects_slug = 'projects';
                static $gantt_admin = 'gantt';
				static $ganttchart_slug = 'ganttchart';
                private $menu_order = 92;
                public function __construct() {
                		global $rt_biz_options;
		                $rt_pm_options = maybe_unserialize( get_option( RT_PM_TEXT_DOMAIN . '_options' ) );
		                $menu_label = $rt_pm_options[ 'menu_label' ];
		                $this->pm_label = $menu_label;
                    
                        parent::start(
                                'pm',
                                __( $this->pm_label, 'buddypress' ),
                               RT_PM_BP_PM_PATH,
                                array(
                                    'adminbar_myaccount_order' => $this->menu_order
                                )
                        );
                        $this->includes();
                }
        
	
                /**
                 * Include files
                 *
                 * @global BuddyPress $bp The one true BuddyPress instance
                 */
                public function includes( $includes = array() ) {
                    
                       $includes = array(
							'screens',
							'functions',
                            'actions',
						);

                       
                        parent::includes( $includes );
                }
                
                /**
                 * Setup globals
                 *
                 * The BP_MESSAGES_SLUG constant is deprecated, and only used here for
                 * backwards compatibility.
                 *
                 * @since BuddyPress (1.5)
                 */
                public function setup_globals( $args = array() ) {
                        $bp = buddypress();

                        // Define a slug, if necessary
                        if ( !defined( 'BP_PM_SLUG' ) )
                                define( 'BP_PM_SLUG', $this->id );

                       

                        // All globals for messaging component.
                        // Note that global_tables is included in this array.
                        $globals = array(
                                'slug'                  => BP_PM_SLUG,
                                'has_directory'         => false,
                                'notification_callback' => 'messages_format_notifications',
                                'search_string'         => __( 'Search Messages...', 'buddypress' ),
                                
                        );

                        $this->autocomplete_all = defined( 'BP_MESSAGES_AUTOCOMPLETE_ALL' );

                        parent::setup_globals( $globals );
                }
        
		/**
		 * Set up your component's navigation.
		 *
		 * The navigation elements created here are responsible for the main site navigation (eg
		 * Profile > Activity > Mentions), as well as the navigation in the BuddyBar. WP Admin Bar
		 * navigation is broken out into a separate method; see
		 * BP_Example_Component::setup_admin_bar().
		 *
		 * @global obj $bp
		 */
		function setup_nav( $nav = array(), $sub_nav = array() ) {
			global $rtbp_pm_screen;

            if( is_main_site() )
                return;
            
            $nav_name = __( 'PM', 'buddypress' );

			// Add 'hrm' to the main navigation
			$main_nav = array(
				'name' 		      => __( $this->pm_label ),
				'slug' 		      => $this->id,
				'position' 	      => $this->menu_order,
				'screen_function'     => array( $rtbp_pm_screen, 'bp_pm_screen' ),
				'default_subnav_slug' => 'projects',
			);

            $user_domain = rtbiz_get_user_domain();
            // Link to user people
            $people_link = trailingslashit( $user_domain . $this->slug );
			
			$add_projects = true;
			if ( bp_is_current_component('pm') && bp_current_action('archives') && isset( $_GET['action'] )){
				$add_projects = false;
			}
			if ( bp_is_current_component('pm') && isset( $_GET['post_type'] ) && ! bp_current_action('archives')){
    			$add_projects = true;
			}
			
			$add_archive = true;
			if ( bp_is_current_component('pm') && ! bp_current_action('archives') && isset( $_GET['rt_project_id'] )){
				$add_archive = false;
			}
			if ( bp_is_current_component('pm') && isset( $_GET['rt_project_id'] ) && bp_current_action('archives') ){
    			$add_archive = true;
			}

            if( isset( $_GET['rt_project_id'] ) ){

                if( 'trash' == get_post_status( $_GET['rt_project_id'] ) && isset( $_GET['rt_project_id'] ) ){
                    $add_archive = true;
                } else if ('trash' != get_post_status( $_GET['rt_project_id'] ) && isset( $_GET['rt_project_id'] ) ) {
                    $add_archive = false;
                }

                if( 'trash' == get_post_status( $_GET['rt_project_id'] ) && isset( $_GET['rt_project_id'] ) ){
                    $add_projects = false;
                } else if ('trash' != get_post_status( $_GET['rt_project_id'] ) && isset( $_GET['rt_project_id'] ) ) {
                    $add_projects = true;
                }

            }

			if( $add_projects == true){
				// Add the subnav items
				$sub_nav[] = array(
					'name'            =>  __( 'Projects' ),
					'slug'            => self::$projects_slug,
					'parent_url'      => $people_link,
					'parent_slug'     =>  $this->id,
					'screen_function' => array( $rtbp_pm_screen, 'bp_pm_projects' ),
					'position'        => 10,
				);
			}
			
			// Add the subnav items
			/*$sub_nav[] = array(
				'name'            =>  __( 'Add New' ),
				'slug'            => 'addnew',
				'parent_url'      => $people_link,
				'parent_slug'     =>  $this->id,
				'screen_function' => 'bp_pm_projects_new',
				'position'        => 10,
			);*/
			
			
			if( $add_archive == true){
				// Add the subnav items
				$sub_nav[] = array(
					'name'            =>  __( 'Archives' ),
					'slug'            => 'archives',
					'parent_url'      => $people_link,
					'parent_slug'     =>  $this->id,
					'screen_function' => array( $rtbp_pm_screen, 'bp_pm_projects' ),
					'position'        => 10,
				);
			}

			// Resources
			if( ! isset($_GET['rt_project_id']) ){

				$sub_nav[] = array(
					'name'            =>  __( 'Resources' ),
					'slug'            => 'resources',
					'parent_url'      => $people_link,
					'parent_slug'     =>  $this->id,
					'screen_function' => array( $rtbp_pm_screen, 'bp_pm_projects' ),
					'position'        => 10,
				);

				$sub_nav[] = array(
					'name'            =>  __( 'Overview' ),
					'slug'            => 'overview',
					'parent_url'      => $people_link,
					'parent_slug'     =>  $this->id,
					'screen_function' => array( $rtbp_pm_screen, 'rtpm_project_overview_screen' ),
					'position'        => 10,
				);
			}

			$project_detail_actions = array('details', 'attachments', 'time-entries', 'tasks', 'notifications', self::$gantt_admin, self::$ganttchart_slug );

			if ( isset($_GET['rt_project_id']) && in_array( bp_current_action(), $project_detail_actions ) ){
				
				$main_url = trailingslashit( $user_domain . $this->slug .'/details');
                $url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-details'  ), $main_url ) );
                $sub_nav[] = array(
                    'name'            =>  __( 'Details' ),
                    'slug'            => 'details',
                    'link'			  => $url,
                    'parent_url'      => $people_link,
                    'parent_slug'     =>  $this->id,
                    'screen_function' => array( $rtbp_pm_screen, 'bp_pm_details' ),
                    'position'        => 20,
                );

				
				$main_url = trailingslashit( $user_domain . $this->slug .'/attachments');
                $url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-files'  ), $main_url ) );
                $sub_nav[] = array(
                    'name'            =>  __( 'Attachments' ),
                    'slug'            => 'attachments',
                    'link'			  => $url,
                    'parent_url'      => $people_link,
                    'parent_slug'     =>  $this->id,
                    'screen_function' => array( $rtbp_pm_screen, 'bp_pm_attachments' ),
                    'position'        => 30,
                );

				$main_url = trailingslashit( $user_domain . $this->slug .'/tasks');
                $url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-task'  ), $main_url ) );
                $sub_nav[] = array(
                    'name'            =>  __( 'Tasks' ),
                    'slug'            => 'tasks',
                    'link'			  => $url,
                    'parent_url'      => $people_link,
                    'parent_slug'     =>  $this->id,
                    'screen_function' => array( $rtbp_pm_screen, 'bp_pm_tasks' ),
                    'position'        => 40,
                );

				
				$main_url = trailingslashit( $user_domain . $this->slug .'/time-entries');
                $url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-timeentry'  ), $main_url ) );
                $sub_nav[] = array(
                    'name'            =>  __( 'Time Entries' ),
                    'slug'            => 'time-entries',
                    'link'			  => $url,
                    'parent_url'      =>  $people_link,
                    'parent_slug'     =>  $this->id,
                    'screen_function' => array( $rtbp_pm_screen, 'bp_pm_time_entries' ),
                    'position'        => 50,
                );

				
				$main_url = trailingslashit( $user_domain . $this->slug .'/notifications');
                $url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-notification'  ), $main_url ) );
                $sub_nav[] = array(
                    'name'            =>  __( 'Notifications' ),
                    'slug'            => 'notifications',
                    'link'			  => $url,
                    'parent_url'      => $people_link,
                    'parent_slug'     =>  $this->id,
                    'screen_function' => array( $rtbp_pm_screen, 'bp_pm_notifications' ),
                    'position'        => 60,
                );

                $main_url = trailingslashit( $user_domain . $this->slug .'/'.self::$gantt_admin);
                $url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-'.self::$gantt_admin  ), $main_url ) );
                $sub_nav[] = array(
                    'name'            =>  __( 'Gantt admin' ),
                    'slug'            => self::$gantt_admin,
                    'link'			  => $url,
                    'parent_url'      => $people_link,
                    'parent_slug'     =>  $this->id,
                    'screen_function' => array( $rtbp_pm_screen, 'bp_pm_gantt' ),
                    'position'        => 70,
                );

				$main_url = trailingslashit( $user_domain . $this->slug .'/'.self::$ganttchart_slug);
				$url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-'.self::$ganttchart_slug  ), $main_url ) );
                $sub_nav[] = array(
                    'name'            =>  __( 'GanttChart' ),
                    'slug'            => self::$ganttchart_slug,
                    'link'			  => $url,
                    'parent_url'      => $people_link,
                    'parent_slug'     =>  $this->id,
                    'screen_function' => array( $rtbp_pm_screen, 'bp_pm_ganttchart' ),
                    'position'        => 80,
                );

			}

			parent::setup_nav( $main_nav, $sub_nav );

		}

		public function setup_admin_bar( $wp_admin_nav = array() ) {

            if( is_main_site() )
                return;

				// The instance
				$bp = buddypress();
		
				// Menus for logged in user
				if ( is_user_logged_in() ) {
		
					// Setup the logged in user variables
					$user_domain   = bp_loggedin_user_domain();
					$crm_link = trailingslashit( $user_domain . $this->slug );
					
					$this->sub_nav_items = array(
		                array(
		                    'name' => __( 'Projects' ),
		                    'slug'  => 'projects',
		                ),
		                array(
		                    'name' =>  'Archives',
		                    'slug'  => 'archives',
		                ),
						array(
		                    'name' =>  'Resources',
		                    'slug'  => 'resources',
		                ),
						array(
		                    'name' =>  'Overview',
		                    'slug'  => 'overview',
		                )
		            );
		
					// Add main Settings menu
					$wp_admin_nav[] = array(
						'parent' => $bp->my_account_menu_id,
						'id'     => 'my-account-' . $this->id,
						'title'  => __( $this->pm_label, 'buddypress' ),
						'href'   => trailingslashit( $crm_link )
					);
		
					
					foreach ($this->sub_nav_items as $item) {
						// Add a few subnav items
						$wp_admin_nav[] = array(
							'parent' => 'my-account-' . $this->id,
							'id'     => 'my-account-' . $this->id . '-'.$item['slug'],
							'title'  => __( $item['name'], 'buddypress' ),
							'href'   => trailingslashit( $crm_link . $item['slug'] )
						);
					}
		
					
				}
		
				parent::setup_admin_bar( $wp_admin_nav );
			
		}
		

	
	}
}