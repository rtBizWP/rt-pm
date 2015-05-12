<?php

/**
 * rtPM Functions
 *
 * Helper functions for rtPM
 *
 * @author udit
 */
function rtpm_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( $args && is_array( $args ) )
		extract( $args );

	$located = rtpm_locate_template( $template_name, $template_path, $default_path );

	do_action( 'rtpm_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'rtpm_after_template_part', $template_name, $template_path, $located, $args );
}

function rtpm_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	global $rt_wp_pm;
	if ( ! $template_path ) {
		$template_path = $rt_wp_pm->templateURL;
	}
	if ( ! $default_path ) {
		$default_path = RT_PM_PATH_TEMPLATES;
	}

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
	return apply_filters( 'rtpm_locate_template', $template, $template_name, $template_path );
}

function rtpm_sanitize_taxonomy_name( $taxonomy ) {
	$taxonomy = strtolower( stripslashes( strip_tags( $taxonomy ) ) );
	$taxonomy = preg_replace( '/&.+?;/', '', $taxonomy ); // Kill entities
	$taxonomy = str_replace( array( '.', '\'', '"' ), '', $taxonomy ); // Kill quotes and full stops.
	$taxonomy = str_replace( array( ' ', '_' ), '-', $taxonomy ); // Replace spaces and underscores.

	return $taxonomy;
}

function rtpm_post_type_name( $name ) {
	return 'rt_' . rtpm_sanitize_taxonomy_name( $name );
}

function rtpm_attribute_taxonomy_name( $name ) {
	return 'rt_' . rtpm_sanitize_taxonomy_name( $name );
}

function rtpm_get_time_entry_table_name() {
	global $rt_pm_time_entries_model;
	return $rt_pm_time_entries_model->table_name;
}

function rtpm_get_settings() {
	$default = array(
		'attach_contacts' => 'yes',
		'attach_accounts' => 'yes',
		'system_email' => '',
		'outbound_emails' => '',
	);
	$settings = get_site_option( 'rt_pm_settings', $default );
	return $settings;
}

function rtpm_update_settings( $key, $value ) {
	
}

function rtpm_update_post_term_count( $terms, $taxonomy ) {
	global $wpdb;

	$object_types = (array) $taxonomy->object_type;

	foreach ( $object_types as &$object_type )
		list( $object_type ) = explode( ':', $object_type );

	$object_types = array_unique( $object_types );

	if ( false !== ( $check_attachments = array_search( 'attachment', $object_types ) ) ) {
		unset( $object_types[ $check_attachments ] );
		$check_attachments = true;
	}

	if ( $object_types )
		$object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );

	foreach ( (array) $terms as $term ) {
		$count = 0;

		// Attachments can be 'inherit' status, we need to base count off the parent's status if so
		if ( $check_attachments )
			$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts p1 WHERE p1.ID = $wpdb->term_relationships.object_id  AND post_type = 'attachment' AND term_taxonomy_id = %d", $term ) );

		if ( $object_types )
			$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id  AND post_type IN ('" . implode("', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );

		do_action( 'edit_term_taxonomy', $term, $taxonomy );
		$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
		do_action( 'edited_term_taxonomy', $term, $taxonomy );
	}
}

/**
 * Render BDM selectbox
 * @param $business_manager
 */
function rtpm_render_manager_selectbox( $project_manager ){ ?>
	<select name="post[project_manager]" >
		<option value=""><?php _e( 'Select PM' ); ?></option>
		<?php
		$employees = rt_biz_get_employees();

		if (!empty( $employees )) {
			foreach ($employees as $bm) {

				$employee_wp_user_id = rt_biz_get_wp_user_for_person( $bm->ID );

				if ( $employee_wp_user_id == $project_manager ) {
					$selected = " selected";
				} else {
					$selected = " ";
				}
				echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rtbiz_get_user_displayname( $employee_wp_user_id ) . '</option>';
			}
		}
		?>
	</select>
<?php }


/**
 * Render BDM selectbox
 * @param $business_manager
 */
function rt_pm_render_bdm_selectbox( $business_manager ){ ?>

	<select name="post[business_manager]" >
		<option value=""><?php _e( 'Select BM' ); ?></option>
		<?php

		$employees = rt_biz_get_employees();

		if (!empty( $employees )) {
			foreach ($employees as $bm) {

				$employee_wp_user_id = rt_biz_get_wp_user_for_person( $bm->ID );

				if ( $employee_wp_user_id == $business_manager) {
					$selected = " selected";
				} else {
					$selected = " ";
				}
				echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rtbiz_get_user_displayname( $employee_wp_user_id ) . '</option>';
			}
		}
		?>
	</select>

<?php }
