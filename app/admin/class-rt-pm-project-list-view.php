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
                                <a href="<?php echo admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&{$rt_pm_project->post_type}_id=" . get_the_id()); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                                    <h2><?php
                                        echo '#';
                                        the_ID();
                                        echo ' : ';
                                        the_title();
                                        ?></h2>
                                </a>
                                <div><strong><?php _e('Status : '); ?></strong><?php echo ucfirst(get_post_status(get_the_ID())); ?></div>
                                <div><strong><?php _e('Type : '); ?></strong><?php
                                    $post_term = wp_get_post_terms(get_the_ID(), Rt_PM_Project_Type::$project_type_tax, array('fields' => 'ids'));
                                    if (!empty($post_term)) {
                                        $term = get_term_by('id', $post_term[0], Rt_PM_Project_Type::$project_type_tax);
                                        echo $term->name;
                                    }
                                    ?></div>
                                <div><strong><?php _e('Project Manager : '); ?></strong><?php
                                    $pm = get_user_by('id', get_post_meta(get_the_ID(), 'project_manager', true));
                                    echo isset($pm->display_name)?$pm->display_name:"";
                                    ?></div>
                                <div><strong><?php _e('Business Manager : '); ?></strong><?php
                                    $bm = get_user_by('id', get_post_meta(get_the_ID(), 'business_manager', true));
                                    echo isset($bm->display_name) ? $bm->display_name : "";
                                    ?></div>
                                <br />
                                <div><strong><?php _e('Commenced on : '); ?></strong><?php
                                    $dt = new DateTime(get_post_field('post_date', get_the_ID()));
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

        public function ui_create_project($user_edit){
            $results = Rt_PM_Utils::get_pm_rtcamp_user();
            $arrProjectMember[] = array();
            $subProjectMemberHTML = "";
            if( !empty( $results ) ) {
                foreach ( $results as $author ) {
                    $arrProjectMember[] = array("id" => $author->ID, "label" => $author->display_name, "imghtml" => get_avatar($author->user_email, 24), 'user_edit_link'=>  get_edit_user_link($author->ID));
                }
            }?>

            <div class="large-12 small-12 columns ui-sortable meta-box-sortables">
                <div class="large-8 small-8 columns">
                    <input type="text" id="new_rt_ptoject_title" name="post[project_title]" value="" placeholder="Project Title">
                </div>
                <div class="large-12 small-12 columns">
                    <textarea id="new_rt_project_contentt" rows="20" autocomplete="off" cols="40"  name="post[project_content]" placeholder="Project Description"></textarea>
                </div>
                <div class="large-5 small-12 columns">
					<h6 class="hndle"><span><i class="general foundicon-tools"></i> <?php _e( 'Project Manager' ); ?></span></h6>
                    <div class="inside">
                        <div class="large-12 mobile-large-3 columns">
							<?php if( $user_edit ) { ?>
                                <select name="post[project_manager]" >
									<option value=""><?php _e( 'Select PM' ); ?></option>
                                    <?php
                                    if (!empty($results)) {
                                        foreach ($results as $author) {
                                            echo '<option value="' . $author->ID . '">' . $author->display_name . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="large-6 small-12 column">
                    <h6 class="hndle"><span><i class="foundicon-smiley"></i> <?php _e("Project Members"); ?></span></h6>
                    <div style="padding-bottom: 0" class="inside">
                        <script>
                                        var arr_project_member_user =<?php echo json_encode($arrProjectMember); ?>;
                        </script>
                        <?php if ( $user_edit ) { ?>
                            <input style="margin-bottom:10px" type="text" placeholder="Type User Name to select" id="project_member_user_ac" />
                            <?php } ?>
                        <ul id="divProjectMemberList" class="large-block-grid-1 small-block-grid-1">
            <?php echo $subProjectMemberHTML; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="large-5 columns">
                <input class="mybutton add-project right" type="submit" id="submit-project" name="project[submitted]" value="<?php _e("Add Project"); ?>">
            </div>
        <?php }

        public function page_action(){
            global $rt_pm_project;
            $error = "";
            if ( !isset($_REQUEST['post_type']) || $_REQUEST['post_type'] != $rt_pm_project->post_type ) {
                wp_die("Opsss!! You are in restricted area");
            }
            if ( isset( $_POST['post'] ) ) {
                $project_meta = $_REQUEST['post'];
                if( !isset( $project_meta['project_title'] ) || empty( $project_meta['project_title'] ) ){
                    $error= "Please Enter Project Title";
                }else{
                    $project_meta['project_date'] = current_time( 'mysql' );
                    $project_meta['project_date_gmt'] = gmdate('Y-m-d H:i:s');
                    $newProject = array(
                        'post_date' => $project_meta['project_date'],
                        'post_date_gmt' => $project_meta['project_date_gmt'],
                        'post_content' => $project_meta['project_content'],
                        'post_status' => 'new',
                        'post_title' => $project_meta['project_title'],
                        'post_type' => $rt_pm_project->post_type,
                    );
                    $newProjectID = wp_insert_post($newProject);
                    update_post_meta( $newProjectID, 'project_manager', $project_meta['project_manager'] );
					update_post_meta( $newProjectID, 'project_member', $project_meta['project_member'] );
                    return 1;
                }
            }
            return $error;
        }
    }
}
