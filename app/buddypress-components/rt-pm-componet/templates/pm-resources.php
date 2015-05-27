<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_bp_pm_project, $bp, $wpdb, $wp_query, $rt_person, $rt_pm_task_resources_model;
$page = max( 1, get_query_var( 'paged' ) );
$project_id = $_GET['rt_project_id'];
$current_date = date( "Y-m-d" );
$dates = rt_get_next_dates( $current_date );
?>
<div class="list-heading">
	<div class="large-10 columns list-title">
		<h4><?php _e( 'Resources', RT_PM_TEXT_DOMAIN ) ?></h4>
	</div>
	<div class="large-2 columns">

	</div>
</div>
<div class="rt-main-resources-container">
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
				$user_ids = $rt_pm_task_resources_model->rtpm_get_resources_users( array( 'project_id' => $project_id ) );
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
		<a id="rtpm-get-prev-calender" class="rtpm-get-calender" href="#" data-flag="prev" data-date="<?php echo $first_date; ?>" data-calender="resources" data-project="<?php echo $project_id; ?>"><?php
			if ( wp_is_mobile() ) {
				echo "prev";
			} else {
				echo "<";
			}
			?></a>
		<table id="rtpm-resources-calender">
			<?php echo rt_create_resources_calender( $dates, $project_id ); ?>
		</table>
		<a id="rtpm-get-next-calender" class="rtpm-get-calender" href="#" data-flag="next" data-date="<?php echo $last_date; ?>" data-calender="resources" data-project="<?php echo $project_id; ?>"><?php
			if ( wp_is_mobile() ) {
				echo "next";
			} else {
				echo ">";
			}
			?></a>
	</div>
	<div class="rt-export-button-container"><a href="#" class="rt-export-button export-csv export-bottom">Export CSV</a></div>
	<div class="rt-export-button-container"><a href="#" class="rt-export-button export-pdf export-bottom">Export PDF</a></div>
</div>