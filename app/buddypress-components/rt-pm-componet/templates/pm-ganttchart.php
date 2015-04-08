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
    <div class="action-bar">
        <input value="Export to PDF" type="button" onclick='gantt.exportToPDF()'>
        <input value="Export to PNG" type="button" onclick='gantt.exportToPNG()'>
    </div>

    <br/>
    <input type="hidden" value="<?php echo $project_id; ?>" id="rtpm_project_id" />
<?php
$rt_pm_project_gantt->rtpm_prepare_ganttchart( $project_id, 'rtpm_project_ganttchart' );

$rt_pm_project_gantt->rtpm_print_ganttchart_script();
?>