<?php
/**
 * Created by JetBrains PhpStorm.
 * User: skraft
 * Date: 10/15/13
 * Time: 3:42 PM
 * To change this template use File | Settings | File Templates.
 */
?>


<form method='POST' action='/users/p_profilefollow/<?php echo $currentuser["user_id"] ?>'>
    <fieldset>
        <legend>User Profile</legend>
        <p>Name: <?php echo stripslashes($currentuser["first_name"]); ?> <?php echo stripslashes($currentuser["last_name"]) ?></p>
        <p>Email: <?php echo stripslashes($currentuser["email"]); ?></p>
        <p>Job Title: <?php echo stripslashes($currentuser["job_title"]); ?></p>

    </fieldset>

</form>