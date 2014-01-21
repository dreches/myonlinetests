<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 12/11/13
 * Time: 7:59 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<h2>Upload Test Takers</h2>

<p>
    Uploading your test takers is easily done using a minimum of 3 fields in a plain old text file. You can either create the
    file using your favorite text editor, or have your IT department generate one from your HRMS or Payroll system.
    Please choose a comma delimited file according to the following format (optional fields in green).
    <ul>
        <li>FirstName - the first name of the test taker</li>
        <li>LastName - the last name of the test taker</li>
        <li>Email - the test taker's email (we don't share or sell - see <a href="/users/privacy">our privacy policy</a>)</li>
        <li style="color:#278200">JobTitle - optional, the appropriate title can be used to assign tests to groups (default is "test taker")</li>
        <li style="color:#278200">Person_Id - optional, this would be an ID your organization uses and is included in reports and feeds to enable data exchange</li>
    </ul>
    <img src="/images/employees_to_upload.png" alt="An example of what the file might look like" title="An example of what the file might look like"/>

</p>

<div id="testtaker-upload">
    <form method='POST' id='frmMain' action='/testtakers/p_upload/' enctype="multipart/form-data">
        <fieldset>
            <legend>Upload test takers</legend>
            <p>
                <label for='file'>Choose a text file to upload</label>
                <input type='file' name='file' id='file'/>
            </p>
        </fieldset>
        <input type='submit' value='Upload'>

    </form>
</div>

