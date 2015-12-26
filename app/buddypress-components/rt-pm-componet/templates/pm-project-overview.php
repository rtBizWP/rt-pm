<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 27/3/15
 * Time: 3:46 PM
 */

global $rt_pm_project, $rt_pm_project_overview, $rt_bp_reports, $rt_person;

$displayed_user_id = bp_displayed_user_id();

$rt_bp_reports->print_scripts();
?>

<div class="small-12 columns">
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
			<option value="<?php echo $user_id;  ?>"><?php echo rtbiz_get_user_displayname( $user_id ); ?></option>
		<?php endforeach; ?>
	</select>
</div>

<div class="small-12 medium-3 columns">
	<select name="post[project_type]">
		<option value=""><?php  _e('Project type', RT_PM_TEXT_DOMAIN ) ?></option>
		<?php
		$terms = get_terms( Rt_PM_Project_Type::$project_type_tax, array( 'hide_empty' => false ) );
		foreach ( $terms as $project_type ) :?>
			<option value="<?php echo $project_type->term_id ?>"><?php echo $project_type->name ?></option>
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
			<option value="<?php echo $project->ID?>"><?php echo $project->post_title ?></option>
		<?php endforeach; ?>
	</select>
</div>

<div class="small-12 medium-3 columns">
	<input name="project_overview_filter" type="submit" value="<?php _e( 'Search', RT_PM_TEXT_DOMAIN ) ?>" />
</div>

</form>
<?php

$rt_pm_project_overview->rtpm_render_project_grid();

$rt_pm_project_overview->rtpm_render_project_charts();