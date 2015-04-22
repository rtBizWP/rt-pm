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

$rt_pm_project_overview->rtpm_render_project_grid();

$rt_pm_project_overview->rtpm_render_project_charts();