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
     * Render all grids for project overview
     * @param $project_data
     */
    public function rtpm_render_project_grid( $project_data ) {
        global $rt_pm_project, $rt_pm_task, $rt_pm_bp_pm;
        ?>

        <script id="task-list-template" type="text/x-handlebars-template">
            <h2>{{assignee_name}}'s Tasks</h2>
            <ul style="list-style-type: none; margin: 0;">
                {{#each tasks}}
                <li style="margin: 0;"><a target="_blank" href="{{task_edit_url}}">{{post_title}}</a></li>
                {{/each}}
            </ul>
        </script>

        <script>
            jQuery(document).ready(function($) {

                $('a.rtcontext-taskbox').contextMenu('div.rtcontext-box');

                var source   = $('#task-list-template').html();
                var template = Handlebars.compile(source);

                $('a.rtcontext-taskbox').click(function(event) {
                    event.stopPropagation();
                    var post = {};
                    var data = {};
                    post.author_id = $(this).data('team-member-id');
                    data.action = 'rtpm_get_user_tasks'
                    data.post = post;
                    var ajax_adminurl = '<?php echo  admin_url( 'admin-ajax.php' ); ?>';

                    $.post( ajax_adminurl, data, function( res ){

                        if( res.success ){
                            $('div.rtcontext-box').html( template( res.data ) );
                        }
                    });
                });
            });
        </script>
        <ul id="activity-stream" class="activity-list item-list">
            <?php
            if( ! empty( $project_data ) ) {

                //Masonry container fix
                if (count( $project_data ) <= 1)
                    wp_localize_script('rt-biz-admin', 'NOT_INIT_MASONRY', 'false');

                foreach( $project_data as $project ):

                    $project_edit_link = rtpm_bp_get_project_details_url( $project->ID );

                    ?>

                    <li class="activity-item">
                        <div class="activity-content">
                            <div class="row activity-inner rt-biz-activity">
                                <div class="rt-voxxi-content-box">
                                        <p><strong>Project Name: </strong><a href="<?php echo $project_edit_link ?>" target="_blank"><?php echo $project->post_title; ?></a></p>
                                        <p><strong>Create date: </strong><?php echo mysql2date( 'd M Y', $project->post_date ) ?></p>
                                        <p><strong>Due date: </strong><?php echo mysql2date( 'd M Y', get_post_meta( $project->ID, 'post_duedate', true ) ); ?></p>
                                        <p><strong>Post status: </strong><?php echo $project->post_status; ?></p>
                                </div>

                                <div class="row post-detail-comment column-title">
                                    <div class="column small-12">
                                        <p>
                                           <?php echo rtbiz_read_more( $project->post_content ); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="row column-title">
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
                                    <div class="small-6 columns" style="  position: absolute;left: 60%;">
                                        <div class="number-circle">
                                            <div class="height_fix"></div>
                                            <div class="content"><?php echo $rt_pm_task->rtpm_get_completed_task_per( $project->ID ) ?>%</div>
                                        </div>
                                    </div>
                                </div>

                                <?php $this->rtpm_prepare_task_chart( $project->ID ) ?>

                                <div class="row rt-pm-team">
                                    <div class="column small-3 bdm-column">
                                        <strong>BDM</strong>
                                        <?php $bdm =  get_post_meta( $project->ID, 'business_manager', true);
                                            if( ! empty( $bdm ) ){ ?>
                                                <a data-team-member-id="<?php echo $bdm; ?>" class="rtcontext-taskbox">
                                                    <?php echo get_avatar( $bdm , 16 ) ; ?>
                                                </a>
                                           <?php } ?>
                                    </div>
                                    <div class="column small-9 team-column" style="float: left;">
                                        <strong class="team-title">Team</strong>
                                        <div class="row team-member">
                                            <?php $team_member = get_post_meta($project->ID, "project_member", true);

                                            if( !empty( $team_member ) ) {

                                                foreach ( $team_member as $member ) {

                                                    if( empty( $member ) )
                                                        continue;
                                                    ?>
                                                    <div class="columns small-3">
                                                        <a data-team-member-id="<?php echo $member; ?>" class="rtcontext-taskbox">
                                                            <?php echo get_avatar( $member ) ; ?>
                                                        </a>
                                                    </div>
                                                <?php }

                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach;
            } ?>
        </ul>

        <div class="rtcontext-box iw-contextMenu" style="display: none;">
        </div>


    <?php }


    /**
     * Render all charts
     */
    public function rtpm_render_project_charts() {

        global $rt_pm_reports;

        $rt_pm_reports->render_chart( $this->rtpm_chars );
    }


    /**
     * Prepare chart of tasks
     * @param $project_id
     */
    public function rtpm_prepare_task_chart( $project_id ) {
        global $rt_pm_task, $rt_pm_project_overview;

        $data_source = array();
        $cols = array( __( 'Hours' ), __( 'Estimated' ), __( 'Billed' ) );
        $rows = array();


        $duedate_array = $rt_pm_task->rtpm_get_all_task_duedate( $project_id );

        if( null === $duedate_array )
            return;

        foreach( $duedate_array as $duedate ) {

            $due_date_obj = new DateTime( $duedate );

            $billed_hours = $rt_pm_task->rtpm_tasks_billed_hours( $project_id, $duedate );
            $estimated_hours = $rt_pm_task->rtpm_tasks_estimated_hours( $project_id, $duedate );

            if( null === $billed_hours )
                $billed_hours = 0;

            if( null === $estimated_hours )
                $estimated_hours = 0;

            $rows[] = array( $due_date_obj->format('d-M-Y'), (float)$estimated_hours, (float)$billed_hours );
        }


        $data_source['cols'] = $cols;

        $data_source['rows'] = array_map( 'unserialize', array_unique( array_map( 'serialize', $rows ) ) );;

        $this->rtpm_chars[] = array(
            'id' => 1,
            'chart_type' => 'area',
            'data_source' => $data_source,
            'dom_element' => 'rtpm_task_status_burnup_'.$project_id,
            'options' => array(
                //'vAxis' => json_encode( array( 'format' => '#', 'gridlines' => array( 'color' => 'transparent' ) ) ),
                'colors' => [ '#66CCFF', '#32CD32' ],
                'legend' => 'top',
                'pointSize' => '5',
            )
        ); ?>
        <div id="rtpm_task_status_burnup_<?php echo $project_id; ?>"></div>
    <?php
    }

    /**
     * Style for table without outer border
     */
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