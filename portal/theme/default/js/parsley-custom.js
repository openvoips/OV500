// JavaScript Document	
if (typeof window.Parsley !== 'undefined') {
	
	
window.Parsley
	.addValidator('multipleemail', {
		validateString: function (value) {
			r = true;
			if (!ValidateEmail(value))
			{
				r = false;
			}
			return r;
		},
		messages: {
			en: 'Enter valid email Id'
		}
});	
	
/*password validation*/	
window.Parsley
  .addValidator('password', { 
    validateString: function(value) {     
		r = true;
		if(!vCheckPassword(value))
		{
		r = false;
		}
		return r;  
    },
    messages: {
      en: 'min 8 char, 1 special char, 1 uppercase, 1 lowercase, 1 number'
    }
});
  
 window.Parsley
  .addValidator('price', {
    validateString: function(value) {
	  return true == (/^\d+(?:[.]\d+)*$/.test(value));
    },
    messages: {
      en: 'This value should be in decimal format'
    }
});
  
  
window.Parsley
  .addValidator('decimal', {
    validateString: function(value) {
	  return true == (/^\d+(?:[.]\d+)*$/.test(value));
    },
    messages: {
      en: 'This value should be in decimal format'
    }
}); 
  
window.Parsley
		.addValidator('alphanumspace', {
			validateString: function (value) {
				return true == (/^[a-zA-Z\d ]+$/.test(value));
			},
			messages: {
				en: 'This value should be in alphanumeric and Space'
			}
		})
  
window.Parsley
  .addValidator('alphanumricspace', {
    validateString: function(value) {
	  return true == (/^[\w]+([-_\s]{1}[a-zA-Z0-9]+)*$/.test(value));
    },
    messages: {
      en: 'Character, Number & Space Only'
    }
  });  
  
window.Parsley
	  .addValidator('ip', { 
		validateString: function(value) {     
			var pattern = /^[0-9:.]+$/;		
			if (!pattern.test(value)) 
				return false;
			else
				return true;  
		},
		messages: {
		  en: 'Invalid IP'
		}
	}); 

window.ParsleyValidator
        .addValidator('fileextension', function (value, requirement) {
            var fileExtension = value.split('.').pop();
            
            return fileExtension === requirement;
        }, 32)
        .addMessage('en', 'fileextension', 'The extension does not match the required');
 	
	
	$( document ).ready(function() {
		var id_form='';						 
		if($('#add_form').length){
			id_form = 'add_form';
		}
		
		if($('#edit_form').length){
			id_form = 'edit_form';
		}						 	
   		if(id_form !='')
		{
			$('#btnSave, #btnSaveClose').click(function() {
			
				var is_ok = $("#"+id_form).parsley().isValid();
				if(is_ok === true)
				{
					var clicked_button_id = this.id;
					if(clicked_button_id=='btnSaveClose')
						$('#button_action').val('save_close');
					else
						$('#button_action').val('save');	
					
					$("#"+id_form).submit();
				}
				else
				{
					$("#"+id_form).parsley().validate();
				}
			
			});
		}
	
	});
	
	
	
	
 
 	$( document ).ready(function() {
		var id_form2='';						 
		if($('#add_form2').length){
			id_form2 = 'add_form2';
		}
		
		if($('#edit_form2').length){
			id_form2 = 'edit_form2';
		}						 	
   		if(id_form2 !='')
		{
			$('#btnSave2, #btnSaveClose2').click(function() {
			
				var is_ok = $("#"+id_form2).parsley().isValid();
				if(is_ok === true)
				{
					var clicked_button_id = this.id;
					if(clicked_button_id=='btnSaveClose2')
						$('#button_action2').val('save_close');
					else
						$('#button_action2').val('save');	
					
					$("#"+id_form2).submit();
				}
				else
				{
					$("#"+id_form2).parsley().validate();
				}
			
			});
		}
	
	});
	
	$( document ).ready(function() {
		var id_form3='';						 
		if($('#add_form3').length){
			id_form3 = 'add_form3';
		}
		
		if($('#edit_form3').length){
			id_form3 = 'edit_form3';
		}						 	
   		if(id_form3 !='')
		{
			$('#btnSave3, #btnSaveClose3').click(function() {
			
				var is_ok = $("#"+id_form3).parsley().isValid();
				if(is_ok === true)
				{
					var clicked_button_id = this.id;
					if(clicked_button_id=='btnSaveClose3')
						$('#button_action3').val('save_close');
					else
						$('#button_action3').val('save');	
					
					$("#"+id_form3).submit();
				}
				else
				{
					$("#"+id_form3).parsley().validate();
				}
			
			});
		}
	
	});
 
 
  
  
  
}//if (typeof window.Parsley !== 'undefined')


