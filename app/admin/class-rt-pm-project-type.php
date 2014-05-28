<?php

/**
 * Don't load this file directly!
 */
if (!defined('ABSPATH'))
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rt_PM_Project_Type
 *
 * @author udit
 */
if ( !class_exists( 'Rt_PM_Project_Type' ) ) {
	class Rt_PM_Project_Type {
		public function __construct() {

		}

		/**
		 * Create taxonomy for accounts
		 */
		function project_type( $post_type ) {
			$labels = array(
				'name' => __( 'Project Type' ),
				'search_items' => __( 'Search Project Type' ),
				'all_items' => __('All Project Types'),
				'edit_item' => __('Edit Project Type'),
				'update_item' => __('Update Project Type'),
				'add_new_item' => __('Add New Project Type'),
				'new_item_name' => __('New Project Type'),
				'menu_name' => __('Project Types'),
				'choose_from_most_used' => __('Choose from the most used Project Type'),
			);
			$editor_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'editor' );
			register_taxonomy(rtpm_attribute_taxonomy_name('project_type'), array( $post_type ), array(
				'hierarchical' => false,
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'update_count_callback' => 'rt_update_post_term_count',
				'rewrite' => array('slug' => rtpm_attribute_taxonomy_name('project_type')),
				'capabilities' => array(
					'manage_terms' => $editor_cap,
					'edit_terms' => $editor_cap,
					'delete_terms' => $editor_cap,
					'assign_terms' => $editor_cap,
				),
			));
		}

		function save_project_type( $post_id, $newLead ) {
			if ( !isset( $newLead['project_type'] ) ) {
				$newLead['project_type'] = array();
			}
			$contacts = array_map('intval', $newLead['project_type']);
			$contacts = array_unique($contacts);
			wp_set_post_terms( $post_id, $contacts, rtpm_attribute_taxonomy_name( 'project_type' ) );
		}

		/**
		 * Render Closing Reasons - DOM Element
		 */
		function get_project_types( $post_id, $user_edit = true ) {
			global $rtpm_form;
			$options = array();
			$terms = get_terms( rtpm_attribute_taxonomy_name( 'project_type' ), array( 'hide_empty' => false ) );
			$post_term = wp_get_post_terms( $post_id, rtpm_attribute_taxonomy_name( 'project_type' ), array( 'fields' => 'ids' ) );
			// Default Selected Term for the attribute. can beset via settings -- later on
			$selected_term = '-11111';
			if( !empty( $post_term ) ) {
				$selected_term = $post_term[0];
				$options[] = array(
					__('Select a Project Type') => '',
					'selected' => false,
				);
			} else {
				$options[] = array(
					__('Select a Project Type') => '',
					'selected' => true,
				);
			}
			foreach ($terms as $term) {
				$options[] = array(
					$term->name => $term->term_id,
					'selected' => ($term->term_id == $selected_term) ? true : false,
				);
			}
			$args = array(
				'id' => 'rtcrm_project_type',
				'name' => 'post[project_type][]',
				'rtForm_options' => $options,
			);

			if ( $user_edit ) {
				echo $rtpm_form->get_select( $args );
			} else {
                //TODO  attr not find
				$term = get_term( $selected_term, rtpm_attribute_taxonomy_name( $attr->attribute_name ) );
				echo '<span class="rtcrm_view_mode">'.$term->name.'</span>';
			}
		}
	}
}
