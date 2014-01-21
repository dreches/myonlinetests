<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 12/11/13
 * Time: 7:59 AM
 * To change this template use File | Settings | File Templates.
 */
?>
    <h2>Test Takers</h2>

    <p>
        <a href='/testtakers/upload'>Upload New Test Takers</a>

    </p>
    <!--List of test takers to follow-->
<?php
    if (isset($user_list)) {
        foreach($user_list AS $current_user) { ?>
        <div>
            <a href="/users/profileedit/<?php echo $current_user['user_id'] ?>">
                <?php echo $current_user['first_name']." ".$current_user['last_name']?>
            </a>
            - <?php echo $current_user['email']?>
            -
            <a href="/tests/viewhistory/<?php echo $current_user['user_id'] ?>">
                View Test History
            </a>
        </div>
<?php }} else {echo ("<h3>No test takers yet created</h3>");} ?>