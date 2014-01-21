<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 12/7/13
 * Time: 7:59 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<style>
    label {text-transform:capitalize;}
</style>
<style>
    .ui-tabs-vertical { width: 55em; }
    .ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 12em; }
    .ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
    .ui-tabs-vertical .ui-tabs-nav li a { display:block; }
    .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; border-right-width: 1px; }
    .ui-tabs-vertical .ui-tabs-panel { padding: 1em; float: right; width: 40em;}
</style>

<?php if (isset($errors)) { ?>
    <?php foreach($errors AS $current_error) { ?>
        <div class='alerttext'>
            <?php echo $current_error ?>
        </div>
    <?php } ?>
<?php }?>

<section>
    <div id="tabs">
        <ul>
            <li><a href="/tests/edit/<?php echo $test_id;?>">Test</a></li>
            <li><a href="#tab-questions<?php echo $test_id;?>">Questions</a></li>
            <li><a href="/materials/<?php echo $test_id;?>">Materials</a></li>
        </ul>
        <div>
            <fieldset>
                <legend>Add Question</legend>
                <p>
                    <label for="question_text">Question Text:</label>
                    <input type='text' name='question_text' id='question_text'/>
                </p>
                <p>
                <div>Question Type:</div>
                <?php foreach($question_types AS $current_question_type) { ?>
                    <label for="question_type_id_<?php echo $current_question_type['question_type_id']?>"><?php echo $current_question_type['question_type_descr']?></label>
                    <input type="radio" name="question_type_id" id="question_type_id_<?php echo $current_question_type['question_type_id']?>" value="<?php echo $current_question_type['question_type_id']?>"/> |
                <?php } ?>


                </p>
                <input type='hidden' name='test_id' id='test_id' value='<?php echo $test_id;?>'/>
                <input type='button' value='Add Question' id='cmdAddQuestion'>
            </fieldset>
        </div>
        <div id="tab-questions">
            <!--List the questions-->
            <ul>
                <?php foreach($question_list AS $current_question) { ?>
                        <li><a href="#tab-question-<?php echo $current_question["question_id"]; ?>">
                                <?php echo siteutils::Truncate($current_question['question_text'], 20,true);?>
                            </a>
                        </li>
                <?php } ?>
            </ul>
            <?php foreach($question_list AS $current_question) { ?>
                <div id='tab-question-<?php echo $current_question["question_id"] ?>' question_id='<?php echo $current_question["question_id"]; ?>' class='question' >

                </div>
            <?php } ?>
        </div>
    </div>
</section>

<script>
    $(function() {
        $( "#tabs" ).tabs();
        var tabs = $( "#tab-questions" ).tabs();

        /*
         tabs.find( ".ui-tabs-nav" ).sortable({
         axis: "x",
         stop: function() {
         tabs.tabs( "refresh" );
         }
         });
         */
        tabs.addClass( "ui-tabs-vertical ui-helper-clearfix" );
        tabs.removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );

    });



    $(document).ready(function()
    {

        $('#cmdAddQuestion').click(function () {
            //Add the question at the server, get the new ID
            var question_text = $('#question_text').val();
            var question_type_id = $("input[name='question_type_id']:checked").val();
            var test_id = $('#test_id').val();
            var question_id = 0;

            if (typeof(question_type_id) == "undefined") {
                alert ("Please choose a question type")
                return;
            }

            if (question_text.length < 10) {
                alert("Please provide a question of 10 characters or more");
                return;
            }
            $.ajax({
                type: "POST",
                url: "/questions/p_create/" + test_id,
                dataType: "json",
                data: { question_text: question_text, question_type_id: question_type_id},
                async: false,
                success : function(data) {
                    question_id = data;
                }
            });

            if (question_id != null) {
                //Add the question to the local page
                var newTabContent = $("#tab-questions").append("<div style='display: none' id='tab-question-" + question_id + "' class='question ui-tabs-panel ui-widget-content ui-corner-bottom' question_id='" + question_id + "' aria-labelledby='ui-id-4' role='tabpanel' aria-expanded='false' aria-hidden='true' style='display: none;'></div>");
                var newTab = $( "#tab-questions .ui-tabs-nav").append("<li><a href='#tab-question-" + question_id + "'>" + question_text + "-" + question_type_id +"</li>");
                $("#tab-questions").tabs("refresh");
                //light up the question
                $( "#tab-question-" + question_id).question({question_text: question_text});
            }
        });

        <?php
        //can't use a class selector for these because it always picks the top one and then $(this) does not work like it should
        //so we are forced to loop here in the PHP
        foreach($question_list AS $current_question) {
            echo "$('#tab-question-".$current_question["question_id"]."').question({question_text: '".$current_question["question_text"]."', question_type_id:".$current_question["question_type_id"]."});";
        }
        ?>

    });


</script>
