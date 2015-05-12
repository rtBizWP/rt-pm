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
            $editor_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'editor' );
            if ( $project_list_query->have_posts() ) {
                ?>
                <div class="tablenav top">
                    <div class="view-switch">
                        <a href="<?php echo admin_url("edit.php?post_type=rt_project&mode=list"); ?>" <img id="view-switch-list" src="<?php echo esc_url( includes_url( 'images/blank.gif' ) ); ?>" width="20" height="20" title="List View" alt="List View"></a>
                        <a href="<?php echo admin_url("edit.php?post_type=rt_project&page=rtpm-all-rt_project"); ?>"  class="current"><img id="view-switch-excerpt" src="<?php echo esc_url( includes_url( 'images/blank.gif' ) ); ?>" width="20" height="20" title="Excerpt View" alt="Excerpt View"></a>
                    </div>
                </div>
                <?php

                    while ($project_list_query->have_posts()) : $project_list_query->the_post();
                        ?>
                        <div class="large-3 small-4 columns">
                            <article id="rtpm-<?php the_id() ?>" <?php post_class('rtpm_admin '); ?>>
                                <?php if( current_user_can( $editor_cap ) || get_current_user_id() == intval( get_the_author_meta('ID') ) ) { ?>
                                <a href="<?php echo admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id=" . get_the_id()); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                                    <h2><?php
                                        echo '#'.get_post_meta( get_the_ID(), 'rtpm_job_no', true );
                                        echo ' : ';
                                        the_title();
                                        ?></h2>
                                </a>
                            <?php }else { ?>
                                    <h2><?php
                                        echo '#'.get_post_meta( get_the_ID(), 'rtpm_job_no', true );
                                        echo ' : ';
                                        the_title();
                                        ?></h2>
                            <?php } ?>
                                <div><strong><?php _e('Status : '); ?></strong><?php echo ucfirst(get_post_status(get_the_ID())); ?></div>
                                <div><strong><?php _e('Type : '); ?></strong><?php
                                    $post_term = wp_get_post_terms(get_the_ID(), Rt_PM_Project_Type::$project_type_tax, array('fields' => 'ids'));
                                    if (!empty($post_term)) {
                                        $term = get_term_by('id', $post_term[0], Rt_PM_Project_Type::$project_type_tax);
                                        echo $term->name;
                                    }
                                    ?></div>
                                <div><strong><?php _e('Project Manager : '); ?></strong><?php
                                    $pm_wp_user_id = get_post_meta(get_the_ID(), 'project_manager', true);
                                    //echo isset($pm->display_name)?$pm->display_name:"";
                                    echo rtbiz_get_user_displayname( $pm_wp_user_id );
                                    ?></div>
                                <div><strong><?php _e('Business Manager : '); ?></strong><?php
                                    $bm_wp_user_id =  get_post_meta(get_the_ID(), 'business_manager', true);
                                    echo !empty( $bm_wp_user_id ) ? rtbiz_get_user_displayname( $bm_wp_user_id ) : "";
                                    ?></div>
                                <br />
                                <div><strong><?php _e('Created on : '); ?></strong><?php
                                    $dt = rt_convert_strdate_to_usertimestamp(get_post_field('post_date_gmt', get_the_ID()));
									$format = $dt->format('Y-m-d');
                                    echo !empty($format)?$format:"";
                                    ?></div>
                                <div><strong><?php _e('Due on : '); ?></strong><?php
                                    if (get_post_meta(get_the_ID(), 'post_duedate', true)) {
                                        $dt = new DateTime(get_post_meta(get_the_ID(), 'post_duedate', true));
										$format = $dt->format('Y-m-d');
										echo !empty($format)?$format:"";
                                    }
                                    ?></div>
                        <?php do_action('rt_pm_project_list_view_details', get_the_ID()) ?>
                                <br />
                                <div class="rtpm-project-detail">
                        <?php echo $this->get_the_project_excerpt(get_the_content()); ?>
                                </div>
                            </article>
                        </div>
                        <?php
                    endwhile;
            }
            wp_reset_query();
        }

        function get_the_project_excerpt($str){
            $excerpt = strip_shortcodes($str);
            $excerpt = strip_tags($str);
            $the_str = substr($excerpt, 0, 250);
            if( strlen($str)> 250 ){
                $the_str = $the_str . '...';
            }
            return $the_str;
        }

    }
}
