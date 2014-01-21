<?php

class timers_controller extends secure_controller {

    /*-------------------------------------------------------------------------------------------------

    -------------------------------------------------------------------------------------------------*/
    public function __construct() {
        parent::__construct();
    }

    /*-------------------------------------------------------------------------------------------------
    Accessed via http://localhost/timers/increment/<timerid>
    Creates and increments a counter based on ID
    -------------------------------------------------------------------------------------------------*/
    public function increment($counter_id = null) {

        # Sanitize the user entered data to prevent any funny-business (re: SQL Injection Attacks)
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);
        $client_ip = $_SERVER['REMOTE_ADDR'];
        //if there is no counter ID we'll create a new one
        if ($counter_id == null) {
            $_POST['created'] = Time::now();
            $_POST['client_ip'] =$client_ip;
            $_POST['elapsed_seconds'] = 0;
            $_POST['start'] = Time::now();
            $_POST['last_updated'] = Time::now();

            $counter_id = DB::instance(DB_NAME)->insert('timers', $_POST);
        } else {//otherwise increment the counter by one

            //We only recognize counter increments from the IP that started them
            $q = "SELECT elapsed_seconds FROM timers WHERE timer_id = ".$counter_id." AND client_ip='".$client_ip."'";
            $existing_elapsed_seconds = DB::instance(DB_NAME)->select_field($q);

            if(isset($existing_elapsed_seconds)) {
                $new_elapsed_seconds = $existing_elapsed_seconds + 1;

                $_POST['elapsed_seconds'] = $new_elapsed_seconds;
                $_POST['last_updated'] = Time::now();

                $returned_id = DB::instance(DB_NAME)->update('timers', $_POST, "WHERE timer_id = ".$counter_id." AND client_ip='".$client_ip."' AND stop IS NULL");
            }
        }

        //send back the ID
        echo json_encode(array($counter_id));

    } # End of increment method

    public function stop($counter_id) {
        # Sanitize the user entered data to prevent any funny-business (re: SQL Injection Attacks)
        $_POST = DB::instance(DB_NAME)->sanitize($_POST);

        //stop the counter
        $_POST['stop'] = Time::now();
        $_POST['last_updated'] = Time::now();
        $client_ip = $_SERVER['REMOTE_ADDR'];//we only let the originating IP stop the timer
        $counter_id = DB::instance(DB_NAME)->update('timers', $_POST, "WHERE timer_id = ".$counter_id." AND client_ip='".$client_ip."' AND stop IS NULL");

        echo json_encode(array($counter_id));

    } # End of stop method


} # End of class timers_controller