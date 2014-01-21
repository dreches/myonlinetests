<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 12/11/13
 * Time: 7:59 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<?php
if (isset($instance_summary)) {?>
    <h2>Test Review</h2>
    <?php if(isset($timeout)) {
        echo "<h3 style='color:red'>You have taken all the time allotted for this test, please review and set to complete</h3>";
    }
    ?>
    <p>Congratulations! You have completed the test: "<?php echo trim($instance_summary[0]["test_name"]);?>".
        Below is a summary of your answers, please click "Finish Test" to submit your test for grading. You may
        also use the links to further review or change your answers.</p>
    <!--List of test takers to follow-->
    <section>
        <?php foreach($instance_summary AS $current_question) {
            $question_id = $current_question["question_id"];
            $question_text = $current_question["question_text"];
            $question_order = $current_question["question_order"];
            $test_instance_id = $current_question["test_instance_id"];
            $test_assign_id = $current_question["test_assign_id"];
            ?>
            <div style="border: 1px solid black;padding: 2px;">
                <div><a href="/tests/take/<?php echo $test_assign_id.'/'.$test_instance_id.'/'.$question_id?>"><?php echo $question_order + 1?>. <?php echo $question_text;?></a></div>
                <div id='tab-question-<?php echo $question_id ?>'
                     question_id='<?php echo $question_id; ?>'
                     class='question' >
                </div>
            </div>
        <?php }?>
        <form action="/tests/p_submit/<?php echo $test_assign_id.'/'.$test_instance_id?>" method="post">
            <input type="submit" value="Finish Test"/>
        </form>
    </section>
<?php } else {echo ("<h3>Not a valid test assignment</h3>");} ?>


<script type="text/javascript">
    <?php foreach($instance_summary AS $current_question) {
    $question_id = $current_question["question_id"];
    $test_instance_id = $current_question["test_instance_id"];
    ?>
        $(document).ready(function(){
            <?php echo "\t$('#tab-question-".$question_id."').question({test_instance_id: ".$test_instance_id.",display_mode: 'review',question_id:".$question_id."});"; ?>
        });
    <?php } ?>
</script>