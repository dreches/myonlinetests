<?php

class secure_controller extends base_controller {

    /*-------------------------------------------------------------------------------------------------
    controller class that implements security for any controller that needs it
    1. Check that the user is logged in
    -------------------------------------------------------------------------------------------------*/
    public function __construct() {
        parent::__construct();
        //we need a user for this controller - check for it
        siteutils::redirectnonloggedinuser($this->user);
    }
}//end of class