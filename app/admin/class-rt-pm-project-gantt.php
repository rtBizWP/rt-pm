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

    private $task_color_hash = '#32CD32';

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
    }

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
        global $rtcrm_ganttchart, $rt_pm_task, $rt_pm_task_links_model;

        $columns = array(
            array( 'name' => 'text', 'label' => 'Task Name', 'tree' => true ),
            array( 'name' => 'add', 'label' => '' ),

        );

        $lightbox =  array(
            array( 'name' => 'description', 'height' => 70, 'map_to' => 'text', 'type' => 'textarea' ),
            array( 'name' => 'type', 'type' => 'typeselect', 'map_to' => 'type' ),
            array( 'name' => 'estimated_hours', 'type' => 'number', 'map_to' => 'estimated_hours' ),
            array( 'name' => 'time', 'height' => 72, 'type' => 'duration', 'map_to' => 'auto' ),

        );

        $args = array(
            'post_parent' => $project_id,
            'nopaging' => true,
            'no_found_rows' => true,
        );

        $task_list = $rt_pm_task->rtpm_get_task_data( $args );

        $data = array();

        $links = array();

        $tentative_tasks = $rt_pm_task->rtpm_get_unassigned_task( $project_id );

        if( !empty( $task_list ) ) {

            foreach( $task_list as $task ) {

                $start_date = rt_convert_strdate_to_usertimestamp( $task->post_date );
                $end_date = rt_convert_strdate_to_usertimestamp( get_post_meta( $task->ID, 'post_duedate', true ) );
                $task_type = get_post_meta( $task->ID, 'rtpm_task_type', true );
                $estimated_hours = get_post_meta( $task->ID, 'post_estimated_hours', true );
                $parent_task = get_post_meta( $task->ID, 'rtpm_parent_task', true );

                $progress_percentage = $rt_pm_task->rtpm_get_task_progress_percentage( $task->ID ) / 100;

                $data[] = array( 'id' => $task->ID, 'text' => $task->post_title, 'start_date' => $start_date->format( "d-m-Y" ), 'end_date' => $end_date->format('d-m-Y'), 'type' => $task_type, 'estimated_hours' => $estimated_hours, 'open' => true, 'parent' => $parent_task, 'color' => $this->task_color_hash, 'progress' => $progress_percentage  );

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

        $rtcrm_chart = compact( 'dom_element', 'data', 'columns', 'links', 'lightbox', 'tentative_tasks' );

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

            //Add new taske
            gantt.attachEvent("onAfterTaskAdd", function( id, item ) {

                var data = {
                    start_date :  rtcrm_get_postdata( item.start_date ),
                    end_date :  rtcrm_get_postdata( item.end_date ),
                    task_title : item.text,
                    parent_project : jQuery('#rtpm_project_id').val(),
                    task_type: item.$rendered_type,
                    parent_task: item.parent,
                    estimated_hours:    item.estimated_hours,
                };

                gantt.getTask(id).color = '<?php echo $this->task_color_hash ?>';

                var send_data = { action : 'rtpm_save_project_task', post: data };

                $.post( admin_url, send_data, function( response ) {
                    if( response.success ) {
                        var data = response.data;

                        gantt.changeTaskId(id,  data.task_id);

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
                    estimated_hours:    item.estimated_hours,
                };

                var send_data = { 'action' : 'rtpm_save_project_task', 'post': data };

                $.post( admin_url, send_data, function( response ){
                    if( response.success ) {
                        rtcrm_gantt_notiy( 'Task has been updated !' )
                    }else {
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


            //Update task link
            gantt.attachEvent("onAfterLinkUpdate", function( id,item ) {
                console.log(id);
            });


            //Delete task link
            gantt.attachEvent("onAfterLinkDelete", function(id,item){

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
                } );
            });


            //Estimated hours field template
            gantt.locale.labels.section_estimated_hours = "Estimated hours";
            gantt.form_blocks["number"] = {
                render:function( sns ){ //sns - the section's configuration object

                    return "<div class='gantt_cal_ltext'><input type='number' step='0.25' min='0' value='' /></div>";
                },
                set_value:function( node,value,task,section ) {
                    var input = node.getElementsByTagName("input")[0];
                    input.value = value || 0;
                },
                get_value:function( node,task,section ) {
                    var input = node.getElementsByTagName("input")[0];
                    return input.value*1;
                },
                focus:function( node ) {
                    var input = node.getElementsByTagName("input")[0];
                    input.focus();
                }
            };

            //Show task detail on hover
            var request;
            gantt.attachEvent("onMouseMove", function(id,item) {

                if ('undefined' != typeof request)
                    request.abort();

                $('div.gantt_task_content, div.gantt_cell').contextMenu('div.rtcontext-box', {triggerOn: 'hover'});

                if (null === id)
                    return;


                var data = {task_id: id};

                var senddata = {
                    action: 'rtpm_get_task_data_for_ganttchart',
                    post: data
                };

                if ('undefined' != typeof request) {
                    request.abort();
                    $('div.rtcontext-box').html('<strong>Loading...</strong>');
                }

                request = $.post( admin_url, senddata, function( response ){
                    if( response.success ){
                        $('div.rtcontext-box').html( template( response.data ) );
                    }
                } );

            });

            //Open task edit side panel
            gantt.attachEvent("onTaskClick", function( id, e ) {

                gantt.hideLightbox();

                var data = {
                    action: 'render_project_slide_panel',
                    template: 'open',
                    id: id,
                    rt_voxxi_blog_id: <?php echo get_current_blog_id(); ?>,
                    actvity_element_id: '',
                    template_name: 'task'
                };

                var pm_script_url = '<?php echo  RT_PM_URL . 'app/buddypress-components/rt-pm-componet/assets/javascripts/rt-bp-pm.min.js'; ?>';

                $.get( ajaxurl, data, function( data ) {
                    $("#rt-action-panel").html( data.html );

                    $.cachedScript( pm_script_url ).done(function( script, textStatus ) {
                        console.log( textStatus );
                    }).fail(function( qXHR, textStatus, errorThrown )  {
                        console.log(  errorThrown );
                    });

                });


                try{
                    $.sidr('open', 'rt-action-panel');
                } catch( err ){

                }
            });

            jQuery( document ).ready( function( $ ) {

//                // Foundation scss error fix
//                Foundation.global.namespace = '';
//
//                $(document).foundation();


                $('div.gantt_task_content, div.gantt_cell').contextMenu('div.rtcontext-box', {triggerOn: 'hover'});

                jQuery.cachedScript = function( url, options ) {

                    // Allow user to set any option except for dataType, cache, and url
                    options = $.extend( options || {}, {
                        dataType: "script",
                        cache: true,
                        url: url
                    });

                    // Use $.ajax() since it is more flexible than $.getScript
                    // Return the jqXHR object so we can chain callbacks
                    return jQuery.ajax( options );
                };

            });

            function rtcrm_get_postdata( post_date ) {

                var todayUTC = new Date(Date.UTC(post_date.getFullYear(), post_date.getMonth(), post_date.getDate()));
                return todayUTC.toISOString().slice(0, 10).replace(/-/g, '-');

            }

            function rtcrm_gantt_notiy( message, type ) {

                noty({
                    text: message,
                    layout: 'topRight',
                    type:  type || 'success',
                    timeout: 'delay',
                    killer: true
                });

            }
        </script>


        <div class="rtcontext-box iw-contextMenu" style="display: none;">
            <strong>Loading...</strong>
        </div>
    <?php }

}