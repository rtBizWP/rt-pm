<?php

/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Description of BP HRM functions.
 *
 * @author kishore
 */


function pm_pagination( $totalPage, $page ){
    global $rt_pm_bp_pm;
   
    if( $totalPage > 1 ){
                                            
        $base = $rt_pm_bp_pm->get_component_root_url().bp_current_action().'/%_%';
        $formate = 'page/%#%';
        if( isset( $_GET['orderby'] ) ) {

                $arr_params = array( 'orderby' => $_GET['orderby'], 'order' => $_GET['order'] );
                $base = add_query_arg( $arr_params, $rt_pm_bp_pm->get_component_root_url().bp_current_action() ) .'%_%' ; 
                $formate = '&paged=%#%';
        }

        $customPagHTML     =  '<div class="projects-lists pagination" role="menubar" aria-label="Pagination"><span class="current">Page '.$page.' of '.$totalPage.'</span>'.paginate_links( array(
        'base' => $base,
        'format' => $formate,
        'total' => $totalPage,
        'current' => $page
        )).'</div>';
        echo $customPagHTML;
    }
}

function render_project_summary_buttons( $post_id ){
	global $rt_pm_bp_pm;
	if (isset($post_id)) {
				$save_button = __( 'Update' );
			} else {
				$save_button = __( 'Add Project' );
			}
	?>
	<button class="mybutton" type="submit" ><?php _e($save_button); ?></button>
				<?php 
				if(isset($post_id)) { 
					$get_post_status = get_post_status( $post_id );
					if ( isset( $get_post_status ) && $get_post_status == 'trash' ){
						$archive_action = 'unarchive';
						$archive_button = __( 'Unarchive' );
						$button_archive_id = 'button-unarchive';
						$redirect = $rt_pm_bp_pm->get_component_root_url(). 'archives';
					} else {
						$archive_action = 'archive';
						$archive_button = __( 'Archive' );
						$button_archive_id = 'button-archive';
						$redirect = $rt_pm_bp_pm->get_component_root_url();
					}
					
				?>
				<button id="top-<?php echo $button_archive_id; ?>" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => $archive_action, 'rt_project_id' => $post_id ), $redirect ); ?>" class=""><?php _e($archive_button); ?></button>
				<button id="top-button-trash" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => 'trash', 'rt_project_id' => $post_id ), $redirect ); ?>" class=""><?php _e( 'Delete' ); ?></button>
				<?php  }
}

/**
 *  returns next dates array
 * @param string $date
 * @return array
 */

function rt_get_next_dates( $date ){
		$date_object = date_create( $date );
		$start = date_timestamp_get( $date_object );
		$dates=array();
		
		// if mobile show only 3 columns
		
		if( wp_is_mobile() ){
			$table_cols = 3;	
		}else{
			$table_cols = 9;
		}
		for($i = 0; $i<=$table_cols; $i++)
		{
			array_push($dates,date('Y-m-d', strtotime("+$i day", $start)));
		}
		return $dates;
}

add_action( 'wp_ajax_rtpm_validate_estimated_date', 'rtpm_validate_estimated_date_ajax' );
add_action( 'wp_ajax_nopriv_rtpm_validate_estimated_date', 'rtpm_validate_estimated_date_ajax' );

/**
 *  Function to check if estimated hours are less than max working hours
 */

function rtpm_validate_estimated_date_ajax(){
	
	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];
	$est_time = $_POST['est_time'];
	$project_id = $_POST['project_id'];
	$max_working_hours = get_post_meta($project_id,'working_hours',true);
	$first_date = date_create_from_format( 'M d, Y H:i A', $start_date );
	$second_date = date_create_from_format( 'M d, Y H:i A', $end_date );
	$diff = $second_date->diff($first_date);
	$diff_hours = $diff->h;
	
	// hours between start date and end date
	
	$diff_hours = $diff_hours + ($diff->days*(int)$max_working_hours);

	if( (int)$diff_hours < (int)$est_time ){
		echo json_encode( array( 'fetched' => false,'diff'=>$diff_hours ) );
	}else{
		echo json_encode( array( 'fetched' => true ) );
	}
	die;
}

function pm_get_attachment_data(){
  
    $meta = get_post( $_POST['attachment_id'] );
   
    echo json_encode( $meta );
    
    die(0);
}

add_action( 'wp_ajax_rtpmattachment_metadata', 'pm_get_attachment_data' );

function pm_save_attachment_data(){
  
  
    $args = array(
        'ID' => $_POST['ID'],
        'post_title' => $_POST['post_title'],
        'post_excerpt' => $_POST['post_excerpt'],
        'post_content' => $_POST['post_content'],
    );
   $post_id = wp_update_post( $args );
   echo $post_id;
   die();
  
}

add_action( 'wp_ajax_rtpmattachment_save_data', 'pm_save_attachment_data' );


function pm_add_new_documents(){

    
    $parent_post_id = $_POST['post_id'];
    $filename = $_POST['filename'];
    //var_dump($filenames);
    //foreach ( $filenames as $filename ) {
        
    
    // $filename should be the path to a file in the upload directory.
   

    // The ID of the post this attachment is for.
    //$parent_post_id = 37;

    // Check the type of tile. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype( basename( $filename ), null );

    // Get the path to the upload directory.
    $wp_upload_dir = wp_upload_dir();

    // Prepare an array of post data for the attachment.
    $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
    );

    // Insert the attachment.
    $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

   $data = array(
           'attachment_id'=>$attach_id
    );
    echo json_encode( $data );
    
    die();
}
add_action( 'wp_ajax_rtpm_add_new_documents', 'pm_add_new_documents'  );


function pm_remove_document(){
    
    $attachment_id = $_POST[ 'attachment_id' ];
    wp_delete_attachment( $attachment_id );
}
add_action( 'wp_ajax_rtpm_remove_document', 'pm_remove_document'  );

/**
 * Get project edit url
 * @param $project_id
 * @return string
 */
function rtpm_bp_get_project_details_url( $project_id ) {
    global $rt_pm_bp_pm, $rt_pm_project;

    $project_edit_link = add_query_arg( array( 'rt_project_id' => $project_id, 'action' => 'edit', 'post_type' => $rt_pm_project->post_type, 'tab' => "{$rt_pm_project->post_type}-details" ), $rt_pm_bp_pm->get_component_root_url().'/details' );
    return $project_edit_link;
}

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

		function rtpm_show_user_task_hovercart( filter ) {

			$('div.rtcontext-box').html('<strong>Loading...</strong>');

			var data = {};

			data.action = 'rtpm_get_user_tasks'
			data.post = filter;

			$.post(ajax_adminurl, data, function (res) {

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

			render_project_slide_panel('open', task_id, <?php echo get_current_blog_id(); ?>, '', 'task');
		}

	</script>
	<div class="rtcontext-box iw-contextMenu" style="display: none;">
		<strong>Loading...</strong>
	</div>

<?php }

/**
 *
 */
function rtpm_validate_user_assigned_hours_script() { ?>
	<script type="text/javascript">

		var ajax_adminurl = '<?php echo  admin_url( 'admin-ajax.php' ); ?>';
		var rtpm_task_assignee, request, id_index = 0;

		(function( $ ) {
			rtpm_task_assignee = {
				init: function() {
					$('div.rt-resources-parent-row').on( 'autocompletechange', 'input.search-contact', rtpm_task_assignee.validate_user_assigned_hours  );
					$('div.rt-resources-parent-row').on( 'change', 'input[name="post[time_duration][]"]', rtpm_task_assignee.validate_user_assigned_hours  );
					$('div.rt-resources-parent-row').on( 'change', 'input[name="post[timestamp][]"]', rtpm_task_assignee.validate_user_assigned_hours  );
					$('div.rt-resources-parent-row').on('click', 'a.resources-add-multiple', rtpm_task_assignee.append_task_resources_markup );
					$('div.rt-resources-parent-row').on('click', 'a.resources-delete-multiple', rtpm_task_assignee.remove_task_resources_markup );
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

					$time_duration_input = $input.eq(2);

					var user_id = $input.eq(1).val();
					var time_duration = $input.eq(2).val();
					var timestamp = $input.eq(3).datepicker('getDate');

					var ajax_nonce = '<?php echo wp_create_nonce( "rtpm-validate-hours" ); ?>';

					var todayUTC = new Date(Date.UTC(timestamp.getFullYear(), timestamp.getMonth(), timestamp.getDate()));
					timestamp = todayUTC.toISOString().slice(0, 10).replace(/-/g, '-');


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

					$element = $main_div.clone();

					$new_input = $element.find('input');
					$new_input.eq(3).removeClass('hasDatepicker');
					$new_input.eq(3).attr( 'id', $input.eq(3).attr('id') + id_index );
					$element.find('a').removeClass('resources-add-multiple').addClass('resources-delete-multiple').find('i').removeClass('fa fa-plus').addClass('fa fa-times');

					var timestamp = $input.eq(3).datepicker('getDate');
					var todayUTC = new Date(Date.UTC(timestamp.getFullYear(), timestamp.getMonth(), timestamp.getDate()));
					timestamp = todayUTC.toISOString().slice(0, 10).replace(/-/g, '-')+' 00:00:00';

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

						}
					});
				},
			};

			$( document ).ready( function() { rtpm_task_assignee.init() } );
		})(jQuery);
	</script>
<?php }
