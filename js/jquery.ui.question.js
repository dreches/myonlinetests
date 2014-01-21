(function($) {
    $.widget("ui.question",{
            options: {
                question_id: null,
                display_mode: "edit",
                question_text: null,
                question_type_id: null,
                test_instance_id: null

            },
            _create: function() {
                var self = this,
                    o = self.options,
                    el = self.element,
                    localId = el[0].id;

                if (this.options.question_id == null) {
                    this.options.question_id = this.element.attr('question_id');
                }

                //Get the test from the server and draw the display
                var json_data = null;
                $.ajax({
                    type: "POST",
                    url: "/questions/get/" + this.options.question_id,
                    dataType: "json",
                    async: false,
                    data: {test_instance_id: this.options.test_instance_id},
                    success : function(data) {
                        json_data = data;
                    }
                });
                if (json_data != null) {
                    this.displayQuestion(json_data);
                }


            },
            displayQuestion: function(data){//displays on a div
                var question_type_id = parseInt(data['question_type_id'])
                    , question_id = this.options.question_id
                    , answers = data.answers
                    , display_text_id = "txt_" + question_id + "_question_text";

                //All questions have display text
                this.disabled_option = this.options.disabled ?  "disabled='true'" : "";
                var display_text = "<div id='" + display_text_id + "'>" + this.options.question_text + "</div>";
                if (this.options.display_mode == "edit"){
                    display_text = "<div class='form-row' style='float: left;'><label style='float:left;clear:both;text-align: left;' for='" + display_text_id + "'>Question Text</label><br/><textarea rows='4' " + this.disabled_option + " cols='50' id='" + display_text_id + "' name='" + display_text_id + "'>" + this.options.question_text + "</textarea></div>";

                    //On display text lost focus or when the enter key is pressed - update the question text
                    this.element.append(display_text);
                    if (!this.options.disabled) {
                        $("#" + display_text_id).bind("blur keyup", (function(e) {
                            if(e.type === 'keyup' && e.keyCode !== 10 && e.keyCode !== 13) return;
                            $('#' + this.id).closest(".question").question("changeQuestionText", $(this).val());
                        }));
                    }
                    this.element.append("<div>Answers:</div>");
                } else {
                    if (this.options.display_mode != "review"){this.element.append(display_text);}
                }
                switch (question_type_id) {
                    case 1 : //1 - choose all correct
                    case 2: {//2 - choose single correct

                        if (this.options.display_mode == "edit"){
                            if (!this.options.disabled) {
                                //Write out a textbox so the user can enter the next answer
                                var new_answer_id = "txt_new_answer_" + this.options.question_id;
                                this.element.append("<div style='float:left;clear:both'><input type='text' id='" + new_answer_id +  "' value=''/></div>");

                                //Bind the keyup events to add a new answer
                                $("#" + new_answer_id).bind("keyup", (function(e) {
                                    if(e.type === 'keyup' && e.keyCode !== 10 && e.keyCode !== 13) return;
                                    $('#' + this.id).closest(".question").question("addAnswer", $(this).val());
                                }));
                                $("#" + new_answer_id).watermark('Enter a new answer', {
                                    className: 'lightText'
                                });
                            }

                            //display all the answers in edit mode
                            for(i=0;i<answers.length;i++) {
                                this._addAnswerDisplay(question_id, question_type_id, answers[i].answer_id, answers[i].answer_text, answers[i].correct);
                            }

                        } else {//The display type is 'take'
                            //display all the answers - same for edit and take
                            for(i=0;i<answers.length;i++) {
                                this._addAnswerDisplay(question_id, question_type_id, answers[i].answer_id, answers[i].answer_text, answers[i].correct);
                            }
                        }
                        break;
                    }//2 - single correct
                    case 3: {//3 - T/F
                        for(i=0;i<answers.length;i++) {
                            var answer_text = answers[i].answer_text;
                            var answer_id = answers[i].answer_id;
                            this._addAnswerDisplay(question_id, question_type_id, answer_id, answer_text, answers[i].correct);
                        }
                        break;
                    }
                    case 4: {//4 - essay
                        for(i=0;i<answers.length;i++) {
                            var answer_text = answers[i].answer_text;
                            var answer_id = answers[i].answer_id;
                            this._addAnswerDisplay(question_id, question_type_id, answer_id, answer_text, answers[i].correct);//correct here means that the queestion is filled out
                        }
                    }
                }
            },
            //Append the proper elements to the DOM to enable the editing of answers
            _addAnswerDisplay: function(question_id, question_type_id, answer_id, answer_text, answer_correct) {
                //console.log("answer_id: " + answer_id + ", question_type_id:" + question_type_id);
                var select_control_prefix = "select_";
                var answer_select= answer_correct == "1" ? "checked='checked'" : "";
                var select_control_id = select_control_prefix + question_id + "_" + answer_id;
                var textbox_control_id = "txt_" + question_id + "_" + answer_id;
                var delete_control_id = "del_" + question_id + "_" + answer_id;
                var rdo_control_name = "question_answer_" + question_id;
                var answer_span_id = "answer_span_" + answer_id;

                switch (question_type_id) {
                    case 1://A check box list, a textbox, and a delete control for edit and a DIV for take
                        switch (this.options.display_mode) {
                            case "edit":
                                var delete_control = this.options.disabled ? "" : " - <a href='#' class='alerttext button' id='" + delete_control_id + "' answer_id='" + answer_id + "' " + this.disabled_option + ">delete</a><br/>";
                                this.element.append("<span style='float:left' class='form-row' class='form-row' id='" + answer_span_id + "'>"
                                 + "<input style='float:left' type='checkbox' id='" + select_control_id + "' name='" + select_control_id + "' " + answer_select + " answer_id='" + answer_id + "' textbox_control_id='" + textbox_control_id + "' " + this.disabled_option + "/> "
                                 + "<input type='text' id='" + textbox_control_id + "' value='" + answer_text + "' " + this.disabled_option + "/>"
                                 + delete_control
                                 + "</span>");
                                break;
                            case "take":
                                this.element.append("<span class='form-row' id='" + answer_span_id + "'>"
                                + "<input style='float:left' type='checkbox' id='" + select_control_id + "' name='" + select_control_id + "' "
                                + answer_select + "answer_id='" + answer_id + "' textbox_control_id='" + textbox_control_id + "'/> "
                                + "<label style='float:left' for='" + select_control_id + "'>" + answer_text + "</label><br/>"
                                + "</span>");
                                break;
                            case "review":
                                if (answer_correct == 1){
                                    this.element.append("<div>A: " + answer_text + "</div>")
                                }
                            break;
                        }
                        break;
                    case 2://a radio button list
                        switch (this.options.display_mode){
                            case "edit":
                                var delete_control = this.options.disabled ? "" : " - <a href='#' class='alerttext button' id='" + delete_control_id + "' answer_id='" + answer_id + "' " + this.disabled_option + ">delete</a><br/>";
                                this.element.append("<span class='form-row' style='float:left' id='" + answer_span_id + "'>"
                                + "<input style='float:left'  type='radio' id='" + select_control_id + "' " + answer_select + " answer_id='" + answer_id + "' textbox_control_id='" + textbox_control_id + "' name='" + rdo_control_name + "' value='" + answer_id + "' " + this.disabled_option + "/> "
                                + "<input type='text' id='" + textbox_control_id + "' value='" + answer_text + "' " + this.disabled_option + "/>"
                                + delete_control
                                + "</span>");
                                break;
                            case "take":
                                //TODO: find out if the answer is selected by the user
                                this.element.append("<span class='form-row' id='" + answer_span_id + "'>"
                                + "<input style='float:left'  type='radio' id='" + select_control_id + "' " + answer_select + " answer_id='" + answer_id + "' textbox_control_id='" + textbox_control_id + "' name='" + rdo_control_name + "' value='" + answer_id + "'/> "
                                + "<label for='" + select_control_id + "'>" + answer_text + "</label><br/>"
                                + "</span>");
                                break;
                            case "review":
                                if (answer_correct == 1){
                                    this.element.append("<div>A: " + answer_text + "</div>")
                                }
                                break;
                        }
                        break;
                    case 3://just radio buttons for true/false - same for edit and take
                        switch (this.options.display_mode){
                            case "edit":
                                this.element.append("<span style='float:left; width: 200px;clear:both' id='" + answer_span_id + "'>"
                                    + "<label style='float:left; text-align: left;width:50px' for='" + select_control_id + "'>" + answer_text + "</label>"
                                    + "<input style='float:left' type='radio' id='" + select_control_id + "' name='" + rdo_control_name + "' " + answer_select + " answer_id='" + answer_id + "' value='" + answer_id + "' " + this.disabled_option + "/>"
                                    + "</span>");
                                break;
                            case "take":
                                this.element.append("<span style='float:left; width: 200px;clear:both' id='" + answer_span_id + "'>"
                                    + "<label style='float:left; text-align: left;width:50px' for='" + select_control_id + "'>" + answer_text + "</label>"
                                    + "<input style='float:left' type='radio' id='" + select_control_id + "' name='" + rdo_control_name + "' " + answer_select + " answer_id='" + answer_id + "' value='" + answer_id + "' />"
                                    + "</span>");
                                break;
                            case "review":
                                if (answer_correct == 1){
                                    this.element.append("<div>A: " + answer_text + "</div>")
                                }
                                break;
                        }

                        break;
                    case 4://Show a text area with prompting text as a watermark
                        var display_text_area = "<br/><span class='form-row' style='float:left;clear:both' id='" + answer_span_id + "'>";
                        switch (this.options.display_mode) {
                            case "edit":
                                display_text_area+= "<label style='float:right;clear:both;width:100%; text-align: left' for='" + textbox_control_id + "'>The test taker will see the following text as a prompt</label>"
                                + "<textarea style='float:left;clear:both' rows='4' cols='50' id='" + textbox_control_id + "' name='" + textbox_control_id + "' " + this.disabled_option + "></textarea>"
                                    + "</span>";

                                this.element.append(display_text_area);
                                $("#" + textbox_control_id).val(answer_text);
                                $("#" + textbox_control_id).bind("blur keyup", (function(e) {
                                    if(e.type === 'keyup' && e.keyCode !== 10 && e.keyCode !== 13) return;
                                    $('#' + this.id).closest(".question").question("changeAnswerText", $(this).val());
                                }));

                                break;
                            case "take":
                                display_text_area+= "<textarea rows='4' cols='50' id='" + textbox_control_id + "' name='" + textbox_control_id + "'></textarea>"
                                    + "</span>";

                                this.element.append(display_text_area);
                                if (answer_correct==0) {//show the prompting watermark
                                    $("#" + textbox_control_id).watermark(answer_text, {
                                        className: 'lightText'
                                    });
                                } else {
                                    $("#" + textbox_control_id).val(answer_text);
                                }
                                break;
                            case "review":
                                if (answer_correct == 1){
                                    this.element.append("<div>A: " + answer_text + "</div>")
                                }
                                break;
                        }//display_mode

                        break;
                }//question_type_id

                $("#" + select_control_id).change(function(){
                    //When this is triggered, we have no answer context - we have to rely on our on attributes to survive
                    $('#' + this.id).closest(".question").question("setAnswer", $(this).attr('answer_id'), this.checked);
                });

                if (question_type_id == "1" || question_type_id == "2") {
                    //Allow deletes for multiple answer questions
                    $("#" + delete_control_id).click(function() {
                        $('#' + this.id).closest(".question").question("deleteAnswer",$(this).attr('answer_id'));
                    });

                    //TODO: allow for text change updates here
                    //reset the new answer text box
                    var new_answer_id = "txt_new_answer_" + this.options.question_id;
                    $("#" + new_answer_id).val("");
                    $("#" + new_answer_id).watermark('Enter a new answer', {
                        className: 'lightText'
                    });
                }
            },
            setAnswer: function(answer_id, is_correct) {
                //alert("question_id: " + this.options.question_id + ", answer_id: " + answer_id + ", is_correct: " + is_correct);
                $.ajax({
                    type: "POST",
                    url: "/questions/p_setanswer/" + this.options.question_id,
                    data: { answer_id: answer_id, correct: is_correct},
                    async: false
                });
            },
            changeQuestionText: function(question_text){
                //alert("question_id: " + this.options.question_id + ", question_text: " + question_text);
                question_text = question_text.replace(/[\n\r]/g, ' ');
                $.ajax({
                    type: "POST",
                    url: "/questions/p_set_question_text/" + this.options.question_id,
                    data: { question_text: question_text},
                    async: true
                });
            },
            changeAnswerText: function(answer_text){
                //alert("question_id: " + this.options.question_id + ", answer_text: " + answer_text);
                answer_text = answer_text.replace(/[\n\r]/g, ' ');
                $.ajax({
                    type: "POST",
                    url: "/questions/p_set_answer_text/" + this.options.question_id,
                    data: { answer_text: answer_text},
                    async: true
                });
            },
            addAnswer: function(answer_text){
                //alert("question_id: " + this.options.question_id + ", answer_text: " + answer_text);
                var answer_id = 0;
                $.ajax({
                    type: "POST",
                    url: "/questions/p_addanswer/" + this.options.question_id,
                    data: { answer_text: answer_text},
                    async: false,
                    dataType: "json",
                    success: function(data) {
                        answer_id = data;
                    }
                });
                //Find the last answer and put this one after it
                //this.element.append("<div>" + answer_text + " - " + answer_id + "</div>")
                this._addAnswerDisplay(this.options.question_id, this.options.question_type_id, answer_id, answer_text, false);
            },
            deleteAnswer: function(answer_id){
                //alert("answer_id: " + answer_id);
                $.ajax({
                    type: "POST",
                    url: "/questions/p_deleteanswer/" + this.options.question_id,
                    data: { answer_id: answer_id},
                    async: false,
                    dataType: "json",
                    success: function(data) {
                        //remove the answer from the DOM
                        var answer_span_id = "answer_span_" + answer_id;
                        $("#" + answer_span_id).remove();
                    }
                });
            },
            serialize: function() {//get a jSon representation of the object to post back to the server
                $elementType = this.isDiv() ? "DIV" : "CANVAS";
                $minutesAllowed =  this.options.minutesAllowed;
                return '{'
                    +'"elementId" : "' + this.element[0].id + '",'
                    +'"display_mode" : "' + this.options.display_mode + '",'
                    +'"question_id" : "' + this.options.question_id + '",'
                    +'}';

            },
            destroy: function() {//take the timer out of the DOM
                this.element.remove();
            },
            _setOption: function(option, value) {//set a single option
                $.Widget.prototype._setOption.apply(this, arguments);

                var el = this.element;
                switch (option) {
                    case "question_text":
                        var newVal = value.replace(/[\n\r]/g, ' ');
                        break;
                    case "backgroundColor":
                        el.css("backgroundColor", value);
                        break;
                }
            },
            _setOptions: function( options ) {//set an array of options
                var that = this,
                    resize = false;

                $.each( options, function( key, value ) {
                    that._setOption( key, value );
                    if ( key === "height" || key === "width" ) {
                        resize = true;
                    }
                });

                if ( resize ) {
                    this.resize();
                }
            }
        }

    );
})(jQuery);