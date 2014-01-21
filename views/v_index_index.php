<h2>Welcome to <?=APP_NAME?><?php if($user) echo ', '.$user->first_name; ?></h2>

<?php if(!$user) { ?>
    <p>
        Our Online Tests is a web based home for all of your company's tests and quizzes. <br/>
        Please <a href="/users/login">log in</a> or <a href="/users/signup">create an account</a> today.
    </p>
<?php } else {?>
    <p>
        Here you will find all tests assigned to you, as well as your test history and grades.
    </p>

<?php }?>

</p>

<?php if($user) { ?>

            <?php
            if (count($assigned_tests) > 0) {?>
            <h3>Your current tests</h3>
            <table id="rounded-corner">
                <thead class="table-header">
                <th scope="col" class="rounded-q1">Test Name</th>
                <th scope="col" class="rounded">Due On</th>
                <th scope="col" class="rounded">Assigned On</th>
                <th scope="col" class="rounded-q4">&nbsp;</th>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="3" class="rounded-foot-left"><em>* See test history for taken tests</em></td>
                    <td class="rounded-foot-right">&nbsp;</td>
                </tr>
                <tfoot>
                <tbody>
                <?php
                foreach($assigned_tests AS $current_test_assign) {
                    $due_on_dt = $current_test_assign["due_on_dt"];
                    if ($due_on_dt != "") {$due_on_dt = date("m/d/Y", $due_on_dt);}
                    $assigned_on_dt = $current_test_assign["assigned_on_dt"];
                    if ($assigned_on_dt != ""){$assigned_on_dt = date("m/d/Y", $assigned_on_dt);}
                    ?>
                        <tr>
                           <td>
                               <?php echo $current_test_assign["test_name"]?>
                            </td>
                            <td><?php echo $due_on_dt;?></td>
                            <td><?php echo $assigned_on_dt;?></td>
                            <td><a href="/tests/assignment/<?php echo $current_test_assign["test_assign_id"]?>">Details</a> | <a href="/tests/take/<?php echo $current_test_assign["test_assign_id"]?>">Take</a></td>
                        </tr>
            <?php } ?>
            </tbody>
                </table>
    <?php } else {echo ("<h3>No test currently assigned</h3>");} ?>
<?php } else { ?>
    <p>Please take a look at this demo showing how the application works</p>
    <iframe width="420" height="315" src="//www.youtube.com/embed/urwIqBk853A" frameborder="0" allowfullscreen></iframe>
<?php } ?>