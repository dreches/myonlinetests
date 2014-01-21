<?php
if (count($test_list) > 0) {?>
    <h3>Test History: <?php echo $test_taker_name?></h3>
    <table id="rounded-corner" >
    <thead class="table-header">
    <th scope="col" class="rounded-q1">Category</th>
    <th scope="col" class="rounded" style="width:200px">Name</th>
    <th scope="col" class="rounded">Due On</th>
    <th scope="col" class="rounded">Assigned</th>
    <th scope="col" class="rounded">Taken</th>
    <th scope="col" class="rounded-q4">Grade</th>
    </thead>
        <tfoot>
        <tr>
            <td colspan="5" class="rounded-foot-left"><em>&nbsp;</em></td>
            <td class="rounded-foot-right">&nbsp;</td>
        </tr>
        </tfoot>

        <tbody>
    <?php
    foreach($test_list AS $current_test) {
        $due_on_dt = $current_test["due_on_dt"];
        if ($due_on_dt != "") {$due_on_dt = date("m/d/Y", $due_on_dt);}
        $assigned_on_dt = $current_test["assigned_on_dt"];
        if ($assigned_on_dt != ""){$assigned_on_dt = date("m/d/Y", $assigned_on_dt);
        $taken_on_dt = $current_test["start_dt"];
        if ($taken_on_dt != "" && $taken_on_dt != null && isset($taken_on_dt)){$taken_on_dt = date("m/d/Y", $taken_on_dt);
        }
        ?>

        <tr>
            <td nowrap>
            <?php echo $current_test["test_category"];?>
            </td>
            <td style="width:200px;" nowrap>
                <?php echo $current_test["test_name"];?>
            </td>
            <td><?php echo $due_on_dt;?></td>
            <td><?php echo $assigned_on_dt;?></td>
            <td><?php echo $taken_on_dt;?></td>
            <td><?php echo $current_test["grade"];?></td>
        </tr>
    <?php } ?>

<?php }?>
    </tbody>
    </table>
    <?php } else {echo ("<h3>No current test history</h3>");} ?>