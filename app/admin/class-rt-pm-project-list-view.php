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
                    <div class="large-3 small-4 columns">
                        <article id="rtpm-<?php the_id() ?>" <?php post_class( 'rtpm_admin ' ); ?>>
                            <a href="<?php echo  admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-add-{$rt_pm_project->post_type}&tab={$rt_pm_project->post_type}-task&{$rt_pm_project->post_type}_id=".get_the_id()); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                                <h2><?php the_title(); ?></h2>
                                <!--<h4 class="rtpm-project-sub-title"><?php /*the_content(); */?></h4>-->
                                <div class="rtpm-project-detail">
                                    <?php echo $this->get_the_project_excerpt( get_the_content() ); ?>
                                </div>
                            </a>
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
                    <textarea id="new_rt_project_contentt" rows="20" autocomplete="off" cols="40"  name="post[project_content]" placeholder="Details about Project"></textarea>
                </div>
                <div class="large-5 small-12 columns">
                    <h6 class="hndle"><span><i class="general foundicon-tools"></i> Assign Manager</span></h6>
                    <div class="inside">
                        <div class="large-12 mobile-large-3 columns">
                            <?php if( $user_edit ) { ?>
                                <select name="post[project_manager]" >
                                    <?php
                                    if (!empty($results)) {
                                        foreach ($results as $author) {
                                            if ($author->ID == get_current_user_id()) {
                                                $selected = " selected";
                                            } else {
                                                $selected = " ";
                                            }
                                            echo '<option value="' . $author->ID . '"' . $selected . '>' . $author->display_name . '</option>';
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
                            var ac_auth_token = '<?php echo get_user_meta(get_current_user_id(), 'rtcrm_activecollab_token', true); ?>';
                            var ac_default_project = '<?php echo get_user_meta(get_current_user_id(), 'rtcrm_activecollab_default_project', true); ?>';
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
            <div class="large-5 columns rtcrm-sticky">
                    <input class="mybutton add-project right" type="submit" id="submit-project" name="project[submitted]" value="<?php _e("Add Project"); ?>">
            </div>
        <?php }

        public function page_action(){
            global $rt_pm_project;
            if( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] != $rt_pm_project->post_type ) {
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
                        'post_author' => $project_meta['project_manager'],
                        'post_date' => $project_meta['project_date'],
                        'post_date_gmt' => $project_meta['project_date_gmt'],
                        'post_content' => $project_meta['project_content'],
                        'post_status' => 'New',
                        'post_title' => $project_meta['project_title'],
                        'post_type' => $rt_pm_project->post_type,
                    );
                    $newProjectID = wp_insert_post($newProject);
                    update_post_meta( $newProjectID, 'project_member', $project_meta['project_member'] );
                    return 1;
                }
            }
            return $error;
        }
	}
}