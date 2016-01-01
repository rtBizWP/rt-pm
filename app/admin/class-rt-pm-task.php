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
			$this->setup();
		}

		private function setup() {

			add_action( 'init', array( $this, 'init_task' ) );
			add_action( "rtpm_after_save_task", array( $this, 'task_add_bp_activity' ), 10, 3 );

			add_action( 'wp_ajax_rtpm_get_task', array( $this, 'get_autocomplate_task' ) );
			add_filter( 'posts_where', array( $this, 'rtcrm_generate_task_sql' ), 10, 2 );
			add_action( 'init', array( $this, 'rtpm_save_task' ) );
			add_action( 'init', array( $this, 'rtpm_task_actions' ) );
			add_action( 'wp_ajax_rtpm_get_user_tasks', array( $this, 'rtpm_get_user_tasks' ) );
			add_action( 'wp_ajax_rtpm_save_project_task', array( $this, 'rtpm_save_project_task' ) );
			add_action( 'wp_ajax_rtpm_delete_project_task', array( $this, 'rtpm_delete_project_task' ) );
			add_action( 'wp_ajax_rtpm_save_project_task_link', array( $this, 'rtpm_save_project_task_link' ) );
			add_action( 'wp_ajax_rtpm_delete_project_task_link', array( $this, 'rtpm_delete_project_task_link' ) );
			add_action( 'wp_ajax_rtpm_get_task_data_for_ganttchart', array( $this, 'rtpm_get_task_data_for_ganttchart' ) );

			add_filter( 'rtpm_insert_task_meta', array( $this, 'rtpm_set_task_parent_and_child_task_count'), 10, 1 );
			add_action( 'rtpm_after_save_task', array( $this, 'rtpm_after_save_task' ), 10, 2 );

			add_action( 'rtpm_before_trash_task', array( $this, 'rtpm_before_trash_task' ), 10, 1 );
			add_action( 'rtpm_after_trash_task', array( $this, 'rtpm_after_trash_task' ), 10, 1 );
			add_action( 'rtpm_before_untrash_task', array( $this, 'rtpm_before_untrash_task' ), 10, 1 );
			add_action( 'rtpm_after_untrash_task', array( $this, 'rtpm_after_untrash_task' ), 10, 1 );
			add_action( 'rtpm_after_delete_task', array( $this, 'rtpm_after_delete_task' ), 10, 1 );

			add_action( 'wp_ajax_rtpm_import_task_json', array( $this, 'rtpm_import_task_json' ) );
		}

		function task_add_bp_activity( $post_id, $update ) {
			global $rt_pm_task_resources_model;

			if( ! function_exists( 'bp_is_active') || bp_is_current_component( BP_CRM_SLUG ) )
				return false;

			$args = array(
				'p' => $post_id,
			);

			$posts = $this->rtpm_get_task_data( $args );

			$post = $posts[0];

			if ( $update ) {

				$action = 'Task updated';
			} else {

				$action = 'Task created';
			}

			//$activity_users = array();

			//$activity_users[] = get_post_meta( $post->ID, "post_assignee", true );

			//$parent_project_id = get_post_meta( $post->ID, "post_project_id", true );

			//$activity_users[] = get_post_meta( $parent_project_id, "project_manager", true );

			$activity_users = $rt_pm_task_resources_model->rtpm_get_task_resources( $post_id );

			$mentioned_user = '';
			foreach ( $activity_users as $activity_user ) {

				if ( get_current_user_id() != intval( $activity_user ) ) {

					$mentioned_user .= '@' . bp_core_get_username( $activity_user ) . ' ';
				}

			}

			$args        = array(
				'action'            => $action,
				'content'           => ! empty( $post->post_content ) ? $post->post_content . $mentioned_user : $post->post_title . $mentioned_user,
				'component'         => 'rt_biz',
				'item_id'           => $post->ID,
				'secondary_item_id' => get_current_blog_id(),
				'type'              => $this->post_type,
			);
			$activity_id = bp_activity_add( $args );

			bp_activity_add_meta( $activity_id, 'activity_users', $activity_users );

		}

		function init_task() {
			$menu_position = 31;
			$this->register_custom_post( $menu_position );
			$this->register_custom_statuses();

			$settings = rtpm_get_settings();
			if ( isset( $settings['attach_contacts'] ) && $settings['attach_contacts'] == 'yes' ) {
				rt_biz_register_person_connection( $this->post_type, $this->labels['name'] );
			}
			if ( isset( $settings['attach_accounts'] ) && $settings['attach_accounts'] == 'yes' ) {
				rt_biz_register_organization_connection( $this->post_type, $this->labels['name'] );
			}
		}

		function register_custom_post( $menu_position ) {
			$logo_url = Rt_PM_Settings::$settings['logo_url'];

			if ( empty( $pm_logo_url ) ) {
				$pm_logo_url = RT_PM_URL . 'app/assets/img/pm-16X16.png';
			}

			$args = array(
				'labels'             => $this->labels,
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false, // Show the UI in admin panel
				'menu_icon'          => $logo_url,
				'menu_position'      => $menu_position,
				'supports'           => array( 'title', 'editor', 'comments', 'custom-fields' ),
				'map_meta_cap' => true,
				'capabilities'      => array(
					'edit_post'              => "projects_edit_task",
					'read_post'              => "projects_read_task",
					'delete_post'            => "projects_delete_task",
					'edit_posts'             => "projects_edit_tasks",
					'edit_others_posts'      => "projects_edit_others_tasks",
					'publish_posts'          => "projects_publish_tasks",
					'read_private_posts'     => "projects_read_private_tasks",
					'read'                   => "projects_read_tasks",
					'delete_posts'           => "projects_delete_tasks",
					'delete_private_posts'   => "projects_delete_private_tasks",
					'delete_published_posts' => "projects_delete_published_tasks",
					'delete_others_posts'    => "projects_delete_others_tasks",
					'edit_private_posts'     => "projects_edit_private_tasks",
					'edit_published_posts'   => "projects_edit_published_tasks",
					'create_posts'           => "projects_create_tasks"
				)
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

				register_post_status( $status['slug'], array(
					'label'       => $status['slug']
				,
					'public'      => true
				,
					'_builtin'    => false
				,
					'label_count' => _n_noop( "{$status['name']} <span class='count'>(%s)</span>", "{$status['name']} <span class='count'>(%s)</span>" ),
				) );
			}
		}

		function get_custom_labels() {
			$this->labels = array(
				'name'          => __( 'Task' ),
				'singular_name' => __( 'Task' ),
				'menu_name'     => __( 'PM-Task' ),
				'all_items'     => __( 'Tasks' ),
				'add_new'       => __( 'Add Task' ),
				'add_new_item'  => __( 'Add Task' ),
				'new_item'      => __( 'Add Task' ),
				'edit_item'     => __( 'Edit Task' ),
				'view_item'     => __( 'View Task' ),
				'search_items'  => __( 'Search Tasks' ),
			);

			return $this->labels;
		}

		function get_custom_statuses() {
			$this->statuses = array(
				array(
					'slug'        => 'new',
					'name'        => __( 'New' ),
					'description' => __( 'New Task is Created' ),
				),
				array(
					'slug'        => 'assigned',
					'name'        => __( 'Assigned' ),
					'description' => __( 'Task is assigned' ),
				),
				array(
					'slug'        => 'inprogress',
					'name'        => __( 'Inprogress' ),
					'description' => __( 'Task is Inprogress' ),
				),
				array(
					'slug'        => 'ask_client',
					'name'        => __( 'Ask-Client' ),
					'description' => __( 'Task is for client' ),
				),
				array(
					'slug'        => 'confirmed',
					'name'        => __( 'Confirmed' ),
					'description' => __( 'Task is Confirmed' ),
				),
				array(
					'slug'        => 'duplicate',
					'name'        => __( 'Duplicate' ),
					'description' => __( 'Task is Duplicate' ),
				),
				array(
					'slug'        => 'blocked',
					'name'        => __( 'Blocked' ),
					'description' => __( 'Task is Blocked' ),
				),
				array(
					'slug'        => 'fixed',
					'name'        => __( 'Fixed' ),
					'description' => __( 'Task is Fixed' ),
				),
				array(
					'slug'        => 'reopened',
					'name'        => __( 'Reopened' ),
					'description' => __( 'Task is Reopened' ),
				),
				array(
					'slug'        => 'verified',
					'name'        => __( 'Verified' ),
					'description' => __( 'Task is verified' ),
				),
				array(
					'slug'        => 'completed',
					'name'        => __( 'Completed' ),
					'description' => __( 'Task is completed' ),
				),
			);

			return $this->statuses;
		}


		function search( $query, $args = array() ) {

			$query_args = array(
				'post_type'      => $this->post_type,
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				's'              => $query,
			);
			$args       = array_merge( $query_args, $args );
			$entity     = new WP_Query( $args );

			return $entity->posts;
		}

		function get_autocomplate_task() {
			global $rt_pm_bp_pm;

			if ( ! isset( $_POST["query"] ) ) {
				wp_die( "Opss!! Invalid request" );
			}

			$tasks  = $this->search( $_POST['query'] );
			$result = array();

			foreach ( $tasks as $task ) {
				$project_id = get_post_meta( $task->ID, 'post_project_id', true );

				$project = get_post( $project_id );

				$url = add_query_arg( array(
					'post_type'     => $project->post_type,
					'rt_project_id' => $project->ID,
					'tab'           => 'rt_project-task',
					'rt_task_id'    => $task->ID
				), $rt_pm_bp_pm->get_component_root_url() . RT_PM_Bp_PM_Loader::$projects_slug );

				$result[] = array(
					'label' => $task->post_title,
					'id'    => $task->ID,
					'slug'  => $task->post_name,
					'url'   => $url,
				);
			}

			echo json_encode( $result );
			die( 0 );

		}


		/**
		 * The days listed will not have work assigned to them and will have a greyed out background.
		 *
		 * @param $project_id
		 */
		public function disable_working_days( $project_id ) {
			$result = $this->rtpm_get_non_working_days( $project_id );
			?>
			<script>
				// Disable working days and working hours
				jQuery(document).ready(function ($) {

					var days_array = [<?php echo implode( ',', $result['days'] );?>];
					var occasion_array = [<?php echo '"'.implode( '","', $result['occasions'] ).'"' ?>];

					occasion_array = $.map( occasion_array, function( occasion ) {
						return occasion.substring( 0, 10 );
					});

					if ($(".datetimepicker").length > 0) {
						$('.datetimepicker').datetimepicker({
							dateFormat: "M d, yy",
							timeFormat: "hh:mm TT",
							beforeShowDay: function (date) {

								var day = date.getDay();
								if ($.inArray(day, days_array) !== -1) {
									return [false];
								}

								var string = jQuery.datepicker.formatDate( 'yy-mm-dd', date );
								if ($.inArray( string, occasion_array) !== -1) {
									return [false];
								}

								return [true];
							}
						}).attr('readonly', 'readonly');
						;
					}
				});
			</script>
		<?php
		}

		/**
		 * Return a non working days of project
		 * @param $project_id
		 *
		 * @return array4
		 */
		public function rtpm_get_non_working_days( $project_id ) {

			$project_working_days = get_post_meta( $project_id, 'working_days', true );

			$days      = array();
			$occasions = array();

			if ( isset( $project_working_days['days'] ) ) {
				$days = $project_working_days['days'];
			}

			if ( isset( $project_working_days['occasions'] ) ) {
				$occasions = array_column( $project_working_days['occasions'], 'date' );
			}

			$result = array(
				'days' => $days,
				'occasions' => $occasions,
			);

			return $result;
		}


		/**
		 * @param array $args
		 *
		 * @return array
		 */
		public function rtpm_get_task_data( $args = array() ) {

			$query = $this->rtpm_prepare_task_wp_query( $args );

			return $query->posts;
		}

		/**
		 * Return wp_query object of get task data
		 * @param array $args
		 *
		 * @return WP_Query
		 */
		public function rtpm_prepare_task_wp_query( $args = array() ) {

			$args['post_type'] = $this->post_type;

			$query = new WP_Query( $args );

			return $query;
		}

		/**
		 *   Post where clues filter for task due date
		 *
		 * @param $where
		 * @param $wp_query
		 *
		 * @return string
		 */
		public function rtcrm_generate_task_sql( $where, &$wp_query ) {
			global $wp_query, $wpdb, $rtbp_todo, $bp;

			if ( function_exists( 'bp_is_active' ) &&
				 current_user_can( 'projects_edit_projects' ) &&
			     bp_is_current_component( $bp->profile->slug ) &&
			     bp_is_current_action( Rt_Bp_People_Loader:: $profile_todo_slug ) &&
			     false !== strpos( $where, 'rt_task' ) && false !== strpos( $where, 'post_duedate' ) ) {

				$period = isset( $_REQUEST['period'] ) ? $_REQUEST['period'] : 'today';

				$date_query = $rtbp_todo->rtbiz_prepare_date_query( $period );

				$task_wp_date_query = new WP_Date_Query( $date_query );
				$date_sql           = $task_wp_date_query->get_sql();


				$where .= str_replace( $wpdb->posts . ".post_date", " STR_TO_DATE( " . $wpdb->postmeta . ".meta_value, '%Y-%m-%d %H:%i') ", $date_sql );
			}

			return $where;
		}

		/**
		 * Return count on overdue task
		 *
		 * @param $project_id
		 *
		 * @return mixed
		 */
		public function rtpm_overdue_task_count( $project_id ) {
			global $wpdb;

			$task_ids = $this->rtpm_get_timeentries_tasks( $project_id );

			if ( empty( $task_ids ) ) {
				return 0;
			}

			$format = implode( ', ', $task_ids );

			$query = "SELECT COUNT(meta_id) FROM $wpdb->postmeta WHERE post_id IN($format) AND meta_key = 'post_duedate' AND STR_TO_DATE( meta_value, '%Y-%m-%d %H:%i' ) < NOW()";

			$overdue_task = $wpdb->get_var( $query );

			return $overdue_task;
		}

		/**
		 * Return open task post count
		 *
		 * @param $project_id
		 *
		 * @return int
		 */
		public function rtpm_open_task_count( $project_id, $date_query = null ) {

			$statues_slug = array_column( $this->statuses, 'slug', 'slug' );

			$task_ids = $this->rtpm_get_timeentries_tasks( $project_id );

			if ( empty( $task_ids ) ) {
				return 0;
			}

			unset( $statues_slug['completed'] );

			$args = array(
				'post__in' => $task_ids,
				'post_status'   => $statues_slug,
				'nopaging' =>   true,
				'fields' => 'ids',
				'no_found_rows' => true,
			);


			if ( null !== $date_query ) {
				$args['date_query'] = $date_query;
			}

			$query = $this->rtpm_prepare_task_wp_query( $args );

			return $query->post_count;
		}

		/**
		 * Return completed task count
		 *
		 * @param $project_id
		 * @param null $date_query
		 *
		 * @return int
		 */
		public function rtpm_completed_task_count( $project_id, $date_query = null ) {

			$task_ids = $this->rtpm_get_timeentries_tasks( $project_id );

			if ( empty( $task_ids ) ) {
				return 0;
			}

			$args = array(
				'post__in'  => $task_ids,
				'fields'   => 'ids',
				'post_status'   => 'completed',
				'nopaging' => true,
				'no_found_rows' => true,
			);

			if ( null !== $date_query ) {
				$args['date_query'] = $date_query;
			}

			$query = $this->rtpm_prepare_task_wp_query( $args );

			//echo $query->request;
			//die();

			return $query->post_count;
		}

		/**
		 * Return all task ids for project
		 *
		 * @param $project_id
		 *
		 * @return WP_Query
		 */
		public function rtpm_get_projects_task_ids( $project_id ) {

			$args = array(
				'nopaging'      => true,
				'post_type'     => $this->post_type,
				'meta_key'      => 'post_project_id',
				'meta_value'    => $project_id,
				'fields'        => 'ids',
				'no_found_rows' => true,
			);

			$query = new WP_Query( $args );

			return $query->posts;

		}

		/**
		 * @param $project_id
		 */
		public function rtpm_get_all_task_duedate( $project_id ) {
			global $wpdb;

			$tasks = $this->rtpm_get_timeentries_tasks( $project_id );

			if ( empty( $tasks ) ) {
				return;
			}

			$task_ids = implode( ',', $tasks );

			$query = "SELECT STR_TO_DATE( meta_value,'%Y-%m-%d') AS task_duedate FROM $wpdb->postmeta WHERE post_id IN( $task_ids ) AND meta_key = 'post_duedate' ORDER BY task_duedate";

			$result = $wpdb->get_col( $query );

			return $result;
		}

		/**
		 * Return completed task percentage
		 *
		 * @param $project_id
		 *
		 * @return float|int
		 */
		public function rtpm_get_completed_task_per( $project_id ) {

			$all_task_count = count( $this->rtpm_get_timeentries_tasks( $project_id ) );

			if ( $all_task_count <= 0 ) {
				return 0;
			}

			$completed_task_count = $this->rtpm_completed_task_count( $project_id );

			$completed_task_per = ( $completed_task_count * 100 ) / $all_task_count;

			return floor( $completed_task_per );
		}


		/**
		 * Return project id of task
		 *
		 * @param $task_id
		 *
		 * @return mixed
		 */
		public function rtpm_get_task_project_id( $task_id ) {

			return get_post_meta( $task_id, 'post_project_id', true );
		}


		/**
		 * Save or Add new Task
		 */
		public function rtpm_save_task() {
			global $rt_pm_project, $rt_pm_task, $rt_pm_project_resources;

			if ( ! isset( $_POST['rtpm_save_task_nonce'] ) || ! wp_verify_nonce( $_POST['rtpm_save_task_nonce'], 'rtpm_save_task' ) ) {
				return;
			}

			$task_post_type = $rt_pm_task->post_type;

			$newTask = $_POST['post'];

			//Switch to blog in MU site while editing from other site
			if ( isset( $newTask['rt_voxxi_blog_id'] ) ) {
				switch_to_blog( $newTask['rt_voxxi_blog_id'] );
			}

			$duedate = $newTask['post_duedate'];
			if ( isset( $duedate ) && $duedate != '' ) {
				try {
					$dr = date_create_from_format( 'M d, Y H:i A', $duedate );
					$newTask['post_duedate'] = $dr->format("Y-m-d H:i:s");
				} catch ( Exception $e ) {
					$newTask['post_duedate'] = current_time( 'mysql' );
				}
			}

			if( 'milestone' !== $newTask['task_type'] ) {
				$creationdate = $newTask['post_date'];
				if ( isset( $creationdate ) && $creationdate != '' ) {
					try {
						$dr  = date_create_from_format( 'M d, Y H:i A', $creationdate );
						$newTask['post_date']     = $dr->format("Y-m-d H:i:s");
						$newTask['post_date_gmt'] = $dr->format("Y-m-d H:i:s");
					} catch ( Exception $e ) {
						$newTask['post_date']     = current_time( 'mysql' );
						$newTask['post_date_gmt'] = gmdate( 'Y-m-d H:i:s' );
					}
				} else {
					$newTask['post_date']     = current_time( 'mysql' );
					$newTask['post_date_gmt'] = gmdate( 'Y-m-d H:i:s' );
				}
			} else {
				$newTask['post_date']     = $newTask['post_duedate'];
				$newTask['post_date_gmt'] = $newTask['post_duedate'];
			}

			// Post Data to be saved.
			$post = array(
				'post_content'  => $newTask['post_content'],
				'post_status'   => $newTask['post_status'],
				'post_title'    => $newTask['post_title'],
				'post_date'     => $newTask['post_date'],
				'post_date_gmt' => $newTask['post_date_gmt'],
				'post_type'     => $task_post_type,
				'post_parent'   => $newTask['post_project_id'],
			);

			$updateFlag = false;
			//check post request is for Update or insert
			if ( isset( $newTask['post_id'] ) ) {
				$updateFlag = true;
				$post       = array_merge( $post, array( 'ID' => $newTask['post_id'] ) );
				$data       = array(
					'post_duedate'         => $newTask['post_duedate'],
					'date_update'          => current_time( 'mysql' ),
					'date_update_gmt'      => gmdate( 'Y-m-d H:i:s' ),
					'user_updated_by'      => get_current_user_id(),
					'rtpm_parent_task'     => $newTask['parent_task'],
					'rtpm_task_type'       => $newTask['task_type'],
				);
				$post_id    = $this->rtpm_save_task_data( $post, $data );

			} else {
				$data    = array(
					'post_duedate'         => $newTask['post_duedate'],
					'date_update'          => current_time( 'mysql' ),
					'date_update_gmt'      => gmdate( 'Y-m-d H:i:s' ),
					'user_updated_by'      => get_current_user_id(),
					'user_created_by'      => get_current_user_id(),
					'rtpm_parent_task'     => $newTask['parent_task'],
					'rtpm_task_type'       => $newTask['task_type'],
				);
				$post_id = $this->rtpm_save_task_data( $post, $data );

				$_REQUEST["new"]    = true;
				$newTask['post_id'] = $post_id;

				//Save task resources
				$rt_pm_project_resources->rtpm_save_task_resources( $post_id, $newTask['post_project_id'], $newTask );
			}

			$rt_pm_project->connect_post_to_entity( $task_post_type, $newTask['post_project_id'], $post_id );

			// link post to user
			if ( ! empty( $newTask['post_assignee'] ) ) {

				$employee_id = rt_biz_get_person_for_wp_user( $newTask['post_assignee'] );
				// remove old data
				$rt_pm_project->remove_post_from_user( $task_post_type, $post_id );
				$rt_pm_project->connect_post_to_user( $task_post_type, $post_id, $employee_id[0]->ID );
			}


			// Attachments
			$args =  array(
				'post_parent'    => $newTask['post_id'],
				'post_type'      => 'attachment',
				'fields'         => 'ids',
				'posts_per_page' => - 1,
			);

			$query = new WP_Query( $args );
			$old_attachments = $query->posts;

			$new_attachments = array();
			if ( is_admin() ) {
				if ( isset( $_POST['attachment'] ) ) {
					$new_attachments = $_POST['attachment'];
					foreach ( $new_attachments as $attachment ) {
						if ( ! in_array( $attachment, $old_attachments ) ) {
							$file                   = get_post( $attachment );
							$filepath               = get_attached_file( $attachment );
							$post_attachment_hashes = get_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash' );
							if ( ! empty( $post_attachment_hashes ) && in_array( md5_file( $filepath ), $post_attachment_hashes ) ) {
								continue;
							}
							if ( ! empty( $file->post_parent ) ) {
								$args               = array(
									'post_mime_type' => $file->post_mime_type,
									'guid'           => $file->guid,
									'post_title'     => $file->post_title,
									'post_content'   => $file->post_content,
									'post_parent'    => $newTask['post_id'],
									'post_author'    => get_current_user_id(),
									'post_status'    => 'inherit'
								);
								$new_attachments_id = wp_insert_attachment( $args, $file->guid, $newTask['post_id'] );
								/*$new_attach_data=wp_generate_attachment_metadata($new_attachments_id,$filepath);
								wp_update_attachment_metadata( $new_attachments_id, $new_attach_data );*/
								add_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
							} else {
								wp_update_post( array( 'ID' => $attachment, 'post_parent' => $newTask['post_id'] ) );
								$filepath = get_attached_file( $attachment );
								add_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
							}
						}
					}

					foreach ( $old_attachments as $attachment ) {
						if ( ! in_array( $attachment, $new_attachments ) ) {
							wp_update_post( array( 'ID' => $attachment, 'post_parent' => '0' ) );
							$filepath = get_attached_file( $attachment );
							delete_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
						}
					}
				} else {
					foreach ( $old_attachments as $attachment ) {
						wp_update_post( array( 'ID' => $attachment, 'post_parent' => '0' ) );
						$filepath = get_attached_file( $attachment );
						delete_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
					}
					delete_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash' );
				}
			}


			//Add success message
			if ( ! is_admin() ) {

				bp_core_add_message( 'Task updated successfully', 'success' );

				if ( isset( $newTask['rt_voxxi_blog_id'] ) ) {
					restore_current_blog();
					add_action( 'wp_head', 'rt_voxxi_js_variables' );
				}
			}
		}

		/**
		 * Task actions:, trash, delete, restore
		 */
		public function rtpm_task_actions() {
			global $rt_pm_project, $rt_pm_bp_pm;

			if ( ! isset( $_REQUEST['post_type'] ) || $rt_pm_project->post_type !== $_REQUEST['post_type'] ) {
				return;
			}

			$task_post_type = $this->post_type;

			$post_type = $_REQUEST['post_type'];

			if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST[ $task_post_type . '_id' ] ) ) {

				$task_id = $_REQUEST[ $task_post_type . '_id' ];

				switch ( $_REQUEST['action'] ) {
					case 'trash':
						$this->rtpm_trash_task( $task_id );
						break;

					case 'restore':
						$this->rtpm_untrash_task( $task_id );
						break;

					case 'delete':
						$this->rtpm_delete_task( $task_id );
						break;
				}

				if ( is_admin() ) {

					$redirect_url = add_query_arg( array(
						'post_type'       => $post_type,
						'page'            => "rtpm-add-{$post_type}",
						"{$post_type}_id" => $_REQUEST["{$post_type}_id"],
						'tab'             => "{$post_type}-task"
					), admin_url( 'edit.php' ) );
				} else {
					$redirect_url = add_query_arg( array(
						'post_type'       => $post_type,
						'page'            => "rtpm-add-{$post_type}",
						"{$post_type}_id" => $_REQUEST["{$post_type}_id"],
						'tab'             => "{$post_type}-task"
					), $rt_pm_bp_pm->get_component_root_url() . bp_current_action() );
				}

				wp_safe_redirect( $redirect_url );
				die();
			}

		}

		public function rtpm_tasks_estimated_hours( $project_id, $due_date ) {
			global $rt_pm_task_resources_model;

			$tasks = $this->rtpm_get_tasks_by_duedate( $project_id, $due_date );

			if ( empty( $tasks ) ) {
				return;
			}

			$task_ids = implode( ',', $tasks );

			$result = (float) $rt_pm_task_resources_model->rtpm_get_tasks_estimated_hours( $task_ids );

			return $result;
		}

		public function rtpm_tasks_billed_hours( $project_id, $due_date ) {
			global $wpdb;

			$tasks = $this->rtpm_get_tasks_by_duedate( $project_id, $due_date );

			if ( empty( $tasks ) ) {
				return;
			}

			$task_ids = implode( ',', $tasks );

			$timeentries_table_name = rtpm_get_time_entry_table_name();

			$query = "SELECT SUM( CAST( time_duration AS DECIMAL( 10, 2 ) ) ) FROM $timeentries_table_name WHERE task_id in ( $task_ids )";

			$result = $wpdb->get_var( $query );

			return $result;
		}

		public function rtpm_get_tasks_by_duedate( $project_id, $due_date ) {
			global $wpdb;

			$tasks = $this->rtpm_get_projects_task_ids( $project_id );

			if ( empty( $tasks ) ) {
				return;
			}

			$task_ids = implode( ',', $tasks );

			$query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'post_duedate' AND STR_TO_DATE( meta_value, '%Y-%m-%d' ) <= '$due_date' and post_id in ( $task_ids )";

			$result = $wpdb->get_col( $query );

			return $result;
		}

		public function rtpm_get_user_tasks() {
			global $rt_pm_bp_pm, $rt_pm_project, $rt_pm_task_resources_model;

			$project_post_type = $rt_pm_project->post_type;
			if( ! isset( $_REQUEST['post'] ) )
				wp_send_json_error();

			$data  = $_REQUEST['post'];

			$tasks_data = $rt_pm_task_resources_model->rtpm_get_resources_tasks( $data );

			$send_data = array();

			$send_data['assignee_name'] = rtbiz_get_user_displayname( $data['user_id'] );

			$send_data['tasks'] = array();

			if ( null !== $tasks_data ) {

				foreach ( $tasks_data as $task ) {

					$task_project_id = $this->rtpm_get_task_project_id( $task );

					$task_edit_url        = add_query_arg( array(
						'post_type'               => $project_post_type,
						"{$project_post_type}_id" => $task_project_id,
						'tab'                     => "{$project_post_type}-task",
						"{$this->post_type}_id"   => $task
					), $rt_pm_bp_pm->get_component_root_url() . 'tasks' );
					$send_data['tasks'][] = array(
						'task_edit_url' => $task_edit_url,
						'post_title'    => get_post_field( 'post_title', $task ),
					);
				}
			}

			if ( ! empty( $send_data ) ) {
				wp_send_json_success( $send_data );
			} else {
				wp_send_json_error();
			}
		}


		/**
		 * Save tasks data
		 *
		 * @param $args
		 * @param $meta_data
		 *
		 * @return int
		 */
		public function rtpm_save_task_data( $args, $meta_data = array() ) {

			$args['post_type'] = $this->post_type;
			$update = false;

			//Save post data
			if ( isset ( $args['ID'] ) ) {

				$update = true;
				$post_id = @wp_update_post( $args );
			} else {

				$post_id = @wp_insert_post( $args );
				$meta_data = apply_filters( 'rtpm_insert_task_meta', $meta_data );
			}

			/**
			 * Since phase 4
			 */
			if ( isset( $args['post_parent'] ) ) {
				$meta_data['post_project_id'] = $args['post_parent'];
			}

			$meta_data = apply_filters( 'rtpm_task_meta', $meta_data );
			//Save post meta data
			foreach ( $meta_data as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}

			do_action( 'rtpm_after_save_task', $post_id, $update );
			return $post_id;
		}

		/**
		 * Set parent task and update child tsk count for parent task
		 * @param $meta_data
		 *
		 * @return mixed
		 */
		public function rtpm_set_task_parent_and_child_task_count( $meta_data ) {

			if ( isset( $meta_data['rtpm_parent_task'] ) ) {
				$parent_task_id   = $meta_data['rtpm_parent_task'];
				$child_task_count = get_post_meta( $parent_task_id, 'rtpm_child_task_count', true );

				if ( ! empty( $child_task_count ) ) {
					$child_task_count = intval( $child_task_count ) + 1;
					update_post_meta( $parent_task_id, 'rtpm_child_task_count', $child_task_count );
				} else {

					update_post_meta( $parent_task_id, 'rtpm_child_task_count', 1 );
				}
			}

			$meta_data['rtpm_child_task_count'] = 0;

			return $meta_data;
		}

		/**
		 * Delete task by id
		 *
		 * @param $task_id
		 *
		 * @return array|bool|WP_Post
		 */
		public function rtpm_delete_task( $task_id ) {

			do_action( 'rtpm_before_delete_task', $task_id );

			$result = wp_delete_post( $task_id );

			if( $result )
				do_action( 'rtpm_after_delete_task' , $task_id );

			return $result;
		}

		/**
		 * Trash task by id
		 *
		 * @param $task_id
		 *
		 * @return array|bool
		 */
		public function rtpm_trash_task( $task_id ) {

			do_action( 'rtpm_before_trash_task', $task_id );

			$result = wp_trash_post( $task_id );

			if( $result )
				do_action( 'rtpm_after_trash_task', $task_id );

			return $result;
		}

		/**
		 * Untrash task by id
		 *
		 * @param $task_id
		 *
		 * @return bool|WP_Post
		 */
		public function rtpm_untrash_task( $task_id ) {

			do_action( 'rtpm_before_untrash_task', $task_id );

			$result = wp_untrash_post( $task_id );

			if( $result )
				do_action( 'rtpm_after_untrash_task', $task_id );

			return $result;
		}


		/**
		 *  Save project task from ganttchart (ajax)
		 */
		public function rtpm_save_project_task() {
			global $rt_pm_task;

			if ( ! isset( $_REQUEST['post'] ) ) {
				return;
			}

			$post = $_REQUEST['post'];

			$args = array(
				'post_title'  => $post['task_title'],
				'post_date'   => $post['start_date'],
				'post_date_gmt' =>$post['start_date'],
				'post_parent' => $post['parent_project'],
				'post_status' => 'new',
			);

			if ( isset( $post['task_id'] ) ) {
				$args['ID'] = $post['task_id'];
			}


			$meta_values = array(
				'post_duedate'         => $post['end_date'],
				'rtpm_task_type'       => $post['task_type'],
		//		'post_estimated_hours' => $post['estimated_hours'],
				'post_project_id'      => $post['parent_project'],
			);

			$send_data = array();
			//Check for parent task id is set
			if ( '0' !== $post['parent_task'] ) {

				$parent_task_id = $post['parent_task'];
				$meta_values['rtpm_parent_task'] = $parent_task_id;
			}

			$post_id = $this->rtpm_save_task_data( $args, $meta_values );

			if( '0' !== $post['parent_task'] ) {

				$parent_task_id = $post['parent_task'];

				$parent_task_start_date = get_post_field( 'post_date', $parent_task_id );
				$parent_task_end_date = get_post_meta( $parent_task_id, 'post_duedate', true );

				$send_data['parent_task_data'] = array(
					'start_date' => $parent_task_start_date,
					'end_date' => $parent_task_end_date,
				);
			}

			$send_data['task_id'] = $post_id;

			if ( $post_id ) {
				@wp_send_json_success( $send_data );
			}

		}

		/**
		 * Delete project task ( ajax )
		 */
		public function rtpm_delete_project_task() {

			if ( ! isset( $_REQUEST['post'] ) ) {
				return;
			}

			$post = $_REQUEST['post'];

			$task = @wp_delete_post( $post['task_id'] );

			if ( $task ) {
				wp_send_json_success( $task );
			}
		}

		/**
		 * Create or Update task link ( ajax )
		 */
		public function rtpm_save_project_task_link() {
			global $rt_pm_task_links_model;

			if ( ! isset( $_REQUEST['post'] ) ) {
				return;
			}

			$post = $_REQUEST['post'];

			$data = array(
				'project_id'     => $post['parent_project'],
				'source_task_id' => $post['source_task_id'],
				'target_task_id' => $post['target_task_id'],
				'type'           => $post['connection_type'],
			);

			$link_id = $rt_pm_task_links_model->rtpm_create_task_link( $data );

			if ( $link_id ) {
				wp_send_json_success( array( 'id' => $link_id ) );
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Delete task link ( ajax )
		 */
		public function rtpm_delete_project_task_link() {

			global $rt_pm_task_links_model;

			if ( ! isset( $_REQUEST['post'] ) ) {
				return;
			}

			$post = $_REQUEST['post'];

			$data = array(
				'id' => $post['link_id'],
			);

			$link_id = $rt_pm_task_links_model->rtpm_delete_task_link( $data );


			if ( $link_id ) {

				wp_send_json_success( array( 'id' => $link_id ) );
			} else {
				wp_send_json_error();
			}
		}


		/**
		 * Render task detail in context box
		 */
		public function rtpm_get_task_data_for_ganttchart() {

			if ( ! isset( $_REQUEST['post'] ) ) {
				return;
			}

			$post = $_REQUEST['post'];

			$task_id = $post['task_id'];

			$task_data = $this->rtpm_get_task_data( array( 'p' => $task_id ) );


			$start_date = rt_convert_strdate_to_usertimestamp( $task_data[0]->post_date );
			$end_date   = rt_convert_strdate_to_usertimestamp( get_post_meta( $task_id, 'post_duedate', true ) );
			if ( ! empty( $task_data ) ) {
				$data = array(
					'task_title'    => $task_data[0]->post_title,
					'start_date'    => $start_date->format( 'd M Y' ),
					'end_date'      => $end_date->format( 'd M  Y' ),
					'task_status'   => $task_data[0]->post_status,
					'task_progress' => $this->rtpm_get_task_progress_percentage( $task_id ),
				);

				wp_send_json_success( $data );
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Get task progress percentage
		 *
		 * @param $task_id
		 *
		 * @return float|int
		 */
		public function rtpm_get_task_progress_percentage( $task_id ) {
			global $rt_pm_task_resources_model, $rt_pm_time_entries_model;

			$tasks = $this->rtpm_get_task_subtasks( $task_id );

			if( empty( $tasks ) ) {

				$tasks = array( $task_id );
			}

			$total_billed_hours = (float) $rt_pm_time_entries_model->rtpm_get_billed_hours( array( 'task__in' => $tasks ) );
			$estimated_hours = (float) $rt_pm_task_resources_model->rtpm_get_tasks_estimated_hours( $tasks );

			if ( 0 < $estimated_hours ) {
				return sprintf( '%0.2f', $total_billed_hours / $estimated_hours * 100 );
			}

			return 0;
		}

		/**
		 * Get all sub task of projects
		 *
		 * @param $project_id
		 *
		 * @return array
		 */
		public function rtpm_get_timeentries_tasks( $project_id ) {

			$task_ids = $this->rtpm_get_projects_task_ids( $project_id );
			$args     = array(
				'post_parent'   => $project_id,
				'nopaging'      => true,
				'fields'        => 'ids',
				'post__in'      => $task_ids,
				'no_found_rows' => true,
				'meta_query'    => array(
					array(
						'key'     => 'rtpm_child_task_count',
						'value'   => '0',
						'compare' => '='
					),
					array(
						'key'   => 'rtpm_task_type',
						'value' =>  array( 'main_task', 'sub_task' ),
					),
				),
			);

			$results = $this->rtpm_get_task_data( $args );

			return $results;
		}

		public function rtpm_tasks_dropdown( $project_id, $task_id = '' ) {
			$tasks = $this->rtpm_user_assigned_tasks( $project_id );
		?>

			<select required='required' name='post[post_task_id]' id='task_id'>;

			<?php foreach ( $tasks as $task ):
				$task_title = get_post_field( 'post_title', $task );?>
				<option  <?php selected( $task_id, $task ) ?> value="<?php echo $task ?>"><?php echo $task_title ?></option>
			<?php endforeach; ?>

			<?php
			//Show "Add Time" option while adding timn entry at project level
			if ( defined('DOING_AJAX') && DOING_AJAX && bp_is_current_action('time-entries') ) {
				echo "<option value='add-time'>Add Time</option>";
			}
			?>
			<select>

			<?php
		}

		public function rtpm_user_assigned_tasks(  $project_id, $user_id = 0 ) {
			global $rt_pm_task_resources_model;

			if( empty( $user_id ) ) {
				if( function_exists('bp_is_active') ) {
					$user_id = bp_displayed_user_id();
				 }

				 if( empty( $user_id ) ) {
				    $user_id = get_current_user_id();
				 }
			}

			$time_entry_tasks = $this->rtpm_get_timeentries_tasks( $project_id );
			$assigned_task = $rt_pm_task_resources_model->rtpm_get_all_task_id_by_user( $user_id );
			$tasks = array_intersect( $assigned_task, $time_entry_tasks );

			return $tasks;
		}

		/**
		 * Update child task count
		 *
		 * @param $task_id
		 * @param $action
		 *
		 * @return bool|int|void
		 */
		public function rtpm_update_child_task_count( $task_id, $action ) {

			$parent_task_id = get_post_meta( $task_id, 'rtpm_parent_task', true );

			if ( empty( $parent_task_id ) )
				return;

			$child_task_count = get_post_meta( $parent_task_id, 'rtpm_child_task_count', true );

			switch ( $action ) {
				case 'trash':
					$child_task_count = max( 0, intval( $child_task_count ) - 1 );
					break;
				case 'restore':
					$child_task_count = max( 0, intval( $child_task_count ) + 1 );
					break;
			}

			return update_post_meta( $parent_task_id, 'rtpm_child_task_count', $child_task_count );
		}


		public function rtpm_get_task_subtasks( $task_id ) {

			$post_status = array_column( $this->statuses, 'slug', 'slug' );

			$args = array(
				'nopaging' => true,
				'post_status' => $post_status,
				'no_found_rows' => true,
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => 'rtpm_parent_task',
						'value' => $task_id,
					)
				)
			);

			return $this->rtpm_get_task_data( $args );
		}

		/**
		 * Get all resources in task
		 * @param $task_is
		 * @param $project_id
		 *
		 * @return mixed
		 */
		public function rtpm_get_task_resources( $task_is, $project_id ) {
			global $rt_pm_task_resources_model;

			$where = array(
				'task_id' => $task_is,
				'project_id' =>$project_id,
			);

			return $rt_pm_task_resources_model->get( $where );
		}

		/**
		 * Set parent task start_date and end_date base on child's task date
		 * @param $post_id
		 * @return bool|void
		 */
		public function rtpm_set_task_group( $post_id, $save ) {
			global $wpdb;
			$parent_task_id = get_post_meta( $post_id, 'rtpm_parent_task',true );

			if( empty( $parent_task_id ) )
				return;

			$child_task_count = get_post_meta( $parent_task_id, 'rtpm_child_task_count', true );

			if( empty( $child_task_count ) )
				return;

//			if( $save ) {
//				$parent_task_start_date =  get_post_field( 'post_date', $parent_task_id  );
//				$parent_task_end_date = get_post_meta( $parent_task_id, 'post_duedate', true );
//
//				$child_task_start_date =   get_post_field( 'post_date', $post_id  );
//				$child_task_end_date = get_post_meta( $post_id, 'post_duedate', true );
//
//				$parent_task_start_date_obj = new DateTime( $parent_task_start_date );
//				$parent_task_end_date_obj = new DateTime( $parent_task_end_date );
//
//				$child_task_start_date_obj = new DateTime( $child_task_start_date );
//				$child_task_end_date_obj = new DateTime( $child_task_end_date );
//
//				if( $parent_task_start_date_obj > $child_task_start_date_obj )
//					$parent_task_start_date = $child_task_start_date;
//
//				if( $parent_task_end_date_obj < $child_task_end_date_obj )
//					$parent_task_end_date = $child_task_end_date;
//			} else {

				$sub_task_ids = $this->rtpm_get_task_subtasks( $parent_task_id );

				$ids = implode( ',', apply_filters( 'rtpm_sub_tasks_to_exclude', $sub_task_ids, $post_id ) );

				$query = "SELECT MIN( post_date ) FROM {$wpdb->posts} WHERE ID IN( $ids )";

				$parent_task_start_date = $wpdb->get_var( $query );

				$query = "SELECT MAX( STR_TO_DATE( meta_value ,'%Y-%m-%d %H:%i:%s' ) ) FROM {$wpdb->postmeta} WHERE meta_key = 'post_duedate' AND post_id IN ( $ids ) ";

				$parent_task_end_date = $wpdb->get_var( $query );
//			}

			$this->rtpm_set_task_group_date( $parent_task_id, $parent_task_start_date, $parent_task_end_date );
		}

		public function rtpm_sub_tasks_to_exclude( $task_ids, $exclude_task_id ) {

			$sub_task_ids = array_diff( $task_ids, array( $exclude_task_id ) );
			return $sub_task_ids;
		}

		/**
		 * After save task action
		 * @param $post_id
		 * @param $update
		 */
		public function rtpm_after_save_task( $post_id, $update ) {
				$this->rtpm_set_task_group( $post_id, true );

				$this->rtpm_set_task_type( $post_id );
		}

		/**
		 * After untrash task action
		 * @param $post_id
		 */
		public function rtpm_after_untrash_task( $post_id ) {
			global $rt_pm_time_entries_model, $rt_pm_task_resources_model;

			$this->rtpm_set_task_group( $post_id, false );

			$data = array( 'post_status' => 'new' );
			$where = array( 'task_id' => $post_id );

			$rt_pm_time_entries_model->update( $data, $where );
			$rt_pm_task_resources_model->update( $data, $where );
		}


		/**
		 * After trash task call back
		 * @param $post_id
		 */
		public function rtpm_after_trash_task( $post_id ) {
			global $rt_pm_time_entries_model, $rt_pm_task_resources_model;

			$data = array( 'post_status' => 'trash' );
			$where = array( 'task_id' => $post_id );

			$rt_pm_time_entries_model->update( $data, $where );
			$rt_pm_task_resources_model->update( $data, $where );
		}

		/**
		 * Action before trashing task
		 * @param $post_id
		 */
		public function rtpm_before_trash_task( $post_id ) {
			add_filter( 'rtpm_sub_tasks_to_exclude', array( $this, 'rtpm_sub_tasks_to_exclude' ), 10, 2 );
			$this->rtpm_set_task_group( $post_id, false );
		}

		/**
		 * Action before untrash task
		 * @param $post_id
		 */
		public function rtpm_before_untrash_task( $post_id ) {

			$this->rtpm_update_child_task_count( $post_id, 'restore' );
		}

		/**
		 * After delete task call back
		 * @param $post_id
		 */
		public function rtpm_after_delete_task( $post_id ) {
			global $rt_pm_time_entries_model, $rt_pm_task_resources_model, $rt_pm_project, $rt_pm_task_links_model;

			$where = array( 'task_id' => $post_id );

			//Delete time entries and resources and task links
			$rt_pm_task_resources_model->delete( $where );
		 	$rt_pm_time_entries_model->delete( $where );
			$rt_pm_task_links_model->delete( $where );

			$rt_pm_project->remove_connect_post_to_entity( $this->post_type, $post_id );
		}

		/**
		 * Set task group period date
		 * @param $post_id
		 * @param $start_date
		 * @param $end_date
		 *
		 * @return bool
		 */
		public function rtpm_set_task_group_date( $post_id, $start_date, $end_date ) {

			$args = array(
				'ID' => $post_id,
				'post_date' => $start_date,
			);

			$updated = @wp_update_post( $args );

			if( ! $updated )
				return false;

			update_post_meta( $post_id, 'post_duedate', $end_date );
		}

		/**
		 * Set the type of a task (Task group, Orindary Task and Sun Task)
		 * @param $post_id
		 * @return int|bool Meta ID if the key didn't exist, true on successful update,
		 *                  false on failure.
		 */
		public function rtpm_set_task_type( $post_id ) {

			$parent_task_id = absint( get_post_meta( $post_id, 'rtpm_parent_task', true ) );

			$child_task_count = absint( get_post_meta( $post_id, 'rtpm_child_task_count', true ) );

			$task_type = get_post_meta( $post_id, 'rtpm_task_type', true );

			if( ! empty( $task_type ) && 'milestone' === $task_type )
				return true;

			if( $parent_task_id === 0 && $child_task_count === 0 ) {
				$task_type = 'main_task';
			} elseif ( $child_task_count > 0 ) {
				$task_type = 'task_group';
			} elseif( $parent_task_id > 0 ) {
				$task_type = 'sub_task';
			}

			return update_post_meta( $post_id, 'rtpm_task_type', $task_type );
		}

		public function rtpm_get_task_type_label( $post_id ) {

			$task_type = get_post_meta( $post_id, 'rtpm_task_type', true );
			$task_label = '';

			switch( $task_type ) {
				case 'task_group':
					$task_label = 'Task Group';
					break;

				case 'main_task':
					$task_label = 'Normal Task';
					break;

				case 'sub_task':
					$task_label = 'Sub Task';
					break;

				case 'milestone':
					$task_label = 'Milestone';
					break;
			}

			return $task_label;
		}

		public function rtpm_render_parent_tasks_dropdown( $project_id, $post_id = '' ) {

			$args = array(
				'fields' => 'ids',
				'nopaging' => true,
				'no_found_rows' => true,
				'post_parent'  =>  $project_id,
				'meta_query' => array(
					array(
						'key' => 'rtpm_task_type',
						'value' => 'task_group',
					),
					array(
						'key' => 'rtpm_task_type',
						'value' => 'main_task',
					),
					'relation' => 'OR'
				),
			);

			$data = $this->rtpm_get_task_data( $args );

			$parent_task_id = '0';

			if( ! empty( $post_id ) )
				$parent_task_id = get_post_meta( $post_id, 'rtpm_parent_task', true );
			?>
			<select name="post[parent_task]">
				<option value="0">Select Parent Task</option>
				<?php foreach( $data as $task_id) : ?>
					<option <?php selected( $task_id, $parent_task_id ); ?> value="<?php echo $task_id ?>"><?php echo get_post_field( 'post_title', $task_id ) ?></option>
				<?php endforeach; ?>
			</select>
		<?php }

		/**+
		 * Ajax import task in json format
		 *
		 */
		public function rtpm_import_task_json() {
			global $rt_pm_task_links_model;

			check_ajax_referer( 'rtpm-import-task-json', 'security' );

			$post = $_REQUEST['post'];

			$attachment_id = $post['attachment_id'];
			$project_id = $post['post_id'];

			$file_url = get_attached_file( $attachment_id );

			$str_task_data = file_get_contents( $file_url );

			$json_task_data = json_decode( $str_task_data );

			//Throw an error if file is not json
			if( ! $json_task_data ) {
				wp_delete_attachment( $attachment_id );
				wp_send_json_error();
			}

			//Task object
			$task_data = $json_task_data->data->data;

			//Link object
			$link_data = $json_task_data->data->links;

			//Map of old_task_id => new_task_id
			$former_task_ids = array(
				0 =>  0
			);

			//Loop over task data
			foreach( $task_data as $task ) {

				$start_date = date_create_from_format( 'd-m-Y H:i', $task->start_date );
				$end_date = date_create_from_format( 'd-m-Y H:i', $task->end_date );

				$args = array(
					'post_title'  => $task->text,
					'post_date'   => $start_date->format('Y-m-d H:i:s'),
					'post_date_gmt' => $start_date->format('Y-m-d H:i:s'),
					'post_parent' => $project_id,
					'post_status' => 'new',
				);

				$meta_values = array(
					'post_duedate'         => $end_date->format( 'Y-m-d H:i:s'),
					'rtpm_task_type'       => $task->type,
					'post_project_id'      => $project_id,
					'rtpm_parent_task'     => $former_task_ids[$task->parent],
				);

				//Create task
				$post_id = $this->rtpm_save_task_data( $args, $meta_values );

				//Map new_task_id with old_task_id
				$former_task_ids[ $task->id ] = $post_id;

			}

			//Loop over link data
			foreach ( $link_data  as $link ) {
				$data = array(
					'project_id'     => $project_id,
					'source_task_id' => $former_task_ids[ $link->source ],
					'target_task_id' => $former_task_ids [ $link->target ],
					'type'           => $link->type,
				);

				//Create new link between task
				$link_id = $rt_pm_task_links_model->rtpm_create_task_link( $data );
			}

			//Delete attachment after proceed
			wp_delete_attachment( $attachment_id );

			wp_send_json_success();
		}
	}

}