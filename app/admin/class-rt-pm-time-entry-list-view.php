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
 * Description of Rt_PM_Time_Entry_List_View
 *
 * @author udit
 */

if( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


if ( !class_exists( 'Rt_PM_Time_Entry_List_View' ) ) {
	class Rt_PM_Time_Entry_List_View extends WP_List_Table {

        var $table_name;
        var $post_type;
		var $post_statuses;
		var $labels;

		var $global_report;

		public function __construct( $global_report = false ) {

			global $rt_pm_time_entries;
			$this->table_name = rtpm_get_time_entry_table_name();
            $this->labels = $rt_pm_time_entries->labels;
			$this->post_type = Rt_PM_Time_Entries::$post_type;
			$this->global_report = $global_report;
			$args = array(
				'singular'=> $this->labels['singular_name'], //Singular label
				'plural' => $this->labels['all_items'], //plural label, also this well be one of the table css class
				'ajax'	=> true, //We won't support Ajax for this table
				'screen' => get_current_screen(),
			);
			parent::__construct( $args );
		}

		/**
		* Add extra markup in the toolbars before or after the list
		* @param string $which, helps you decide if you add the markupafter (bottom) or before (top) the list */
		function extra_tablenav( $which ) {
			$search = @$_POST['s'] ? esc_attr( $_POST['s'] ) : '';
			if ( $which == 'top' ) {
				//The code that goes before the table is here
//				echo"Before the table";
				//$this->search_box( 'Search', 'search_id' );
			}
			if ( $which == 'bottom' ) {
				//The code that goes after the table is there
//				echo"After the table";
			}
		}


		/**
		* Define the columns that are going to be used in the table
		* @return array $columns, the array of columns to use with the table */
		public function get_columns() {

			$columns = array(
				//'cb' => '<input type="checkbox" />',
				'rtpm_message'=> __( 'Time Entry Message' ),
                'rtpm_task_id'=> __( 'Task' ),
				'rtpm_time_entry_type' => __( 'Type' ),
                'rtpm_create_date'=> __( 'Date' ),
				'rtpm_Duration'=> __( 'Duration' ),
				'rtpm_created_by'=> __( 'Logged By' ),
			);

			if ( $this->global_report ) {
				$columns = array_slice( $columns, 0, 3, true ) + array(	'rtpm_project_id' => __( 'Project' ) ) + array_slice( $columns, 3, count( $columns )-1, true );
			}

			return $columns;
		}

		/**
		* Decide which columns to activate the sorting functionality on
		* @return array $sortable, the array of columns that can be sorted by the user */
		public function get_sortable_columns() {
			$sortable = array(
				'rtpm_title'=> array('task_id', false),
				'rtpm_create_date'=> array('timestamp', false),
				'rtpm_created_by'=> array('author', false),
			);
			return $sortable;
		}

		/**
		 * Get an associative array ( option_name => option_title ) with the list
		 * of bulk actions available on this table.
		 *
		 * @since 3.1.0
		 * @access protected
		 *
		 * @return array
		 */
		function get_bulk_actions() {
			$actions = array(
				'delete' => __( 'Trash' ),
			);
			return $actions;
		}

		/**
		 * Prepare the table with different parameters, pagination, columns and table elements */
		function prepare_items() {
			global $wpdb,$rt_pm_task;

            if ( ! isset( $_REQUEST["{$_REQUEST['post_type']}_id"] ) ){
                return;
            }
            $project_id = $_REQUEST["{$_REQUEST['post_type']}_id"];

            $task_post_type=$rt_pm_task->post_type;


			/* -- Preparing your query -- */
            $query = "SELECT * FROM $this->table_name WHERE project_id = $project_id";

            if ( isset( $_REQUEST["{$task_post_type}_id"] ) ) {
                $query .= " AND task_id = '".$_REQUEST["{$task_post_type}_id"]."'";
            }

			if ( isset( $_REQUEST['time_entry_type'] ) ) {
				$query .= " AND type = '".$_REQUEST['time_entry_type']."'";
			}

            if ( isset( $_REQUEST["post_author"] ) ) {
                $query .= " AND author = '".$_REQUEST["post_author"]."'";
            }

            if ( !current_user_can( rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) . '_' . 'admin') &&  !current_user_can( rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) . '_' . 'editor') && current_user_can( rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) . '_' . 'author') ){
                $query .= " AND author = '".get_current_user_id()."'";
            }

			/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result

            $orderby = (! empty( $_GET["orderby"] ))? filter_var($_GET["orderby"], FILTER_SANITIZE_STRING):'ASC';
			$order = ! empty( $_GET["order"] ) ? filter_var($_GET["order"], FILTER_SANITIZE_STRING): '';

            if ( ! empty( $orderby ) & ! empty( $order ) ) {
				$query.=' ORDER BY '.$orderby.' '.$order;
			}

			/* -- Pagination parameters -- */
			//Number of elements in your table?
			$totalitems = $wpdb->query( $query );
			//return the total number of affected rows

			//How many to display per page?
			//$perpage = 25;
			$perpage = 25;

			//Which page is this?
			$paged = ! empty( $_GET['paged'] ) ? mysql_real_escape_string( $_GET['paged'] ) : '';
			//Page Number
			if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) { $paged=1; }
			//How many pages do we have in total?
			$totalpages = ceil( $totalitems / $perpage );
			//adjust the query to take pagination into account
			if ( ! empty( $paged ) && ! empty( $perpage ) ) {
				$offset = ( $paged - 1 ) * $perpage;
				$query .= ' LIMIT ' . (int) $offset . ' , ' . (int) $perpage;
			}

			/* -- Register the pagination -- */
			$this->set_pagination_args(
				array(
					'total_items' => $totalitems,
					'total_pages' => $totalpages,
					'per_page' => $perpage,
				)
			);
			//The pagination links are automatically built according to those parameters

			/* -- Register the Columns -- */
            $columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);

			/* -- Fetch the items -- */
            $this->items = $wpdb->get_results( $query );
		}

		/**
		 * Prepare the table with different parameters, pagination, columns and table elements */
		function prepare_global_items() {
			global $wpdb,$rt_pm_task;

            $task_post_type=$rt_pm_task->post_type;

			/* -- Preparing your query -- */
            $query = "SELECT * FROM $this->table_name WHERE 1=1";

			if ( ! empty( $_REQUEST['rtpm_user'] ) ) {
				$query .= " AND author = '".$_REQUEST["rtpm_user"]."'";
			}

			if ( ! empty( $_REQUEST['rtpm_time_entry_type'] ) ) {
				$query .= " AND type = '".$_REQUEST['rtpm_time_entry_type']."'";
			}

			if ( ! empty( $_REQUEST['rtpm_project'] ) ) {
				$query .= " AND project_id = '".$_REQUEST['rtpm_project']."'";
			}

			/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result

            $orderby = (! empty( $_GET["orderby"] ))? filter_var($_GET["orderby"], FILTER_SANITIZE_STRING):'ASC';
			$order = ! empty( $_GET["order"] ) ? filter_var($_GET["order"], FILTER_SANITIZE_STRING): '';

            if ( ! empty( $orderby ) & ! empty( $order ) ) {
				$query.=' ORDER BY '.$orderby.' '.$order;
			}

			/* -- Pagination parameters -- */
			//Number of elements in your table?
			$totalitems = $wpdb->query( $query );
			//return the total number of affected rows

			//How many to display per page?
			//$perpage = 25;
			$perpage = 25;

			//Which page is this?
			$paged = ! empty( $_GET['paged'] ) ? mysql_real_escape_string( $_GET['paged'] ) : '';
			//Page Number
			if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) { $paged=1; }
			//How many pages do we have in total?
			$totalpages = ceil( $totalitems / $perpage );
			//adjust the query to take pagination into account
			if ( ! empty( $paged ) && ! empty( $perpage ) ) {
				$offset = ( $paged - 1 ) * $perpage;
				$query .= ' LIMIT ' . (int) $offset . ' , ' . (int) $perpage;
			}

			/* -- Register the pagination -- */
			$this->set_pagination_args(
				array(
					'total_items' => $totalitems,
					'total_pages' => $totalpages,
					'per_page' => $perpage,
				)
			);
			//The pagination links are automatically built according to those parameters

			/* -- Register the Columns -- */
            $columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);

			/* -- Fetch the items -- */
            $this->items = $wpdb->get_results( $query );
		}

		/**
		 *
		 */
		function get_status_label( $slug ) {
			foreach ($this->post_statuses as $status) {
				if ( $slug === $status['slug'] ) {
					return $status;
				}
			}
			return false;
		}

		/**
		 * Display the rows of records in the table
		 * @return string, echo the markup of the rows */
		function display_rows() {

            global $wpdb,$rt_pm_project,$rt_pm_task,$rt_pm_time_entries;

			//Get the records registered in the prepare_items method
			$records = $this->items;
			//Get the columns registered in the get_columns and get_sortable_columns methods
			list( $columns, $hidden, $sortable ) = $this->get_column_info();
			//Loop for each record
			$i = 0;
			if ( ! empty( $records ) ) {
				foreach( $records as $rec ) {
					//Open the line
					echo '<tr id="record_'.$rec->id.'" class="'.(($i%2)?'alternate':'').'">';
					foreach ( $columns as $column_name => $column_display_name ) {

						$class = "class='$column_name column-$column_name'";
						$style = "";
						if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
						$attributes = $class . $style;

						//Display the cell
						switch ( $column_name ) {
							case "cb":
								echo '<th scope="row" class="check-column">';
									echo '<input type="checkbox" name="'.$this->post_type.'[]" id="cb-select-'.$rec->id.'" value="'.$rec->id.'" />';
								echo '</th>';
								break;
							case "rtpm_task_id":
								echo '<td '.$attributes.'>'.'<a href="'.add_query_arg( $rt_pm_task->post_type.'_id', $rec->task_id ).'">'. get_the_title( $rec->task_id ).'</a>';
								//.'< /td>';
								break;
							case "rtpm_project_id":
								echo '<td '.$attributes.'>'.'<a href="'.add_query_arg( $rt_pm_project->post_type.'_id', $rec->project_id ).'">'. get_the_title( $rec->project_id ).'</a>';
								//.'< /td>';
								break;
                            case "rtpm_message":
                                echo '<td '.$attributes.'>'.$rec->message;
                                $actions = array(
                                    'edit'      => '<a href="'.admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$rec->project_id}&tab={$rt_pm_project->post_type}-timeentry&{$this->post_type}_id={$rec->id}").'">Edit</a>',
                                    'delete'    => '<a href="'.admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$rec->project_id}&tab={$rt_pm_project->post_type}-timeentry&{$this->post_type}_id={$rec->id}&action=delete").'">Delete</a>',
                                );
                                echo $this->row_actions( $actions );
                                //.'< /td>';
								break;
							case 'rtpm_time_entry_type':
								$term = get_term_by( 'slug', $rec->type, Rt_PM_Time_Entry_Type::$time_entry_type_tax );
								if ( $term ) {
									echo '<td '.$attributes.'>'.'<a href="'.add_query_arg( 'time_entry_type', $rec->type ).'">'.$term->name.'</a>';
								} else {
									echo '<td '.$attributes.'>-';
								}
								break;
                            case "rtpm_create_date":
                                $date = date_parse($rec->timestamp);
                                if(checkdate($date['month'], $date['day'], $date['year'])) {
                                    $dtObj = new DateTime($rec->timestamp);
                                    echo '<td '.$attributes.'><span title="'.$rec->timestamp.'">' . human_time_diff( $dtObj->format('U') , time() ) . __(' ago') . '</span>';
                                } else {
                                    echo '<td '.$attributes.'>-';
                                }
                                //.'< /td>';
                                break;
                            case "rtpm_Duration":
                                if(!empty($rec->time_duration)) {
                                    echo '<td '.$attributes.'><span title="'.$rec->time_duration.'">'. $rt_pm_time_entries->get_timer($rec->time_duration) .'</span>';
                                } else
                                    echo '<td '.$attributes.'>-';
                                //.'< /td>';
                                break;
                            case "rtpm_created_by":
                                if(!empty($rec->author)) {
                                    $user = get_user_by('id', $rec->author);
                                    $url = add_query_arg( 'post_author', $rec->author );
                                    echo '<td '.$attributes.'><a href="'.$url.'">'.$user->display_name.'</a>';
                                } else
                                    echo '<td '.$attributes.'>-';
                                //.'< /td>';
                                break;
						}
					}
				}
			}
		}
	}
}
