<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 12/11/13
 * Time: 7:59 AM
 * To change this template use File | Settings | File Templates.
 */
?>
    <h2>Test Assignment</h2>

    <!--List of test takers to follow-->
<?php
if (isset($assign_details)) {
    $due_on_dt = $assign_details["due_on_dt"];
    if ($due_on_dt != "") {$due_on_dt = date("m/d/Y", $due_on_dt);}
    $timer_text = $assign_details["minutes_to_complete"] > 0 ? $assign_details["minutes_to_complete"] : "Not Timed";
    ?>
    <section>
        <div>
            <fieldset>
                <legend>Details</legend>
                <p>Test Name: <?php echo $assign_details["test_name"];?></p>
                <p>Description: <?php echo $assign_details["test_descr"];?></p>
                <p>Category: <?php echo $assign_details["test_category"];?></p>
                <p>Number of Questions: <?php echo $assign_details["question_count"];?></p>
                <p>Minutes to complete: <?php echo $timer_text;?></p>
                <p>Assignment State: <?php echo $assign_details["test_assign_status_descr"];?></p>
                <p>Due Date: <?php echo $due_on_dt;?></p>

            </fieldset>
            <?php if ($assign_details["test_assign_status_id"] <= 2) {//allow the user to take the test?>
                <form action="/tests/take/<?php echo $assign_details['test_assign_id']?>" method="get">
                    <input type="submit" value="Take Test"/>
                </form>
            <?php }?>
        </div>
    </section>
 <?php } else {echo ("<h3>Not a valid test assignment</h3>");} ?>