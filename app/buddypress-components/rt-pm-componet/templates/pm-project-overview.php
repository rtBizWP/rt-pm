<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 27/3/15
 * Time: 3:46 PM
 */

global $rt_pm_project, $rt_pm_project_overview, $rt_bp_reports;

$displayed_user_id = bp_displayed_user_id();

$rt_bp_reports->print_scripts();

$admin_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'admin' );

if (  current_user_can( $admin_cap ) ) {

    $project_data = $rt_pm_project->rtpm_get_project_data();

    $rt_pm_project_overview->rtpm_render_project_grid( $project_data );
} else{

    $projects = $rt_pm_project->rtpm_get_users_projects( $displayed_user_id );

    $args = array(
        'post__in' => ( ! empty( $projects ) ) ? $projects : array('0')
    );


    $project_data = $rt_pm_project->rtpm_get_project_data( $args );

    $rt_pm_project_overview->rtpm_render_project_grid( $project_data );
}

$rt_pm_project_overview->rtpm_render_project_charts();