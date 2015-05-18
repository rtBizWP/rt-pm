<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 3/4/15
 * Time: 2:44 PM
 */

/**
 * Class Rt_PM_Project_Gantt
 * @author paresh
 */
class Rt_PM_Project_Gantt {

    /**
     * Placeholder method
     *
     */

    private $child_task_color = '#32CD32';
    private $parent_task_color = '#C0C0C0';

    public function __construct() {

        $this->setup();
    }

    /**
     * Setup actions and filters
     *
     */
    public function setup() {

        add_action('wp_enqueue_scripts', array( $this, 'rtpm_ganttchart_load_style_script' ) );
        add_action('buddyboss_after_header', array( $this, 'buddyboss_after_header_rt_wrapper' ) );
    }

    /**
     * Load script and stylesheets
     */
    public function rtpm_ganttchart_load_style_script() {

        wp_enqueue_style( 'rt-biz-sidr-style', get_stylesheet_directory_uri().'/css/jquery.sidr.light.css',  array() );
        wp_enqueue_script( 'rt-biz-sidr-script', get_stylesheet_directory_uri().'/assets/js/jquery.sidr.min.js', array('jquery') );

        wp_enqueue_style( 'rt-bp-hrm-calender-css', RT_HRM_BP_HRM_URL . 'assets/css/calender.css', false );

        wp_enqueue_script( 'rtbiz-common-script', get_stylesheet_directory_uri().'/assets/js/rtbiz-common.js', array(), BUDDYBOSS_CHILD_THEME_VERS );
        wp_enqueue_script( 'rtbiz-side-panel-script', get_stylesheet_directory_uri().'/assets/js/rtbiz-fetch-side-panel.js', array(), BUDDYBOSS_CHILD_THEME_VERS );

        wp_localize_script('rtbiz-side-panel-script', 'pm_script_url', RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/rt-bp-pm.js' );
    }

    /**
     * Add div for sidr side panel
     */
    function buddyboss_after_header_rt_wrapper(){ ?>
        <div id="rt-action-panel" class="sidr right"></div>
    <?php }

    /**
     * Return singleton instance of class
     *
     * @return Rt_PM_Project_Gantt
     */
    public static function factory() {
        static $instance = false;
        if ( ! $instance  ) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Prepare ganttchart and data
     * @param int $project_id
     * @param $dom_element
     */
    public function rtpm_prepare_ganttchart( $project_id = 0, $dom_element ) {
        global $rtcrm_ganttchart, $rt_pm_task, $rt_pm_task_links_model, $rt_pm_task_resources_model;

        $columns = array(
            array(
                'name' => 'text',
                'label' => 'Task Name',
                'tree' => true,
                'resize' => true,
                'min_width' => 500,
            ),
            array(
                'name' => 'start_date',
                'label' => 'Start Date',
                'resize' => true
            ),
            array(
                'name' => 'end_date',
                'label' => 'End Date',
                'resize' => true
            ),
            array(
                'name'  => 'estimated_hours',
                'label' =>  'Estimated Hours',
                'resize' => true,
            ),
            array(
                'name' => 'resources',
                'label' => 'Resources',
                'resize' => true
            ),
            array(
                'name' => 'add',
                'label' => '',
                'width' => '30'
            ),
        );

        $lightbox =  array(
            array(
                'name' => 'description',
                'height' => 70,
                'map_to' => 'text',
                'type' => 'textarea'
            ),
            array(
                'name' => 'type',
                'type' => 'typeselect',
                'map_to' => 'type'
            ),
            array(
                'name' => 'estimated_hours',
                'type' => 'number',
                'map_to' => 'estimated_hours'
            ),
            array(
                'name' => 'time',
                'height' => 72,
                'type' => 'duration',
                'map_to' => 'auto'
            )
        );

        $args = array(
            'post_parent' => $project_id,
            'nopaging' => true,
            'no_found_rows' => true,
        );

        $task_list = $rt_pm_task->rtpm_get_task_data( $args );

        $data = array();

        $links = array();

        $tentative_tasks = array();

        $non_working_days = $rt_pm_task->rtpm_get_non_working_days( $project_id );

        if( !empty( $task_list ) ) {

            foreach( $task_list as $task ) {

                $start_date = rt_convert_strdate_to_usertimestamp( $task->post_date );
                $end_date = rt_convert_strdate_to_usertimestamp( get_post_meta( $task->ID, 'post_duedate', true ) );
                $task_type = get_post_meta( $task->ID, 'rtpm_task_type', true );
                $estimated_hours = $rt_pm_task_resources_model->rtpm_get_tasks_estimated_hours( $task->ID );
                $parent_task = get_post_meta( $task->ID, 'rtpm_parent_task', true );

                //Set task color
                if( ! empty( $parent_task ) )
                    $task_color = $this->child_task_color;
                else
                    $task_color = $this->parent_task_color;

                $progress_percentage = $rt_pm_task->rtpm_get_task_progress_percentage( $task->ID ) / 100;

                $resources_wp_user = $rt_pm_task_resources_model->rtpm_get_task_resources( $task->ID );

                $resources_user_displayname = array();

                if( ! empty( $resources_wp_user ) ) {
                    foreach ( $resources_wp_user as $resource_wp_user_id ) {
                        $resources_user_displayname[] = rtbiz_get_user_displayname( $resource_wp_user_id );
                    }
                } else {
                    $tentative_tasks[] = $task->ID;
                }

                $data[] = array(
                    'id' => $task->ID,
                    'text' => $task->post_title,
                    'start_date' => $start_date->format( "d-m-Y" ),
                    'end_date' => $end_date->format('d-m-Y'),
                    'type' => $task_type,
                    'estimated_hours' => ! empty( $estimated_hours ) ? $estimated_hours : 0,
                    'open' => true,
                    'parent' => $parent_task,
                    'color' => $task_color,
                    'progress' => $progress_percentage,
                    'resources' =>  implode( ', ', $resources_user_displayname ),
                );

                $links_data = $rt_pm_task_links_model->rtpm_get_task_links( $project_id,   $task->ID );

                foreach( $links_data as $link ) {

                    $links[] = array(
                        'id' => $link->id,
                        'source' => $link->source_task_id,
                        'target' => $link->target_task_id,
                        'type' => $link->type,
                    );
                }

            }
        }

        $rtcrm_chart = compact( 'dom_element', 'data', 'columns', 'links', 'lightbox', 'tentative_tasks', 'non_working_days', 'config' );

        ?>

        <div id="<?php echo $dom_element; ?>" style='width:1000px; height:400px;'></div>
    <?php
        $rtcrm_ganttchart->rtgantt_render_chart( $rtcrm_chart );
    }


    /**
     * Print script for ganttchart Tasks ajax
     */
    public function rtpm_print_ganttchart_script() { ?>
        <script id="task-detail-template" type="text/x-handlebars-template">
            <ul style="list-style-type: none; margin: 0;">
                <li style="margin: 0;"><strong>Task Title: </strong><span>{{task_title}}</span></li>
                <li style="margin: 0;"><strong>Status: </strong><span>{{task_status}}</span></li>
                <li style="margin: 0;"><strong>Progress: </strong><span>{{task_progress}}%</span></li>
                <li style="margin: 0;"><strong>Start Date: </strong><span>{{start_date}}</span></li>
                <li style="margin: 0;"><strong>End Date: </strong><span>{{end_date}}</span></li>
            </ul>
        </script>

        <script type="text/javascript">

            var source   = $('#task-detail-template').html();
            var template = Handlebars.compile(source);


            var admin_url = '<?php echo admin_url('admin-ajax.php');  ?>';

            //Add new task
            gantt.attachEvent("onAfterTaskAdd", function( id, item ) {

                var data = {
                    start_date :  rtcrm_get_postdata( item.start_date ),
                    end_date :  rtcrm_get_postdata( item.end_date ),
                    task_title : item.text,
                    parent_project : jQuery('#rtpm_project_id').val(),
                    task_type: item.$rendered_type,
                    parent_task: item.parent,
                };

                //Set task color after adding
                if( 0 === item.parent ) {
                    gantt.getTask(id).color = '<?php echo $this->parent_task_color ?>';
                }else {
                    gantt.getTask(id).color = '<?php echo $this->child_task_color ?>';
                }

                var send_data = { action : 'rtpm_save_project_task', post: data };

                $.post( admin_url, send_data, function( response ) {
                    if( response.success ) {
                        var data = response.data;

                        gantt.changeTaskId( id,  data.task_id );

                        rtpm_set_task_group_date( item.parent, data );

                        rtcrm_gantt_notiy( 'Task has been created !' )
                    }else {
                        rtcrm_gantt_notiy( 'Something went wrong !', 'error' );
                    }
                } );

            });


            //Update task
            gantt.attachEvent("onAfterTaskUpdate", function( id,item ) {

                var data = {
                    task_id : id,
                    start_date :  rtcrm_get_postdata( item.start_date ),
                    end_date:  rtcrm_get_postdata( item.end_date ),
                    task_title : item.text,
                    parent_project : jQuery('#rtpm_project_id').val(),
                    task_type: item.$rendered_type,
                    parent_task: item.parent,
                };

                var send_data = { 'action' : 'rtpm_save_project_task', 'post': data };

                $.post( admin_url, send_data, function( response ) {
                    if( response.success ) {

                        rtpm_set_task_group_date( item.parent, response.data );
                        rtcrm_gantt_notiy( 'Task has been updated !' )
                    } else {
                        rtcrm_gantt_notiy( 'Something went wrong !', 'error' );
                    }
                } );
            });

            //Delete task
            gantt.attachEvent("onAfterTaskDelete", function( id, item ) {

                var data = {
                    'task_id' : id
                };

                var send_data = { 'action' : 'rtpm_delete_project_task', 'post': data };

                $.post( admin_url, send_data, function( response ) {

                    if( response.success ) {
                        rtcrm_gantt_notiy( 'Task has been deleted !' )
                    }else {
                        rtcrm_gantt_notiy( 'Something went wrong !', 'error' );
                    }
                } );
            });

            //Add new task link
            gantt.attachEvent("onAfterLinkAdd", function( id,item ) {
                var data = {
                    source_task_id: item.source,
                    target_task_id: item.target,
                    connection_type: item.type,
                    parent_project : jQuery('#rtpm_project_id').val()
                };

                var send_data = { 'action' : 'rtpm_save_project_task_link', 'post': data };

                $.post( admin_url, send_data, function( response ) {
                    if( response.success ) {
                        rtcrm_gantt_notiy( 'Task link has been established !' )
                    }else {
                        rtcrm_gantt_notiy( 'Something went wrong !', 'error' );
                    }
                } );

            });


            //Delete task link
            gantt.attachEvent("onAfterLinkDelete", function(id,item) {

                var data = {
                    link_id : id
                };

                var send_data = { 'action' : 'rtpm_delete_lead_task_link', 'post' : data };

                $.post( admin_url, send_data, function( response ) {
                    if( response.success ) {
                        rtcrm_gantt_notiy( 'Task link has been removed !' )
                    }else {
                        rtcrm_gantt_notiy( 'Something went wrong !', 'error' );
                    }
                });
            });

//
//            //Estimated hours field template
//            gantt.locale.labels.section_estimated_hours = "Estimated hours";
//            gantt.form_blocks["number"] = {
//                render:function( sns ) { //sns - the section's configuration object
//
//                    return "<div class='gantt_cal_ltext'><input type='number' step='0.25' min='0' value='' /></div>";
//                },
//                set_value:function( node,value,task,section ) {
//                    var input = node.getElementsByTagName("input")[0];
//                    input.value = value || 0;
//                },
//                get_value:function( node,task,section ) {
//                    var input = node.getElementsByTagName("input")[0];
//                    return input.value*1;
//                },
//                focus:function( node ) {
//                    var input = node.getElementsByTagName("input")[0];
//                    input.focus();
//                }
//            };

//            //Show task detail on hover
//            var request;
//            gantt.attachEvent("onMouseMove", function(id,item) {
//
//                if ('undefined' != typeof request)
//                    request.abort();
//
//                $('div.gantt_task_content, div.gantt_cell').contextMenu('div.rtcontext-box', {triggerOn: 'hover'});
//
//                if (null === id)
//                    return;
//
//
//                var data = {task_id: id};
//
//                var senddata = {
//                    action: 'rtpm_get_task_data_for_ganttchart',
//                    post: data
//                };
//
//                if ( 'undefined' != typeof request ) {
//                    request.abort();
//                    $('div.rtcontext-box').html('<strong>Loading...</strong>');
//                }
//
//                request = $.post( admin_url, senddata, function( response ){
//                    if( response.success ){
//                        $('div.rtcontext-box').html( template( response.data ) );
//                    }
//                } );
//
//            });

            //Close side panel before open lightbox
            gantt.attachEvent("onTaskCreated", function(task) {
                try{
                    $.sidr('close', 'rt-action-panel');
                } catch( err ){

                }
                return true;
            });


            //Open task edit side panel
            gantt.attachEvent("onTaskSelected", function( id, item ) {

                var task = gantt.getTask(id);

                if ( task.$new )
                    return false;

                gantt.hideLightbox();

                block_ui();

                render_project_slide_panel( 'open', id, <?php echo get_current_blog_id(); ?>, '', 'task' );
            });

            gantt.attachEvent("onTaskDrag", function(id, mode, task, original){
                var modes = gantt.config.drag_mode;
                if(mode == modes.move){
                    var diff = task.start_date - original.start_date;
                    gantt.eachTask(function(child){
                        child.start_date = new Date(+child.start_date + diff);
                        child.end_date = new Date(+child.end_date + diff);
                        gantt.refreshTask(child.id, true);
                    },id );
                }
                return true;
            });
            //rounds positions of the child items to scale
            gantt.attachEvent("onAfterTaskDrag", function(id, mode, e){
                var modes = gantt.config.drag_mode;
                if(mode == modes.move ){
                    gantt.eachTask(function(child){
                        gantt.roundTaskDates(child);
                        gantt.refreshTask(child.id, true);

                        var data = {
                            task_id : child.id,
                            start_date :  rtcrm_get_postdata( child.start_date ),
                            end_date:  rtcrm_get_postdata( child.end_date ),
                            task_title : child.text,
                            parent_project : jQuery('#rtpm_project_id').val(),
                            task_type: child.$rendered_type,
                            parent_task: id,
                        };

                        var send_data = { 'action' : 'rtpm_save_project_task', 'post': data };

                        $.post( admin_url, send_data, function( response ){
                            if( response.success ) {

                               // rtpm_set_task_group_date( id, data );
                                rtcrm_gantt_notiy( 'Task has been updated !' )
                            }else {
                                rtcrm_gantt_notiy( 'Something went wrong !', 'error' );
                            }
                        } );
                    },id );
                }
            });

            jQuery( document ).ready( function( $ ) {

              //  $('div.gantt_task_content, div.gantt_cell').contextMenu('div.rtcontext-box', {triggerOn: 'hover'});

            });


            /**
             * Notify message after ajax action
             * @param message
             * @param type
             */
            function rtcrm_gantt_notiy( message, type ) {

                noty({
                    text: message,
                    layout: 'topRight',
                    type:  type || 'success',
                    timeout: 2000,
                    killer: true
                });

            }

            function rtpm_set_task_group_date( parent_task_id, data ) {


                if( 'undefined' != typeof data.parent_task_data ) {

                    gantt.getTask(  parent_task_id ).start_date = new Date( data.parent_task_data.start_date );
                    gantt.getTask(  parent_task_id ).end_date = new Date( data.parent_task_data.end_date );
                    gantt.updateTask( parent_task_id );
                    gantt.refreshTask( parent_task_id, true);
                    gantt.render();
                }
            }

            /**
             * Convert date into wp default date format(yyyy-mm-dd hh:mm:ss)
             * @param post_date
             * @returns {string}
             */
            function rtcrm_get_postdata( post_date ) {

                var todayUTC = new Date(Date.UTC(post_date.getFullYear(), post_date.getMonth(), post_date.getDate()));
                return todayUTC.toISOString().slice(0, 10).replace(/-/g, '-')+' 00:00:00';
            }
        </script>


        <div class="rtcontext-box iw-contextMenu" style="display: none;">
            <strong>Loading...</strong>
        </div>
    <?php }

}