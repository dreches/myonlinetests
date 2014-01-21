<?php
/**
 * Created by JetBrains PhpStorm.
 * User: skraft
 * Date: 10/15/13
 * Time: 3:42 PM
 * To change this template use File | Settings | File Templates.
 */
?>

<?php if (isset($_GET["updated"])) { ?>
    <div class="alerttext">User Updated!</div>
<?php } ?>

<h2>Edit Your Profile</h2>

<form method='POST' id="frmMain" action='/users/p_profileedit/<?php echo $currentuser["user_id"] ?>' enctype="multipart/form-data">
    <fieldset>
        <legend>Profile</legend>

        <p class="form-row">
            Company Name: <?php echo $currentuser["account_name"] ?>
        </p>
        <p class="form-row">
            <label for='first_name'>First Name</label>
            <input type='text' name='first_name' id="first_name" value='<?php echo stripslashes($currentuser["first_name"]) ?>'>
        </p>

        <p class="form-row">
            <label for='last_name'>Last Name</label>
            <input type='text' name='last_name' id="last_name" value='<?php echo stripslashes($currentuser["last_name"]); ?>'>
        </p>
        <p class="form-row">
            <label for='title'>Job Title</label>
            <input type='text' name='title' value='<?php echo stripslashes($currentuser["job_title"]); ?>'>
        </p>
        <p class="form-row">
            <label for='email'>Email (this is also the username)</label>
            <input type='text' name='email' value='<?php echo stripslashes($currentuser["email"]); ?>'>
            <br>
        </p>

        <input type='submit' value='Update Profile'>
    </fieldset>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $("#frmMain").validate({
            rules: {
                first_name: {required: true},
                last_name: {required: true},
                email: {
                    required: true,
                    email: true
                },
                title : {required:true}
            },
            messages: {
                first_name: "Please enter a first name.",
                last_name: "please enter a last name",
                email: "A valid email is also your username",
                title: "Please provide a title"
            }
        });
    });
</script>