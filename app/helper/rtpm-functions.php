<?php

/**
 * rtPM Functions
 *
 * Helper functions for rtPM
 *
 * @author udit
 */

function rtpm_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( $args && is_array($args) )
		extract( $args );

	$located = rtpm_locate_template( $template_name, $template_path, $default_path );

	do_action( 'rtpm_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'rtpm_after_template_part', $template_name, $template_path, $located, $args );
}

function rtpm_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	global $rt_wp_pm;
	if ( ! $template_path ) { $template_path = $rt_wp_pm->templateURL; }
	if ( ! $default_path ) { $default_path = RT_PM_PATH_TEMPLATES; }

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters('rtpm_locate_template', $template, $template_name, $template_path);
}

