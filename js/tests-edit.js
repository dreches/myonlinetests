//show a loading image to give the screen time to draw itself
$(window).bind("load", function() {
	$("#loading").fadeOut("slow");
	$("#test_details").fadeIn("slow");
});

$(document).ready(function()   {

	/*********************************************
	Create horizontal tab widgets for editing the test
	Create vertical tabs for editing the questions
	*********************************************/
	$( "#tabs" ).tabs();
	var tabs = $( "#tab-questions" ).tabs();
	// Classes added to create vertical widgets
	tabs.addClass( "ui-tabs-vertical ui-helper-clearfix" );
	tabs.find("[role=tab]").removeClass( "ui-corner-top" ).addClass( "ui-corner-right" );

	/***************************************
	Code for creating sortable questions
	****************************************/
	tabs.find( ".ui-tabs-nav" ).sortable({
		 items: "li:not(#new-question-tab)",  // Adding a new question should always be on top
		 axis: "y",
		 stop: function( event, ui) {
			 // Determine where the question ended by looking at it's index in the context of all question
			 // tabs
			 var end_position = $("li[id^=q-]").index(ui.item);
			 // Subtract one because in the database the count starts from 0, not 1
			 // (or maybe only get the start position from the database?)
			 var start_position = parseInt(ui.item.find("span.question-order").text())-1;
			 var test_id = $('#test_id').val();
			 var question_id = ui.item.attr("id").substr(2);
			 var error;

			 console.log( "End="+end_position );
			 console.log( "Start="+start_position );
			 console.log("testid="+test_id);
			 console.log("questionid="+question_id);

			 if ( start_position != end_position ) {
				 $.ajax({
						type: "POST",
						url: "/questions/p_reorder/" + test_id,
						dataType: "json",
						data: { start_position : start_position, end_position: end_position, question_id: question_id},
						async: false,
						success : function(data) {
							error = data["ERROR"];
							updatedRows = data["ROWS"];
							if (error) {
								$.each(error, function(index,value){
									console.log("ERROR: "+value +"\n")
								});
								//TODO: processError(error);
							}
							if (updatedRows)
							{
								// Change the numeric label for each affected tab.
								// This value should reflect the current database order
								$.each(updatedRows, function(index,row) {
									qid = row["question_id"];
									qorder = Number(row["question_order"])+1; // add 1 to the database value
									console.log("qid: "+qid+", order: " + qorder);
									$("#q-"+qid).find("span.question-order").text(qorder+".");
								});
							}
							tabs.tabs("refresh");
						}
				}); //end ajax
			}
			else
				tabs.tabs( "refresh" );
		 } // stop()
 	});

	/****************************************************
	Code for adding a new question /tab
	*****************************************************/

	var tabTemplate = "<li id='#{qid}'><span class='question-order'>#{order}.</span>" +
							"<a href='#{href}'>#{label}</a>" +
					"<span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>";
	function addTab(tabTitle, question_id, question_order) {

		var label = tabTitle || "Question " + question_id,
		id = "tab-question-" + question_id,
		li = $( tabTemplate.replace( /#\{href\}/g, "#" + id )
			.replace( /#\{qid\}/g, "q-" + question_id)
			.replace( /#\{label\}/g, label )
				.replace(/#\{order\}/g, question_order ));

		tabs.find( ".ui-tabs-nav" ).append( li );
		tabs.append( "<div style='display: none' id='" + id + "' class = 'question' question_id='" + question_id + "'></div>" );


		tabs.tabs( "refresh" );
		newTab = tabs.find("[aria-controls="+id+"]").removeClass( "ui-corner-top" ).addClass( "ui-corner-right" );
		return newTab;
		//tabCounter++;
	};

	$('#cmdAddQuestion').click(function () {
			//Add the question at the server, get the new ID
			var question_text = $('#question_text').val();
			var question_type_id = parseInt($("input[name='question_type_id']:checked").val());
			var test_id = $('#test_id').val();
			var question_id = 0;
			var question_order = -1;

			if (typeof(question_type_id) == "undefined") {
				alert ("Please choose a question type")
				return;
			}

			if (question_text.length < 10) {
				alert("Please provide a question of 10 characters or more");
				return;
			}

			// TODO:? See If question text not unique

			$.ajax({
				type: "POST",
				url: "/questions/p_create/" + test_id,
				dataType: "json",
				data: { question_text: question_text, question_type_id: question_type_id},
				async: false,
				success : function(data) {
					question_id = Number(data[0]);
					question_order = Number(data[1]);
				}
			});  // end $.ajax

			if (question_id != null) {
				 $('#question_text').val("");//blank out the question so doesn't get readded by mistake
				//Add the question to the local page

				/*
				NO NEED TO ADD THE TAB WIDGET CLASSES WHEN ADDING THE TABS
				var newTabContent = $("#tab-questions").append(
					"<div style='display: none' id='tab-question-" + question_id + "' class='question ui-tabs-panel
					ui-widget-content ui-corner-bottom' question_id='" + question_id + "' aria-labelledby='ui-id-4' role='tabpanel'
					aria-expanded='false' aria-hidden='true' style='display: none;'></div>");

				var newTab = $( "#tab-questions .ui-tabs-nav").append("<li><a href='#tab-question-" + question_id + "'>" + question_text.trunc(15, true) + "</li>");
				$("#tab-questions").tabs("refresh");
				 */

				// Add 1 to question order because they start at 0
				var thisTab = addTab(question_text.trunc(20, true),question_id, question_order+1);
				$( "#tab-question-" + question_id).question({question_text: question_text, question_type_id: question_type_id, question_id: question_id});
				//display the newly added question
				tabs.tabs( "option","active", -1 );
			}
			else {
				// Give an error message indicating question was not added
				alert ("Unable to add question.");
			}

	});

	/************************************
	Code for deleting a tab (question)
	*************************************/


	// modal dialog init: custom buttons and a "close" callback resetting the form inside
	var confirmDialog = $( "#dialog-confirm" ).dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			Delete: function() {
				var tabToRemove = $("li.remove_tab");
				var question_id = tabToRemove.attr("id").substr(2);
				console.log( "Question id#: " + question_id );

				// Delete the question from the database
				deleteQuestion( question_id );
				console.log("Deleted question");
				var panelId = tabToRemove.remove().attr( "aria-controls" );
				$( "#" + panelId ).remove();
				tabs.tabs( "refresh" );
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$(".remove_tab").removeClass("remove_tab");
				console.log( this );
				$( this ).dialog( "close" );
			}
		}
	});

	function deleteQuestion (question_id) {
		// Send the question order to the server.
		var question_order = parseInt($("#q-"+question_id).find("span.question-order").text())-1;
		$.ajax({
				type: "POST",
				url: "/questions/p_delete_question/" + $('#test_id').val(),
				dataType: "json",
				data: {question_id: question_id, question_order: question_order},
				async: false,
				success : function(data) {
					error = data["ERROR"];
					updatedRows = data["ROWS"];
					if (error) {
						$.each(error, function(index,value){
							console.log("ERROR: "+value +"\n")
						});
						//TODO: processError(error);
					}
					if (updatedRows) {

						// Change the numeric label for each affected tab.
						// This value should reflect the current database order
						// The data is sent back as an array where question_id is the key and
						// question_order is the value
						$.each(updatedRows, function(qid,qorder) {

							// If there was an error we are getting the values in the database,
							// Otherwise, if there was no error, we are using the predelete question_order value,
							// which should reflect the correct number after deletion.
							if (error) qorder = Number(qorder)+1; // add 1 to the database value
							console.log("qid: "+qid+", order: " + qorder);
							$("#q-"+qid).find("span.question-order").text(qorder+".");
						}); //end $.each
					} // end if (updatedRows)

					//tabs.tabs("refresh");
				} //end success
			}); //end ajax
	}

	// close icon: removing the tab on click
	tabs.delegate( "span.ui-icon-close", "click", function() {
		// Find the tab that was clicked on and select it
		var tabToRemove = $( this ).closest( "li" );
		tabToRemove.addClass("remove_tab");
		tabs.tabs("option","active",tabToRemove.index());
		// Show a dialog box asking if this should be removed
		confirmDialog.dialog( "open" );


	});

	tabs.bind( "keyup", function( event ) {
		if ( event.altKey && event.keyCode === $.ui.keyCode.BACKSPACE ) {
			var tabToRemove = tabs.find( ".ui-tabs-active" );
			tabToRemove.addClass("remove_tab");
			confirmDialog.dialog( "open" );
		}
	});



	/*******************************************
	Functions for Assigning tests
	********************************************/

	Date.prototype.addDays = function(days) {
		this.setDate(this.getDate() + days);
		return this;
	};

	Date.prototype.formatMMDDYYY = function() {
		var return_date = "";
		var dd = this.getDate();
		var mm = this.getMonth()+1; //January is 0!

		var yyyy = this.getFullYear();
		if(dd<10){dd='0'+dd} if(mm<10){mm='0'+mm} return_date = mm+'/'+dd+'/'+yyyy;
		return return_date;
	};

	//thanks to KooiInc on stackoverflow for this next block
	String.prototype.trunc =
		function(n,useWordBoundary){
			var toLong = this.length>n,
				s_ = toLong ? this.substr(0,n-1) : this;
			s_ = useWordBoundary && toLong ? s_.substr(0,s_.lastIndexOf(' ')) : s_;
			return  toLong ? s_ + '&hellip;' : s_;
		};


	$("#chkCheckAll").change(function () {
		$(".checkbox").prop('checked', this.checked);
	});

	$("#cmdAddMonth").click(function() {
		var due_date_text = $(".due_date");
		var due_date = new Date(due_date_text.val());
		if (!(due_date instanceof Date && !isNaN(due_date.valueOf()))) {due_date = new Date();}

		due_date = due_date.addDays(30);
		due_date_text.val(due_date.formatMMDDYYY())
	});

	$("#cmdEOY").click(function() {
		var the_date = new Date().getFullYear();
		the_date = "12/31/" + the_date;
		var due_date_text = $(".due_date").val(the_date);
	});


	/**************************************************
	Validation for Test Details form
	***************************************************/

	$("#frmTest").validate({
		rules: {
			test_name: {required: true, minlength: 5},
			test_descr: {required: true, minlength: 5},
			test_category: {required: true, minlength: 5},
			test_year:{required: true,number: true},
			minutes_to_complete:{required: true,number: true,range: [0,1000]},
			passing_grade:{required: true,number: true,range: [0,100]}

		},
		messages: {
			test_name: "Please enter a name of at least 5 characters",
			test_descr: "Please enter a description of at least 5 characters",
			test_category: "Please enter a category of at least 5 characters",
			test_year: "Enter a 4 digit year",
			minutes_to_complete: "Should be 0 for not timed or the number of minutes the test taker has to complete",
			passing_grade: "Enter a number between 1 and 100"
		}
	});

}); // $document.ready

