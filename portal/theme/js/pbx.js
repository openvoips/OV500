
function doConfirmDeletePBX(delete_val, delete_type, parameter_two)
{
	if(parameter_two === undefined) {
      parameter_two = false;
   }	

	$('#main-id').addClass('overlay');
	$('#overlay-img-id').removeClass('hide');
	
	var target = BASE_URL+"/pbx/delete_check"; 
	
	////
	/*
	var modal_body =  '<p class="text-center">Wait1</p>';	
	var modal_footer = '';
	openModal('lg','<h4 class="text-center">Wait3</h4>',modal_body, modal_footer);
	$("#my-modal").modal('show');
	*/
	////
	
	$.ajax({
	  method: "POST",
	  url: target,
	  data: { delete_type: delete_type, parameter_one: delete_val, parameter_two:parameter_two }
	})
	.done(function( msg ) {	
		if(msg=='ok')
		{//clear, ask for second confirm
			
			
			var modal_body = '<h1 class="text-center"><i class="fa fa-exclamation-circle"></i></h1>'+
			  '<h4 class="text-center">Are you sure!</h4>'+
			  '<p class="text-center">You won\'t be able to revert this!</p>';
	
			var modal_footer = '<button type="button" class="btn btn-primary" id="modal-btn-no-single" data-dismiss="modal">Cancel</button>'+
					  '<button type="button" class="btn btn-danger" id="modal-btn-yes-single">Yes. delete it!</button>';
					  
			//alert("modal");
			openModal('','',modal_body, modal_footer);
			$("#my-modal").modal('show');
			
			$("#modal-btn-no-single").on("click", function(){
				$('#main-id').removeClass('overlay');
				$('#overlay-img-id').addClass('hide');
			});
			$("#modal-btn-yes-single").on("click", function(){
				//alert("single");		
				
				var form = document.createElement("form");
				document.body.appendChild(form);
				form.method = "POST";
				
									
				form.action = SITE_URL  + 'pbx/delete';
					
					
				var element2 = document.createElement("INPUT");         
				element2.name="action";
				element2.value = 'OkDeleteData';
				element2.type = 'hidden';
				form.appendChild(element2);				
				
				var element4 = document.createElement("INPUT");         
				element4.name="delete_type";
				element4.value = delete_type;
				element4.type = 'hidden';
				form.appendChild(element4);	
				
				var element3 = document.createElement("INPUT");         
				element3.name="parameter_one";
				element3.value = delete_val;
				element3.type = 'hidden';
				form.appendChild(element3);				
				
								
				
				var element5 = document.createElement("INPUT");         
				element5.name="parameter_two";
				element5.value = parameter_two;
				element5.type = 'hidden';
				form.appendChild(element5);	
				
				
				
				form.submit();
				
				//alert("yes");
				$("#my-modal").modal('hide');
				
				$('#main-id').removeClass('overlay');
				$('#overlay-img-id').addClass('hide');
					
			
			});//click
			
		}
		else
		{//cannot delete
		
			var modal_body = 
			  '<p class="text-center">'+msg+'</p>';
	
			var modal_footer = '<button type="button" class="btn btn-primary" data-dismiss="modal" id="modal-btn-cancel">Cancel</button>';
					  
			
			openModal('lg','<h4 class="text-center">Delete Failed</h4>',modal_body, modal_footer);
			$("#my-modal").modal('show');
			
			//,close: function() {   $('#main-id').removeClass('overlay');$('#overlay-img-id').addClass('hide');}
			
			$("#modal-btn-cancel").on("click", function(){
				$('#main-id').removeClass('overlay');
				$('#overlay-img-id').addClass('hide');
			});
			
			$('#my-modal').on('hidden.bs.modal', function () {
				$('#main-id').removeClass('overlay');
				$('#overlay-img-id').addClass('hide');
			  // do something�
			})
		
		
		
		} 
	 
	 });
	
	
	
//alert("SSS");

}
