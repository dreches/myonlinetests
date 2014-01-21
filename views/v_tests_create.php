<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 10/8/13
 * Time: 8:00 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<?php if (isset($errors)) { ?>
    <?php foreach($errors AS $current_error) { ?>
        <div class='alerttext'>
            <?php echo $current_error ?>
        </div>
    <?php } ?>
<?php }?>

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
            <input type='text' name='test_category' placeholder = 'At least 5 characters' value='<?php echo $test_category;?>'/>
        </p>
    </fieldset>
    <input type='submit' value='Create Test'>

</form>

<script type="text/javascript">
    $(document).ready(function() {
        $("#frmMain").validate({
            rules: {
                    test_name: {required: true, minlength: 5},
                    test_descr: {required: true, minlength: 5},
                    test_category: {required: true, minlength: 5}
            },
            messages: {
                test_name: "Please enter a name of at least 5 characters",
                test_descr: "Please enter a description of at least 5 characters",
                test_category: "Please enter a category of at least 5 characters"
            }
        });
    });
</script>