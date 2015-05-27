<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

global $rt_pm_task, $rt_pm_task_resources_model, $rt_pm_project_resources;

$current_date = date( "Y-m-d" );
$dates        = rt_get_next_dates( $current_date );
$project_ids  = $rt_pm_task_resources_model->rtpm_get_users_projects( bp_displayed_user_id() );

// lets start with all projects
if ( ! empty( $project_ids ) ) { ?>
	<div class="list-heading">
		<div class="large-10 columns list-title">
			<h4><?php _e( 'My Tasks', RT_PM_TEXT_DOMAIN ) ?></h4>
		</div>
		<div class="large-2 columns">

		</div>
	</div>

	<div class="rt-main-resources-container rt-my-tasks-container">
		<div class="rt-export-button-container"><a href="#" class="rt-export-button export-csv">Export CSV</a></div>
		<div class="rt-export-button-container"><a href="#" class="rt-export-button export-pdf">Export PDF</a></div>
		<div class="rt-left-container">
			<table>
				<thead>
				<tr>
					<td>
						<?php _e( 'My Tasks by Project', RT_PM_TEXT_DOMAIN ); ?>
					</td>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $project_ids as $key => $project_id ) { ?>
					<?php $task_ids = $rt_pm_task_resources_model->rtpm_get_all_task_id_by_user( bp_displayed_user_id(), $project_id );

					if( empty( $task_ids ) )
						continue;

					$task_list = $rt_pm_task->rtpm_get_task_data( array( 'post__in' => $task_ids, 'nopaging' => true, 'no_found_rows' => true ) );

					?>
					<tr>
						<td class="rt_project_resources_title"><?php echo mb_strimwidth( get_post_field( 'post_title', $project_id ), 0, 25, '..' ); ?></td>
					</tr>
					<?php
					foreach ( $task_list as $key => $task_data ) { ?>
						<tr>
							<td class="rt_project_tasks">
								<div class="rtpm-show-user-tooltip rtpm-task-icon">
									<?php echo mb_strimwidth( $task_data->post_title, 0, 20, '..' ); ?>
								</div>
							</td>
						</tr>
					<?php
					}
				}
				?>
				</tbody>
				<tfoot>
				<tr>
					<th>TOTAL HOURS</th>
				</tr>
				</tfoot>

			</table>
		</div>
		<div class="rt-right-container">
			<?php
			$first_date = $dates[0];
			$last_date  = $dates[ count( $dates ) - 1 ];
			?>
			<a id="rtpm-get-prev-calender" class="rtpm-get-calender" data-flag="prev"
			   data-date="<?php echo $first_date; ?>" data-calender="my-tasks"><?php
				if ( wp_is_mobile() ) {
					echo "prev";
				} else {
					echo "<";
				}
				?></a>
			<table id="rtpm-resources-calender">
				<?php echo $rt_pm_project_resources->rt_create_my_task_calender( $dates ); ?>
			</table>
			<a id="rtpm-get-next-calender" class="rtpm-get-calender" data-flag="next"
			   data-date="<?php echo $last_date; ?>" data-calender="my-tasks"><?php
				if ( wp_is_mobile() ) {
					echo "next";
				} else {
					echo ">";
				}
				?></a>
		</div>
		<div class="rt-export-button-container"><a href="#" class="rt-export-button export-csv export-bottom">Export
				CSV</a></div>
		<div class="rt-export-button-container"><a href="#" class="rt-export-button export-pdf export-bottom">Export
				PDF</a></div>
	</div>    <?php } else {
	?>
	<div>No tasks to show</div>
<?php
}