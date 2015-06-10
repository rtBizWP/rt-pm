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
 * Copy of Rt_PM_Task_List_View
 *
 * @author kishore
 */

if( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if ( ! function_exists( 'get_current_screen' ) ){
	if ( ! isset( $GLOBALS['hook_suffix'] ) )
	$GLOBALS['hook_suffix'] = '';
	require_once(ABSPATH . 'wp-admin/includes/screen.php');
}
	
if ( ! function_exists( 'convert_to_screen' ) )
    require_once(ABSPATH . '/wp-admin/includes/template.php');

if ( !class_exists( 'Rt_PM_BP_PM_Task_List_View' ) ) {
	class Rt_PM_BP_PM_Task_List_View extends WP_List_Table {

		var $post_type;
		var $post_statuses;
		var $labels;
        static $project_id_key = 'post_project_id';
        var $user_edit;

		public function __construct( $user_edit ) {

			global $rt_pm_task;
			$this->labels = $rt_pm_task->labels;
			$this->post_type = $rt_pm_task->post_type;
			$this->post_statuses = $rt_pm_task->get_custom_statuses();
			$this->user_edit = $user_edit;
			$args = array(
				'singular'=> $this->labels['singular_name'], //Singular label
				'plural' => $this->labels['all_items'], //plural label, also this well be one of the table css class
				'ajax'	=> true, //We won't support Ajax for this table
				'screen' => get_current_screen(),
			);
			parent::__construct( $args );
		}

		/**
		* Define the columns that are going to be used in the table
		* @return array $columns, the array of columns to use with the table */
		public function get_columns() {

			$columns = array(
				//'cb' => '<input type="checkbox" />',
				'rtpm_title'=> __( 'Title' ),
				'rtpm_task_type'=> __( 'Type' ),
				'rtpm_create_date'=> __( 'Created' ),
				'rtpm_update_date'=> __( 'Updated' ),
				'rtpm_Due_date'=> __( 'Due Date' ),
				'rtpm_status'=> __( 'Status' ),
			);

			return $columns;
		}

		/**
		* Decide which columns to activate the sorting functionality on
		* @return array $sortable, the array of columns that can be sorted by the user */
		public function get_sortable_columns() {
			$sortable = array(
				'rtpm_title'=> array('post_title', false),
				//'rtpm_assignee'=> array('post_author', false),
				'rtpm_create_date'=> array('post_date', false),
				'rtpm_update_date'=> array('post_modified', false),
				'rtpm_Due_date'=> array('post_duedate', false),
				'rtpm_status'=> array('post_status', false),
			);
			return $sortable;
		}


		/**
		 * Prepare the table with different parameters, pagination, columns and table elements */
		function prepare_items() {
			global $wpdb;
            $screen = get_current_screen();

            if ( ! isset( $_REQUEST["{$_REQUEST['post_type']}_id"] ) ){
                return;
            }
            $project_id = $_REQUEST["{$_REQUEST['post_type']}_id"];
           // $this->user_edit = $user_edit;

            $s = @$_POST['s'];
			
			/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result

            $orderby = (! empty( $_GET["orderby"] ))? filter_var($_GET["orderby"], FILTER_SANITIZE_STRING):'ASC';
			$order = ! empty( $_GET["order"] ) ? filter_var($_GET["order"], FILTER_SANITIZE_STRING): '';
			
			/* -- Preparing your query -- */
			$project_id_key = self::$project_id_key;
			
			if( $orderby == 'post_duedate' ){
				$query = "SELECT * FROM $wpdb->posts INNER JOIN $wpdb->postmeta pm ON $wpdb->posts.ID = pm.post_id WHERE $wpdb->posts.post_type = '$this->post_type' AND pm.meta_key='post_duedate' AND pm.post_id IN ( SELECT post_id from $wpdb->postmeta WHERE meta_key = 'post_project_id' AND meta_value = '$project_id' ) ";
			}else{
				$query = "SELECT * FROM $wpdb->posts INNER JOIN $wpdb->postmeta pm ON $wpdb->posts.ID = pm.post_id WHERE $wpdb->posts.post_type = '$this->post_type' AND pm.meta_key='$project_id_key' AND pm.meta_value='$project_id'";
			}
			

            if ( $s ) {
                $query .= " AND ( ";
                $query .= " $wpdb->posts.post_title LIKE '%{$s}%' ";
                $query .= " OR $wpdb->posts.post_content LIKE '%{$s}%' ";
                $query .= ') ';
            }

            if ( isset( $_GET['post_status'] ) ) {
                $query .= " AND $wpdb->posts.post_status = '".$_REQUEST['post_status']."'";
            }

            if ( isset( $_GET['assignee'] ) ) {
                $query .= " AND $wpdb->postmeta.meta_value = '".$_REQUEST['assignee']."'";
                $query .= " AND $wpdb->postmeta.meta_key = 'post_assignee'";
            }

            if ( !current_user_can( rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) . '_' . 'admin') &&  !current_user_can( rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) . '_' . 'editor') && current_user_can( rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) . '_' . 'author') ){
                $query .= " AND $wpdb->postmeta.meta_value = '".get_current_user_id()."'";
                $query .= " AND $wpdb->postmeta.meta_key = 'post_assignee'";
            }

            if ( ! empty( $orderby ) & ! empty( $order ) ) {
				if( $orderby == 'post_duedate' ){
					$query.=' ORDER BY meta_value '.$order;
				}else{
					$query.=' ORDER BY '.$orderby.' '.$order;
				}
			}
			/* -- Pagination parameters -- */
			//Number of elements in your table?
			$totalitems = $wpdb->query( $query );
			//return the total number of affected rows

			//How many to display per page?
			// set through session variable
			$user_id = get_current_user_id();
			$user_post_per_page = get_user_meta($user_id,'rt_task_per_page',true);
			if( isset($user_post_per_page) ){
				$perpage = $user_post_per_page;
			}else{
				$perpage = 25;
			}
			//$perpage = $this->get_items_per_page( rtpm_post_type_name( $this->labels['name'] ).'_per_page', 25 );

			//Which page is this?
			$paged = ! empty( $_GET['paged'] ) ?  $_GET['paged'] : '';
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
		public function print_column_headers($with_id = true) {
			if( isset( $_GET['orderby'] ) ) {
                $args['orderby'] = $_GET['orderby'];
                $args['order'] =  $_GET['order'];
        	}

			$columns = array(
		        array(
		                'column_label' => __( 'Title', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => true,
		                'orderby' => 'post_title',
		                'order' => 'asc'
		        ),
		        array(
		                'column_label' => __( 'Type', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => false,
		        ),
		        array(
		                'column_label' => __( 'Created', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => true,
		                'orderby' => 'post_date',
		                'order' => 'asc'
		              
		        ),
		        array(
		                'column_label' => __( 'Updated', RT_PM_TEXT_DOMAIN ),
		                'sortable' => true,
		                'orderby' => 'post_modified',
		                'order' => 'asc'
		        ),
		        array(
		                'column_label' => __( 'Due Date', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => true,
		                'orderby' => 'post_duedate',
		                'order' => 'asc'
		        ),
		        array(
		                'column_label' => __( 'Status', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => true,
		                'orderby' => 'post_status',
		                'order' => 'asc'
		        ),
		        /*array(
		                'column_label' => __( 'Created By', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => false,
		                //'orderby' => 'post_duedate',
		               // 'order' => 'asc'
		        ),
		        array(
		                'column_label' => __( 'Updated By', RT_PM_TEXT_DOMAIN ) ,
		                'sortable' => false,
		                //'orderby' => 'post_duedate',
		                //'order' => 'asc'
		        ),*/
		
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
			?>
			<table class="responsive">
				<thead>
				<tr>
					<?php $this->print_column_headers('true'); ?>
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

            global $wpdb,$rt_pm_project, $rt_pm_bp_pm, $rt_pm_task;

            $blog_date_format = get_option( 'date_format' );
			//Get the records registered in the prepare_items method
			$records = $this->items;
			//Get the columns registered in the get_columns and get_sortable_columns methods
			list( $columns, $hidden, $sortable ) = $this->get_column_info();
			//Loop for each record
			$i = 0;
			if ( ! empty( $records ) ) {
				foreach( $records as $rec ) {
					//Open the line
                    $query = "SELECT * FROM $wpdb->postmeta m1 WHERE m1.post_id = $rec->ID";
                    $task_meta = $wpdb->get_results( $query );
                    $temp=null;
                    foreach ( $task_meta as $meta ){
                        $temp[$meta->meta_key]=$meta->meta_value;
                    }
					echo '<tr id="record_'.$rec->ID.'" class="'.(($i%2)?'alternate':'').'">';
					foreach ( $columns as $column_name => $column_display_name ) {

						$class = "class='$column_name column-$column_name'";
						$style = "";
						if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
						$attributes = $class . $style;

						//Display the cell
						switch ( $column_name ) {
							case "cb":
								echo '<th scope="row" class="check-column">';
									echo '<input type="checkbox" name="'.$this->post_type.'[]" id="cb-select-'.$rec->ID.'" value="'.$rec->ID.'" />';
								echo '</th>';
								break;
							case "rtpm_title":
                                if ($this->user_edit){
								    echo '<td '.$attributes.'><span class="rtpm_readmore">'.'<a href="'. $rt_pm_bp_pm->get_component_root_url().bp_current_action() .'?post_type='. $rt_pm_project->post_type.'&'.$rt_pm_project->post_type.'_id='.$_REQUEST["{$rt_pm_project->post_type}_id"] .'&tab='.$rt_pm_project->post_type .'-task&'.$this->post_type.'_id='.$rec->ID.'"'.'">'.$rec->post_title.'</a></span>';
                                }else{
                                    echo '<td '.$attributes.'><span class="rtpm_readmore">'.$rec->post_title.'</span>';
                                }
                                if ( $rec->post_status !='trash'){
                                    $actions = array(
                                        'edit'      => '<a class="rtpm_task_edit" href="'. $rt_pm_bp_pm->get_component_root_url().bp_current_action() .'?post_type='. $rt_pm_project->post_type.'&'.$rt_pm_project->post_type.'_id='.$_REQUEST["{$rt_pm_project->post_type}_id"] .'&tab='.$rt_pm_project->post_type .'-task&'.$this->post_type.'_id='.$rec->ID.'"'.'">Edit</a>',
                                        'timeentry'    => '<a class="rtpm_task_timeentries" href="'.$rt_pm_bp_pm->get_component_root_url() .'time-entries?post_type='. $rt_pm_project->post_type.'&'.$rt_pm_project->post_type.'_id='.$_REQUEST["{$rt_pm_project->post_type}_id"].'&tab='.$rt_pm_project->post_type .'-timeentry&task_id='.$rec->ID.'&action=timeentry">Time</a>',
                                        'delete'    => '<a class="deletepostlink" href="'. $rt_pm_bp_pm->get_component_root_url().bp_current_action() .'?post_type='. $rt_pm_project->post_type.'&'.$rt_pm_project->post_type.'_id='.$_REQUEST["{$rt_pm_project->post_type}_id"] .'&tab='.$rt_pm_project->post_type .'-task&'.$this->post_type.'_id='.$rec->ID .'&action=trash'.'">Delete</a>',
                                    );
                                }else{
                                    $actions = array(
                                        'restore'    => '<a href="'. $rt_pm_bp_pm->get_component_root_url().bp_current_action() .'?post_type='. $rt_pm_project->post_type.'&'.$rt_pm_project->post_type.'_id='.$_REQUEST["{$rt_pm_project->post_type}_id"] .'&tab='.$rt_pm_project->post_type .'-task&'.$this->post_type.'_id='.$rec->ID .'&action=restore'.'">Restore</a>',
                                    	'delete'    => '<a class="deletepostlink" href="'. $rt_pm_bp_pm->get_component_root_url().bp_current_action() .'?post_type='. $rt_pm_project->post_type.'&'.$rt_pm_project->post_type.'_id='.$_REQUEST["{$rt_pm_project->post_type}_id"] .'&tab='.$rt_pm_project->post_type .'-task&'.$this->post_type.'_id='.$rec->ID .'&action=delete'.'">Delete</a>',
                                    );
                                }
                                if ($this->user_edit){
								    echo $this->row_actions( $actions );
                                }
								//.'< /td>';
								break;
                            case "rtpm_task_type":

								$task_type = $rt_pm_task->rtpm_get_task_type_label( $rec->ID );

								if( ! empty( $task_type ) ) {

                                        echo '<td '.$attributes.'><span>'.$task_type.'</span>';
                                } else {
                                    echo '<td '.$attributes.'>-';
                                }
                                //.'< /td>';
                                break;
                            case "rtpm_create_date":
                                $date = date_parse($rec->post_date);
                                if(checkdate($date['month'], $date['day'], $date['year'])) {
                                    $dtObj = rt_convert_strdate_to_usertimestamp($rec->post_date_gmt);
                                    // echo '<td '.$attributes.'><span title="'.$rec->post_date.'" class="moment-from-now">' . $rec->post_date . '</span>';
									echo '<td '.$attributes.'><span title="'.$rec->post_date.'">' .  $dtObj->format( $blog_date_format ) . '</span>';
                                } else {
                                    echo '<td '.$attributes.'>-';
                                }
                                //.'< /td>';
                                break;
                            case "rtpm_update_date":
                                $date = date_parse($rec->post_modified);
                                if(checkdate($date['month'], $date['day'], $date['year'])) {
                                    $dtObj = rt_convert_strdate_to_usertimestamp($rec->post_modified_gmt);

									echo '<td '.$attributes.'><span title="'.$rec->post_modified.'">' .  $dtObj->format( $blog_date_format )   . '</span>';
                                } else {
                                    echo '<td '.$attributes.'>-';
                                }
                                //.'< /td>';
                                break;
                            case "rtpm_Due_date":
                                $date = date_parse($temp['post_duedate']);
                                if(checkdate($date['month'], $date['day'], $date['year'])) {
                                    $dtObj = rt_convert_strdate_to_usertimestamp($temp['post_duedate']);
                                    // echo '<td '.$attributes.'><span title="'.$temp['post_duedate'].'" class="moment-from-now">' . $temp['post_duedate'] . '</span>';
									echo '<td '.$attributes.'><span title="'.$temp['post_duedate'].'">'. $dtObj->format( $blog_date_format ) . '</span>';
                                } else {
                                    echo '<td '.$attributes.'>-';
                                }
                                //.'< /td>';
                                break;
                            case "rtpm_status":
                                if(!empty($rec->post_status)) {
                                    $status = $this->get_status_label($rec->post_status);
                                     $url = $rt_pm_bp_pm->get_component_root_url().bp_current_action() .'?post_type='. $rt_pm_project->post_type.'&'.$rt_pm_project->post_type.'_id='.$_REQUEST["{$rt_pm_project->post_type}_id"] .'&tab='.$rt_pm_project->post_type .'-task';
                                    $url = add_query_arg( 'post_status', $rec->post_status, $url );
                                    if ( !empty( $status ) ) {
                                        if ($this->user_edit){
                                            echo '<td '.$attributes.'><a href="'.$url.'">'.$status['name'].'</a>';
                                        }else{
                                            echo '<td '.$attributes.'>'.$status['name'];
                                        }
                                    } else {
                                        if ($this->user_edit){
                                            echo '<td '.$attributes.'><a href="'.$url.'">'.$rec->post_status.'</a>';
                                        }else{
                                            echo '<td '.$attributes.'>'.$rec->post_status;
                                        }
                                    }
                                } else {
                                    echo '<td '.$attributes.'>-';
                                }
                                //.'< /td>';
                                break;
                            case "rtpm_created_by":
                                if(!empty($rec->post_author)) {
                                    $user = get_user_by('id', $rec->post_author);
                                    $url = admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$_REQUEST["{$rt_pm_project->post_type}_id"]}&tab={$rt_pm_project->post_type}-task&{$this->post_type}_id={$rec->ID}");
                                    $url = add_query_arg( 'user_created_by', $rec->post_author, $url );
                                    echo '<td '.$attributes.'>'.$user->display_name;
                                    //echo '<td '.$attributes.'><a href="'.$url.'">'.$user->display_name.'</a>';
                                } else
                                    echo '<td '.$attributes.'>-';
                                //.'< /td>';
                                break;
                            case "rtpm_updated_by":
                                if(!empty($temp['user_updated_by'])) {
                                    $user = get_user_by('id', $temp['user_updated_by']);
                                    $url = admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$_REQUEST["{$rt_pm_project->post_type}_id"]}&tab={$rt_pm_project->post_type}-task&{$this->post_type}_id={$rec->ID}");
                                    $url = add_query_arg( 'user_updated_by  ', $temp['user_updated_by'], $url );
                                    echo '<td '.$attributes.'>'.$user->display_name;
                                    //echo '<td '.$attributes.'><a href="'.$url.'">'.$user->display_name.'</a>';
                                } else
                                    echo '<td '.$attributes.'>-';
                                //.'< /td>';
                                break;
						}
					}
				}
			}
		}

        function get_drop_down($task_Id=0){
            $records = $this->items;
            echo '<select required="required" name="post[post_task_id]" id="task_id">';
            if ( ! empty( $records ) ) {
                foreach( $records as $rec ) {
                    $selected = $task_Id == $rec->ID ? 'selected="selectet"' : '';
                    echo '<option value="'.$rec->ID.'" '.$selected.' >'.$rec->post_title.'</option>';
                }
            }
            echo '</select>';
        }
	}
}
