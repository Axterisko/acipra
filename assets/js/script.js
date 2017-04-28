// JavaScript Document
var uuid = function () {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(
        /[xy]/g,
        function (match) {
            /*
            * Create a random nibble. The two clever bits of this code:
            *
            * - Bitwise operations will truncate floating point numbers
            * - For a bitwise OR of any x, x | 0 = x
            *
            * So:
            *
            * Math.random * 16
            *
            * creates a random floating point number
            * between 0 (inclusive) and 16 (exclusive) and
            *
            * | 0
            *
            * truncates the floating point number into an integer.
            */
            var randomNibble = Math.random() * 16 | 0;

            /*
            * Resolves the variant field. If the variant field (delineated
            * as y in the initial string) is matched, the nibble must
            * match the mask (where x is a do-not-care bit):
            *
            * 10xx
            *
            * This is achieved by performing the following operations in
            * sequence (where x is an intermediate result):
            *
            * - x & 0x3, which is equivalent to x % 3
            * - x | 0x8, which is equivalent to x + 8
            *
            * This results in a nibble between 8 inclusive and 11 exclusive,
            * (or 1000 and 1011 in binary), all of which satisfy the variant
            * field mask above.
            */
            var nibble = (match == 'y') ?
                (randomNibble & 0x3 | 0x8) :
                randomNibble;

            /*
            * Ensure the nibble integer is encoded as base 16 (hexadecimal).
            */
            return nibble.toString(16);
        }
    );
};

function consoleLog(log){
	try{console.log(log);}catch(e){}
}
function show_loading(text){
	if(text) $(".overlay-loading span").html(text);
	$(".overlay-loading").show();
}
function hide_loading(){
	$(".overlay-loading span").html('Loading...');
	$(".overlay-loading").hide();
}

function set_error_field(selector,errorMessage){
	
	var $formGroup = $(selector).parent()
	
	$formGroup.addClass('has-error');
	if(!$formGroup.find('.help-block.with-errors > ul').length)
		$formGroup.append("<div class=\"help-block with-errors\"><ul class=\"list-unstyled\"><li>"+errorMessage+"</li></ul></div>");
	else
		$formGroup.find('.help-block.with-errors  > ul').append("<li>"+errorMessage+"</li>");
}

function set_error(message){
	var id = 'alert-'+ uuid();
	var $alert = '<div id="'+id+'" class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'+message+'</div>'
	$("form").append($alert);
	
	setTimeout(function(){$("#"+id).remove()},5000);
}

function set_success(message){
	var $alert = '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'+message+'</div>'
	$("form").append($alert);
}

function reset_form(){
	$("form [type='reset']").trigger("click");
}

jQuery(function($){
	
		$(document).on("click","[data-toggle='add-targa']",function(e){
			e.preventDefault();
			
			var $lastRow = $(this).parents("form").find(".row").last();
			var $newRow = $lastRow.clone();
			
			$newRow.find("[name='targa[]']").val("");
			$newRow.find("[name='tipo-veicolo[]']").val("1");
			$newRow.find(".has-error").removeClass('has-error');
			$newRow.find(".help-block.with-errors").remove();

			$lastRow.after($newRow);
			
			$(this).parents("form").find(".row").last().find("[name='targa[]']").focus();
				
		});


		$(document).on("click","[data-toggle='delete-targa']",function(e){
			e.preventDefault();
			
			var $row = $(this).parents(".row");
			$row.remove();
				
		});
		
		$(document).on("click","form [type='reset']",function(e){
			$("[data-toggle='delete-targa']:visible").trigger("click");
			$(".has-error").removeClass('has-error');
			$(".help-block.with-errors").remove();
			$("form .alert.alert-danger").remove();
		});
		$(document).on("submit","form",function(e){
			show_loading('Recupero visure in corso...');
			$(".has-error").removeClass('has-error');
			$(".help-block.with-errors").remove();
			$("form .alert").remove();
		});
});