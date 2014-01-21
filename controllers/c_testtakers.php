<?php

class testtakers_controller extends secure_controller {

    /*-------------------------------------------------------------------------------------------------

    -------------------------------------------------------------------------------------------------*/
    public function __construct() {
        parent::__construct();
    }

    /*-------------------------------------------------------------------------------------------------
    Accessed via http://localhost/tests/index/
    - Show a list of the test takers with a link to edit them, don't show the current user
    -------------------------------------------------------------------------------------------------*/
    public function index() {
        $user_list = siteutils::getUsersWithAccount($this->user->account_id);

        $this->template->content = View::instance('v_testtakers_index');
        $this->template->content->user_list = $user_list;

        # Now set the <title> tag
        $this->template->title = "Test Takers";

        # Render the view
        echo $this->template;


    } # End of index

    //Allow the user to upload a file full of test takers
    public function upload($errors=null) {

        $this->template->content = View::instance('v_testtakers_upload');
        $this->template->content->errors = $errors;

        # Now set the <title> tag
        $this->template->title = "Upload Test Takers";

        # Render the view
        echo $this->template;


    } # End of edit

    //Show the user the uploaded testtakers and let them decide if it's final
    public function approve($testtaker_staging_id) {
        $this->template->content = View::instance('v_testtakers_approve');
        $this->template->content->errors = null;

        $q = "SELECT testtaker_staging_row_id, first_name, last_name, email, job_title, person_id, issue_text FROM testtaker_staging_rows WHERE testtaker_staging_id = ".$testtaker_staging_id;
        $this->template->content->user_list = DB::instance(DB_NAME)->select_rows($q);
        $this->template->content->testtaker_staging_id = $testtaker_staging_id;

        # Now set the <title> tag
        $this->template->title = "Approve Test Takers";

        # Render the view
        echo $this->template;
    }//end of approve

    //save all the approved uploads
    public function p_approve() {
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $testtaker_staging_id = $_POST["testtaker_staging_id"];
        $errors = array();
        foreach($_POST as $key => $value) {
            if (strpos($key, 'chk_') === 0) {
                if (is_numeric($value)) {
                    //first get the details from the row
                    $q="SELECT first_name, last_name, email, job_title, person_id, issue_text FROM testtaker_staging_rows WHERE testtaker_staging_row_id = ".$value." AND issue_text IS NULL";
                    $testtaker_staging_row = DB::instance(DB_NAME)->select_row($q);

                    if (count($testtaker_staging_row) > 0) {//this could be blank if there were issues in the issue_text
                        //second get the job ID or add it for the title
                        $job_title = trim($testtaker_staging_row["job_title"]);
                        $job_title = $job_title != "" ? $job_title : "Test Taker";
                        $job_id = siteutils::getJobId($job_title, $this->user->account_id);

                        //third add the user
                        $user_id = siteutils::createUser($this->user->account_id,$job_id,$testtaker_staging_row['first_name'],$testtaker_staging_row['last_name']
                            ,$testtaker_staging_row['email'],$_POST["txtPassword"], false);

                    }
                } else {
                    $errors[] = "Invalid values posted";
                }
            }
        }

        if (count($errors) == 0) {
            //Finally delete the staging data
            $q = "DELETE FROM testtaker_staging_rows WHERE testtaker_staging_id=".$testtaker_staging_id;
            DB::instance(DB_NAME)->query($q);
            $q = "DELETE FROM testtaker_staging WHERE testtaker_staging_id=".$testtaker_staging_id;
            DB::instance(DB_NAME)->query($q);
            //redirect so the user can see the new recruits
            Router::redirect("/testtakers/");
        } else {//display the errors

        }
    }

    //Suck in the file of test takers and create users
    public function p_upload() {
        $errors = array();
        $allowedExts = array("txt", "csv");
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = end($temp);
        $added_count = 0;
        if ((($_FILES["file"]["type"] == "image/gif")
                || ($_FILES["file"]["type"] == "text/plain")
                || ($_FILES["file"]["type"] == "text/csv")
            )
            && ($_FILES["file"]["size"] < 20000)
            && in_array($extension, $allowedExts))
        {
            if ($_FILES["file"]["error"] > 0)
            {
                $errors[] = "Error: " . $_FILES["file"]["error"];
            }
            else
            {
                //store the contents of the file in the database staging table
                $testtaker_staging_id = DB::instance(DB_NAME)->insert('testtaker_staging', array("created" => Time::now(),
                        "created_by_user_id" => $this->user->user_id
                ));
                if (($handle = fopen($_FILES["file"]["tmp_name"], "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $issue_text = "";
                        $num = count($data);
                        $testtaker_staging_row = array("testtaker_staging_id"=> $testtaker_staging_id, "first_name" => null
                        ,"last_name" => null, "email" => null, "job_title" => null, "person_id" => null);

                        for ($c=0; $c < $num; $c++) {
                            if ($c<=5) {
                                    switch ($c) {
                                        case 0:
                                            $testtaker_staging_row["first_name"] = $data[$c];
                                            break;
                                        case 1:
                                            $testtaker_staging_row["last_name"] = $data[$c];
                                            break;
                                        case 2:
                                            $current_email = $data[$c];
                                            $existing_user_id = siteutils::getExisitingUser_Id($current_email);
                                            if ($existing_user_id > 0) {
                                                $issue_text = $issue_text." - This email already exists in the system, please use another";
                                            }
                                            $testtaker_staging_row["email"] = $current_email;
                                            break;
                                        case 3:
                                            $testtaker_staging_row["job_title"] = $data[$c];
                                            break;
                                        case 4:
                                            $testtaker_staging_row["person_id"] = $data[$c];
                                            break;
                                    }
                            }
                        }
                        //Don't add the headers as names
                        if ($testtaker_staging_row["first_name"] != "FirstName" && $testtaker_staging_row["first_name"] != "LastName"
                            && $testtaker_staging_row["first_name"] != "Email" && $testtaker_staging_row["first_name"] != "JobTitle"
                            && $testtaker_staging_row["first_name"] != "Person_Id") {

                            if ($issue_text != "") {$testtaker_staging_row["issue_text"] = $issue_text;}
                            DB::instance(DB_NAME)->insert("testtaker_staging_rows", $testtaker_staging_row);
                            $added_count++;
                        }
                    }
                    fclose($handle);
                    if ($added_count == 0) {
                        $errors[] = "No records in file";
                    }

                }
            }
        }
        else
        {
            $errors[] = "Invalid file";
        }

        if (count($errors) > 0) {
            $this->upload($errors);
        } else {
            //Redirect to the staging page
            Router::redirect("/testtakers/approve/".$testtaker_staging_id);
        }


    } # End of edit



} # End of class
