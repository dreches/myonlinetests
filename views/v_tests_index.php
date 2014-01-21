<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 12/7/13
 * Time: 7:59 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<h2>Tests</h2>
<!--
<div id="dialog-form" title="Create new user">
  <p class="validateTips">All form fields are required.</p>
  <form method='POST' action='p_create' id="frmMain">
    <fieldset>
        <legend>Create a Test</legend>
        <p class="form-row">
            <label for="test_name">Test Name:</label>
            <input type='text' name='test_name' id='test_name' placeholder = 'At least 5 characters' value='<?php echo $test_name;?>'/>
        </p>
        <p class="form-row">
            <label for='test_descr'>Description:</label>
            <input type='text' name='test_descr' id='test_descr' placeholder = 'At least 5 characters' value='<?php echo $test_descr; ?>'/>
        </p>
        <p class="form-row">
            <label for='test_category'>Category:</label>
            <input type='text' name='test_category' id='test_category' placeholder = 'At least 5 characters' value='<?php echo $test_category;?>'/>
        </p>
    </fieldset>
    

</form>
</div>
-->

<p>
    <a class="button" href='tests/create'>Create a New Test</a>

</p>
<!--List of tests to follow-->
<div id="existing-tests-div">

<?php foreach($test_list AS $current_test) { ?>
    <div>
        <a href="/tests/edit/<?php echo $current_test['test_id'] ?>">
            <?php echo $current_test['test_name']?>
        </a>
        - <?php echo $current_test['test_descr']?>
    </div>
</div>

<?php } ?>