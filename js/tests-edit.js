
    $(document).ready(function()   {
		$( "#tabs" ).tabs();
		var tabs = $( "#tab-questions" ).tabs();
		var tabTemplate = "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>";

		 tabs.find( ".ui-tabs-nav" ).sortable({

			 axis: "y",
			 stop: function() {
			 	tabs.tabs( "refresh" );
			 }
		 });
		 tabs.addClass( "ui-tabs-vertical ui-helper-clearfix" );
		 tabs.find("[role=tab]").removeClass( "ui-corner-top" ).addClass( "ui-corner-right" );

		 function addTab(tabTitle, question_id) {

			var label = tabTitle || "Question " + question_id,
			id = "tab-question-" + question_id,
			li = $( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ) );
			//tabContentHtml = tabContent.val() || "Tab " + tabCounter + " content.";


			tabs.append( "<div style='display: none' id='" + id + "' class = 'question' question_id='" + question_id + "'></div>" );
			tabs.tabs( "refresh" );
			tabs.find( ".ui-tabs-nav" ).append( li ).removeClass("ui-corner-top").addClass("ui-corner-right");
			tabCounter++;
    	};





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

        $('#cmdAddQuestion').click(function () {
            //Add the question at the server, get the new ID
            var question_text = $('#question_text').val();
            var question_type_id = parseInt($("input[name='question_type_id']:checked").val());
            var test_id = $('#test_id').val();
            var question_id = 0;

            if (typeof(question_type_id) == "undefined") {
                alert ("Please choose a question type")
                return;
            }

            if (question_text.length < 10) {
                alert("Please provide a question of 10 characters or more");
                return;
            }

            $.ajax({
                type: "POST",
                url: "/questions/p_create/" + test_id,
                dataType: "json",
                data: { question_text: question_text, question_type_id: question_type_id},
                async: false,
                success : function(data) {
                    question_id = Number(data);
                }
            });

            if (question_id != null) {
                //Add the question to the local page

                /*
                var newTabContent = $("#tab-questions").append(
					"<div style='display: none' id='tab-question-" + question_id + "' class='question ui-tabs-panel
					ui-widget-content ui-corner-bottom' question_id='" + question_id + "' aria-labelledby='ui-id-4' role='tabpanel'
					aria-expanded='false' aria-hidden='true' style='display: none;'></div>");

                var newTab = $( "#tab-questions .ui-tabs-nav").append("<li><a href='#tab-question-" + question_id + "'>" + question_text.trunc(15, true) + "</li>");
                $("#tab-questions").tabs("refresh");
                 */
                //light up the question
                addTab(question_text.trunc(15, true),question_id);
                $( "#tab-question-" + question_id).question({question_text: question_text, question_type_id: question_type_id, question_id: question_id});
            }

            $('#question_text').val("");//blank out the question
        });


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



    });

    //show a loading image to give the screen time to draw itself
    $(window).bind("load", function() {
        $("#loading").fadeOut("slow");
        $("#test_details").fadeIn("slow");
    });
