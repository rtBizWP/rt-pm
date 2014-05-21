<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

	$leadsTable = new Rt_PM_Project_List_View();
?>
<?php screen_icon(); ?>
<div class="wrap">
    <h2>
        <?php echo $labels['all_items']; ?>
    </h2>
    <?php $leadsTable->table_view(); ?>
</div>