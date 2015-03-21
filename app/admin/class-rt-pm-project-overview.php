<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 17/3/15
 * Time: 12:39 PM
 */
class Rt_Pm_Project_Overview {

    private $rtpm_chars = array();

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

        add_action( 'wp_head', array( $this, 'rtpm_print_style' ) );
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
                                <hr/>
                                <div class="row">
                                    <div class="small-6 columns">
                                        <table class="no-outer-border">
                                            <tr class="orange-text">
                                                <td><strong class="right"><?php _e( 'Over Due', RT_PM_TEXT_DOMAIN ) ?></strong></td>
                                                <td><strong class="left"><?php echo $overdue_task = $rt_pm_task->rtpm_overdue_task_count( $project->ID ) ?></strong></td>
                                            </tr>
                                            <tr class="blue-text">
                                                <td><strong class="right"><?php _e( 'Open', RT_PM_TEXT_DOMAIN ) ?></strong></td>
                                                <td><strong class="left"><?php  echo $open_task = $rt_pm_task->rtpm_open_task_count( $project->ID ) ?></strong></td>
                                            </tr>
                                            <tr class="green-text">
                                                <td><strong class="right"><?php _e( 'Completed', RT_PM_TEXT_DOMAIN ) ?></strong></td>
                                                <td><strong class="left"><?php  echo $completed_task = $rt_pm_task->rtpm_completed_task_count( $project->ID ) ?></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="small-6 columns" style="  position: absolute;left: 60%;top: 20%;">
                                        <div class="number-circle">
                                            <div class="height_fix"></div>
                                            <div class="content"><?php echo $rt_pm_task->rtpm_get_completed_task_per( $project->ID ) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <hr/>

                                <?php $this->rtpm_prepare_task_chart( $project->ID ) ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach;
            } ?>
        </ul>

    <?php }


    /**
     * Render all charts
     */
    public function rtpm_render_project_charts() {

        global $rt_pm_reports;

        $rt_pm_reports->render_chart( $this->rtpm_chars );
    }



    public function rtpm_prepare_task_chart( $project_id ) {
        global $rt_pm_task, $rt_pm_project_overview;

        $data_source = array();
        $cols = array( __( 'Time' ), __( 'Open' ), __( 'Completed' ) );
        $rows = array();

        $project_start_date = get_post_field( 'post_date', $project_id );

        $project_end_date = get_post_meta( $project_id, 'post_duedate', true );

        $first_date = new DateTime( $project_start_date  );
        $last_date = new DateTime( $project_end_date  );

        do{

            $date_query = array(
                'before' => $first_date->format('Y-m-d')
            );

            $open_task_count = $rt_pm_task->rtpm_open_task_count( $project_id, $date_query );

            $completed_task_count = $rt_pm_task->rtpm_completed_task_count( $project_id, $date_query );


            $rows[] = array( $first_date->format('d-m-Y'), $open_task_count, $completed_task_count );

            $first_date->modify('+5 days');


        }while( $first_date < $last_date );


        $date_query = array(
            'before' => $last_date->format('Y-m-d')
        );


        $open_task_count = $rt_pm_task->rtpm_open_task_count( $project_id, $date_query );

        $completed_task_count = $rt_pm_task->rtpm_completed_task_count( $project_id, $date_query );


        $rows[] = array( $first_date->format('d-m-Y'), $open_task_count, $completed_task_count );

        $data_source['cols'] = $cols;
        $data_source['rows'] = $rows;

        $this->rtpm_chars[] = array(
            'id' => 1,
            'chart_type' => 'area',
            'data_source' => $data_source,
            'dom_element' => 'rtpm_task_status_burnup_'.$project_id,
            'options' => array(
                'colors' => [ '#66CCFF', '#32CD32' ],
            )
        ); ?>
        <div id="rtpm_task_status_burnup_<?php echo $project_id; ?>"></div>
    <?php
    }

    public function rtpm_print_style() { ?>
    <style>
        table.no-outer-border {
            border-collapse: collapse;
        }
        table.no-outer-border td, table.no-outer-border th {
            border: 1px solid black;
        }
        table.no-outer-border tr:first-child th {
            border-top: 0;
        }
        table.no-outer-border tr:last-child td {
            border-bottom: 0;
        }
        table.no-outer-border tr td:first-child,
        table.no-outer-border tr th:first-child {
            border-left: 0;
        }
        table.no-outer-border tr td:last-child,
        table.no-outer-border tr th:last-child {
            border-right: 0;
        }
    </style>
    <?php }

}