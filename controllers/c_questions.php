<?php

class questions_controller extends secure_controller {

    /*-------------------------------------------------------------------------------------------------

    -------------------------------------------------------------------------------------------------*/
    public function __construct() {
        parent::__construct();
    }

    /*-------------------------------------------------------------------------------------------------
    Accessed via http://localhost/tests/index/
    - Show the user a list of tests for the account
    -------------------------------------------------------------------------------------------------*/
    public function index() {



    } # End of index

    //probably delete this
    public function edit($test_id) {

        $this->template->content = View::instance('v_questions_edit');
        //Get the list of tests
        $q = "SELECT question_id, question_order, test_id, created_by_user_id, question_text, question_type_id, question_image
            , created, updated, all_or_none, deleted FROM questions WHERE test_id = ".$test_id;

        $question_list = DB::instance(DB_NAME)->select_rows($q);

        $this->template->content->question_list = $question_list;
        $this->template->content->test_id = $test_id;

        $this->template->content->question_types = questions_controller::getQuestionTypes();

        # Now set the <title> tag
        $this->template->title = "Test Questions";

        # Render the view
        echo $this->template;


    } # End of edit

    //Get a single question with its answers and pass it back json style
    public function get($question_id) {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $test_instance_id = null;
        if (isset($_POST["test_instance_id"])) {
            $test_instance_id = $_POST["test_instance_id"];
        }
        //if we were passed a test instance_id we know it's a user taking a test so we only send the answers that the user gave
        $q = "SELECT question_id, question_order, test_id, created_by_user_id, question_text, question_type_id, question_image
                , created, updated, all_or_none, deleted FROM questions WHERE question_id = ".$question_id;

        $question = DB::instance(DB_NAME)->select_row($q);

        if ($test_instance_id != null) {//return the question as the test taker answered it
            $q = "SELECT A.answer_id, A.question_id, IF(TIA.answer_text IS NULL,A.answer_text, TIA.answer_text) AS answer_text
                , A.answer_order, IF(TIA.is_selected IS NULL, 0, TIA.is_selected ) AS correct
                FROM answers A
                LEFT JOIN test_instance_answer TIA ON TIA.answer_id = A.answer_id AND TIA.test_instance_id = ".$test_instance_id."
                WHERE A.question_id = ".$question_id." ORDER BY answer_order ASC";
            $answers = DB::instance(DB_NAME)->select_rows($q);
            $question["answers"] = $answers;
        } else {//the test is being edited - check that this is an admin
            if ($this->user->is_admin) {
                $q = "SELECT answer_id, question_id, answer_text, answer_order, correct FROM answers WHERE question_id = ".$question_id." ORDER BY answer_order ASC";
                $answers = DB::instance(DB_NAME)->select_rows($q);
                $question["answers"] = $answers;
            } else {
                echo json_encode("invalid user");
                die;
            }
        }

        echo json_encode($question);
    }

    //add a new answer for a question
    public function p_addanswer($question_id) {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $_POST{"question_id"} = $question_id;
        //get the next answer_order for the question
        $answer_order = $this->getNextAnswerOrder($question_id);
        $_POST["answer_order"] = $answer_order;
        $_POST["correct"] = 0;//default to not-correct

        //Set the question to whatever was sent in
        $answer_id = DB::instance(DB_NAME)->insert("answers", $_POST);

        echo json_encode($answer_id);
    }

    //remove an answer from a question
    public function p_deleteanswer($question_id) {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $answer_id = $_POST["answer_id"];
        //delete the answer
        $q = "DELETE from answers WHERE answer_id = ".$answer_id;
        DB::instance(DB_NAME)->query($q);

        //re-order the questions
        $q = "UPDATE answers SET ";

        echo(json_encode(true));
    }

    //update the text of a question
    public function p_set_question_text($question_id) {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $question_text = trim($_POST["question_text"]);
        //Set the question to whatever was sent in
        $q = "UPDATE questions SET question_text = '".$question_text."' WHERE question_id = ".$question_id;
        DB::instance(DB_NAME)->query($q);
    }

    //update the text of a question
    public function p_set_answer_text($question_id) {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $answer_text = trim($_POST["answer_text"]);
        $answer_id = DB::instance(DB_NAME)->select_field("SELECT MAX(answer_id) FROM answers WHERE question_id = ".$question_id." AND question_id IN (SELECT question_id FROM questions WHERE question_id =".$question_id." AND question_type_id = 4)");
        if ($answer_id) {
            //Set the question to whatever was sent in
            $q = "UPDATE answers SET answer_text = '".$answer_text."' WHERE answer_id = ".$answer_id;
            DB::instance(DB_NAME)->query($q);
        }
    }

    //update the answer to a single question
    public function p_setanswer($question_id) {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $answer_id = $_POST["answer_id"];
        $correct = $_POST["correct"];
        if ($correct == "" || $correct == null || !isset($correct)) {$correct = "0";}
        //first reset the answers to not correct, unless the question type is 1 aka. "all correct answers"
        $q = "UPDATE answers SET correct = 0 WHERE question_id IN (SELECT question_id FROM questions WHERE question_id = ".$question_id." AND question_type_id <> 1)";
        DB::instance(DB_NAME)->query($q);

        //Set the answer to whatever was sent in
        $q = "UPDATE answers SET correct = ".$correct." WHERE answer_id = ".$answer_id;
        DB::instance(DB_NAME)->query($q);
    }

    //Add a question and refresh the page
    public function p_create($test_id) {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $errors = array();
        $data = ob_get_clean();
        //check that the question text is not blank
        if (!isset($_POST["question_text"])) {
            $errors[] = "Question text is not filled out - ";
        }
        $question_type_id = $_POST["question_type_id"];

        if (count($errors)==0) {//no errors - go ahead
            # Insert this test into the database
            $question_order = $this->getNextQuestionOrder($test_id);
            $_POST["created"] = Time::now();
            $_POST["created_by_user_id"] = $this->user->user_id;
            $_POST["updated"] = Time::now();
            $_POST["test_id"] = $test_id;
            $_POST["question_order"] = $question_order;

            $question_id = DB::instance(DB_NAME)->insert('questions', $_POST);

            //If the question is true/false add the two possible answers here
            if ($question_type_id == 3) {
                //insert the tru
                $default_questions = array(
                    "answer_text" => "True",
                    "correct" => "1",
                    "answer_order" => "0",
                    "question_id" => $question_id
                );
                DB::instance(DB_NAME)->insert("answers", $default_questions);
                //insert the false
                $default_questions = array(
                    "answer_text" => "False",
                    "correct" => "0",
                    "answer_order" => "1",
                    "question_id" => $question_id
                );
                DB::instance(DB_NAME)->insert("answers", $default_questions);

            }

            //If we have an essay question add one possible answer
            if ($question_type_id == 4) {
                //insert the tru
                $default_questions = array(
                    "answer_text" => "Please fill out the essay question",
                    "correct" => "1",
                    "answer_order" => "0",
                    "question_id" => $question_id
                );
                DB::instance(DB_NAME)->insert("answers", $default_questions);
            }

            //send back the ID
            echo json_encode(array($question_id));
        } else {//there were errors
            echo json_encode(null);
        }
    } //end of question/p_create


    public static function getQuestionTypes() {
        $q = "SELECT question_type_id,question_type_descr FROM question_types";
        return DB::instance(DB_NAME)->select_rows($q);
    }

    private function getNextQuestionOrder($test_id) {
        $q = "SELECT MAX(question_order) + 1 FROM questions WHERE test_id=".$test_id;
        return DB::instance(DB_NAME)->select_field($q);
    }

    private function getNextAnswerOrder($question_id) {
        $q = "SELECT MAX(answer_order) + 1 FROM answers WHERE question_id=".$question_id;
        return DB::instance(DB_NAME)->select_field($q);
    }


} # End of class
