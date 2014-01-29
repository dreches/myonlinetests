<?php

class tests_controller extends secure_controller {

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
        $this->template->content = View::instance('v_tests_index');
        //Get the list of tests
        $q = "SELECT test_id,test_name,test_descr, test_category, created_on_dt, test_year FROM tests WHERE account_id = ".$this->user->account_id;
        $test_list = DB::instance(DB_NAME)->select_rows($q);
        $this->template->content->test_list = $test_list;

        # Now set the <title> tag
        $this->template->title = "Tests";

        # Render the view
        echo $this->template;

    } # End of index

    //Display enough fields to allow the user to create a test
    public function create() {
        $this->template->content = View::instance('v_tests_create');
        # Now set the <title> tag
        $this->template->title = "Create Test";

        $this->template->content->test_name = "";
        $this->template->content->test_descr = "";
        $this->template->content->test_category = "";

        # Render the view
        echo $this->template;

    } # End of create

    public function p_create() {

        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $errors = array();
        $data = ob_get_clean();
        //check that the test does not yet exist
        $test_name = $_POST["test_name"];
        $existing_test_id = $this->getExistingTestId(trim($test_name));

        if ($existing_test_id) {$errors[] = "The test named, ".$test_name.", already exists";}

        if (count($errors)==0) {//no errors - go ahead
            # Insert this test into the database
            $_POST["test_year"] = date("Y"); //the current year
            $_POST["account_id"] = $this->user->account_id;
            $_POST["created_by_user_id"] = $this->user->user_id;
            $_POST["created_on_dt"] = Time::now();
            $_POST["last_updated_dt"] = Time::now();
            $_POST["passing_grade"] = 100;
            $_POST["minutes_to_complete"] = 0;

            $test_id = DB::instance(DB_NAME)->insert('tests', $_POST);

            Router::redirect("/tests/edit/".$test_id);

        } else {//there were errors
            $this->template->content = View::instance('v_tests_create');
            $this->template->title   = "Create Test";
            $this->template->content->errors = $errors;

            $this->template->content->test_name = $_POST["test_name"];
            $this->template->content->test_descr = $_POST["test_descr"];
            $this->template->content->test_category = $_POST["test_category"];

            echo $this->template;

        }

    } # End of p_create

    //display an editable test with its answers and materials
    public function edit($test_id) {

        $this->template->content = View::instance('v_tests_edit');
        # Now set the <title> tag
        $this->template->title = "Edit Test";
		# CSS/JS includes
			/*
			$client_files_head = Array("");
	    	$this->template->client_files_head = Utils::load_client_files($client_files);
	    	*/
			#Keep the JS in a separate file
			
	    	$client_files_body = Array("/js/tests-edit.js");
	    	$this->template->client_files_body = Utils::load_client_files($client_files_body);   
			

        $return_row = $this->getExistingTest($test_id);

        if ($return_row) {
            $this->template->content->test_id = $return_row["test_id"];
            $this->template->content->test_name = $return_row["test_name"];
            $this->template->content->test_descr = $return_row["test_descr"];
            $this->template->content->test_category = $return_row["test_category"];
            $this->template->content->test_year = $return_row["test_year"];
            $this->template->content->passing_grade=$return_row["passing_grade"];
            $this->template->content->created_on_dt =$return_row["created_on_dt"];
            $this->template->content->minutes_to_complete = $return_row["minutes_to_complete"];

            //setup the questions
            $this->setupTestQuestionsForDisplay($this->template, $test_id);

            //setup the assignments
            $test_assign_status = siteutils::getTestAssignStatus($test_id);
            $this->template->content->test_assign_status = $test_assign_status;
            $this->template->content->disable_control = "";
            $this->template->content->editable = true;
            foreach ($test_assign_status as $current_test_assign_status) {
                $status_id = $current_test_assign_status["test_assign_status_id"];
                if ($status_id > 1) {//anything higher than assigned means we can't edit anymore
                    $this->template->content->editable = false;
                    $this->template->content->disable_control = "disabled='true'";
                }
            }
        } else {
            Router::redirect("/error/generic");
        }

        # Render the view
        echo $this->template;
    } # End of edit

    //set the assignments for a test
    public function p_assign($test_id) {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        //echo var_dump($_POST);
        $errors = array();
        //For any assignment to this test that is in assigned state - delete it
        $q = "DELETE FROM test_assign_user WHERE test_assign_status_id = 1 AND test_id=.".$test_id;
        DB::instance(DB_NAME)->query($q);

        //Find the passed in checkboxes (these are selected by the user)
        foreach($_POST as $key => $value) {
            if (strpos($key, 'chk_') === 0) {//we have a checkbox
                if (is_numeric($value)) {//this value should be the user_id
                    //Get the status of any existing assignment
                    $user_assign_status = siteutils::getTestAssignStatus($test_id, $value);
                    $test_assign_status_id = $user_assign_status[0]["test_assign_status_id"];

                    if (!isset($test_assign_status_id)) {//there is no assignment
                        //insert a new assignment
                        $due_on_date = "txt_due_".$value;
                        $due_on_date = $_POST[$due_on_date];
                        $due_on_date = strtotime($due_on_date);
                        $assign_test = array("assigned_by_user_id" => $this->user->user_id,
                            "assigned_on_dt" => Time::now(),
                            "due_on_dt" => $due_on_date,
                            "test_assign_status_id" => "1",
                            "test_id" => $test_id,
                            "user_id" => $value
                        );

                        $test_assign_id = DB::instance(DB_NAME)->insert('test_assign_user', $assign_test);
                    }
                } else {
                    $errors[] = "Invalid values posted";
                }
            }
        }
        Router::redirect("/tests/edit/".$test_id);

    }// end p_assign

    //Get the assignment record with some details about the test and display it to the user
    public function assignment($test_assign_id) {
        $errors = array();
        $test_assign_id =  DB::instance(DB_NAME)->sanitize($test_assign_id);

        //Setup the view
        $this->template->content = View::instance('v_test_assign_details');

        if (!is_numeric($test_assign_id)) {
            $errors[] = "Invalid assignment";
        }
        if (count($errors) == 0) {
            $assign_details = siteutils::getTestAssignmentDetails($test_assign_id);
            $this->template->content->assign_details = $assign_details;
        }

        # Now set the <title> tag
        $this->template->title = "Test Assignment";
        $this->template->content->errors = $errors;

        # Render the view
        echo $this->template;

    }//end of assignment


    //Get the test and allow the user to answer the questions
    public function take($test_assign_id, $test_instance_id = null, $question_id = null) {
        $errors = $this->checkQuestionInput($test_assign_id, $test_instance_id, $question_id, 2);

        //Setup the view
        $this->template->content = View::instance('v_test_take_question');

        if (count($errors) == 0) {
            //If there is no test instance for this assignment we need to create one
            $instance_details = null;
            if ($test_instance_id == null) {
                $q = "SELECT test_instance_id, test_assign_id, start_dt, finish_dt FROM test_instance WHERE test_assign_id =".$test_assign_id;
                $instance_details = DB::instance(DB_NAME)->select_row($q);
            } else {
                $q = "SELECT test_instance_id, test_assign_id, start_dt, finish_dt FROM test_instance WHERE test_instance_id=".$test_instance_id;
                $instance_details = DB::instance(DB_NAME)->select_row($q);
            }
            if (count($instance_details) == 0) {//no existing instance
                $create_instance =array("start_dt" => Time::now(),"test_assign_id" => $test_assign_id);
                $test_instance_id = DB::instance(DB_NAME)->insert("test_instance", $create_instance);
            } else {
                $test_instance_id = $instance_details["test_instance_id"];//needs to be set because an assign ID may be all that was sent
                $finish_dt = $instance_details["finish_dt"];
                if (isset($finish_dt)) {$errors[] = "This test has already been completed";}
                $check_test_assign_id = $instance_details["test_assign_id"];
                if ($check_test_assign_id != $test_assign_id) {$errors[] = "Instance is not valid";}
            }

            //mark the test_assign_user as being taken
            $test_assign_update = array("test_assign_status_id" => 2);
            DB::instance(DB_NAME)->update("test_assign_user", $test_assign_update, "WHERE test_assign_id = ".$test_assign_id);

            //get the question details and setup the form
            $question_details = siteutils::getQuestionDetails($test_instance_id, $question_id);
            $timer_id = $question_details[0]['timer_id'];
            $secondsEt = 0;
            if ($timer_id != null) {
                $secondsEt = DB::instance(DB_NAME)->select_field("select elapsed_seconds FROM timers WHERE timer_id=".$timer_id);
                if ($secondsEt == null) {$secondsEt = 0;}
            } else {$timer_id = "null";}//this will render for javascript correctly now

            $this->template->content->question_details = $question_details;
            $this->template->content->question_text = $question_details[0]['question_text'];
            $this->template->content->question_type_id = $question_details[0]['question_type_id'];
            $this->template->content->test_assign_id = $test_assign_id;
            $this->template->content->test_instance_id = $test_instance_id;
            $this->template->content->question_id = $question_details[0]['question_id'];
            $this->template->content->next_question_id = $question_details[0]['next_question_id'];
            $this->template->content->prior_question_id = $question_details[0]['prior_question_id'];
            $this->template->content->question_order = $question_details[0]['question_order'];
            $this->template->content->minutes_to_complete = $question_details[0]['minutes_to_complete'];
            $this->template->content->serverTimerId = $timer_id;
            $this->template->content->secondsEt = $secondsEt;
            $due_on_dt = $question_details[0]["due_on_dt"];
            if ($due_on_dt != "") {$due_on_dt = date("m/d/Y", $due_on_dt);}
            $this->template->content->$due_on_dt = $due_on_dt;

        }

        # Now set the <title> tag
        $this->template->title = "Test Assignment";
        $this->template->content->errors = $errors;

        # Render the view
        echo $this->template;

    }//end of take

    //question is being submitted with an answer
    public function p_take($test_assign_id, $test_instance_id, $question_id) {
        $errors = $this->checkQuestionInput($test_assign_id, $test_instance_id, $question_id, 2);
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);

        $next_question_id = $question_id;
        if (count($errors) == 0){
            $question_details = siteutils::getQuestionDetails($test_instance_id,$question_id);
            $question_type_id = $question_details[0]["question_type_id"];
            $next_question_id = $question_details[0]["next_question_id"];

            //delete any existing answer for this test instance
            $q = "DELETE FROM test_instance_answer WHERE test_instance_id = ".$test_instance_id." AND question_id =".$question_id;
            DB::instance(DB_NAME)->query($q);

            switch ($question_type_id) {
                case 1://check boxes
                    //Find the passed in checkboxes (these are selected by the user)
                    foreach($_POST as $key => $value) {
                        if (strpos($key, 'select_') === 0) {//we have a checkbox
                            //parse out the answer id and pop it into the database
                            $arr = explode("_", $key);
                            $answer_id = $arr[count($arr)-1];
                            $test_instance_answer =  array(
                                "answer_id" => $answer_id,
                                "is_selected" => 1,
                                "question_id" => $question_id,
                                "test_instance_id" => $test_instance_id
                            );
                            //echo var_dump($test_instance_answer);
                            $test_instance_answer_id = DB::instance(DB_NAME)->insert("test_instance_answer",$test_instance_answer);
                        }
                    }
                    break;
                case 2://radio buttons
                case 3://true or false (which is similar to radio buttons)
                    foreach($_POST as $key => $value) {//the value will be the answer_id that was chosen
                        if (strpos($key, "question_answer") === 0) {//we have a checkbox
                            $test_instance_answer = array(
                                "answer_id" => $value,
                                "is_selected" => 1,
                                "question_id" => $question_id,
                                "test_instance_id" => $test_instance_id
                            );
                            $test_instance_answer_id = DB::instance(DB_NAME)->insert("test_instance_answer",$test_instance_answer);
                        }
                    }
                    break;
                case 4:
                    $answer_id = $question_details[0]["answer_id"];
                    $answer_control_name = "txt_".$question_id."_".$answer_id;
                    $answer_text = $_POST[$answer_control_name];
                    $test_instance_answer = array(
                        "answer_id" => $answer_id,
                        "is_selected" => 1,
                        "question_id" => $question_id,
                        "test_instance_id" => $test_instance_id,
                        "answer_text" => $answer_text
                    );
                    $test_instance_answer_id = DB::instance(DB_NAME)->insert("test_instance_answer",$test_instance_answer);
                    break;
            }
        }
        if ($next_question_id != null){//redirect to the next question
            Router::redirect("/tests/take/".$test_assign_id."/".$test_instance_id."/".$next_question_id);
        } else {//go to the test summary
            Router::redirect("/tests/takesummary/".$test_assign_id."/".$test_instance_id);
        }
    }

    //Show a summary of the test to the user with a "finish" button
    public function takesummary($test_assign_id, $test_instance_id) {
        $errors = $this->checkQuestionInput($test_assign_id, $test_instance_id, null);

        if (count($errors) == 0){
            $test_assign_id =  DB::instance(DB_NAME)->sanitize($test_assign_id);
            $test_instance_id = DB::instance(DB_NAME)->sanitize($test_instance_id);

            $instance_summary = siteutils::getTestInstanceSummary($test_instance_id);

            $this->template->content = View::instance('v_tests_take_summary');
            $this->template->title   = "Test Summary";
            $this->template->content->instance_summary = $instance_summary;
            $this->template->content->test_assign_id = $test_assign_id;
            $this->template->content->test_instance_id = $test_instance_id;

            if (isset($_GET["timeout"])) {
                $this->template->content->timeout=true;
            }

            echo $this->template;
        }

    }

    //Display the history of tests for the user
    public function viewhistory($user_id = null) {
        $user_id = siteutils::getLegitUserId($user_id, $this->user);
        $test_list = siteutils::getTestsAssigedToUser($user_id, 3);

        //Display the test history
        $this->template->content = View::instance('v_test_display_user_history');
        $this->template->title   = "Test History";
        $this->template->content->test_list = $test_list;
        $this->template->content->test_taker_name = DB::instance(DB_NAME)->select_field("SELECT CONCAT(first_name,' ',last_name) AS person_name FROM users WHERE user_id =".$user_id);

        echo $this->template;
    }

    //testtaker is submitting the test - mark it as taken and grade it
    public function p_submit($test_assign_id, $test_instance_id) {
        $errors = $this->checkQuestionInput($test_assign_id, $test_instance_id, null);
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);

        if (count($errors) == 0){
            //mark the test_assign_user as being taken
            $test_assign_update = array("test_assign_status_id" => 3);
            DB::instance(DB_NAME)->update("test_assign_user", $test_assign_update, "WHERE test_assign_id = ".$test_assign_id);

            $test_instance_update = array("test_instance_id" => $test_instance_id, "finish_dt" => Time::now());
            DB::instance(DB_NAME)->update("test_instance", $test_instance_update, "WHERE test_instance_id = ".$test_instance_id);

            //Grade the test
            $graded_test = siteutils::gradeTest($test_instance_id);
            if (count($graded_test) > 0){$graded_test = $graded_test[0];}

            //Display the grade
            $this->template->content = View::instance('v_test_display_grade');
            $this->template->title   = "Grade Summary";
            $this->template->content->graded_test = $graded_test;
            $this->template->content->test_assign_id = $test_assign_id;
            $this->template->content->test_instance_id = $test_instance_id;

            echo $this->template;

        }
    }

    //Set the timer_id to the test_instance
    public function settimer($test_instance_id, $timer_id) {
        $errors = array();
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);

        if (count($errors) == 0){
            $test_instance_update = array("timer_id" => $timer_id);
            DB::instance(DB_NAME)->update("test_instance", $test_instance_update, "WHERE test_instance_id = ".$test_instance_id." AND timer_id IS NULL");//don't let the timer_id get overwritten

        }
    }

    //Make the changes required to the test and re-direct to the edit screen again
    public function p_edit($test_id) {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $errors = array();
        $data = ob_get_clean();
        //check if the test exists
        $test_name = trim($_POST["test_name"]);
        $existing_test_id = $this->getExistingTestId(trim($test_name));
        //echo '|'.$existing_test_id.'|';
        if (($existing_test_id != null) && ($existing_test_id != $test_id)) {$errors[] = "The test named, ".$test_name.", already exists";}

        if (count($errors)==0) {//no errors - go ahead
            # update the test
            $_POST["last_updated_dt"] = Time::now();
            DB::instance(DB_NAME)->update('tests', $_POST, " WHERE test_id =".$test_id);

            Router::redirect("/tests/edit/".$test_id);

        } else {//there were errors
            $this->template->content = View::instance('v_tests_edit');
            $this->template->title   = "Edit Test";
            $this->template->content->errors = $errors;

            $this->template->content->test_id = $_POST["test_id"];
            $this->template->content->test_name = $_POST["test_name"];
            $this->template->content->test_descr = $_POST["test_descr"];
            $this->template->content->test_category = $_POST["test_category"];
            $this->template->content->test_year = $_POST["test_year"];
            $this->template->content->passing_grade=$_POST["passing_grade"];
            $this->template->content->minutes_to_complete = $_POST["minutes_to_complete"];

            $this->setupTestQuestionsForDisplay($this->template, $test_id);
            echo $this->template;

        }

    } # End of p_edit



    //Get the ID for an existing test with the given name
    private function getExistingTestId($test_name) {
        $q = "SELECT test_id FROM tests WHERE deleted = 0 AND test_name = '".$test_name."' AND account_id = ".$this->user->account_id;
        $existing_test_id = DB::instance(DB_NAME)->select_field($q);
        return $existing_test_id;
    }

    //Get the details for a specific test
    private function getExistingTest($test_id) {
        $q = "SELECT test_id, account_id, copied_from_test_id, test_name, test_descr, public,
        test_year, created_by_user_id, created_on_dt, last_updated_dt, minutes_to_complete
        , passing_grade, deleted, deleted_date, test_category FROM tests WHERE deleted = 0 AND test_id = ".$test_id;
        return DB::instance(DB_NAME)->select_row($q);
    }

    private function setupTestQuestionsForDisplay($template_instance, $test_id) {
        //setup the questions
        $q = "SELECT question_id, question_order, test_id, created_by_user_id, question_text, question_type_id, question_image
            , created, updated, all_or_none, deleted FROM questions WHERE test_id = ".$test_id. " ORDER BY question_order ASC";

        $question_list = DB::instance(DB_NAME)->select_rows($q);

        $template_instance->content->question_list = $question_list;
        $template_instance->content->question_types = questions_controller::getQuestionTypes();
    }

    /*Checks the following
    1. test_assign_id and test_instance_id are numeric
    2. The test_assign_id is in the database
    3. The user assigned ot the test is the logged in user
    4. Is the test in a status greater than what is required (ex: if test is complete and we are looking for assinged)
    */
    private function checkQuestionInput($test_assign_id, $test_instance_id = null, $question_id = null, $min_status_id = null) {
        $errors = array();
        //Is our input legit at all?
        $test_assign_id =  DB::instance(DB_NAME)->sanitize($test_assign_id);
        $test_instance_id = DB::instance(DB_NAME)->sanitize($test_instance_id);
        $question_id = DB::instance(DB_NAME)->sanitize($question_id);
        if (!is_numeric($test_assign_id)) {$errors[] = "Invalid assignment";}
        if ($test_instance_id != null && !is_numeric($test_instance_id)) {$errors[] = "Invalid test";}
        if ($question_id != null && !is_numeric($question_id)) {$errors[] = "Invalid question";}

        if (count($errors) == 0) {
            $test_assign = DB::instance(DB_NAME)->select_row("SELECT test_assign_id, user_id, test_assign_status_id FROM test_assign_user WHERE test_assign_id=".$test_assign_id);
            //Does our legit input exist in our DB for this user?
            if (count($test_assign) > 0) {
                //Is this test assigned to the logged in user
                $check_user_id = $test_assign["user_id"];
                if ($check_user_id != $this->user->user_id) {$errors[] = "Invalid user";}

                //Check the test_assign is not in a greater status then what we're looking for
                if ($min_status_id != null) {
                    $current_status_id = $test_assign["test_assign_status_id"];
                    if ($current_status_id > $min_status_id) {$errors[] = "Cannot complete test at this time";}
                }
            } else {$errors[] = "Invalid assignment";}
        }

        return $errors;
    }

} # End of class
