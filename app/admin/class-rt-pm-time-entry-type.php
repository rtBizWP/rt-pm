<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of Rt_PM_Time_Entry_Type
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_PM_Time_Entry_Type' ) ) {

	class Rt_PM_Time_Entry_Type {

		static $time_entry_type_tax = 'rtpm_time_entry_type';

		public function __construct() {
			add_action( 'init', array( $this, 'register_time_entry_type' ) );

			add_action( self::$time_entry_type_tax.'_add_form_fields', array( $this, 'add_meta_field' ), 10, 1 );
			add_action( self::$time_entry_type_tax.'_edit_form_fields', array( $this, 'edit_meta_field' ), 10, 2 );
			add_action( 'edited_'.self::$time_entry_type_tax, array( $this, 'save_meta_field' ), 10, 2 );
			add_action( 'create_'.self::$time_entry_type_tax, array( $this, 'save_meta_field' ), 10, 2 );
			add_filter('manage_edit-'.self::$time_entry_type_tax.'_columns', array( $this, 'add_meta_field_columns' ), 10, 1 );
			add_filter('manage_'.self::$time_entry_type_tax.'_custom_column', array( $this, 'add_meta_field_columns_content' ), 10, 3 );
		}

		function add_meta_field( $taxonomy ) { ?>
			<div class="form-field">
				<label for="term_meta[time_entry_charge_rate]"><?php _e( 'Charge Rate' ); ?></label>
				<input type="number" min="0" name="term_meta[time_entry_charge_rate]" id="term_meta[time_entry_charge_rate]" />
				<p class="description"><?php _e( 'Global Charge Rate for Time Entry Type' ); ?></p>
			</div>
		<?php }

		function edit_meta_field( $term, $taxonomy ) {
			// put the term ID into a variable
			$t_id = $term->term_id;

			// retrieve the existing value(s) for this meta field. This returns an array
			$term_meta = get_option( "rtpm_taxonomy_$t_id" ); ?>
			<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[time_entry_charge_rate]"><?php _e( 'Charge Rate' ); ?></label></th>
				<td>
					<input type="number" min="0" name="term_meta[time_entry_charge_rate]" id="term_meta[time_entry_charge_rate]" value="<?php echo $term_meta['time_entry_charge_rate']; ?>" />
					<p class="description"><?php _e( 'Global Charge Rate for Time Entry Type' ); ?></p>
				</td>
			</tr>
		<?php }

		function save_meta_field( $term_id, $tt_id ) {
			if ( isset( $_POST['term_meta'] ) ) {
				$t_id = $term_id;
				$term_meta = get_option( "rtpm_taxonomy_$t_id" );
				$cat_keys = array_keys( $_POST['term_meta'] );
				foreach ( $cat_keys as $key ) {
					if ( isset ( $_POST['term_meta'][$key] ) ) {
						$term_meta[$key] = $_POST['term_meta'][$key];
					}
				}
				// Save the option array.
				update_option( "rtpm_taxonomy_$t_id", $term_meta );
			}
		}

		static function get_charge_rate_meta_field( $term_id ) {
			$term_meta = get_option( "rtpm_taxonomy_$term_id" );
			return floatval( $term_meta['time_entry_charge_rate'] );
		}

		function add_meta_field_columns( $columns ) {
			$columns['time_entry_charge_rate'] = __( 'Charge Rate' );
			return $columns;
		}

		function add_meta_field_columns_content( $content ,$column_name, $term_id ) {
			switch( $column_name ) {
				case 'time_entry_charge_rate' :
					$term_meta = get_option( "rtpm_taxonomy_$term_id" );
					$content = $term_meta['time_entry_charge_rate'];
					break;
			}
			return $content;
		}

		function register_time_entry_type() {
            // Tax Labels
            $labels = array(
                'name'              => _x( 'Time Entry Types', 'taxonomy general name', 'time_entry_type' ),
                'singular_name'     => _x( 'Time Entry Type', 'taxonomy singular name', 'time_entry_type' ),
                'search_items'      => __( 'Search Time Entry Types', 'time_entry_type' ),
                'all_items'         => __( 'All Time Entry Types', 'time_entry_type' ),
                'parent_item'       => __( 'Parent Time Entry Type', 'time_entry_type' ),
                'parent_item_colon' => __( 'Parent Time Entry Type:', 'time_entry_type' ),
                'edit_item'         => __( 'Edit Time Entry Type', 'time_entry_type' ),
                'update_item'       => __( 'Update Time Entry Type', 'time_entry_type' ),
                'add_new_item'      => __( 'Add New Time Entry Type', 'time_entry_type' ),
                'new_item_name'     => __( 'New Time Entry Type Name', 'time_entry_type' ),
                'menu_name'         => __( 'Time Entry Types', 'time_entry_type' ),
            );

            $args = array(
                'hierarchical' => FALSE,
                'labels'       => $labels,
                'show_ui'      => TRUE,
                'show_admin_column' => TRUE,
                'query_var'    => TRUE,
                'rewrite'      => TRUE,
                'update_count_callback' => '_update_generic_term_count'
            );

			register_taxonomy(
				self::$time_entry_type_tax,
				Rt_PM_Time_Entries::$post_type,
				$args
			);
		}

	}

}