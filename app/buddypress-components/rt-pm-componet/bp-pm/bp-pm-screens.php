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
 * If your component uses a top-level directory, this function will catch the requests and load
 * the index page.
 *
 * @package BuddyPress_Template_Pack
 * @since 1.6
 */
function bp_pm_screen() {

    bp_core_load_template( 'members/single/home'  );	
}
add_action( 'bp_screens', 'bp_hrm_screen' );

function bp_pm_projects() { 
	add_filter('bp_located_template','load_projects_template');
}

function load_projects_template() {
    return  RT_PM_BP_PM_PATH.'/templates/pm-projects.php';
}


function bp_pm_details() {
    add_filter('bp_located_template','load_details_template');
}

function load_details_template() {
    return  RT_PM_BP_PM_PATH.'/templates/pm-details.php';
}

function bp_pm_attachments() {
	add_filter('bp_located_template','load_attachments_template');
}

function load_attachments_template() {
    return  RT_PM_BP_PM_PATH.'/templates/pm-attachments.php';
}

function bp_pm_tasks() {
	add_filter('bp_located_template','load_tasks_template');
}

function load_tasks_template() {
    return  RT_PM_BP_PM_PATH.'/templates/pm-tasks.php';
}
?>