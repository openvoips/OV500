var is_save_card = 'no';
$('#'+payment_method+'-button').click(function() {	
$('#st-message').html('');
$("#"+payment_method+"_pay_form").parsley().reset();
var is_ok = $("#"+payment_method+"_pay_form").parsley().isValid();
if(is_ok === true)
{	
	/*if checked save card data*/
	
	if($("#id_save_card_details").is(':checked'))
	{
		//save_card_data();
		is_save_card = 'yes';
	}
	
	///////////save card details for tracking/record///////////
	var target_card = BASE_URL +'payment/save_payment_attempt';	 
	var data_card = {
		account_id: account_id,
		amount: $('#'+payment_method+'amount').val(),
		method: "secure_trading",					
		card_number: $('#id_card_number').val(),
		card_expirymonth: $('#id_expirymonth').val(),
		card_expiryyear: $('#id_expiryyear').val(),		
		card_securitycode: $('#id_securitycode').val(),		
		is_save_card: is_save_card,					
	};
	console.log(data_card);

	$.ajax({
		url: target_card,				
		type: 'POST',
		data: data_card,
		success: function(data, textStatus, XMLHttpRequest)
		{
			//alert(data.valid);				
			if(data.valid)
			{
				$('#'+payment_method+'-button').attr('disabled',true);
				$( "#mybtn" ).trigger("click");//this makes Secure Trading process
			
				/////
				setTimeout(function(){ $('#'+payment_method+'-button').attr('disabled',false); }, 6000);
			}
			else
			{
				var error_message = '<p class="st-error">'+data.message+'</p>';
				$('#st-message').html(error_message);
				//alert(data.message);
			}
		
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
		}
	});				
	/////////////////////
	
	

	
	
	
}
else
{
	$("#"+payment_method+"_pay_form").parsley().validate();
}

});

var target = BASE_URL +'payment/secure_trading_initiate_payment';
new SecureTrading.Standard({
	sitereference: sitereference,
	locale: "en_gb",
	messageId: "st-message",
	formId: payment_method+"_pay_form",
	submitFormCallback: function(responseObj){//alert("on success");
	var cachetoken = responseObj['response'][0]['cachetoken']; <!-- Grab token -->
	console.log(cachetoken); <!-- Logs the token to the console (Additional steps can be performed here before submitting to your server) -->				
	
	var data = {
		account_id: account_id,
		amount: $('#'+payment_method+'amount').val(),
		cachetoken: cachetoken,
		method: "secure-trading",		
		
		card_number: $('#id_card_number').val(),
		card_expirymonth: $('#id_expirymonth').val(),
		card_expiryyear: $('#id_expiryyear').val(),									
	};

	$.ajax({
		url: target,
		dataType: 'json',
		type: 'POST',
		data: data,
		success: function(data, textStatus, XMLHttpRequest)
		{
			if(data.valid)
			{
				document.getElementById(payment_method+"_pay_form").submit();
			}
			else
			{
				$('#'+payment_method+'-button').attr('disabled',false);
				$('#st-message').html('<p class="st-error">'+data.error+'</p>');			
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			$('#'+payment_method+'-button').attr('disabled',false);
			$('#st-message').html('<p class="st-error">Something bad happened.Please try again after some time.</p>');	
			//alert('error');
		}
	});		
}
});		