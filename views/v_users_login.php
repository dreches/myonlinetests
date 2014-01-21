<?php ?>


<h3>Welcome! please login below</h3>

<?php if(isset($error)) { ?>
    <div class='alerttext'>
        <?php echo str_replace("_", " ", $error)?>. Please double check your email and password. <br/>

        If you don't have an account, <a href="/users/signup">sign up here</a>
    </div>
    <br>
<?php }?>

<form method='POST' action='/users/p_login' id="frmMain">
    <fieldset>
        <legend>Login</legend>

        <p class="form-row">
            <label for="email">Email Address:</label>
            <input type='text' name='email' id='email' value='<?php echo $new_user;?>'/>
        </p>

        <p class="form-row">
            <label for="password">Password:</label>
            <input type='password' name='password' id="password"/>

        </p>
        <span id="pwdError"></span>
    </fieldset>
    <input type='submit' value='Log in'>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $("#frmMain").validate({
            rules: {
                email: {             // compound rule
                    required: true,
                    email: true
                },
                password: {required: true}
            },
            messages: {
                email: "Your username is a valid email address",
                password: "Please enter your password"
            },
            errorPlacement: function(error, element) {
                console.log(element.attr("name"));
                if (element.attr("name") == "password") {
                    error.appendTo($("#pwdError"));
                } else {
                    error.insertAfter( element );
                }
            }
        });
    });
</script>