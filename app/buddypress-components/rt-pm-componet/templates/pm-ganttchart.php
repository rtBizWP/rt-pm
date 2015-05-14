<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 8/4/15
 * Time: 6:01 PM
 */
global $rt_pm_project_gantt;

$project_id = $_REQUEST['rt_project_id'];

?>
    <div class="list-heading">
        <div class="large-6 columns list-title">
            <h2><?php _e( '#'.get_post_meta(  $project_id, 'rtpm_job_no', true ).' '. get_post_field( 'post_title', $project_id ), RT_PM_TEXT_DOMAIN );?></h2>
        </div>
        <div class="large-6 columns action-bar">
            <input value="Export to Excel" type="button" onclick='gantt.exportToExcel()'>
            <input value="Export to iCal" type="button" onclick='gantt.exportToICal()'>
            <input value="Export to PDF" type="button" onclick='gantt.exportToPDF()'>
            <input value="Export to PNG" type="button" onclick='gantt.exportToPNG()'>
        </div>
    </div>


    <br/>
    <input type="hidden" value="<?php echo $project_id; ?>" id="rtpm_project_id" />
<?php
$rt_pm_project_gantt->rtpm_prepare_ganttchart( $project_id, 'rtpm_project_ganttchart' );

$rt_pm_project_gantt->rtpm_print_ganttchart_script();
?>