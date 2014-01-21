<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 10/8/13
 * Time: 8:00 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<h3>Create a New Account</h3>

<p>This is the first step to creating and assigning your tests. Once your account is created you can
add new "test takers" and "test admins".</p>

<?php if (isset($errors)) { ?>
    <?php foreach($errors AS $current_error) { ?>
        <div class='alerttext'>
            <?php echo $current_error ?>
        </div>
    <?php } ?>
<?php }?>


<form method='POST' action='/users/p_signup' id="frmMain">
    <fieldset>
        <legend>Create Your User</legend>
            <p class="form-row">
                <label for="first_name">First Name:</label>
                <input type='text' name='first_name' id='first_name' value='<?php echo $first_name;?>'/>
            </p>
            <p class="form-row">
                <label for='last_name'>Last Name:</label>
                <input type='text' name='last_name' id='last_name' value='<?php echo $last_name; ?>'/>
            </p>
            <p class="form-row">
                <label for='email'>Email (this will be your username):</label>
                <input type='text' name='email' value='<?php echo $email;?>' style='<?php echo isset($duplicate_username) ? "color:red;" : "" ?>'/>
            </p>
            <p class="form-row">
                <label for='email'>Company/Account Name:</label>
                <input type='text' name='company' value='<?php echo $company;?>' style='<?php echo isset($duplicate_account) ? "color:red;" : "" ?>'/>
            </p>
            <p class="form-row">
                <label for='password'>Password:</label>
                <input type='password' name='password01' id='password01'>
            </p>
            <p class="form-row">
                <label for='password02'>Password Again:</label>
                <input type='password' name='password02' id='password02'>
            </p>

    </fieldset>
    <input type='submit' value='Sign up' class="submit">

</form>

<script type="text/javascript">
    $(document).ready(function() {
        $("#frmMain").validate({
            rules: {
                first_name: {required: true,minlength: 2},
                last_name: {required: true,minlength: 2},
                company: {required: true, minlength: 3},
                email: {
                    required: true,
                    email: true
                },
                password01: {required: true, minlength: 6},
                password02: {required: true, equalTo: password01}
            },
            messages: {
                first_name: "Please enter a first name.",
                last_name: "please enter a last name",
                email: "A valid email is also your username",
                password01: "We require a password of at least 6 characters",
                company: "Please enter a company name of at least 3 characters"
            }
        });
    });
</script>