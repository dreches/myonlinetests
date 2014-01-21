<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 12/13/13
 * Time: 7:59 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<?php
if (isset($question_details)) {
    ?>
    <section>
        <div>
            <?php if($minutes_to_complete > 0) {?>
                <span style="vertical-align: middle">You have <?php echo $minutes_to_complete ?> minutes to complete the test:</span>
                <canvas id="canTimerDisplay" width="75" height="50"></canvas>

                <script type="text/javascript">
                    $( document ).ready(function() {
                        //hook up the panel for the timer display
                        $("#canTimerDisplay").timer({ added: function(e, ui){}
                            , minutesAllowed:<?php echo $minutes_to_complete?>
                            , timeTakenColor: 'red', timeLeftColor: 'green', synchWithServer: true
                            ,ajaxUrlRoot: '/timers/', serverTimerId: <?php echo $serverTimerId?>, secondsEt: <?php echo $secondsEt?>
                            ,timeup: function(e,ui){finishTestTimer();}
                            ,initServerTimer: function(e,ui){updateTestTimer(ui.serverTimerId);}
                        });
                    });
                </script>
            <?php }?>
        </div>
        <div>
            <form action="/tests/p_take/<?php echo $test_assign_id.'/'.$test_instance_id.'/'.$question_id?>" method="post">
                <fieldset>
                    <legend>Question #<?php echo $question_order + 1?></legend>
                    <div id='tab-question-<?php echo $question_id ?>'
                         question_id='<?php echo $question_id; ?>'
                         class='question' >
                    </div>

                </fieldset>
                <div>
                    <?php if (isset($prior_question_id)) {?>
                        <a href="/tests/take/<?php echo $test_assign_id.'/'.$test_instance_id.'/'.$prior_question_id?>">&lt; Previous</a>
                    <?php }?>
                    <input type="submit" value="Submit Answer"/>
                    <?php if (isset($next_question_id)) {?>
                        <a href="/tests/take/<?php echo $test_assign_id.'/'.$test_instance_id.'/'.$next_question_id?>">Next &gt;</a>
                    <?php }?>
                </div>

            </form>

        </div>
    </section>
<?php } else {echo ("<h3>Not a valid question</h3>");} ?>

<script type="text/javascript">
    $(document).ready(function(){
        <?php echo "\t$('#tab-question-".$question_id."').question({test_instance_id: ".$test_instance_id.",display_mode: 'take',question_id:".$question_id.",question_text: '".$question_text."', question_type_id:".$question_type_id."});"; ?>
    });

    //Link the test instance with the timer
    function updateTestTimer(timer_id) {
        $.ajax({
            type: "GET",
            url: "/tests/settimer/<?php echo $test_instance_id?>/" + timer_id
        });
    }

    //navigate to the force finish page
    function finishTestTimer() {
        window.location.href = "/tests/takesummary/<?php echo $test_assign_id?>/<?php echo $test_instance_id?>?timeout=true";
    }
</script>