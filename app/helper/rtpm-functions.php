<?php

/**
 * rtPM Functions
 *
 * Helper functions for rtPM
 *
 * @author udit
 */
function rtpm_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( $args && is_array( $args ) )
		extract( $args );

	$located = rtpm_locate_template( $template_name, $template_path, $default_path );

	do_action( 'rtpm_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'rtpm_after_template_part', $template_name, $template_path, $located, $args );
}

function rtpm_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	global $rt_wp_pm;
	if ( ! $template_path ) {
		$template_path = $rt_wp_pm->templateURL;
	}
	if ( ! $default_path ) {
		$default_path = RT_PM_PATH_TEMPLATES;
	}

	// Look within passed path within the theme - this is priority
	$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name
			)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters( 'rtpm_locate_template', $template, $template_name, $template_path );
}

function rtpm_sanitize_taxonomy_name( $taxonomy ) {
	$taxonomy = strtolower( stripslashes( strip_tags( $taxonomy ) ) );
	$taxonomy = preg_replace( '/&.+?;/', '', $taxonomy ); // Kill entities
	$taxonomy = str_replace( array( '.', '\'', '"' ), '', $taxonomy ); // Kill quotes and full stops.
	$taxonomy = str_replace( array( ' ', '_' ), '-', $taxonomy ); // Replace spaces and underscores.

	return $taxonomy;
}

function rtpm_post_type_name( $name ) {
	return 'rt_' . rtpm_sanitize_taxonomy_name( $name );
}

function rtpm_attribute_taxonomy_name( $name ) {
	return 'rt_' . rtpm_sanitize_taxonomy_name( $name );
}

function rtpm_get_time_entry_table_name() {
	global $rt_pm_time_entries_model;
	return $rt_pm_time_entries_model->table_name;
}

function rtpm_get_settings() {
	$default = array(
		'attach_contacts' => 'yes',
		'attach_accounts' => 'yes',
		'system_email' => '',
		'outbound_emails' => '',
	);
	$settings = get_site_option( 'rt_pm_settings', $default );
	return $settings;
}

function rtpm_update_settings( $key, $value ) {
	
}

function rtpm_update_post_term_count( $terms, $taxonomy ) {
	global $wpdb;

	$object_types = (array) $taxonomy->object_type;

	foreach ( $object_types as &$object_type )
		list( $object_type ) = explode( ':', $object_type );

	$object_types = array_unique( $object_types );

	if ( false !== ( $check_attachments = array_search( 'attachment', $object_types ) ) ) {
		unset( $object_types[ $check_attachments ] );
		$check_attachments = true;
	}

	if ( $object_types )
		$object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );

	foreach ( (array) $terms as $term ) {
		$count = 0;

		// Attachments can be 'inherit' status, we need to base count off the parent's status if so
		if ( $check_attachments )
			$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts p1 WHERE p1.ID = $wpdb->term_relationships.object_id  AND post_type = 'attachment' AND term_taxonomy_id = %d", $term ) );

		if ( $object_types )
			$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id  AND post_type IN ('" . implode("', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );

		do_action( 'edit_term_taxonomy', $term, $taxonomy );
		$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
		do_action( 'edited_term_taxonomy', $term, $taxonomy );
	}
}

/**
 * Render BDM selectbox
 * @param $business_manager
 */
function rtpm_render_manager_selectbox( $project_manager ){ ?>
	<select name="post[project_manager]" >
		<option value=""><?php _e( 'Select PM' ); ?></option>
		<?php
		$employees = rt_biz_get_employees();

		if (!empty( $employees )) {
			foreach ($employees as $bm) {

				$employee_wp_user_id = rt_biz_get_wp_user_for_person( $bm->ID );

				if ( $employee_wp_user_id == $project_manager ) {
					$selected = " selected";
				} else {
					$selected = " ";
				}
				echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rtbiz_get_user_displayname( $employee_wp_user_id ) . '</option>';
			}
		}
		?>
	</select>
<?php }


/**
 * Render BDM selectbox
 * @param $business_manager
 */
function rt_pm_render_bdm_selectbox( $business_manager ){ ?>

	<select name="post[business_manager]" >
		<option value=""><?php _e( 'Select BM' ); ?></option>
		<?php

		$employees = rt_biz_get_employees();

		if (!empty( $employees )) {
			foreach ($employees as $bm) {

				$employee_wp_user_id = rt_biz_get_wp_user_for_person( $bm->ID );

				if ( $employee_wp_user_id == $business_manager) {
					$selected = " selected";
				} else {
					$selected = " ";
				}
				echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rtbiz_get_user_displayname( $employee_wp_user_id ) . '</option>';
			}
		}
		?>
	</select>

<?php }


/**
 * Task detail hover cart
 */
function rtpm_task_detail_hover_cart() { ?>

	<script id="task-detail-template" type="text/x-handlebars-template">
		<ul style="list-style-type: none; margin: 0;">
			<li style="margin: 0;"><strong>Task Title: </strong><span>{{task_title}}</span></li>
			<li style="margin: 0;"><strong>Status: </strong><span>{{task_status}}</span></li>
			<li style="margin: 0;"><strong>Progress: </strong><span>{{task_progress}}%</span></li>
			<li style="margin: 0;"><strong>Start Date: </strong><span>{{start_date}}</span></li>
			<li style="margin: 0;"><strong>End Date: </strong><span>{{end_date}}</span></li>
		</ul>
	</script>

	<script type="text/javascript">
		var source   = $('#task-detail-template').html();
		var template = Handlebars.compile(source);


		var admin_url = '<?php echo admin_url('admin-ajax.php');  ?>';

		function rtpm_show_task_detail_hovercart( id ) {

			if (null === id)
				return;

			var data = {task_id: id};

			var senddata = {
				action: 'rtpm_get_task_data_for_ganttchart',
				post: data
			};

			if ( 'undefined' != typeof request ) {
				request.abort();
				$('div.rtcontext-box').html('<strong>Loading...</strong>');
			}

			request = $.post( admin_url, senddata, function( response ){
				if( response.success ){
					$('div.rtcontext-box').html( template( response.data ) );
					// $('div.gantt_task_content').contextMenu('div.rtcontext-box', {triggerOn: 'hover'});
				}
			} );

		}
	</script>

	<div class="rtcontext-box iw-contextMenu" style="display: none;">
		<strong>Loading...</strong>
	</div>
<?php }

/**
 * Show all task assigned to user
 */
function rtpm_user_tasks_hover_cart() { ?>

	<script id="task-list-template" type="text/x-handlebars-template">
		<h2>{{assignee_name}}'s Tasks</h2>
		<ul style="list-style-type: none; margin: 0;">
			{{#each tasks}}
			<li style="margin: 0;"><a class="user_tasks" href="{{task_edit_url}}">{{post_title}}</a></li>
			{{/each}}
		</ul>
	</script>

	<script type="text/javascript">
		var ajax_adminurl = '<?php echo  admin_url( 'admin-ajax.php' ); ?>';

		var source = $('#task-list-template').html();
		var template = Handlebars.compile(source);

		var current_blog_id = '<?php echo get_current_blog_id() ?>';

		function rtpm_show_user_task_hovercart( filter, blog_id ) {

			$('div.rtcontext-box').html('<strong>Loading...</strong>');

			var data = {};

			data.action = 'rtpm_get_user_tasks'
			data.post = filter;

			if( 'undefined' != typeof blog_id ) {
				data.rt_voxxi_blog_id = blog_id;
				current_blog_id = blog_id;
			}


			$.post(ajaxurl, data, function (res) {

				if (res.success) {
					$('div.rtcontext-box').html(template(res.data));
					jQuery( 'div.rtcontext-box' ).on( 'click', 'a.user_tasks', rtpm_open_task_side_panel);
				}
			});
		}



		function rtpm_open_task_side_panel( e ) {
			e.preventDefault();

			var task_id = get_parameter_by_name('?' + $(this).attr('href'), 'rt_task_id');

			block_ui();

			render_project_slide_panel('open', task_id, current_blog_id, '', 'task');
		}

	</script>
	<div class="rtcontext-box iw-contextMenu" style="display: none;">
		<strong>Loading...</strong>
	</div>

<?php }

/**
 *  Script for task resource assignment validation
 */
function rtpm_validate_user_assigned_hours_script( ) {

	if( isset( $_REQUEST['rt_voxxi_blog_id'] ) )
		restore_current_blog();

	?>
	<script type="text/javascript">

		var ajax_adminurl = '<?php echo  admin_url( 'admin-ajax.php' ); ?>';

		var rtpm_task_assignee, request, id_index = 0;

		function addZero(i) {
			if (i < 10) {
				i = "0" + i;
			}
			return i;
		}

		(function( $ ) {
			rtpm_task_assignee = {
				init: function() {
					$('div.rt-resources-parent-row').on( 'autocompletechange', 'input.search-contact', rtpm_task_assignee.validate_user_assigned_hours  );
					$('div.rt-resources-parent-row').on( 'change', 'input[name="post[time_duration][]"]', rtpm_task_assignee.validate_user_assigned_hours  );
					$('div.rt-resources-parent-row').on( 'change', 'input[name="post[timestamp][]"]', rtpm_task_assignee.validate_user_assigned_hours  );
					$('div.rt-resources-parent-row').on( 'click', 'a.resources-add-multiple', rtpm_task_assignee.append_task_resources_markup );
					$('div.rt-resources-parent-row').on( 'click', 'a.resources-delete-multiple', rtpm_task_assignee.remove_task_resources_markup );
				},

				validate_user_assigned_hours: function() {


					$main_div =  $(this).parents('div.rt-resources-row');

					$input = $main_div.find('input');

					var $emptyFields = $input.filter(function() {

						// remove the $.trim if whitespace is counted as filled
						return $.trim(this.value) === "";
					});

					if ( $emptyFields.length )
						return false;

					if( ! rtpm_task_assignee.check_task_dates( $main_div, $input ) )
						return false;


					$time_duration_input = $input.eq(2);

					var user_id = $input.eq(1).val();
					var time_duration = $input.eq(2).val();
					var timestamp = $input.eq(3).datepicker('getDate');

					var ajax_nonce = '<?php echo wp_create_nonce( "rtpm-validate-hours" ); ?>';

					var todayUTC = new Date(Date.UTC(timestamp.getFullYear(), timestamp.getMonth(), timestamp.getDate()));
					timestamp = todayUTC.toISOString().slice(0, 10).replace(/-/g, '-')+' '+addZero(timestamp.getHours())+':'+addZero(timestamp.getMinutes())+':'+addZero(timestamp.getSeconds());


					var post = {
						user_id: user_id,
						time_duration: time_duration,
						timestamp: timestamp,
						project_id: $('input[name="post[post_project_id]"]').val(),
						resource_id: $main_div.data('resource-id'),
					};


					var data = {
						action: 'rtpm_validate_user_assigned_hours',
						post:   post,
						security: ajax_nonce,
					};

					if( 'undefined' != typeof $("input[name='post[rt_voxxi_blog_id]']") ) {
						data.rt_voxxi_blog_id = $("input[name='post[rt_voxxi_blog_id]']").val();
					}
					//block_ui();

					if( 'undefined' == typeof request ) {
						request = $.post( ajax_adminurl, data, rtpm_task_assignee.set_hours_limit );
					}
				},

				set_hours_limit: function( response ) {
					var data = response.data;
					if( response.success ) {
						//$time_duration_input.attr( 'max', data.max_hours );

						if( data.message ) {

							$error_div = $main_div.find('small.error');
							$error_div.remove();
							$('<small class="error" style="display: inline-block; width: 100%;">'+data.message+'</small>').appendTo($main_div).hide().show('slow');

						} else {
							$error_div = $main_div.find('small.error');
							$error_div.hide( 'slow', function() { $error_div.remove() } );
						}
					}

					request = undefined;
				},

				remove_task_resources_markup: function() {
					var ajax_nonce = '<?php echo wp_create_nonce( "rtpm-remove-resources" ); ?>';

					$elm = $(this).parents('div.rt-resources-row');

					var post = {
						resource_id: $elm.data('resource-id')
					};

					var data = {
						action: 'rtpm_remove_resources',
						security: ajax_nonce,
						post: post,
					};

					if( 'undefined' != typeof $("input[name='post[rt_voxxi_blog_id]']") ) {
						data.rt_voxxi_blog_id = $("input[name='post[rt_voxxi_blog_id]']").val();
					}

					$.post( ajax_adminurl, data, function( response ) {

						if( response.success ) {
							$elm.remove();
						}
					});
				},

				append_task_resources_markup: function() {
					$main_div =  $(this).parents('div.rt-resources-row');

					$input = $main_div.find('input');

					var $emptyFields = $input.filter(function() {
						return $.trim(this.value) === "";
					});

					if ( $emptyFields.length )
						return false;

					if( ! rtpm_task_assignee.check_task_dates( $main_div, $input ) )
						return false;

					$element = $main_div.clone();

					$new_input = $element.find('input');
					$new_input.eq(3).removeClass('hasDatepicker');
					$new_input.eq(3).attr( 'id', $input.eq(3).attr('id') + id_index );
					$element.find('a').removeClass('resources-add-multiple').addClass('resources-delete-multiple').find('i').removeClass('fa fa-plus').addClass('fa fa-times');

					var timestamp = $input.eq(3).datepicker('getDate');
					var todayUTC = new Date(Date.UTC(timestamp.getFullYear(), timestamp.getMonth(), timestamp.getDate()));
					timestamp = todayUTC.toISOString().slice(0, 10).replace(/-/g, '-')+' '+addZero(timestamp.getHours())+':'+addZero(timestamp.getMinutes())+':'+addZero(timestamp.getSeconds());

					var user_id = $input.eq(1).val();
					var time_duration = $input.eq(2).val();


					var post = {
						user_id: user_id,
						time_duration: time_duration,
						timestamp: timestamp,
					};


					if( 'undefined' != typeof $('input[name="post[post_id]"]') )
						post.task_id = $('input[name="post[post_id]"]').val();

					if( 'undefined' != typeof $('input[name="post[post_project_id]"]') )
						post.project_id = $('input[name="post[post_project_id]"]').val();

					var ajax_nonce = '<?php echo wp_create_nonce( "rtpm-save-resources" ); ?>';

					var data = {
						action: 'rtpm_save_resources',
						security: ajax_nonce,
						post: post
					};

					if( 'undefined' != typeof $("input[name='post[rt_voxxi_blog_id]']") ) {
						data.rt_voxxi_blog_id = $("input[name='post[rt_voxxi_blog_id]']").val();
					}

					$parent_div = $(this).parents('div.rt-resources-parent-row');

					$.post( ajax_adminurl, data, function( response ) {

						if( response.success ) {

							if( 'undefined' != typeof data ) {
								var data = response.data;
								$element.data( 'resource-id', data.resource_id );
							}

							$parent_div.append( $element )
							$input.val('');
							id_index++;

							//Remove the error message
							$error_div = $element.find('small.error');
							$error_div.remove();

						}
					});
				},

				check_task_dates: function( $main_div, $input ) {
					var start_date_val = $("input[name='post[post_date]']").val();
					var end_date_val = $("input[name='post[post_duedate]']").val();

					//Check task's start and end date is not empty
					if( ! start_date_val || ! end_date_val ) {

						//Show error message
						$error_div = $main_div.find('small.error');
						$error_div.remove();
						$('<small class="error" style="display: inline-block; width: 100%;">Task Start date or Due date is not selected</small>').appendTo($main_div).hide().show('slow');
						return false;
					}

					var timestamp = $input.eq(3).datepicker('getDate');
					var start_date = new Date( start_date_val );
					var end_date = new Date( end_date_val );

					//Check assign date is between task's start date and end date
					if( timestamp < start_date || timestamp > end_date ) {

						// Show error message
						$error_div = $main_div.find('small.error');
						$error_div.remove();
						$('<small class="error" style="display: inline-block; width: 100%;">Date must be between Task Start date and Due date</small>').appendTo($main_div).hide().show('slow');

						return false;
					}

					return true;

				}
			};

			$( document ).ready( function() { rtpm_task_assignee.init() } );
		})(jQuery);
	</script>
<?php }


/**
 * Check lead has project associated with them
 * @param $post_id
 *
 * @return bool
 */
function rtpm_lead_has_project( $post_id ) {

	$query = new WP_Query( array(
		'post_type' => 'rt_project',
		'post_parent' => $post_id,
		'no_found_rows' => true,
		'fields' => 'ids',
		'post_status' => array( 'trash', 'any' )
	));

	if( $query->have_posts() )
		return $query->posts[0];

	return false;
}