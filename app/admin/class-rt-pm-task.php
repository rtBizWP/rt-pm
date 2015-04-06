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
			add_action( "save_task", array( $this, 'task_add_bp_activity' ), 10, 2 );
			add_action( 'wp_ajax_rtpm_get_task', array( $this, 'get_autocomplate_task' ) );
			add_filter( 'posts_where', array( $this, 'rtcrm_generate_task_sql'), 10, 2 );
			add_action( 'init', array( $this, 'rtpm_save_task' ) );
			add_action( 'init', array( $this, 'rtpm_task_actions' ) );
			add_action( 'wp_ajax_rtpm_get_user_tasks', array( $this, 'rtpm_get_user_tasks' ) );
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
		 * @param array $args
		 * @return array
		 */
		public function rtpm_get_task_data( $args = array() ) {

			$args['post_type'] = $this->post_type;

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

			if( empty( $task_ids ) )
				return 0;

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
		public function rtpm_open_task_count( $project_id, $date_query = null ) {

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


			if( null !== $date_query )
				$args['date_query'] = $date_query;

			$query = new WP_Query( $args );

			return $query->post_count;
		}

		/**
		 * Return completed task count
		 * @param $project_id
		 * @param null $date_query
		 * @return int
		 */
		public function rtpm_completed_task_count( $project_id, $date_query = null ) {

			$args = array(
				'nopaging' => true,
				'post_type' => $this->post_type,
				'fields' => 'ids',
				'no_found_rows' => true,
				'meta_key' => 'post_project_id',
				'meta_value' => $project_id,
				'post_status' => 'completed'
			);

			if( null !== $date_query )
				$args['date_query'] = $date_query;

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

		/**
		 * @param $project_id
		 */
		public function rtpm_get_all_task_duedate( $project_id ) {
			global $wpdb;

			$tasks = $this->rtpm_get_projects_task_ids( $project_id );

			if( empty( $tasks ) )
				return;

			$task_ids = implode( ',', $tasks );

			$query = "SELECT STR_TO_DATE( meta_value,'%Y-%m-%d') AS task_duedate FROM $wpdb->postmeta WHERE post_id IN( $task_ids ) AND meta_key = 'post_duedate' ORDER BY task_duedate";

			$result = $wpdb->get_col( $query );

			return $result;
		}

		/**
		 * Return completed task percentage
		 * @param $project_id
		 * @return float|int
		 */
		public function rtpm_get_completed_task_per( $project_id ) {

			$all_task_count = count( $this->rtpm_get_projects_task_ids( $project_id ) );

			if( $all_task_count <= 0 )
				return 0;

			$completed_task_count = $this->rtpm_completed_task_count( $project_id );

			$completed_task_per = ( $completed_task_count * 100 ) / $all_task_count;

			return floor( $completed_task_per );
		}


		/**
		 * Return project id of task
		 * @param $task_id
		 * @return mixed
		 */
		public function rtpm_get_task_project_id( $task_id ) {

			return get_post_meta( $task_id, 'post_project_id', true );
		}


		/**
		 * Save or Add new Task
		 */
		public function rtpm_save_task() {
			global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_task, $rt_pm_time_entries_model;

			if ( ! isset( $_POST['rtpm_save_task_nonce'] ) || ! wp_verify_nonce( $_POST['rtpm_save_task_nonce'], 'rtpm_save_task' ) )
				return;

			$post_type=$rt_pm_project->post_type;
			$task_post_type=$rt_pm_task->post_type;
			$task_labels=$rt_pm_task->labels;

			$newTask = $_POST['post'];

			//Switch to blog in MU site while editing from other site
			if( isset( $newTask['rt_voxxi_blog_id'] ) )
				switch_to_blog( $newTask['rt_voxxi_blog_id'] );

			$creationdate = $newTask['post_date'];
			if ( isset( $creationdate ) && $creationdate != '' ) {
				try {
					$dr = date_create_from_format( 'M d, Y H:i A', $creationdate );
					$UTC = new DateTimeZone('UTC');
					$dr->setTimezone($UTC);
					$timeStamp = $dr->getTimestamp();
					$newTask['post_date'] = gmdate('Y-m-d H:i:s', intval($timeStamp) );
					$newTask['post_date_gmt'] = rt_set_date_to_utc( gmdate('Y-m-d H:i:s', (intval($timeStamp))) );
				} catch ( Exception $e ) {
					$newTask['post_date'] = current_time( 'mysql' );
					$newTask['post_date_gmt'] = gmdate('Y-m-d H:i:s');
				}
			} else {
				$newTask['post_date'] = current_time( 'mysql' );
				$newTask['post_date_gmt'] = gmdate('Y-m-d H:i:s');
			}

			$duedate = $newTask['post_duedate'];
			if ( isset( $duedate ) && $duedate != '' ) {
				try {
					$dr = date_create_from_format( 'M d, Y H:i A', $duedate );
					//  $UTC = new DateTimeZone('UTC');
					//  $dr->setTimezone($UTC);
					$timeStamp = $dr->getTimestamp();
					$newTask['post_duedate'] = rt_set_date_to_utc( gmdate('Y-m-d H:i:s', intval($timeStamp) ) );
				} catch ( Exception $e ) {
					$newTask['post_duedate'] = current_time( 'mysql' );
				}
			}

			// Post Data to be saved.
			$post = array(
				'post_content' => $newTask['post_content'],
				'post_status' => $newTask['post_status'],
				'post_title' => $newTask['post_title'],
				'post_date' => $newTask['post_date'],
				'post_date_gmt' => $newTask['post_date_gmt'],
				'post_type' => $task_post_type
			);

			$updateFlag = false;
			//check post request is for Update or insert
			if ( isset($newTask['post_id'] ) ) {
				$updateFlag = true;
				$post = array_merge( $post, array( 'ID' => $newTask['post_id'] ) );
				$data = array(
					'post_assignee' => $newTask['post_assignee'],
					'post_project_id' => $newTask['post_project_id'],
					'post_estimated_hours' => $newTask['post_estimated_hours'],
					'post_duedate' => $newTask['post_duedate'],
					'date_update' => current_time( 'mysql' ),
					'date_update_gmt' => gmdate('Y-m-d H:i:s'),
					'user_updated_by' => get_current_user_id(),
				);
				$post_id = @wp_update_post( $post );

				$operation_type = 'update';
			}else{
				$data = array(
					'post_assignee' => $newTask['post_assignee'],
					'post_project_id' => $newTask['post_project_id'],
					'post_duedate' => $newTask['post_duedate'],
					'post_estimated_hours' => $newTask['post_estimated_hours'],
					'date_update' => current_time( 'mysql' ),
					'date_update_gmt' => gmdate('Y-m-d H:i:s'),
					'user_updated_by' => get_current_user_id(),
					'user_created_by' => get_current_user_id(),
				);
				$post_id = @wp_insert_post($post);

				$_REQUEST["new"]=true;
				$newTask['post_id']= $post_id;
				$operation_type = 'insert';
			}

			$rt_pm_project->connect_post_to_entity($task_post_type,$newTask['post_project_id'],$post_id);
			foreach ( $data as $key=>$value ) {
				update_post_meta( $post_id, $key, $value );
			}

			// link post to user
			if( ! empty( $newTask['post_assignee'] ) ) {

				$employee_id = rt_biz_get_person_for_wp_user( $newTask['post_assignee'] );
				// remove old data
				$rt_pm_project->remove_post_from_user( $task_post_type, $post_id );
				$rt_pm_project->connect_post_to_user( $task_post_type,$post_id,$employee_id[0]->ID );
			}


			// Attachments
			$old_attachments = get_posts( array(
				'post_parent' => $newTask['post_id'],
				'post_type' => 'attachment',
				'fields' => 'ids',
				'posts_per_page' => -1,
			));
			$new_attachments = array();
			if ( isset( $_POST['attachment'] ) ) {
				$new_attachments = $_POST['attachment'];
				foreach ( $new_attachments as $attachment ) {
					if( !in_array( $attachment, $old_attachments ) ) {
						$file = get_post($attachment);
						$filepath = get_attached_file( $attachment );
						$post_attachment_hashes = get_post_meta( $newTask['post_id'], '_rt_wp_pm_attachment_hash' );
						if ( ! empty( $post_attachment_hashes ) && in_array( md5_file( $filepath ), $post_attachment_hashes ) ) {
							continue;
						}
						if( !empty( $file->post_parent ) ) {
							$args = array(
								'post_mime_type' => $file->post_mime_type,
								'guid' => $file->guid,
								'post_title' => $file->post_title,
								'post_content' => $file->post_content,
								'post_parent' => $newTask['post_id'],
								'post_author' => get_current_user_id(),
								'post_status' => 'inherit'
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
					if( !in_array( $attachment, $new_attachments ) ) {
						wp_update_post( array( 'ID' => $attachment, 'post_parent' => '0' ) );
						$filepath = get_attached_file( $attachment );
						delete_post_meta($newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
					}
				}
			} else {
				foreach ( $old_attachments as $attachment ) {
					wp_update_post( array( 'ID' => $attachment, 'post_parent' => '0' ) );
					$filepath = get_attached_file( $attachment );
					delete_post_meta($newTask['post_id'], '_rt_wp_pm_attachment_hash', md5_file( $filepath ) );
				}
				delete_post_meta($newTask['post_id'], '_rt_wp_pm_attachment_hash' );
			}

			do_action( 'save_task', $newTask['post_id'], $operation_type );

			//Add success message
			if( ! is_admin() ) {

				bp_core_add_message( 'Task updated successfully', 'success' );

				if( isset( $newTask['rt_voxxi_blog_id'] ) ){
					restore_current_blog();
					add_action ( 'wp_head', 'rt_voxxi_js_variables' );
				}
			}
		}

		/**
		 * Task actions:, trash, delete, restore
		 */
		public function rtpm_task_actions() {
			global $rt_pm_project,$rt_pm_task, $rt_pm_time_entries_model, $rt_pm_bp_pm;

			$task_post_type = $rt_pm_task->post_type;


			if( ! isset( $_REQUEST['post_type'] ) || $rt_pm_project->post_type !== $_REQUEST['post_type'])
				return;

			$post_type=$_REQUEST['post_type'];

			if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'trash' && isset( $_REQUEST[$task_post_type.'_id'] ) ) {

				switch( $_REQUEST['action'] ){
					case 'trash':
						wp_delete_post( $_REQUEST[$task_post_type.'_id'] );
						$rt_pm_time_entries_model->delete_timeentry( array( 'task_id' => $_REQUEST[$task_post_type.'_id'] ) );
						break;

					case 'restore':
						wp_untrash_post( $_REQUEST[$task_post_type.'_id'] );
						break;

					case 'delete':
						wp_delete_post( $_REQUEST[$task_post_type.'_id'] );
						$rt_pm_project->remove_connect_post_to_entity($task_post_type,$_REQUEST[$task_post_type.'_id']);
						break;
				}

				if( is_admin() ){

					$redirect_url = add_query_arg( array( 'post_type' => $post_type, 'page' => "rtpm-add-{$post_type}", "{$post_type}_id" => $_REQUEST["{$post_type}_id"], 'tab' => "{$post_type}-task" ), admin_url('edit.php') );
				}else {
					$redirect_url = add_query_arg( array( 'post_type' => $post_type, 'page' => "rtpm-add-{$post_type}", "{$post_type}_id" => $_REQUEST["{$post_type}_id"], 'tab' => "{$post_type}-task" ), $rt_pm_bp_pm->get_component_root_url().bp_current_action() );
				}

				wp_safe_redirect( $redirect_url );
			}


		}

		public function rtpm_tasks_estimated_hours( $project_id, $due_date ) {
			global $wpdb;

			$tasks = $this->rtpm_get_tasks_by_duedate( $project_id, $due_date );

			if( empty( $tasks ) )
				return;

			$task_ids = implode( ',', $tasks );


			$query = "SELECT SUM( CAST( meta_value AS DECIMAL( 10, 2 ) ) ) FROM $wpdb->postmeta WHERE meta_key = 'post_estimated_hours' AND post_id in ( $task_ids )";

			$result = $wpdb->get_var( $query );

			return $result;
		}

		public function rtpm_tasks_billed_hours( $project_id, $due_date ) {
			global $wpdb;

			$tasks = $this->rtpm_get_tasks_by_duedate( $project_id, $due_date );

			if( empty( $tasks ) )
				return;

			$task_ids = implode( ',', $tasks );

			$timeentries_table_name = rtpm_get_time_entry_table_name();

			$query = "SELECT SUM( CAST( time_duration AS DECIMAL( 10, 2 ) ) ) FROM $timeentries_table_name WHERE task_id in ( $task_ids )";

			$result = $wpdb->get_var( $query );

			return $result;
		}

		public function rtpm_get_tasks_by_duedate( $project_id, $due_date ) {
			global $wpdb;

			$tasks = $this->rtpm_get_projects_task_ids( $project_id );

			if( empty( $tasks ) )
				return;

			$task_ids = implode( ',', $tasks );

			$query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'post_duedate' AND STR_TO_DATE( meta_value, '%Y-%m-%d' ) <= '$due_date' and post_id in ( $task_ids )";

			$result = $wpdb->get_col( $query );

			return $result;
		}


		public function rtpm_get_task_by_user( $assignee_id ) {

			$args = array(
				'nopaging' => true,
				'post_type' => $this->post_type,
				'meta_key' => 'post_project_id',
				'meta_key' => 'post_assignee',
				'meta_value' => $assignee_id,
				'no_found_rows' => true,
			);

			$query = new WP_Query( $args );

			if( $query->have_posts() )
				return $query->posts;
			else
				return null;
		}

		public function rtpm_get_user_tasks() {
			global $rt_pm_bp_pm, $rt_pm_project;

			$project_post_type = $rt_pm_project->post_type;
			$data = $_REQUEST['post'];

			$author_id = $data['author_id'];

			$tasks_data = $this->rtpm_get_task_by_user( $author_id );

			$send_data = array();

			$send_data['assignee_name'] = rt_get_user_displayname( $author_id );

			$send_data['tasks'] = array();

			if( null !== $tasks_data ) {

				foreach( $tasks_data as $task ) {

					$task_project_id = $this->rtpm_get_task_project_id( $task->ID );

					$task_edit_url = add_query_arg( array( 'post_type' => $project_post_type, "{$project_post_type}_id" => $task_project_id, 'tab' => "{$project_post_type}-task", "{$this->post_type}_id" => $task->ID  ), $rt_pm_bp_pm->get_component_root_url().'tasks' );
					$send_data['tasks'][] = array(
						'task_edit_url' => $task_edit_url,
						'post_title' => $task->post_title,
					);
				}
			}

			if( ! empty( $send_data ) )
				wp_send_json_success( $send_data );
			else
				wp_send_json_error();
		}


        /**
         * Save tasks data
         * @param $args
         * @param $meta_data
         * @return int
         */
        public function rtpm_save_task_data( $args, $meta_data = array() ) {

            $args['post_type'] = $this->post_type;

			//Save post data
			if( isset ( $args['ID'] ) ) {
				$post_id = @wp_update_post( $args );
			}else{
				$post_id = @wp_insert_post( $args );
			}

			//Save post meta data
            foreach ( $meta_data as $key => $value ) {
                update_post_meta( $post_id, $key, $value );
            }

            return $post_id;
        }


	}

}