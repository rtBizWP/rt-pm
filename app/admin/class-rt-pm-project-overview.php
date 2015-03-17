<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 17/3/15
 * Time: 12:39 PM
 */
class Rt_Pm_Project_Overview {

    /**
     * Return singleton instance of class
     *
     * @return Rt_Pm_Project_Overview
     */
    public static function factory() {
        static $instance = false;
        if ( ! $instance  ) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Placeholder method
     */
    public function __construct() {
        $this->setup();
    }

    /**
     * Setup actions and filters
     */
    public function setup() {

    }

    /**
     * Project list
     * @param int $author_id
     * @param string $project_status_type
     * @param null $date_query
     * @return array
     */
    public function rtpm_get_project_data( $author_id = 0, $project_status_type = 'any', $date_query = null ){
        global $rt_pm_project;

        $args = array(
            'nopaging' => true,
            'post_status' => array( $project_status_type ),
            'post_type' => $rt_pm_project->post_type,
            'no_found_rows' => true,
        );

        if( $author_id !== 0 )
            $args['author'] = $author_id;


        if( null !== $date_query )
            $args['date_query'] = $date_query;

        $query = new WP_Query( $args );

        return $query->posts;
    }


    public function rtpm_render_project_grid( $project_data ) {
        global $rt_biz_wall, $rt_pm_task;
        ?>

        <ul id="activity-stream" class="activity-list item-list">
            <?php
            if( ! empty( $project_data ) ) {

                //Masonry container fix
                if (count( $project_data ) <= 1)
                    wp_localize_script('rt-biz-admin', 'NOT_INIT_MASONRY', 'false');

                foreach( $project_data as $project ): ?>

                    <li class="activity-item">
                        <div class="activity-content">
                            <div class="row activity-inner rt-biz-activity">
                                <div class="rt-voxxi-content-box">
                                        <p><strong>Project Name: </strong><?php echo $project->post_title; ?></p>
                                        <p><strong>Create date: </strong><?php echo $project->post_date; ?></p>
                                        <p><strong>Due date: </strong><?php echo get_post_meta( $project->ID, 'post_duedate', true ); ?></p>
                                        <p><strong>Post status: </strong><?php echo $project->post_status; ?></p>
                                </div>
                                <div class="post-detail-comment column-title">
                                    <div class="columns small-12">
                                        <p>
                                           <?php echo  $project->post_content ?>
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <?php echo $rt_pm_task->rtpm_overdue_task_count( $project->ID ) ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach;
            } ?>
        </ul>

    <?php }

}