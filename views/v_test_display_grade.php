<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 12/11/13
 * Time: 7:59 AM
 * To change this template use File | Settings | File Templates.
 */
?>
    <h2>Test Grade</h2>

    <!--List of test takers to follow-->
<?php
if (isset($graded_test)) {
    $due_on_dt = $graded_test["due_on_dt"];
    $minutes_taken = $graded_test["elapsed_seconds"] == null ? "Not timed" : $graded_test["elapsed_seconds"] / 60;
    if ($due_on_dt != "") {$due_on_dt = date("m/d/Y", $due_on_dt);}
    $timer_text = $graded_test["minutes_to_complete"] > 0 ? $graded_test["minutes_to_complete"] : "Not Timed";
    ?>
    <section>
        <div>
            <fieldset>
                <legend>Graded Test Details</legend>
                <p>Test Name: <?php echo $graded_test["test_name"];?></p>
                <p>Description: <?php echo $graded_test["test_descr"];?></p>
                <p>Category: <?php echo $graded_test["test_category"];?></p>
                <p>Minutes to complete: <?php echo $timer_text;?></p>
                <p>Minutes Taken: <?php echo $minutes_taken;?></p>
                <p>Grade: <?php echo $graded_test["grade"];?></p>

            </fieldset>
        </div>
    </section>
<?php } else {echo ("<h3>Not a valid test assignment</h3>");} ?>