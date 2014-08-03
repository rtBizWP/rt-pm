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
 * Description of class-rt-pm-time-entries
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Custom_Media_Fields' ) ) {

	class Rt_Custom_Media_Fields {
		//put your code here

        /*private $media_fields;
        public $media_attributes_tag = 'media_tag';*/

        public function __construct() {

            add_action( 'init', array($this,'register_media_attributes') );
            add_filter('wp_get_attachment_url', array($this,'media_link_updated'),10,2);

		}

        function register_media_attributes() {

            $attachment_taxonomies = array();

            // Tags
            $labels = array(
                'name'              => _x( 'Categories', 'taxonomy general name', 'attachment_taxonomies' ),
                'singular_name'     => _x( 'Category', 'taxonomy singular name', 'attachment_taxonomies' ),
                'search_items'      => __( 'Search Categories', 'attachment_taxonomies' ),
                'all_items'         => __( 'All Categories', 'attachment_taxonomies' ),
                'parent_item'       => __( 'Parent Category', 'attachment_taxonomies' ),
                'parent_item_colon' => __( 'Parent Category:', 'attachment_taxonomies' ),
                'edit_item'         => __( 'Edit Category', 'attachment_taxonomies' ),
                'update_item'       => __( 'Update Category', 'attachment_taxonomies' ),
                'add_new_item'      => __( 'Add New Category', 'attachment_taxonomies' ),
                'new_item_name'     => __( 'New Category Name', 'attachment_taxonomies' ),
                'menu_name'         => __( 'Categories', 'attachment_taxonomies' ),
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

            $attachment_taxonomies[] = array(
                'taxonomy'  => 'attachment_tag',
                'post_type' => 'attachment',
                'args'      => $args
            );

            $attachment_taxonomies = apply_filters( 'rt_attachment_taxonomies', $attachment_taxonomies );
            foreach ( $attachment_taxonomies as $attachment_taxonomy ) {
                register_taxonomy(
                    $attachment_taxonomy['taxonomy'],
                    $attachment_taxonomy['post_type'],
                    $attachment_taxonomy['args']
                );
            }
        }

        function media_link_updated($url, $post_ID) {
            if ( get_post_meta( $post_ID, '_wp_attached_external_file', true) == 1){
                $url = get_the_guid( $post_ID);
            }
            return $url;
        }
	}
}
