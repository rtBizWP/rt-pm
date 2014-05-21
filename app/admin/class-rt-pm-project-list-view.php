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
 * Description of Rt_PM_Project_List_View
 *
 * @author dipesh
 */

if ( !class_exists( 'Rt_PM_Project_List_View' ) ) {
	class Rt_PM_Project_List_View  {

		public function __construct() {

		}

        public function table_view(){
            global $rt_pm_project;
            $args = array( 'post_type' => $rt_pm_project->post_type );
            $project_list_query = null;
            $project_list_query = new WP_Query($args);

            if( $project_list_query->have_posts() ) {
                while ($project_list_query->have_posts()) : $project_list_query->the_post(); ?>
                    <article id="rtpm-<?php the_id() ?>" <?php post_class( 'rtpm_admin' ); ?>>
                        <a href="#" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                            <h4><?php the_title(); ?></h4>
                            <div class="rtpm-project-detail"><?php the_content(); ?></div>
                            <div class="rtpm-project-detail">
                            </div>
                            <div class="rtpm-project-member">
                            </div>
                        </a>
                    </article>

                <?php
                endwhile;
            }
            wp_reset_query();
        }

	}
}
