<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sean
 * Date: 10/8/13
 * Time: 8:00 AM
 * To change this template use File | Settings | File Templates.
 */
?>

<style>
    label {text-transform:capitalize;}
</style>
<style>
    
    .ui-tabs-vertical { width: 55em; }
    .ui-tabs-vertical .ui-tabs-nav { padding: 0em .3em 0em 0em; float: right;
									 margin-right: 0em;
									 width: 16em; }
    .ui-tabs-vertical .ui-tabs-nav li { clear: right; 
										width: 100%;
										font-size: 90%;
										border: ridge grey;
										border-width: 2px 4px 2px 0px !important ;
										border-width-left: 0px !important;
										padding-left: 0px; 
										margin: 2px 0px 2px .1em; }
    .ui-tabs-vertical .ui-tabs-nav li a { display:block; }
    .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-left: 0px;
													   background-color: #EBFFD6;
													   background-image:  none;
													   margin: 2px 0px 2px -.1em;
													   padding-right: .1em; 
													   border-width: 1px;
													   border-left-width: 0px; 								   
													   border: groove grey;}
    .ui-tabs-vertical .ui-tabs-panel { padding: 1em; 
									   background-color: #EBFFD6;
									   float: left; 
									   width: 40em; 
									   border: 1px solid red;}
	.ui-tabs-vertical {background-color:#EBFFD6;} 
	section {border : 1px solid orange; padding: 0px;}
	#tab-questions .ui-tabs-nav li.ui-tabs-active, 
	#tab-questions .ui-tabs-nav li.ui-tabs-active * {background-color: #EBFFD6 !important}
	
	span.question-order {float: left;  margin-top: .5em; margin-left: .3em; color: green; font-weight: bold; }
	#tab-question-edit {border: 2px dotted cyan; 
						padding-left: 0px; padding-right: 0px;
						background-color: #EBFFD6;}
	div[id^="tab-"] form { border: 1px solid green; }
	#tab-questions { margin-left: 0px; margin-right: 0px; 
					border: 1px solid magenta;
					padding-left: 0px;
					margin-right: 0px;
					padding-right: 0px;
					width: 100%;
					background-color: #EBFFD6 !important}
	#tab-questions li .ui-icon-close { float: right; margin: 0.4em 0.2em 0 0; cursor: pointer; }
	#new-question-tab { font-weight: bold; font-style: italic; }
	#tab-question-new fieldset { border-width: 0px;}
	#tab-question-new fieldset legend{ font-weight: bold}
	#cmdAddQuestion{ padding-top: .5em; padding-bottom: .5em;  margin-top: 1em; }
	span.instructions { display: block; font-size: 90%; color: #CC0000;}
</style>


<div id="loading"> </div>

<div id="test_details" style="display:none">
	<h3>Edit Test: "<?php echo $test_name;?>"</h3>

	<?php if (!$editable) {?>
		<div class="alerttext">This test has already been taken, it is therefore read-only.</div>
	<?php } ?>

	<section>
		<div id="tabs">
			<ul>
				<li><a href="#tab-test">Test</a></li>
				<li><a href="#tab-question-edit">Questions</a></li>
				<li><a href="#tab-assign">Assign</a></li>
				<li><a href="#tab-materials">Materials</a></li>
			</ul>
			<div id="tab-test">
				<form method='POST' id='frmTest' name="frmTest" action='/tests/p_edit/<?php echo $test_id;?>'>
					<fieldset>
						<legend>Test Details</legend>
						<p class="form-row">
							<label for="test_name">Test Name:</label>
							<input type='text' name='test_name' id='test_name' value='<?php echo $test_name;?>' <?php echo $disable_control?> />
						</p>
						<p class="form-row">
							<label for='test_descr'>Description:</label>
							<input type='text' name='test_descr' id='test_descr' value='<?php echo $test_descr; ?>' <?php echo $disable_control?>/>
						</p>
						<p class="form-row">
							<label for='test_category'>Category:</label>
							<input type='text' name='test_category' value='<?php echo $test_category;?>' <?php echo $disable_control?>/>
						</p>
						<p class="form-row">
							<label for='test_year'>Test Year:</label>
							<input type='text' name='test_year' value='<?php echo $test_year;?>' <?php echo $disable_control?>/>
						</p>
						<p class="form-row">
							<label for='passing_grade'>Passing Grade:</label>
							<input type='text' name='passing_grade' value='<?php echo $passing_grade;?>' <?php echo $disable_control?>/>
						</p>
						<p class="form-row">
							<label for='minutes_to_complete'>Minutes to complete:</label>
							<input type='text' name='minutes_to_complete' value='<?php echo $minutes_to_complete;?>' <?php echo $disable_control?>/>(0 for no timer)
						</p>
					</fieldset>
					<input type='hidden' name='test_id' id='test_id' value='<?php echo $test_id;?>'/>
					<input type='submit' value='Save Test' <?php echo $disable_control?>>

				</form>
			</div>
			<div id="tab-question-edit">
				<div id="dialog-confirm" title="Delete the question?">
					<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Delete this question from the test?</p>
				</div>
				<div id="tab-questions">
					<ul> <!-- Moved the list up here -->
					<?php if ($editable) {?>
					
						<li id="new-question-tab"><a href="#tab-question-new">Add a new question</a></li>									
					<?php }?>
					<!--List the questions
					<ul > took out ul tag -->
						<?php foreach($question_list AS $current_question) { ?>
							<li id="q-<?php echo $current_question["question_id"]; ?>">
								<!-- question_order field starts from 0 so need to add 1 -->
								<span class="question-order"><?php echo $current_question["question_order"]+1?>.</span>
								<a href="#tab-question-<?php echo $current_question["question_id"]; ?>">
									<?php echo siteutils::Truncate($current_question['question_text'], 20,true);//truncate so the text fits in the tab?>
								</a>
								<span class="ui-icon ui-icon-close" role="presentation">Remove Tab</span>
							</li>
						<?php } ?>
					</ul>
					<?php if ($editable) {?>
						<div id="tab-question-new" >
							<fieldset>
								<legend>Add a new Question</legend>
								<p class="form-row">
									<label for="question_text">Question Text:</label>
									<input type='text' name='question_text' id='question_text' style="width:450px" />
								</p>
								<?php foreach($question_types AS $current_question_type) {
									$selected = $current_question_type['question_type_id'] == "1" ? "checked='checked'" : "";//select the first type by default
									?>
									<p class="form-row" style="width:400px">
										<label style="white-space:nowrap;text-align: left;" for="question_type_id_<?php echo $current_question_type['question_type_id']?>"><?php echo $current_question_type['question_type_descr']?></label>
										<input type="radio" style="float:right;width:200px" <?php echo $selected;?> name="question_type_id" id="question_type_id_<?php echo $current_question_type['question_type_id']?>" value="<?php echo $current_question_type['question_type_id']?>"/>
									</p>
								<?php } ?>
								<input type='hidden' name='test_id' id='test_id' value='<?php echo $test_id;?>'/>
								<input type='button' value='Add New Question' id='cmdAddQuestion'>
							</fieldset>
						</div>
					<?php } ?>
					<?php foreach($question_list AS $current_question) { ?>
						<div id='tab-question-<?php echo $current_question["question_id"] ?>'
							 question_id='<?php echo $current_question["question_id"]; ?>'
							 class='question' >

						</div>
					<?php } ?>
				</div>
			</div>
			<div id="tab-assign">
				<form method='POST' id='frmAssign' action='/tests/p_assign/<?php echo $test_id?>' >
					<table>
						<thead class="table-header">
						<td><input type="checkbox" id="chkCheckAll" /></td>
						<td>Name</td>
						<td>Due Date (<a id="cmdAddMonth" href="#" title="Plus 30 days">+30</a> | <a href="#" id="cmdEOY" title="End of year">EOY</a>)</td>
						<td>Assigned Date</td>
						</thead>
						<tbody>
							<?php
							if ($test_assign_status) {

								foreach($test_assign_status AS $current_test_assign_status) {
									$check_setup = "";
									$disable_controls = "";
									$status_id = $current_test_assign_status["test_assign_status_id"];
									$due_on_dt = $current_test_assign_status["due_on_dt"];
									if ($due_on_dt != "") {$due_on_dt = date("m/d/Y", $due_on_dt);}
									$assigned_on_dt = $current_test_assign_status["assigned_on_dt"];
									if ($assigned_on_dt != ""){$assigned_on_dt = date("m/d/Y", $assigned_on_dt);}
									if (isset($status_id)) {$check_setup = "checked='checked'";}
									if ($status_id > 1){$disable_controls = "disabled='true'";}//status > 1 means test is being taken or has been taken
									?>
									<tr>
										<td><input class="checkbox" <?php echo $check_setup." ".$disable_controls;?> type="checkbox" id="chk_<?php echo $current_test_assign_status['user_id']?>" name="chk_<?php echo $current_test_assign_status['user_id']?>" value="<?php echo $current_test_assign_status['user_id']?>"></td>
										<td>
											<label for="txt_due_<?php echo $current_test_assign_status['user_id']?>" >
												<?php echo $current_test_assign_status['first_name']?>&nbsp;<?php echo $current_test_assign_status['last_name']?>
											</label>
										</td>
										<td><input <?php echo $disable_controls ?> class="due_date" type="text" id="txt_due_<?php echo $current_test_assign_status['user_id']?>" name="txt_due_<?php echo $current_test_assign_status['user_id']?>" value="<?php echo $due_on_dt?>"/></td>
										<td><?php echo $assigned_on_dt;?></td>
									</tr>
							<?php }} else {echo ("<h3>No test takers exist to be assigned</h3>");} ?>
						</tbody>
					</table>
					<input type="submit" value="Update Assignments"/>
				</form>
			</div>
			<div id="tab-materials">
				<form method='POST' action='tests/p_addMaterial' name='form_material' id="frmMaterial">
					<fieldset>
						<legend>Upload Materials (2 maximum)</legend>
						<p class="form-row">
							<label for="material_1">First supplement:</label>
							<input type='file' name='material_1' id='material_1' placeholder = 'At least 5 characters' />
						</p>
						<p class="form-row">
							<label for='material_2'>Second supplement:</label>
							<input type='file' name='material_2' id='material_2' placeholder = 'At least 5 characters' />
						</p>
						
					</fieldset>
					<input type='submit' value='Add materials'>

				</form>
			</div>
		</div>
	</section>
</div>
<script>
$(document).ready(function() {
	console.log("Document ready");
	function linkTestQuestions() {
		/*
		$(".question").each( function (index, el) {
			var questionText = $(this)
			$(this).question
		}
		*/
        <?php
        //can't use a class selector for these because it always picks the top one and then $(this) does not work like it should
        //so we are forced to loop here in the PHP
        foreach($question_list AS $current_question) {
            $disabled_text = $editable ? "false" : "true";
            echo "$('#tab-question-".$current_question["question_id"]."').question({question_text: '".$current_question["question_text"]."', question_type_id:".$current_question["question_type_id"].",disabled:".$disabled_text."});";
        }
        ?>
    }
	
	window.setTimeout(linkTestQuestions, 1000);//wait a second to load the questions
});
</script>	



