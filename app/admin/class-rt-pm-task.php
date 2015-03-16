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
                
                function get_autocomplate_task(){
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
		function disable_working_days( $project_id ){

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

		public function rtpm_get_task_data( $author_id = 0, $task_status = 'any', $date_query = null, $date_period = 'today' ){


			global $rt_crm_module,$wpdb;
			
			$sql_query = $this->rtp_get_task_sql_query($date_query, $date_period);
			
			foreach ( $date_query as $key => $date ) {
				$value = $date['year'].'-'.$date['month'].'-'.$date['day'];
				$compare = $key;
			}
			if( $compare === 'before' ){
				$compare = '<';
			}else{
				$compare = '=';
			}
			$args = array(
				'nopaging' => true,
				'post_status' => array( $task_status ),
				'post_type' => $this->post_type,
				'no_found_rows' => true,
			);
			$test = new WP_Date_Query($date_query);

			if( $author_id !== 0 )
				$args['author'] = $author_id;


			//if( null !== $date_query ) {
			//	$args['date_query'] = $date_query;
			//}

			$query = new WP_Query( $args );
			var_dump($sql_query);
			return $wpdb->get_results($sql_query,OBJECT);
			//return $query->posts;
		}
		
		public function rtp_get_task_sql_query($date_query,$date_period){
			
		global $wpdb;
		
		foreach ( $date_query as $key => $date ) {
				$year = $date['year'];
				$month = $date['month'];
				$day = $date['day'];
				$week = $date['week'];
			}
	
			switch ($date_period){
				case 'today':
					$sql_query = "SELECT   $wpdb->posts.* FROM $wpdb->posts  INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE 1=1  AND $wpdb->posts.post_author IN (1)  AND $wpdb->posts.post_type = 'rt_task' AND (($wpdb->posts.post_status <> 'trash' AND $wpdb->posts.post_status <> 'auto-draft')) AND ( 
  ( $wpdb->postmeta.meta_key = 'post_duedate' AND YEAR($wpdb->postmeta.meta_value) = '$year' AND MONTH($wpdb->postmeta.meta_value) = '$month' AND DAYOFMONTH($wpdb->postmeta.meta_value) = '$day' )
) GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC ";
					break;
				case 'overdue':
					$sql_query = "SELECT   $wpdb->posts.* FROM $wpdb->posts  INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE 1=1  AND $wpdb->posts.post_author IN (1)  AND $wpdb->posts.post_type = 'rt_task' AND (($wpdb->posts.post_status <> 'trash' AND $wpdb->posts.post_status <> 'auto-draft')) AND ( 
  ( $wpdb->postmeta.meta_key = 'post_duedate' AND $wpdb->postmeta.meta_value < '$year-$month-$day 00:00:00')) GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC ";
						break;
				case 'this-week':
					$sql_query = "SELECT   $wpdb->posts.* FROM $wpdb->posts  INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE 1=1  AND $wpdb->posts.post_author IN (1)  AND $wpdb->posts.post_type = 'rt_task' AND (($wpdb->posts.post_status <> 'trash' AND $wpdb->posts.post_status <> 'auto-draft')) AND ( 
  ( $wpdb->postmeta.meta_key = 'post_duedate' AND YEAR($wpdb->postmeta.meta_value) = '$year' AND WEEK($wpdb->postmeta.meta_value,1) = '$week' )
) GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC ";
						break;
				case 'next-week':
					$sql_query = "SELECT   $wpdb->posts.* FROM $wpdb->posts  INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE 1=1  AND $wpdb->posts.post_author IN (1)  AND $wpdb->posts.post_type = 'rt_task' AND (($wpdb->posts.post_status <> 'trash' AND $wpdb->posts.post_status <> 'auto-draft')) AND ( 
  ( $wpdb->postmeta.meta_key = 'post_duedate' AND YEAR($wpdb->postmeta.meta_value) = '$year' AND WEEK($wpdb->postmeta.meta_value,1) = '$week' )
) GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC ";
						break;
				case 'all':
					$sql_query = "SELECT   $wpdb->posts.* FROM $wpdb->posts  INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE 1=1  AND $wpdb->posts.post_author IN (1)  AND $wpdb->posts.post_type = 'rt_task' AND (($wpdb->posts.post_status <> 'trash' AND $wpdb->posts.post_status <> 'auto-draft')) GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC ";
					break;
			}
			return $sql_query;
		}

	}

}