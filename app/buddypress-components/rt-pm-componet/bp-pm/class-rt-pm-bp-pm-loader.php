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
                public function __construct() {
                    
                        parent::start(
                                'pm',
                                __( 'PM', 'buddypress' ),
                               RT_PM_BP_PM_PATH,
                                array(
                                        'adminbar_myaccount_order' => 9998
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
            	
			// Determine user to use -- only
			if ( bp_loggedin_user_id() !== bp_displayed_user_id() ) {
				return;
			}

			// Add 'hrm' to the main navigation
			$main_nav = array(
				'name' 		      => __( 'PM' ),
				'slug' 		      => $this->id .'/projects',
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


			// Add the subnav items
			$sub_nav[] = array(
				'name'            =>  __( 'Projects' ),
				'slug'            => 'projects',
				'parent_url'      => $people_link,
				'parent_slug'     =>  $this->id,
				'screen_function' => 'bp_pm_projects',
				'position'        => 10,
			);
			
			// Add the subnav items
			$sub_nav[] = array(
				'name'            =>  __( 'Add New' ),
				'slug'            => 'addnew',
				'parent_url'      => $people_link,
				'parent_slug'     =>  $this->id,
				'screen_function' => 'bp_pm_projects_new',
				'position'        => 10,
			);
			
			// Add the subnav items
			$sub_nav[] = array(
				'name'            =>  __( 'Archives' ),
				'slug'            => 'archives',
				'parent_url'      => $people_link,
				'parent_slug'     =>  $this->id,
				'screen_function' => 'bp_pm_archives',
				'position'        => 10,
			);

			// Add a few subnav items
			// $sub_nav[] = array(
				// 'name'            =>  __( 'Details' ),
				// 'slug'            => 'details',
				// 'parent_url'      => $people_link,
				// 'parent_slug'     =>  $this->id,
				// 'screen_function' => 'bp_pm_details',
				// 'position'        => 20,
			// );
                        
			// Add a few subnav items
			// $sub_nav[] = array(
				// 'name'            =>  __( 'Attachments' ),
				// 'slug'            => 'attachments',
				// 'parent_url'      => $people_link,
				// 'parent_slug'     =>  $this->id,
				// 'screen_function' => 'bp_pm_attachments',
				// 'position'        => 30,
			// );
			
			// Add a few subnav items
			// $sub_nav[] = array(
				// 'name'            =>  __( 'Tasks' ),
				// 'slug'            => 'tasks',
				// 'parent_url'      => $people_link,
				// 'parent_slug'     =>  $this->id,
				// 'screen_function' => 'bp_pm_tasks',
				// 'position'        => 40,
			// );

			parent::setup_nav( $main_nav, $sub_nav );

		}

	
	}
}