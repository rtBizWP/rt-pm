<?php

/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 17/3/15
 * Time: 12:39 PM
 */
class Rt_Pm_Project_Overview {

	private $rtpm_chars = array();

	/**
	 * Return singleton instance of class
	 *
	 * @return Rt_Pm_Project_Overview
	 */
	public static function factory() {
		static $instance = false;
		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Placeholder method
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * Setup actions and filters
	 */
	public function setup() {

		add_action( 'wp_head', array( $this, 'rtpm_print_style' ) );
		add_action( 'wp_ajax_rtpm_get_older_projects', array( $this, 'rtpm_get_older_projects' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'rtpm_load_style_script' ) );
	}

	public function rtpm_load_style_script() {

		if ( in_array( bp_current_action(), array( 'todo', 'overview' ) ) ) {
			//Enqueue sidr scripts
			rt_enqueue_sidr( true );

			wp_enqueue_script( 'rtvoxxi-context-script', get_stylesheet_directory_uri() . '/assets/js/contextMenu.min.js', array( 'jquery' ), BUDDYBOSS_CHILD_THEME_VERS );
			wp_enqueue_style( 'rtvoxxi-context-style', get_stylesheet_directory_uri() . '/assets/css/contextMenu.css' );

			wp_enqueue_script( 'rtpm-handlebar-script', RT_PM_URL . 'app/assets/javascripts/handlebars.js', "", true );

			wp_localize_script( 'rtbiz-side-panel-script', 'pm_script_url', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/rt-bp-pm.min.js' );

			wp_enqueue_style( 'rtpm-grid-style', get_stylesheet_directory_uri() . '/css/style.css' );
		}
	}

	/**
	 * Render all grids for project overview
	 */
	public function rtpm_render_project_grid() { ?>
		<ul id="activity-stream" class="activity-list item-list">
			<?php
			if ( is_main_site() ) {
				$args = array(
					'fields'   => 'ids',
					'nopaging' => true,
				);


				$project_data = $this->rtpm_project_main_site_data( $args );

				$project_total_counts           = count( $project_data );
				$_REQUEST['project_total_page'] = ceil( $project_total_counts / 2 );
			}
			$this->rtpm_project_block_list( 1 );
			$max_num_pages = absint( $_REQUEST['project_total_page'] );

			if ( 1 < $max_num_pages ) : ?>
				<li class="load-more activity-item">
					<a href="#more" id="load-more" data-page="2" data-max-pages="<?php echo $max_num_pages ?>">Load
						More</a>
				</li>
			<?php endif; ?>

		</ul>

		<script type="text/javascript">
			var project_overview;

			var current_blog_id = '<?php echo get_current_blog_id() ?>';
			(function ($) {

				project_overview = {
					init: function () {

						$('a#load-more').click(function (e) {
							e.preventDefault();
							return project_overview.load_more_projects($(this));
						});

						$(document).on('click', 'a.project_edit_link', project_overview.open_project_side_panel);

						project_overview.init_contextmenu();
					},

					init_contextmenu: function () {

						$('a.rtcontext-taskbox').contextMenu('div.rtcontext-box');

						$('a.rtcontext-taskbox').click(function (e) {
							return project_overview.fill_tasks_list($(this), e);
						});
					},

					fill_tasks_list: function (elm, event) {
						event.stopPropagation();

						var post = {};

						post.user_id = elm.data('team-member-id');
						post.project_id = elm.parents('li').data('project-id');

						var that = elm.parents('.activity-item');
						var blog_id = that.data('rt-blog-id');
						rtpm_show_user_task_hovercart( post, blog_id );
					},

					load_more_projects: function (elm) {

						$('#buddypress li.load-more').addClass('loading');
						var page = parseInt(elm.data('page'));

						var total_page = parseInt(elm.data('max-pages'));

						var post = {'page': page};

						var senddata = {'action': 'rtpm_get_older_projects', 'post': post};

						$.post(ajaxurl, senddata, function (response) {

							$('#buddypress li.load-more').removeClass('loading');
							if (response.success) {
								var data = response.data;

								$('#activity-stream').append(response.data.content);

								if (total_page <= page) {
									elm.parent().hide();
								} else {
									elm.data('page', page + 1);
									elm.parent().appendTo('#activity-stream');
								}

								project_overview.init_contextmenu();

								rt_reports_draw_charts();

								init_masonary_container();

								bol_masonry_reload();
							}
						});
					},

					open_project_side_panel: function (e) {
						e.preventDefault();

						block_ui();
						$element = $(this);
						$url = $element.attr('href');

						var that = $element.parents('.activity-item');
						var blog_id = that.data('rt-blog-id');


						var project_id = get_parameter_by_name($url, 'rt_project_id');
						render_project_slide_panel('open', project_id, blog_id, '', 'project');
					}
				};
				$(document).ready(function () {
					project_overview.init();
				});
			}(jQuery));
		</script>

		<?php
		rtpm_user_tasks_hover_cart();
	}

	public function rtpm_project_block_list( $page ) {
		global $rt_pm_project, $rt_pm_task, $rt_pm_task_resources_model, $rt_pm_time_entries_model, $table_prefix;

		$displayed_user_id = bp_displayed_user_id();

		$args = array(
			'paged'          => $page,
			'posts_per_page' => 2,
		);

		$old_blog_id = get_current_blog_id();

		if ( is_main_site() ) {
			$project_data = $this->rtpm_project_main_site_data( $args );
		} else {

			$admin_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'admin' );

			if ( ! current_user_can( $admin_cap ) ) {

				$projects         = $rt_pm_project->rtpm_get_users_projects( $displayed_user_id );
				$args['post__in'] = ( ! empty( $projects ) ) ? $projects : array( '0' );
			}

			$query = $rt_pm_project->rtpm_prepare_project_wp_query( $args );

			$_REQUEST['project_total_page'] = $query->max_num_pages;

			$project_data = $query->posts;

			$blog_id = get_current_blog_id();

		}

		if ( empty( $project_data ) ) {
			return;
		}

		//Masonry container fix
		if ( count( $project_data ) <= 1 ) {
			wp_localize_script( 'rt-biz-admin', 'NOT_INIT_MASONRY', 'false' );
		}

		if( is_main_site() ) {
			$rt_pm_task_resources_table_name = $rt_pm_task_resources_model->table_name;
			$rt_pm_time_entries_table_name = $rt_pm_time_entries_model->table_name;
			$old_table_prefix = $table_prefix;
		}

		foreach ( $project_data as $project ):

			/**
			 * Switch to blog and set table name prefix
			 */
			if( isset( $project->blog_id ) ) {

				$blog_id = $project->blog_id;

				switch_to_blog( $project->blog_id );
				$blog_table_prefix = $table_prefix;
				$rt_pm_task_resources_model->table_name  = str_replace( $old_table_prefix, $blog_table_prefix, $rt_pm_task_resources_table_name );
				$rt_pm_time_entries_model->table_name = str_replace( $old_table_prefix, $blog_table_prefix, $rt_pm_time_entries_table_name );
			}


			$project_edit_link = rtpm_bp_get_project_details_url( $project->ID );
			?>
			<li class="activity-item" data-project-id="<?php echo $project->ID ?>" data-rt-blog-id="<?php echo $blog_id; ?>">
				<div class="activity-content">
					<div class="row activity-inner rt-biz-activity">
						<div class="rt-voxxi-content-box">
							<p><strong>Project Name: </strong><a class="project_edit_link"
							                                     href="<?php echo $project_edit_link ?>"><?php echo $project->post_title; ?></a>
							</p>

							<p><strong>Create date: </strong><?php echo mysql2date( 'd M Y', $project->post_date ) ?>
							</p>

							<p><strong>Due
									date: </strong><?php echo mysql2date( 'd M Y', get_post_meta( $project->ID, 'post_duedate', true ) ); ?>
							</p>

							<p><strong>Post status: </strong><?php echo $project->post_status; ?></p>
						</div>

						<div class="row post-detail-comment column-title">
							<div class="column small-12">
								<p>
									<?php echo rtbiz_read_more( $project->post_content ); ?>
								</p>
							</div>
						</div>

						<div class="row column-title">
							<div class="small-6 columns">
								<table class="no-outer-border">
									<tr class="orange-text">
										<td><strong class="right"><?php _e( 'Over Due', RT_PM_TEXT_DOMAIN ) ?></strong>
										</td>
										<td><strong
												class="left"><?php echo $overdue_task = $rt_pm_task->rtpm_overdue_task_count( $project->ID ) ?></strong>
										</td>
									</tr>
									<tr class="blue-text">
										<td><strong class="right"><?php _e( 'Open', RT_PM_TEXT_DOMAIN ) ?></strong></td>
										<td><strong
												class="left"><?php echo $open_task = $rt_pm_task->rtpm_open_task_count( $project->ID ) ?></strong>
										</td>
									</tr>
									<tr class="green-text">
										<td><strong class="right"><?php _e( 'Completed', RT_PM_TEXT_DOMAIN ) ?></strong>
										</td>
										<td><strong
												class="left"><?php echo $completed_task = $rt_pm_task->rtpm_completed_task_count( $project->ID ) ?></strong>
										</td>
									</tr>
								</table>
							</div>
							<div class="small-6 columns" style="  position: absolute;left: 60%;">
								<div class="number-circle">
									<div class="height_fix"></div>
									<div
										class="content"><?php echo $rt_pm_task->rtpm_get_completed_task_per( $project->ID ) . '%' ?>
									</div>
								</div>
							</div>
						</div>

						<?php $this->rtpm_prepare_task_chart( $project->ID ) ?>

						<div class="row rt-pm-team">
							<div class="column small-3 bdm-column">
								<strong>BDM</strong>
								<?php $bdm = get_post_meta( $project->ID, 'business_manager', true );
								if ( ! empty( $bdm ) ) { ?>
									<a data-team-member-id="<?php echo $bdm; ?>" class="rtcontext-taskbox">
										<?php echo get_avatar( $bdm, 16 ); ?>
									</a>
								<?php } ?>
							</div>
							<div class="column small-9 team-column" style="float: left;">
								<strong class="team-title">Team</strong>

								<div class="row team-member">
									<?php $team_member = get_post_meta( $project->ID, "project_member", true );

									if ( ! empty( $team_member ) ) {

										foreach ( $team_member as $member ) {

											if ( empty( $member ) ) {
												continue;
											}
											?>
											<div class="columns small-3">
												<a data-team-member-id="<?php echo $member; ?>"
												   class="rtcontext-taskbox">
													<?php echo get_avatar( $member ); ?>
												</a>
											</div>
										<?php }
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</li>
		<?php
		endforeach;

		/**
		 * Restore blog and table name
		 */
		if( 1 === $old_blog_id ) {

			switch_to_blog( 1 );
			$rt_pm_task_resources_model->table_name = $rt_pm_task_resources_table_name;
			$rt_pm_time_entries_model->table_name = $rt_pm_time_entries_table_name;
		}
	}

	public function rtpm_get_older_projects() {

		if ( ! isset( $_POST['post'] ) ) {
			wp_send_json_error();
		}

		$data = $_POST['post'];

		ob_start();

		if ( isset( $data['page'] ) ) {
			$this->rtpm_project_block_list( $data['page'] );
		}

		$this->rtpm_render_project_charts();

		$html_content = ob_get_contents();
		ob_end_clean();

		$send_data = array(
			'content' => $html_content,
		);

		wp_send_json_success( $send_data );

	}


	/**
	 * Render all charts
	 */
	public function rtpm_render_project_charts() {

		global $rt_pm_reports;

		$rt_pm_reports->render_chart( $this->rtpm_chars );
	}


	/**
	 * Prepare chart of tasks
	 *
	 * @param $project_id
	 */
	public function rtpm_prepare_task_chart( $project_id ) {
		global $rt_pm_task;

		$data_source = array();
		$cols        = array( __( 'Hours' ), __( 'Estimated' ), __( 'Billed' ) );
		$rows        = array();


		$duedate_array = $rt_pm_task->rtpm_get_all_task_duedate( $project_id );

		if ( null === $duedate_array ) {
			return;
		}

		foreach ( $duedate_array as $duedate ) {

			$due_date_obj = new DateTime( $duedate );

			$billed_hours    = $rt_pm_task->rtpm_tasks_billed_hours( $project_id, $duedate );
			$estimated_hours = $rt_pm_task->rtpm_tasks_estimated_hours( $project_id, $duedate );

			if ( null === $billed_hours ) {
				$billed_hours = 0;
			}

			if ( null === $estimated_hours ) {
				$estimated_hours = 0;
			}

			$rows[] = array( $due_date_obj->format( 'd-M-Y' ), (float) $estimated_hours, (float) $billed_hours );
		}


		$data_source['cols'] = $cols;

		$data_source['rows'] = array_map( 'unserialize', array_unique( array_map( 'serialize', $rows ) ) );;

		$this->rtpm_chars[] = array(
			'id'          => 1,
			'chart_type'  => 'area',
			'data_source' => $data_source,
			'dom_element' => 'rtpm_task_status_burnup_' . $project_id,
			'options'     => array(
				//'vAxis' => json_encode( array( 'format' => '#', 'gridlines' => array( 'color' => 'transparent' ) ) ),
				'colors'    => [ '#66CCFF', '#32CD32' ],
				'legend'    => 'top',
				'pointSize' => '5',
			)
		); ?>
		<div id="rtpm_task_status_burnup_<?php echo $project_id; ?>" class="rtpm-gantt-graph-container"></div>
	<?php
	}

	/**
	 * Style for table without outer border
	 */
	public function rtpm_print_style() {
		if ( in_array( bp_current_action(), array( 'todo', 'overview' ) ) ) {
			?>
			<style>
				table.no-outer-border {
					border-collapse: collapse;
				}

				table.no-outer-border td, table.no-outer-border th {
					border: 1px solid black;
				}

				table.no-outer-border tr:first-child th {
					border-top: 0;
				}

				table.no-outer-border tr:last-child td {
					border-bottom: 0;
				}

				table.no-outer-border tr td:first-child,
				table.no-outer-border tr th:first-child {
					border-left: 0;
				}

				table.no-outer-border tr td:last-child,
				table.no-outer-border tr th:last-child {
					border-right: 0;
				}
			</style>
		<?php }
	}

	/**
	 * Prepare data for main site, Project data from all sub site
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function rtpm_project_main_site_data( $args ) {
		global $rt_pm_project, $wpdb, $rt_pm_task_resources_model, $table_prefix;

		$args['no_found_rows'] = true;

		$displayed_user_id = bp_displayed_user_id();
		$project_query     = $rt_pm_project->rtpm_prepare_project_wp_query( $args );


		$project_blog_query = array();

		/**
		 * task sql query
		 */
		$project_data_query = $project_query->request;


		$admin_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'admin' );
		if ( ! current_user_can( $admin_cap ) ) {

			/**
			 * Inner join query
			 */
			$inner_join         = " INNER JOIN {$rt_pm_task_resources_model->table_name} ON {$wpdb->posts}.ID = {$rt_pm_task_resources_model->table_name}.project_id  ";
			$pos                = strpos( $project_data_query, "FROM {$wpdb->posts}" ) + strlen( "FROM {$wpdb->posts}" );
			$project_data_query = substr_replace( $project_data_query, $inner_join, $pos, 0 );


			/**
			 * Where clause
			 */
			$where_clause       = " {$rt_pm_task_resources_model->table_name}.user_id = {$displayed_user_id} AND {$rt_pm_task_resources_model->table_name}.post_status <> 'trash' AND";
			$pos                = strpos( $project_data_query, "WHERE" ) + strlen( "WHERE" );
			$project_data_query = substr_replace( $project_data_query, $where_clause, $pos, 0 );

		}

		/**
		 * Limit, Pagination
		 */
		if ( false !== strpos( $project_data_query, 'LIMIT' ) ) {
			$limits             = substr( $project_data_query, strpos( $project_data_query, 'LIMIT' ) );
			$project_data_query = str_replace( $limits, '', $project_data_query );
		}

		$sites = rtbiz_get_our_sites();

		foreach ( $sites as $site ) {

			/**
			 * Skip 1st blog or main site
			 */
			if ( '1' === $site->blog_id ) {
				continue;
			}

			/**
			 * Replace table name with blog specific table name like wp_2_posts
			 */
			$blog_table_prefix = $table_prefix . $site->blog_id . '_';
			$blog_query        = str_replace( $table_prefix, $blog_table_prefix, $project_data_query );

			/**
			 * INNER JOIN with pm_task_resources
			 */
			$blog_id_column = " DISTINCT '{$site->blog_id}' AS blog_id, ";
			$blog_query     = substr_replace( $blog_query, $blog_id_column, 7, 0 );

			/**
			 * Filter split_the_query change * to ID
			 */
			if ( ! isset( $args['fields'] ) ) {
				$pos = strpos( $blog_query, 'ID' );
				if ( false !== $pos ) {
					$blog_query = substr_replace( $blog_query, '*', $pos, strlen( 'ID' ) );
				}
			}

			/**
			 * Blog query
			 */
			$project_blog_query[] = '(' . $blog_query . ')';
		}

		/**
		 * Whole query
		 */
		$project_main_query = implode( ' UNION ALL ', $project_blog_query );

		//Pagination
		if ( isset( $limits ) ) {
			$project_main_query .= ' ' . $limits;
		}

		return $wpdb->get_results( $project_main_query );
	}

}