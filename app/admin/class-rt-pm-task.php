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
if ( ! class_exists( 'Rt_PM_Task' ) ) {

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
            add_action( "save_task", array( $this, 'task_add_bp_activity' ), 10, 2 );
            add_action( 'wp_ajax_rtpm_get_task', array( $this, 'get_autocomplate_task' ) );
			add_filter( 'posts_where', array( $this, 'rtcrm_generate_task_sql'), 10, 2 );
		}

        function task_add_bp_activity( $post_id, $operation_type ) {

            $post_action = 0;


            $query = new WP_Query( array(
                'p' => $post_id,
                'post_type' => $this->post_type,
                'no_found_rows' => true,
            ));

            $post = $query->posts[0];

            if( $operation_type == 'update' ) {

                $action = 'Task updated';
            }else if( $operation_type == 'insert' ) {

                $action = 'Task created';
            }

            $activity_users = array();

            $activity_users[] =  get_post_meta( $post->ID, "post_assignee", true );

            $parent_project_id =  get_post_meta( $post->ID, "post_project_id", true );

            $activity_users[] =  get_post_meta( $parent_project_id, "project_manager", true);

            $mentioned_user = '';
            foreach ( $activity_users as $activity_user ) {

                if( get_current_user_id() != intval( $activity_user ) ){

                    $mentioned_user .=  '@' . bp_core_get_username( $activity_user ).' ';
                }

            }

            $args = array(
                'action'=> $action,
                'content' =>  !empty( $post->post_content ) ? $post->post_content.$mentioned_user : $post->post_title.$mentioned_user,
                'component' => 'rt_biz',
                'item_id' => $post->ID,
                'secondary_item_id' => get_current_blog_id(),
                'type' =>  $this->post_type,
            );
            $activity_id = bp_activity_add( $args );

            bp_activity_add_meta( $activity_id ,'activity_users', $activity_users );

        }

		function init_task() {
			$menu_position = 31;
			$this->register_custom_post( $menu_position );
			$this->register_custom_statuses();

			$settings = rtpm_get_settings();
			if ( isset( $settings[ 'attach_contacts' ] ) && $settings[ 'attach_contacts' ] == 'yes' ) {
				rt_biz_register_person_connection( $this->post_type, $this->labels[ 'name' ] );
			}
			if ( isset( $settings[ 'attach_accounts' ] ) && $settings[ 'attach_accounts' ] == 'yes' ) {
				rt_biz_register_organization_connection( $this->post_type, $this->labels[ 'name' ] );
			}
		}

		function register_custom_post( $menu_position ) {
			$logo_url = Rt_PM_Settings::$settings[ 'logo_url' ];

			if ( empty( $pm_logo_url ) ) {
				$pm_logo_url = RT_PM_URL . 'app/assets/img/pm-16X16.png';
			}

			$args = array(
				'labels' => $this->labels,
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => false, // Show the UI in admin panel
				'menu_icon' => $logo_url,
				'menu_position' => $menu_position,
				'supports' => array( 'title', 'editor', 'comments', 'custom-fields' ),
				'capability_type' => $this->post_type,
			);
			register_post_type( $this->post_type, $args );
		}

		function footer_scripts() {
			?>
			<script>postboxes.add_postbox_toggles(pagenow);</script>
		<?php

		}

		function register_custom_statuses() {
			foreach ( $this->statuses as $status ) {

				register_post_status( $status[ 'slug' ], array(
					'label' => $status[ 'slug' ]
					, 'public' => true
					, '_builtin' => false
					, 'label_count' => _n_noop( "{$status[ 'name' ]} <span class='count'>(%s)</span>", "{$status[ 'name' ]} <span class='count'>(%s)</span>" ),
				) );
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
                
                                
		function search( $query, $args = array() ) {
                   
			$query_args = array(
				'post_type' => $this->post_type,
				'post_status' => 'any',
				'posts_per_page' => -1,
				's' => $query,
			);
			$args = array_merge( $query_args, $args );
			$entity = new WP_Query( $args );

			return $entity->posts;
		}
                
		function get_autocomplate_task() {
			global $rt_pm_bp_pm;

			if (!isset($_POST["query"])) {
				wp_die("Opss!! Invalid request");
			}

			$tasks = $this->search( $_POST['query'] );
			$result = array();

			foreach ( $tasks as $task ) {
				$project_id = get_post_meta( $task->ID, 'post_project_id', true );

				$project = get_post( $project_id );

				$url = add_query_arg( array( 'post_type' => $project->post_type, 'rt_project_id' => $project->ID, 'tab' => 'rt_project-task', 'rt_task_id' => $task->ID ), $rt_pm_bp_pm->get_component_root_url().  RT_PM_Bp_PM_Loader::$projects_slug );

				$result[] = array(
					'label' => $task->post_title,
					'id' => $task->ID,
					'slug' => $task->post_name,
					'url' => $url,
				);
			}

			echo json_encode($result);
			die(0);
                    
		}


		/**
		 * The days listed will not have work assigned to them and will have a greyed out background.
		 * @param $project_id
		 */
		public function disable_working_days( $project_id ) {

            $project_working_days = get_post_meta( $project_id, 'working_days' , true);

            $days = array();
            $occasions = array();

            if( isset( $project_working_days['days'] ) )
				$days = $project_working_days['days'];

            if( isset( $project_working_days['occasions'] )  )
				$occasions = array_column( $project_working_days['occasions'], 'date' );

            ?>
				<script>
					// Disable working days and working hours
					jQuery(document).ready(function($) {

						var days_array =[<?php echo implode( ',', $days );?>];
						var occasion_array = [<?php echo '"'.implode( '","', $occasions ).'"' ?>];

						if ($(".datetimepicker").length > 0) {
							$('.datetimepicker').datetimepicker({
								dateFormat: "M d, yy",
								timeFormat: "hh:mm TT",
								beforeShowDay: function (date) {

									var day = date.getDay();
									if(  $.inArray( day, days_array ) !== -1  ){
										return [false];
									}

									var string = jQuery.datepicker.formatDate('dd/mm/yy', date);
									if($.inArray( string, occasion_array) !== -1 ){
										return [false];
									}

									return [true];
								}
							}).attr('readonly','readonly'); ;
						}
					});
				</script>
		<?php
		}

		/**
		 * Return task post data
		 * @param int $author_id
		 * @param string $task_status
		 * @param null $date_query
		 * @return array
		 */
		public function rtpm_get_task_data( $author_id = 0, $task_status = 'any', $date_query = null ){


			global $rt_crm_module, $wpdb;

			$args = array(
				'nopaging' => true,
				'post_status' => array( $task_status ),
				'post_type' => $this->post_type,
				'no_found_rows' => true,
				'meta_key' => 'post_duedate',
			);

			if( $author_id !== 0 )
				$args['author'] = $author_id;

			$query = new WP_Query( $args );

			return $query->posts;
		}

		/**
		 *	Post where clues filter for task due date
		 * @param $where
		 * @param $wp_query
		 * @return string
		 */
		public function rtcrm_generate_task_sql( $where, &$wp_query ) {
			global $wp_query, $wpdb, $rtbp_todo, $bp;

			$author_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'author' );

			if( current_user_can( $author_cap ) && bp_is_current_component( $bp->profile->slug ) && bp_is_current_action( Rt_Bp_People_Loader:: $profile_todo_slug ) && false !== strpos( $where, 'rt_task')  && false !== strpos( $where, 'post_duedate') ) {

				$period = isset( $_REQUEST['period'] ) ? $_REQUEST['period'] : 'today';

				$date_query = $rtbp_todo->rtbiz_prepare_date_query( $period );

				$task_wp_date_query = new WP_Date_Query( $date_query  );
				$date_sql = $task_wp_date_query->get_sql();



				$where .= str_replace( $wpdb->posts.".post_date",  " STR_TO_DATE( ". $wpdb->postmeta.".meta_value, '%Y-%m-%d %H:%i') ", $date_sql );
			}

			return $where;
		}

		/**
		 * Return count on overdue task
		 * @param $project_id
		 * @return mixed
		 */
		public function rtpm_overdue_task_count( $project_id ) {
			global $wpdb;

			$task_ids = $this->rtpm_get_projects_task_ids( $project_id );

			$format = implode( ', ', $task_ids );

			$query = "SELECT COUNT(meta_id) FROM $wpdb->postmeta WHERE post_id IN($format) AND meta_key = 'post_duedate' AND STR_TO_DATE( meta_value, '%Y-%m-%d %H:%i' ) < NOW()";

			$overdue_task = $wpdb->get_var( $query );

			return $overdue_task;
		}

		/**
		 * Return open task post count
		 * @param $project_id
		 * @return int
		 */
		public function rtpm_open_task_count( $project_id ) {

			$statues_slug = array_column( $this->statuses, 'slug', 'slug' );

			unset( $statues_slug['completed'] );

			$args = array(
				'nopaging' => true,
				'post_type' => $this->post_type,
				'fields' => 'ids',
				'no_found_rows' => true,
				'meta_key' => 'post_project_id',
				'meta_value' => $project_id,
				'post_status' => $statues_slug,
			);

			$query = new WP_Query( $args );

			return $query->post_count;
		}

		public function rtpm_completed_task_count( $project_id ) {

			$args = array(
				'nopaging' => true,
				'post_type' => $this->post_type,
				'fields' => 'ids',
				'no_found_rows' => true,
				'meta_key' => 'post_project_id',
				'meta_value' => $project_id,
				'post_status' => 'completed'
			);

			$query = new WP_Query( $args );

			return $query->post_count;
		}

		/**
		 * Return all task ids for project
		 * @param $project_id
		 * @return WP_Query
		 */
		public function rtpm_get_projects_task_ids( $project_id ) {

			$args = array(
				'nopaging' => true,
				'post_type' => $this->post_type,
				'meta_key' => 'post_project_id',
				'meta_value' => $project_id,
				'fields' => 'ids',
				'no_found_rows' => true,
			);

			$query = new WP_Query( $args );

			return $query->posts;

		}

		public function rtpm_task_chart_date_range( $project_id ) {
			global $wpdb;

			$task_ids = $this->rtpm_get_projects_task_ids( $project_id );

			$format = implode( ', ', $task_ids );

			$query = "SELECT MIN( STR_TO_DATE( meta_value, '%Y-%m-%d %H:%i' ) ) AS start_date, MAX( STR_TO_DATE( meta_value, '%Y-%m-%d %H:%i' ) ) AS end_date  FROM $wpdb->postmeta WHERE post_id IN($format) AND meta_key = 'post_duedate'";

			var_dump( $query );

			$result = $wpdb->get_row( $query );

			var_dump( $result );
		}

	}

}