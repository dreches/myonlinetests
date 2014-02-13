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
	
	public function p_add_question_image( $question_id ) {
		# Check if an image was actually loaded
		$key = key($_FILES);
		$f_error = $_FILES[$key]['error'];
		$filename = $_FILES[$key]['name'];
		#$errstr = '';
		if ( $f_error>0) {
			#echo "error value: $f_error";
			# if a file was specified, but not loaded, report an error so user can retry 
			if ($f_error <> 4) {
				$error = true;
				$errstr .= "Error attempting to upload $filename: ";
				switch ($f_error) {
					case 1:
					case 2: 
						$errstr .= "File size was too large. <br>";
						break;
					case 3:
						$errstr .= "File only partially uploaded<br>";
						break;
					default:
						$errstr .= "Error code: $f_error.<br>";
				}
				# Set up the View
				$this->template->content = View::instance('v_users_profile');
				$this->template->title = "DocTalk: Profile";
			    $this->template->content->error = $errstr;
				$this->template->content->user_name = $this->user->first_name.' '.$this->user->last_name;
				echo $this->template;
			}
			else
			{
				$errstr .= "No file was specified for upload. Press Skip button to leave the page.<br>";
			}
			
		}
		
		
		# At this point, either no file was specified, in which case proceed, or a file was uploaded to a temp directory.
		if (!$error) {
			if (!empty($filename))
			{
				# Give the file a unique name by incorporating the user_id.
				$new_name = "image".$question_id;
				$new_name = Upload::upload($_FILES,QIMAGE_PATH,array("jpg","JPG","jpeg","JPEG","gif","GIF","png","PNG"),$new_name);
				if ($new_name === "Invalid file type.") {
					#echo "Invalid file type";
					$errstr .= "Invalid file type for $filename.<br>";
					$error = true;
				}
				else {
					# Put the avatar in $_POST 
					#echo ($new_name);
					$_POST["avatar"]= $new_name;
				}
			}
			else
			{
				$errstr .= "No file uploaded.";
			}
		}
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
			// Make sure we were able to file question
			if ($question_id) {
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
				echo json_encode(array($question_id,$question_order));
			}
        } else {//there were errors
            echo json_encode(null);
        }
    } //end of question/p_create
	
    public function p_reorder( $test_id )
	{
		$_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $errors = array();
        $data = ob_get_clean();
        //check that the variables we need are set
        if ( empty($test_id) || !is_numeric($test_id) ||
		    !isset($_POST["start_position"]) || !is_numeric($_POST["start_position"]) ||
		    !isset($_POST["end_position"]) || !is_numeric($_POST["end_position"]) ||
			!isset( $_POST["question_id"]) || !is_numeric($_POST["question_id"])){
            $errors[] = "Invalid arguments for reordering questions. ";
        }
        
        if (count($errors)>0) {
			echo json_encode(array("ERROR"=>$errors));
		} 
		elseif ( $_POST["start_position"] == $_POST["end_position"] ) {
			echo json_encode(null);
		}
		else {		//no errors - go ahead
            # Insert this test into the database
            $affected_tabs = $this->updateQuestionOrder($_POST["start_position"], $_POST["end_position"], $test_id, $_POST["question_id"]);
			echo json_encode($affected_tabs);
		}
	}
	
	/* Potential new functionality */
	public function p_delete_question( $test_id )
	{
		$_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $errors = array();
        $data = ob_get_clean();
		$affected_questions = array();
        //check that the variables we need are set
        if ( empty($test_id) || !is_numeric($test_id) ||
			!isset( $_POST["question_id"]) || !is_numeric($_POST["question_id"]) ||
			!isset( $_POST["question_order"]) || !is_numeric($_POST["question_order"])){
            $errors[] = "Invalid arguments for delete question. ";
			echo json_encode(array("ERROR"=>$errors));
		}
		else {
			// Make sure the question is actually associated with this test. 
			$question_id = $_POST["question_id"];
			$deleted_question_order = $_POST["question_order"];
			$q= "SELECT question_id, question_order FROM questions WHERE test_id = ".$test_id.
				" AND question_order >= ".$deleted_question_order;
			$affected_questions = DB::instance(DB_NAME)->select_kv($q, 'question_id', 'question_order');
			/*
			$q= "SELECT question_order FROM questions WHERE test_id = ".$test_id.
				" AND question_id = ".$question_id;
			$deleted_question_order = DB::instance(DB_NAME)->select_field($q); */
			
			// If the input data was accurate, procede to delete
			if ( $affected_questions[$question_id] == $deleted_question_order )
			{	
				// TODO: Delete or mark as deleted the question from the database
				DB::instance(DB_NAME)->delete('questions',"WHERE question_id = ".$question_id);
				// remove the deleted question from the array
				unset($affected_questions[$question_id]);
				
				$u = "UPDATE questions 
				      SET question_order = question_order-1 
					  WHERE test_id = ". $test_id .
					  " AND question_order > ".$deleted_question_order;
				
				DB::instance(DB_NAME)->query($u);
				$affected_rows = DB::instance(DB_NAME)->connection->affected_rows;
				
				if ( count($affected_questions) == $affected_rows )
					echo json_encode( array('ROWS'=>$affected_questions));
				else {
					// We don't know what happened, so reselect the rows (using $q and return)
					$affected_questions = DB::instance(DB_NAME)->select_kv($q, 'question_id', 'question_order');
					echo json_encode( array('ERROR'=>array( "Attempt to delete question ".$deleted_question_order." did not complete successfully. Please refresh",
					'ROWS'=>$affected_questions	)));
				}
			}
		}
	}
	
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
	
	private function updateQuestionOrder($start_value, $end_value, $test_id, $start_question_id)
	{
		if ($start_value < $end_value) {
			$q = "SELECT question_id, question_order
					FROM questions 
					WHERE test_id = ".$test_id.
					" AND question_order BETWEEN ".$start_value.
					" AND ".$end_value;
			// Get the affected rows in an array
			$affected_rows = DB::instance(DB_NAME)->select_rows($q);
			$found_question = false;
			foreach (  $affected_rows AS &$row ) {
				if ($row["question_id"] != $start_question_id)
				   $row["question_order"] -= 1;
				else {
					$found_question = true;
					$row["question_order"] = $end_value;
				}
			}
			unset($row);
		}						
		elseif ($start_value > $end_value) {
			$q = "SELECT question_id, question_order
					FROM questions 
					WHERE test_id = ".$test_id.
					" AND question_order BETWEEN ".$end_value.
					" AND ".$start_value;
			// Get the affected rows in an array
			$affected_rows = DB::instance(DB_NAME)->select_rows($q);
			foreach (  $affected_rows AS &$row ) {
				if ($row["question_id"] != $start_question_id)
				   $row["question_order"] += 1;
				else {
					$found_question = true;
					$row["question_order"] = $end_value;
				}
			}
			unset($row);
		}
		if (!$found_question) { // The question was not associated with this test
			return  array('ERROR'=>array( "Invalid attempt to reorder question not associated with this test." ));
		}	
		if (!empty($affected_rows)) {
			
			// Update the values in the database. Done this way to make a single
			// transaction for the update and have a record of what changed.
			// INSERT OR UPDATE counts 2 affected rows  for each updated row.
			$updated_rows = DB::instance(DB_NAME)->update_or_insert_rows('questions', $affected_rows); 
			if ( count($affected_rows)*2 == $updated_rows )
				return  array('ROWS'=>$affected_rows);
			else
				// Get the current order of the rows we attempted to update
				$affected_rows = DB::instance(DB_NAME)->select_rows($q);
				return array('ERROR'=>array( "Attempt to update question orders did not complete successfully" ),
				'ROWS'=>$affected_rows);
		}
		return null;
	}	

} # End of class
