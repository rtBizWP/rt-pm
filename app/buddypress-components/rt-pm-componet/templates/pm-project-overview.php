<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 27/3/15
 * Time: 3:46 PM
 */

global $rt_pm_project, $rt_pm_project_overview, $rt_bp_reports, $rt_person, $rt_pm_task, $rt_pm_time_entries;

$displayed_user_id = bp_displayed_user_id();

$rt_bp_reports->print_scripts();

//auto select fields after submit
$pre_selected_project_member = '';
$pre_selected_project_type = '';
$pre_selected_project_id = '';

//Pre select dropdown stuff
if ( ! empty( $_REQUEST['post'] ) ) {

	$filter_data = $_REQUEST['post'];

	if ( ! empty( $filter_data['project_member'] ) ) {
		$pre_selected_project_member = $filter_data['project_member'];
	}

	if ( ! empty( $filter_data['project_type'] ) ) {
		$pre_selected_project_type = $filter_data['project_type'];
	}

	if ( ! empty( $filter_data['project_id'] ) ) {
		$pre_selected_project_id = $filter_data['project_id'];
	}
}
?>

<div class="small-12 columns" xmlns:clear="http://www.w3.org/1999/xhtml">
	<h2><?php _e( 'Project Overview', RT_PM_TEXT_DOMAIN ) ?></h2>
</div>

<p><?php _e( 'Select your project to display the results', RT_PM_TEXT_DOMAIN ) ?></p>

<form action="" method="post">

<div class="small-12 medium-3 columns">
	<select name="post[project_member]">
		<option value=""><?php  _e('Team members', RT_PM_TEXT_DOMAIN ) ?></option>
		<?php $employees = rt_biz_get_employees();
		foreach ( $employees as $employee ):
			$user_id = rt_biz_get_wp_user_for_person( $employee->ID );
			?>
			<option value="<?php echo $user_id;  ?>" <?php selected( $pre_selected_project_member, $user_id ) ?>><?php echo rtbiz_get_user_displayname( $user_id ); ?></option>
		<?php endforeach; ?>
	</select>
</div>

<div class="small-12 medium-3 columns">
	<select name="post[project_type]">
		<option value=""><?php  _e('Project type', RT_PM_TEXT_DOMAIN ) ?></option>
		<?php
		$terms = get_terms( Rt_PM_Project_Type::$project_type_tax, array( 'hide_empty' => false ) );
		foreach ( $terms as $project_type ) :?>
			<option value="<?php echo $project_type->term_id ?>" <?php selected( $pre_selected_project_type, $project_type->term_id ) ?>><?php echo $project_type->name ?></option>
		<?php endforeach; ?>
	</select>
</div>

<div class="small-12 medium-3 columns">
	<select name="post[project_id]">
		<option value=""><?php _e( 'Project', RT_PM_TEXT_DOMAIN ) ?></option>
		<?php
		$project_args_arr = array(
			'nopaging' => true,
			'post_status' => 'any'
		);
		$project_data = $rt_pm_project->rtpm_get_project_data( $project_args_arr );

		foreach ( $project_data as $project ):?>
			<option value="<?php echo $project->ID?>" <?php selected( $pre_selected_project_id, $project->ID ) ?>><?php echo $project->post_title ?></option>
		<?php endforeach; ?>
	</select>
</div>

<div class="small-12 medium-3 columns">
	<input name="project_overview_filter" type="submit" value="<?php _e( 'Run', RT_PM_TEXT_DOMAIN ) ?>" />
</div>

</form>
<?php

//Comment this line for testing purpose
$filtered_projects = $rt_pm_project_overview->rtpm_filtered_project_block();

foreach ( $filtered_projects as $project ):

//Uncomment for the testing$project  = get_post('126');

$project_budget = get_post_meta( $project->ID, '_rtpm_project_budget', true );
?>

<br/><br/>
<div class="panel" style="overflow: auto;">

	<div class="small-12 medium-5 columns" style="border-right: 1px solid #E6E6E6;">
		<div class="activity-content">
			<div class="row activity-inner rt-biz-activity">
				<div class="rt-voxxi-content-box">
					<p><strong>Project Name: </strong><a class="project_edit_link"
					                                     href="<?php echo '' ?>"><?php echo $project->post_title; ?></a>
					</p>

					<p><strong>Create date: </strong><?php echo mysql2date( 'd M Y', $project->post_date ) ?>
					</p>

					<p><strong>Due
							date: </strong><?php echo mysql2date( 'd M Y', get_post_meta( $project->ID, 'post_duedate', true ) ); ?>
					</p>

					<p><strong>Post status: </strong><?php echo $project->post_status; ?></p>
				</div>

				<div class="row post-detail-comment column-title" style="height: 160px; overflow: hidden">
					<div class="column small-12">
						<p>
							<?php echo rtbiz_read_more( $project->post_content ); ?>
						</p>
					</div>
				</div>

				<div class="rt-pm-team" style="border-top: 1px solid #E6E6E6;">
					<div class="columns small-1 bdm-column" style="padding-right: 0;">
						<strong style="float:left;">BDM</strong>
						<?php $bdm = get_post_meta( $project->ID, 'business_manager', true );
						if ( ! empty( $bdm ) ) { ?>
							<a data-team-member-id="<?php echo $bdm; ?>" class="rtcontext-taskbox" style="float: left; clear: both;">
								<?php echo get_avatar( $bdm, 16 ); ?>
							</a>
						<?php } ?>
					</div>
					<div class="column small-10 team-column" style="float: left; border-left: 1px solid #E6E6E6; margin-left: 10px;">
						<strong class="team-title">Team</strong>

						<div class="team-member">
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
	</div>

	<div class="small-12 medium-7 column" >

			<div class="small-5 columns">
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
			<div class="small-4 columns" style="  position: absolute;left: 60%;">
				<div class="number-circle">
					<div class="height_fix"></div>
					<div
						class="content"><?php echo $rt_pm_task->rtpm_get_completed_task_per( $project->ID ) . '%' ?>
					</div>
				</div>
			</div>

		<div style="clear: both;">
			<?php $rt_pm_project_overview->rtpm_prepare_task_chart( $project->ID ) ?>
		</div>

	</div>


</div>
<?php
endforeach;

$rt_pm_project_overview->rtpm_filtered_project_block();

$rt_pm_project_overview->rtpm_render_project_grid();

$rt_pm_project_overview->rtpm_render_project_charts();