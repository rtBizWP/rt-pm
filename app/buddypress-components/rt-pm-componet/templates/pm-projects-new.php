<?php
	global $rt_pm_project, $rt_pm_bp_pm, $rt_pm_bp_pm_project, $bp, $wpdb,  $wp_query;
	$_REQUEST['post_type'] = 'rt_project';
	$rt_pm_bp_pm_project->custom_page_ui();
?>