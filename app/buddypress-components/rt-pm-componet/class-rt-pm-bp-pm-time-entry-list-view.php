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
 * Copy of Rt_PM_Time_Entry_List_View
 *
 * @author kishore
 */

if( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	
if ( ! function_exists( 'get_current_screen' ) )
    require_once(ABSPATH . 'wp-admin/includes/screen.php');
	
if ( ! function_exists( 'convert_to_screen' ) )
    require_once(ABSPATH . '/wp-admin/includes/template.php');

if ( !class_exists( 'Rt_PM_BP_PM_Time_Entry_List_View' ) ) {
	class Rt_PM_BP_PM_Time_Entry_List_View extends WP_List_Table {

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
				//'rtpm_title'=> array('task_id', false),
				'rtpm_task_id'=> array('task_id', false),
				'rtpm_time_entry_type' => array('type', false),
				'rtpm_message' => array('message', false),
				'rtpm_create_date' => array('timestamp', false),
				'rtpm_Duration'=> array('time_duration', false),
				'rtpm_created_by' => array('author', false),
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
				//'delete' => __( 'Trash' ),
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
			$perpage = 5;

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
					// Set ordering values if needed (useful for AJAX)
			        'orderby'   => ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'rtpm_message',
			        'order'     => ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'asc'
    
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
			$perpage = 5;

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
		 * Display the pagination.
		 *
		 * @since 3.1.0
		 * @access protected
		 */
		protected function pagination( $which ) {
			if ( empty( $this->_pagination_args ) ) {
				return;
			}
	
			$total_items = $this->_pagination_args['total_items'];
			$total_pages = $this->_pagination_args['total_pages'];
			$infinite_scroll = false;
			if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
				$infinite_scroll = $this->_pagination_args['infinite_scroll'];
			}
	
			$output = '';
	
			$current = $this->get_pagenum();
	
			$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	
			$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );
	
			$page_links = array();
	
			$disable_first = $disable_last = '';
			if ( $current == 1 ) {
				$disable_first = ' disabled';
			}
			if ( $current == $total_pages ) {
				$disable_last = ' disabled';
			}
			/*$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
				'first-page' . $disable_first,
				esc_attr__( 'Go to the first page' ),
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				'&laquo;'
			);*/
	
			if ( $current != 1  ){
				$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
					'prev prev-page' . $disable_first,
					esc_attr__( 'Go to the previous page' ),
					esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
					'&lsaquo; Previous'
				);
			}
	
			if ( 'bottom' == $which ) {
				$html_current_page = $current;
			} else {
				$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' title='%s' type='text' name='paged' value='%s' size='%d' />",
					'<label for="current-page-selector" class="screen-reader-text">' . __( 'Select Page' ) . '</label>',
					esc_attr__( 'Current page' ),
					$current,
					strlen( $total_pages )
				);
			}
			$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
			
			
			$total_pages_count = $total_pages;
			$pages_count =1;
			while ( $pages_count <= $total_pages && $pages_count > 0) {
				if ($pages_count == $current) {
					$page_links[] = '<span class="page-numbers current">' . sprintf( _x( '%1$s', 'paging' ), $html_current_page ) . '</span>';
				} else {
					$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
						'page-numbers' . $disable_last,
						esc_attr__( 'Go to the next page' ),
						esc_url( add_query_arg( 'paged', min( $total_pages, $pages_count ), $current_url ) ),
						$pages_count
					);
				}
				
				$pages_count++;
			}
	
			if ( $total_pages != $current ){
				$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
					'page-numbers' . $disable_last,
					esc_attr__( 'Go to the next page' ),
					esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
					'Next &rsaquo;'
				);
			}
	
			/*$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
				'next page-numbers' . $disable_last,
				esc_attr__( 'Go to the last page' ),
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				'&raquo;'
			);*/
	
			$pagination_links_class = 'pagination-links';
			if ( ! empty( $infinite_scroll ) ) {
				$pagination_links_class = ' hide-if-js';
			}
			$output .= "\n" . join( "\n", $page_links );
	
			if ( $total_pages ) {
				$page_class = $total_pages < 2 ? ' one-page' : '';
			} else {
				$page_class = ' no-pages';
			}
			$this->_pagination = "<div class='projects-lists pagination role='menubar' aria-label='Pagination'><span class='current'>Page $current of $total_pages</span>$output</div>";
	
			if ( $total_pages > 1 ){
				echo $this->_pagination;
			}
	   
		}
			
		
		
		/**
		 * Print column headers, accounting for hidden and sortable columns.
		 *
		 * @since 3.1.0
		 * @access public
		 *
		 * @param bool $with_id Whether to set the id attribute or not
		 */
		public function print_column_headers() {
			if( isset( $_GET['orderby'] ) ) {
                $args['orderby'] = $_GET['orderby'];
                $args['order'] =  $_GET['order'];
        	}
			
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
			$sortable = array(
				//'rtpm_title'=> array('task_id', false),
				'rtpm_task_id'=> array('task_id', false),
				'rtpm_time_entry_type' => array('type', false),
				'rtpm_message' => array('message', false),
				'rtpm_create_date' => array('timestamp', false),
				'rtpm_Duration'=> array('time_duration', false),
				'rtpm_created_by' => array('author', false),
			);
			$columns = array(
		        array(
		                'column_label' => __( 'Time Entry Message', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => true,
		                'orderby' => 'message',
		                'order' => 'asc'
		        ),
		        array(
		                'column_label' => __( 'Task', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => true,
		                'orderby' => 'task_id',
		                'order' => 'asc'
		        ),
		        array(
		                'column_label' => __( 'Type', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => true,
		                'orderby' => 'type',
		                'order' => 'asc'
		              
		        ),
		        array(
		                'column_label' => __( 'Date', RT_PM_TEXT_DOMAIN ),
		                'sortable' => true,
		                'orderby' => 'timestamp',
		                'order' => 'asc'
		        ),
		        array(
		                'column_label' => __( 'Duration', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => true,
		                'orderby' => 'time_duration',
		                'order' => 'asc'
		        ),
		        array(
		                'column_label' => __( 'Logged By', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => true,
		                'orderby' => 'author',
		                'order' => 'asc'
		        ),
		
			);
			
			foreach ( $columns as $column ) { 
                ?>
                <th>
                    <?php
                    if(  $column['sortable']  ) {

                            if ( isset( $_GET['orderby'] ) && $column['orderby']  == $_GET['orderby'] ) {
                               
                                $current_order = $_GET['order'];
                               
                                $order = 'asc' == $current_order ? 'desc' : 'asc';
                                
                                printf( __('<a href="%s">%s <i class="fa fa-sort-%s"></i> </a>'), esc_url( add_query_arg( array( 'orderby' => $column['orderby'] ,'order' => $order ) ) ), $column['column_label'], $order );
                                
                            }else{
                                  printf( __('<a href="%s">%s <i class="fa fa-sort"></i> </a>'), esc_url( add_query_arg( array( 'orderby' => $column['orderby'] ,'order' => 'desc' ) ) ), $column['column_label'] );
                            }
                          
                    }else{
                            echo $column['column_label'];
                    }

                    ?>
                </th>
                <?php
            }
		}
		
		/**
		 * Display the table
		 *
		 * @since 3.1.0
		 * @access public
		 */
		public function display() {
			$singular = $this->_args['singular'];
			?>
			<table class="responsive">
				<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
				</thead>
			
				<tbody>
					<?php $this->display_rows_or_placeholder(); ?>
				</tbody>
			</table>
			<?php
			$this->display_tablenav( 'bottom' );
		}

		/**
		 * Display the rows of records in the table
		 * @return string, echo the markup of the rows */
		function display_rows() {

            global $wpdb,$rt_pm_project,$rt_pm_bp_pm,$rt_pm_task,$rt_pm_time_entries;

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
								echo '<td '.$attributes.'><span class="rtpm_readmore">'.'<a href="'.add_query_arg( $rt_pm_task->post_type.'_id', $rec->task_id ).'">'. get_the_title( $rec->task_id ).'</a></span>';
								//.'< /td>';
								break;
							case "rtpm_project_id":
								echo '<td '.$attributes.'>'.'<a href="'.add_query_arg( $rt_pm_project->post_type.'_id', $rec->project_id ).'">'. get_the_title( $rec->project_id ).'</a>';
								//.'< /td>';
								break;
                            case "rtpm_message":
                                echo '<td '.$attributes.'><span class="rtpm_readmore">'.$rec->message .'</span>';
                                $actions = array(
                                    'edit'      => '<a href="'.$rt_pm_bp_pm->get_component_root_url().bp_current_action() .'?post_type='.$rt_pm_project->post_type.'&'.$rt_pm_project->post_type.'_id='.$rec->project_id.'&tab='.$rt_pm_project->post_type.'-timeentry&'.$this->post_type.'_id='.$rec->id.'"'.'">Edit</a>',
                                	'delete'    => '<a class="deletepostlink" href="'.$rt_pm_bp_pm->get_component_root_url().bp_current_action() .'?post_type='.$rt_pm_project->post_type.'&'.$rt_pm_project->post_type.'_id='.$rec->project_id.'&tab='.$rt_pm_project->post_type.'-timeentry&'.$this->post_type.'_id='.$rec->id.'&action=delete">Delete</a>',
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
