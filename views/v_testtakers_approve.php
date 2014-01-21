<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 12/11/13
 * Time: 7:59 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<h2>Uploaded Test Takers</h2>

<!--List of test takers to follow-->
<form method='POST' id='frmMain' action='/testtakers/p_approve/' >
    <div style="float:left;">
        <p  class="form-row" >
            <label style="float: left;width:350px;text-align: left" for="txtPassword" >Enter a default password for the new users: </label><br/>
            <input style="clear:left" type="text" id="txtPassword" name="txtPassword" value="p@$$w0rd"/>
        </p>
    </div>

    <div style="clear:left"></div>

    <table id="rounded-corner">
        <thead class="table-header">
            <th scope="col" class="rounded-q1">Include</th>
            <th scope="col" class="rounded" nowrap>First Name</th>
            <th scope="col" class="rounded" nowrap>Last Name</th>
            <th scope="col" class="rounded" nowrap>Email</th>
            <th scope="col" class="rounded" nowrap>Job Title</th>
            <th scope="col" class="rounded" nowrap>Person ID</th>
            <th scope="col" class="rounded-q4" nowrap>Issues</th>
        </thead>
        <tfoot>
        <tr>
            <td colspan="6" class="rounded-foot-left"><em>* please check test takers you want to import and click approve below</em></td>
            <td class="rounded-foot-right">&nbsp;</td>
        </tr>
        </tfoot>
        <tbody>
    <?php
        if ($user_list) {

        foreach($user_list AS $current_user) { ?>
            <tr>
                <td class="table-row" style="text-align: center">
                    <?php if ($current_user['issue_text'] == "") { //don't allow any items with issues to be selected?>
                        <input type="checkbox" checked="checked" id="chk_<?php echo $current_user['testtaker_staging_row_id']?>"
                           name="chk_<?php echo $current_user['testtaker_staging_row_id']?>" value="<?php echo $current_user['testtaker_staging_row_id']?>">
                    <?php } ?>
                </td>
                <td nowrap><?php echo $current_user['first_name']?></td>
                <td nowrap><?php echo $current_user['last_name']?></td>
                <td nowrap><?php echo $current_user['email']?></td>
                <td nowrap><?php echo $current_user['job_title']?></td>
                <td nowrap><?php echo $current_user['person_id']?></td>
                <td nowrap><?php echo $current_user['issue_text']?></td>
            </tr>
        <?php }} else {echo ("<h3>No test takers exist for this instance</h3>");} ?>
        <tbody>
    </table>
    <input type="hidden" id="testtaker_staging_id" name="testtaker_staging_id" value="<?php echo $testtaker_staging_id?>"/>
    <input type='submit' value='Approve'>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $("#frmMain").validate({
            rules: {
                txtPassword: {required: true, minlength: 6}
            },
            messages: {
                txtPassword: "We require a password of at least 6 characters"
            }
        });
    });
</script>