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
                public function __construct() {
                    
                        parent::start(
                                'pm',
                                __( 'PM', 'buddypress' ),
                               RT_PM_BP_PM_PATH,
                                array(
                                        'adminbar_myaccount_order' => 9999
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
			global $rt_pm_bp_pm;
            	
			// Determine user to use -- only
			if ( bp_loggedin_user_id() !== bp_displayed_user_id() ) {
				return;
			}
			
            $nav_name = __( 'PM', 'buddypress' );

			// Add 'hrm' to the main navigation
			$main_nav = array(
				'name' 		      => __( 'PM' ),
				'slug' 		      => $this->id,
				'position' 	      => 80,
				'screen_function'     => 'bp_pm_projects',
				'default_subnav_slug' => 'projects',
			);

            // Determine user to use
            if ( bp_displayed_user_domain() ) {
                    $user_domain = bp_displayed_user_domain();
            } elseif ( bp_loggedin_user_domain() ) {
                    $user_domain = bp_loggedin_user_domain();
            } else {
                    return;
            }

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
					'screen_function' => 'bp_pm_projects',
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
					'screen_function' => 'bp_pm_archives',
					'position'        => 10,
				);
			}

			
			if ( isset($_GET['rt_project_id']) || bp_is_current_action( 'details' ) || bp_is_current_action( 'attachments' ) 
			|| bp_is_current_action( 'time-entries' ) || bp_is_current_action( 'tasks' )
			|| bp_is_current_action( 'notifications' )){
				
				$main_url = trailingslashit( $user_domain . $this->slug .'/details');
				
				$url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-details'  ), $main_url ) );
				
				// Add a few subnav items
				$sub_nav[] = array(
					'name'            =>  __( 'Details' ),
					'slug'            => 'details',
					'link'			  => $url,
					'parent_url'      => $people_link,
					'parent_slug'     =>  $this->id,
					'screen_function' => 'bp_pm_details',
					'position'        => 20,
				);
				
				$main_url = trailingslashit( $user_domain . $this->slug .'/attachments');
				
				$url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-files'  ), $main_url ) );
				
			
				// Add a few subnav items
				$sub_nav[] = array(
					'name'            =>  __( 'Attachments' ),
					'slug'            => 'attachments',
					'link'			  => $url,
					'parent_url'      => $people_link,
					'parent_slug'     =>  $this->id,
					'screen_function' => 'bp_pm_attachments',
					'position'        => 30,
				);
				
				$main_url = trailingslashit( $user_domain . $this->slug .'/time-entries');
				
				$url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-timeentry'  ), $main_url ) );
				
				
				// Add a few subnav items
				$sub_nav[] = array(
					'name'            =>  __( 'Time Entries' ),
					'slug'            => 'time-entries',
					'link'			  => $url,
					'parent_url'      =>  $people_link,
					'parent_slug'     =>  $this->id,
					'screen_function' => 'bp_pm_time_entries',
					'position'        => 40,
				);
				
				$main_url = trailingslashit( $user_domain . $this->slug .'/tasks');
				
				$url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-task'  ), $main_url ) );
				
				// Add a few subnav items
				$sub_nav[] = array(
					'name'            =>  __( 'Tasks' ),
					'slug'            => 'tasks',
					'link'			  => $url,
					'parent_url'      => $people_link,
					'parent_slug'     =>  $this->id,
					'screen_function' => 'bp_pm_tasks',
					'position'        => 50,
				);
				
				$main_url = trailingslashit( $user_domain . $this->slug .'/notifications');
				
				$url = esc_url( add_query_arg( array( 'post_type' => 'rt_project' ,'rt_project_id' => $_GET['rt_project_id'], 'tab' => 'rt_project-notification'  ), $main_url ) );
				// Add a few subnav items
				$sub_nav[] = array(
					'name'            =>  __( 'Notifications' ),
					'slug'            => 'notifications',
					'link'			  => $url,
					'parent_url'      => $people_link,
					'parent_slug'     =>  $this->id,
					'screen_function' => 'bp_pm_notifications',
					'position'        => 60,
				);
			}

			parent::setup_nav( $main_nav, $sub_nav );

		}

		public function setup_admin_bar( $wp_admin_nav = array() ) {
                   
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
		                    'screen_function' => 'bp_pm_projects',
		                ),
		                array(
		                    'name' =>  'Archives',
		                    'slug'  => 'archives',
		                    'screen_function' => 'bp_pm_archives',
		                )               
		            );
		
					// Add main Settings menu
					$wp_admin_nav[] = array(
						'parent' => $bp->my_account_menu_id,
						'id'     => 'my-account-' . $this->id,
						'title'  => __( 'PM', 'buddypress' ),
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