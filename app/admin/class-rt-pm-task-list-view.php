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
 * Description of Rt_PM_Task_List_View
 *
 * @author udit
 */

if( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if ( ! function_exists( 'get_current_screen' ) )
    require_once(ABSPATH . 'wp-admin/includes/screen.php');
	
if ( ! function_exists( 'convert_to_screen' ) )
    require_once(ABSPATH . '/wp-admin/includes/template.php');

if ( !class_exists( 'Rt_PM_Task_List_View' ) ) {
	class Rt_PM_Task_List_View extends WP_List_Table {

		var $post_type;
		var $post_statuses;
		var $labels;
        static $project_id_key = 'post_project_id';
        var $user_edit;
        var $is_trash;

		public function __construct( $user_edit ) {

			global $rt_pm_task;
			$this->labels = $rt_pm_task->labels;
			$this->post_type = $rt_pm_task->post_type;
			$this->post_statuses = $rt_pm_task->get_custom_statuses();
			$this->user_edit = $user_edit;
			$args = array(
				'singular'=> $this->labels['singular_name'], //Singular label
				'plural' => 'posts', //plural label, also this well be one of the table css class
				'ajax'	=> true, //We won't support Ajax for this table
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
				'cb' => '<input type="checkbox" />',
				'rtpm_title'=> __( 'Title' ),
				'rtpm_task_type'=> __( 'Type' ),
				'rtpm_create_date'=> __( 'Created' ),
				'rtpm_update_date'=> __( 'Last Updated' ),
				'rtpm_Due_date'=> __( 'Due Date' ),
				'rtpm_status'=> __( 'Status' ),
				'rtpm_created_by'=> __( 'Created By' ),
				'rtpm_updated_by'=> __( 'Last Updated By' ),
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
				'rtpm_status'=> array('post_status', false),
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
            global $rt_pm_task;
            $post_type_obj = get_post_type_object( $rt_pm_task->post_type );

            $actions = array();

            if ( $this->is_trash ) {
                $actions['untrash'] = __( 'Restore' );
            }

            if ( current_user_can( $post_type_obj->cap->delete_posts ) ) {
                if ( $this->is_trash || ! EMPTY_TRASH_DAYS ) {
                    $actions['delete'] = __( 'Delete Permanently' );
                } else {
                    $actions['trash'] = __( 'Move to Trash' );
                }
            }


			return $actions;
		}

        /**
         * Display the list of views available on this table.
         * @return array
         */
        protected function get_views() {
            global $locked_post_status, $rt_pm_task;

            $post_type = $rt_pm_task->post_type;

            if ( !empty($locked_post_status) )
                return array();

            $status_links = array();
            $project_id = $_REQUEST['rt_project_id'];
            $num_posts = $this->rtpm_count_posts( $project_id, 'readable' );

            $class = '';
            $allposts = '';

            $current_user_id = get_current_user_id();


            if ( $this->user_posts_count ) {
                if ( isset( $_GET['author'] ) && ( $_GET['author'] == $current_user_id ) )
                    $class = ' class="current"';
                $status_links['mine'] = "<a href='edit.php?post_type=$post_type&author=$current_user_id'$class>" . sprintf( _nx( 'Mine <span class="count">(%s)</span>', 'Mine <span class="count">(%s)</span>', $this->user_posts_count, 'posts' ), number_format_i18n( $this->user_posts_count ) ) . '</a>';
                $allposts = '&all_posts=1';
                $class = '';
            }

            $total_posts = array_sum( (array) $num_posts );

            // Subtract post types that are not included in the admin all list.
            foreach ( get_post_stati( array('show_in_admin_all_list' => false) ) as $state )
                $total_posts -= $num_posts->$state;

            if ( empty( $class ) && ! isset( $_REQUEST['post_status'] ) && ! $this->user_posts_count ) {
                $class =  ' class="current"';
            }

            $all_inner_html = sprintf(
                _nx(
                    'All <span class="count">(%s)</span>',
                    'All <span class="count">(%s)</span>',
                    $total_posts,
                    'posts'
                ),
                number_format_i18n( $total_posts )
            );

            $url = remove_query_arg( 'post_status' );
            $status_links['all'] = "<a href='$url'$class>" . $all_inner_html . '</a>';

            foreach ( get_post_stati(array('show_in_admin_status_list' => true), 'objects') as $status ) {
                $class = '';

                $status_name = $status->name;

                if ( empty( $num_posts->$status_name ) )
                    continue;

                if ( isset($_REQUEST['post_status']) && $status_name == $_REQUEST['post_status'] )
                    $class = ' class="current"';

                $url = add_query_arg( array( 'post_status' => $status_name ) );
                $status_links[$status_name] = "<a href='$url'$class>" . sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
            }

            if ( ! empty( $this->sticky_posts_count ) ) {
                $class = ! empty( $_REQUEST['show_sticky'] ) ? ' class="current"' : '';

                $sticky_link = array( 'sticky' => "<a href='edit.php?post_type=$post_type&amp;show_sticky=1'$class>" . sprintf( _nx( 'Sticky <span class="count">(%s)</span>', 'Sticky <span class="count">(%s)</span>', $this->sticky_posts_count, 'posts' ), number_format_i18n( $this->sticky_posts_count ) ) . '</a>' );

                // Sticky comes after Publish, or if not listed, after All.
                $split = 1 + array_search( ( isset( $status_links['publish'] ) ? 'publish' : 'all' ), array_keys( $status_links ) );
                $status_links = array_merge( array_slice( $status_links, 0, $split ), $sticky_link, array_slice( $status_links, $split ) );
            }

            return $status_links;
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

            $this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';

            $s = @$_POST['s'];

			/* -- Preparing your query -- */
			$project_id_key = self::$project_id_key;
            $query = "SELECT * FROM $wpdb->posts INNER JOIN $wpdb->postmeta pm ON $wpdb->posts.ID = pm.post_id WHERE $wpdb->posts.post_type = '$this->post_type' AND pm.meta_key='$project_id_key' AND pm.meta_value='$project_id'";

            if ( $s ) {
                $query .= " AND ( ";
                $query .= " $wpdb->posts.post_title LIKE '%{$s}%' ";
                $query .= " OR $wpdb->posts.post_content LIKE '%{$s}%' ";
                $query .= ') ';
            }

           // $this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';
            if ( isset( $_GET['post_status'] ) ) {
                $query .= " AND $wpdb->posts.post_status = '".$_REQUEST['post_status']."'";
            } else {
                $query .= " AND $wpdb->posts.post_status != 'trash'";
            }

            if ( isset( $_GET['assignee'] ) ) {
                $query .= " AND $wpdb->postmeta.meta_value = '".$_REQUEST['assignee']."'";
                $query .= " AND $wpdb->postmeta.meta_key = 'post_assignee'";
            }

            if ( !current_user_can( rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) . '_' . 'admin') &&  !current_user_can( rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) . '_' . 'editor') && current_user_can( rt_biz_sanitize_module_key( RT_PM_TEXT_DOMAIN ) . '_' . 'author') ){
                $query .= " AND $wpdb->postmeta.meta_value = '".get_current_user_id()."'";
                $query .= " AND $wpdb->postmeta.meta_key = 'post_assignee'";
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
			$perpage = $this->get_items_per_page( rtpm_post_type_name( $this->labels['name'] ).'_per_page', 10 );

			//Which page is this?
			$paged = ! empty( $_GET['paged'] ) ?  $_GET['paged']  : '';
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

            global $wpdb,$rt_pm_project, $rt_pm_task;

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
								    echo '<td '.$attributes.'>'.'<a href="'.admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$_REQUEST["{$rt_pm_project->post_type}_id"]}&tab={$rt_pm_project->post_type}-task&{$this->post_type}_id={$rec->ID}").'">'.$rec->post_title.'</a>';
                                }else{
                                    echo '<td '.$attributes.'>'.$rec->post_title;
                                }
                                if ( $rec->post_status !='trash'){
                                    $actions = array(
                                        'edit'      => '<a href="'.admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$_REQUEST["{$rt_pm_project->post_type}_id"]}&tab={$rt_pm_project->post_type}-task&{$this->post_type}_id={$rec->ID}").'">Edit</a>',
                                        'timeentry'    => '<a target="_blank" href="'.admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$_REQUEST["{$rt_pm_project->post_type}_id"]}&tab={$rt_pm_project->post_type}-timeentry&task_id={$rec->ID}").'&action=timeentry">Time Entry</a>',
                                        'trash'    => '<a href="'.admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$_REQUEST["{$rt_pm_project->post_type}_id"]}&tab={$rt_pm_project->post_type}-task&{$this->post_type}_id={$rec->ID}&action=trash").'">Trash</a>',
                                    );
                                }else{
                                    $actions = array(
                                        'restore'    => '<a href="'.admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$_REQUEST["{$rt_pm_project->post_type}_id"]}&tab={$rt_pm_project->post_type}-task&{$this->post_type}_id={$rec->ID}&action=restore").'">Restore</a>',
                                        'delete'    => '<a href="'.admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$_REQUEST["{$rt_pm_project->post_type}_id"]}&tab={$rt_pm_project->post_type}-task&{$this->post_type}_id={$rec->ID}&action=delete").'">Delete Permanently</a>',
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
                                $date = date_parse($rec->post_date_gmt);
                                if(checkdate($date['month'], $date['day'], $date['year'])) {
                                    $dtObj = rt_convert_strdate_to_usertimestamp($rec->post_date_gmt);
                                    echo '<td '.$attributes.'><span title="'. $dtObj->format('Y-m-d H:i:s') .'" class="moment-from-now">' . $dtObj->format('Y-m-d H:i:s') . '</span>';
                                } else {
                                    echo '<td '.$attributes.'>-';
                                }
                                //.'< /td>';
                                break;
                            case "rtpm_update_date":
                                $date = date_parse($rec->post_modified_gmt);
                                if(checkdate($date['month'], $date['day'], $date['year'])) {
                                    $dtObj =rt_convert_strdate_to_usertimestamp($rec->post_modified_gmt);
                                    echo '<td '.$attributes.'><span title="'. $dtObj->format('Y-m-d H:i:s') .'" class="moment-from-now">' . $dtObj->format('Y-m-d H:i:s') . '</span>';
                                } else {
                                    echo '<td '.$attributes.'>-';
                                }
                                //.'< /td>';
                                break;
                            case "rtpm_Due_date":
                                $date = date_parse($temp['post_duedate']);
                                if(checkdate($date['month'], $date['day'], $date['year'])) {
                                    $dtObj =rt_convert_strdate_to_usertimestamp($temp['post_duedate']);
                                    echo '<td '.$attributes.'><span title="'. $dtObj->format('Y-m-d H:i:s')  .'" class="moment-from-now">' . $dtObj->format('Y-m-d H:i:s')  . '</span>';
                                } else {
                                    echo '<td '.$attributes.'>-';
                                }
                                //.'< /td>';
                                break;
                            case "rtpm_status":
                                if(!empty($rec->post_status)) {
                                    $status = $this->get_status_label($rec->post_status);
                                    $url = admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id={$_REQUEST["{$rt_pm_project->post_type}_id"]}&tab={$rt_pm_project->post_type}-task");
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

        function get_drop_down($task_Id=0) {
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

        /**
         * Task count
         * @param $project_id
         * @param string $perm
         *
         * @return mixed|void
         */
        function rtpm_count_posts( $project_id, $perm = '' ) {
            global $wpdb, $rt_pm_task;

            $type = $rt_pm_task->post_type;


            $cache_key = _count_posts_cache_key( $type, $perm );

            $counts = wp_cache_get( $cache_key, 'counts' );
            if ( false !== $counts ) {
                /** This filter is documented in wp-includes/post.php */
                return apply_filters( 'wp_count_posts', $counts, $type, $perm );
            }

            $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %d  ";
            if ( 'readable' == $perm && is_user_logged_in() ) {
                $post_type_object = get_post_type_object($type);
                if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
                    $query .= $wpdb->prepare( " AND (post_status != 'private' OR ( post_author = %d AND post_status = 'private' ))",
                        get_current_user_id()
                    );
                }
            }
            $query .= ' GROUP BY post_status';

            $results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type, $project_id ), ARRAY_A );
            $counts = array_fill_keys( get_post_stati(), 0 );

            foreach ( $results as $row ) {
                $counts[ $row['post_status'] ] = $row['num_posts'];
            }

            $counts = (object) $counts;
            wp_cache_set( $cache_key, $counts, 'counts' );


            return apply_filters( 'wp_count_posts', $counts, $type, $perm );
        }

    }
}
