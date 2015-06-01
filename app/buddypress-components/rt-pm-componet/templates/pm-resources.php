<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_bp_pm_project, $bp, $wpdb, $wp_query, $rt_person, $rt_pm_task_resources_model, $rt_pm_project_resources;
$page = max( 1, get_query_var( 'paged' ) );
$project_id = $_GET['rt_project_id'];
$current_date = date( "Y-m-d" );
$dates = rt_get_next_dates( $current_date );
$user_ids = $rt_pm_task_resources_model->rtpm_get_resources_users( array( 'project_id' => $project_id ) );
?>
<div class="list-heading">
	<div class="large-10 columns list-title">
		<h4><?php _e( 'Resources', RT_PM_TEXT_DOMAIN ) ?></h4>
	</div>
	<div class="large-2 columns">

	</div>
</div>
<div class="rt-main-resources-container">
	<?php  if(!empty($user_ids)) { ?>
	<div class="rt-export-button-container"><a href="#" class="rt-export-button export-csv">Export CSV</a></div>
	<div class="rt-export-button-container"><a href="#" class="rt-export-button export-pdf">Export PDF</a></div>
	<div class="rt-left-container">
		<table>
			<thead>
				<tr>
					<td>
						<?php _e( 'Project Resources', RT_PM_TEXT_DOMAIN ); ?>
					</td>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach ( $user_ids as  $user_id ): ?>
						<tr>
							<td>
								<div class="rtpm-show-user-tooltip">
									<?php echo  rtbiz_get_user_displayname( $user_id ); ?>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td><?php _e( 'TOTAL HOURS', RT_PM_TEXT_DOMAIN ) ?></td>
				</tr>
			</tfoot>
		</table>
	</div>
	<div class="rt-right-container">
		<?php
		$first_date = $dates[0];
		$last_date = $dates[count( $dates ) - 1];
		?>
		<a id="rtpm-get-prev-calender" class="rtpm-get-calender"  data-flag="prev" data-date="<?php echo $first_date; ?>" data-calender="resources" data-project="<?php echo $project_id; ?>"><?php
			if ( wp_is_mobile() ) {
				echo "prev";
			} else {
				echo "<";
			}
			?></a>
		<table id="rtpm-resources-calender" data-project-id="<?php echo $project_id ?>">
			<?php echo $rt_pm_project_resources->rt_create_resources_calender( $dates, $project_id ); ?>
		</table>
		<a id="rtpm-get-next-calender" class="rtpm-get-calender"  data-flag="next" data-date="<?php echo $last_date; ?>" data-calender="resources" data-project="<?php echo $project_id; ?>"><?php
			if ( wp_is_mobile() ) {
				echo "next";
			} else {
				echo ">";
			}
			?></a>
	</div>
	<div class="rt-export-button-container"><a href="#" class="rt-export-button export-csv export-bottom">Export CSV</a></div>
	<div class="rt-export-button-container"><a href="#" class="rt-export-button export-pdf export-bottom">Export PDF</a></div>
	<?php } else { ?>
	<div><?php _e( 'No Resources to show', RT_PM_TEXT_DOMAIN ) ?></div>
	<?php } ?>
</div>
<!-- Inline javascript -->
<script type="text/javascript">

	var rtpm_project_resources;
	(function() {

		rtpm_project_resources = {
			init: function() {

				$( document).on( 'click', 'a.rtpm_user_task_estimated_hours', rtpm_project_resources.rtpm_show_user_task );
				$('a.rtpm_user_task_estimated_hours').contextMenu('div.rtcontext-box');
				$( document ).ajaxComplete( rtpm_project_resources.rtpm_refresh_user_task_link );
			},

			rtpm_show_user_task: function( e ) {
				e.preventDefault();

				var post = {};

				var elm = $(this);

				post.timestamp = elm.data('timestamp');
				post.user_id = elm.parents('tr').data('user-id');
				post.project_id = elm.parents('table').data('project-id');

				rtpm_show_user_task_hovercart( post );
			},

			rtpm_refresh_user_task_link: function( event,request, settings ) {

				var action = get_parameter_by_name('?' + settings.data, 'action');

				var allowed_actions = ['rtpm_get_resources_calender'];

				if ($.inArray(action, allowed_actions) > -1) {
					// $('a.rtpm_user_task_estimated_hours').contextMenu('refresh');
					$('a.rtpm_user_task_estimated_hours').contextMenu('div.rtcontext-box');
				}
			}
		};

		$( document).ready( function() { rtpm_project_resources.init() });
	})(jQuery);

</script>

<?php rtpm_user_tasks_hover_cart() ?>