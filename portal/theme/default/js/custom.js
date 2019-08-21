function init_sidebar() {
    var a = function() {
        $RIGHT_COL.css("min-height", $(window).height());
        var a = $BODY.outerHeight(),
            b = $BODY.hasClass("footer_fixed") ? -10 : $FOOTER.height(),
            c = $LEFT_COL.eq(1).height() + $SIDEBAR_FOOTER.height(),
            d = a < c ? c : a;
        d -= $NAV_MENU.height() + b, $RIGHT_COL.css("min-height", d)
    };
    $SIDEBAR_MENU.find("a").on("click", function(b) {
        var c = $(this).parent();
        c.is(".active") ? (c.removeClass("active active-sm"), $("ul:first", c).slideUp(function() {
            a()
        })) : (c.parent().is(".child_menu") ? $BODY.is(".nav-sm") && ($SIDEBAR_MENU.find("li").removeClass("active active-sm"), $SIDEBAR_MENU.find("li ul").slideUp()) : ($SIDEBAR_MENU.find("li").removeClass("active active-sm"), $SIDEBAR_MENU.find("li ul").slideUp()), c.addClass("active"), $("ul:first", c).slideDown(function() {
            a()
        }))
    }), $MENU_TOGGLE.on("click", function() {
        $BODY.hasClass("nav-md") ? ($SIDEBAR_MENU.find("li.active ul").hide(), $SIDEBAR_MENU.find("li.active").addClass("active-sm").removeClass("active"), $('#sidebar_logo').hide() ) : ($SIDEBAR_MENU.find("li.active-sm ul").show(), $SIDEBAR_MENU.find("li.active-sm").addClass("active").removeClass("active-sm"),  $('#sidebar_logo').show()), $BODY.toggleClass("nav-md nav-sm"), a()
    }), $SIDEBAR_MENU.find('a[href="' + CURRENT_URL + '"]').parent("li").addClass("current-page"), $SIDEBAR_MENU.find("a").filter(function() {
        return this.href == CURRENT_URL
    }).parent("li").addClass("current-page").parents("ul").slideDown(function() {
        a()
    }).
	parent().addClass("active"), a(), $.fn.mCustomScrollbar && $(".menu_fixed").mCustomScrollbar({
        autoHideScrollbar: !0,
        scrollInertia: 0,
        theme: "minimal",
        mouseWheel: {
            preventDefault: !0
        }
    })
	
	
	/////custom
	$("ul.child_menu").find(".current-page" ).parent().show(function() {
			$("ul.child_menu").find(".current-page" ).parent().parent("li").addClass("active");			
            a()
	})
	
}

function countChecked() {
    "all" === checkState && $(".bulk_action input[name='table_records']").iCheck("check"), "none" === checkState && $(".bulk_action input[name='table_records']").iCheck("uncheck");
    var a = $(".bulk_action input[name='table_records']:checked").length;
    a ? ($(".column-title").hide(), $(".bulk-actions").show(), $(".action-cnt").html(a + " Records Selected")) : ($(".column-title").show(), $(".bulk-actions").hide())
}

function gd(a, b, c) {
    return new Date(a, b - 1, c).getTime()
}

function init_autosize() {
    "undefined" != typeof $.fn.autosize && autosize($(".resizable_textarea"))
}



var CURRENT_URL = window.location.href.split("#")[0].split("?")[0],
    $BODY = $("body"),
    $MENU_TOGGLE = $("#menu_toggle"),
    $SIDEBAR_MENU = $("#sidebar-menu"),
    $SIDEBAR_FOOTER = $(".sidebar-footer"),
    $LEFT_COL = $(".left_col"),
    $RIGHT_COL = $(".right_col"),
    $NAV_MENU = $(".nav_menu"),
    $FOOTER = $("footer"),
    randNum = function() {
        return Math.floor(21 * Math.random()) + 20
    };
$(document).ready(function() {
    $(".collapse-link").on("click", function() {
        var a = $(this).closest(".x_panel"),
            b = $(this).find("i"),
            c = a.find(".x_content");
        a.attr("style") ? c.slideToggle(200, function() {
            a.removeAttr("style")
        }) : (c.slideToggle(200), a.css("height", "auto")), b.toggleClass("fa-chevron-up fa-chevron-down")
    }), $(".close-link").click(function() {
        var a = $(this).closest(".x_panel");
        a.remove()
    })
}), $(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip({
        container: "body"
    })
}), $(".progress .progress-bar")[0] && $(".progress .progress-bar").progressbar(), $(document).ready(function() {
    if ($(".js-switch")[0]) {
        var a = Array.prototype.slice.call(document.querySelectorAll(".js-switch"));
        a.forEach(function(a) {
            new Switchery(a, {
                color: "#26B99A"
            })
        })
    }
}), $(document).ready(function() {
    $("input.flat")[0] && $(document).ready(function() {
        $("input.flat").iCheck({
            checkboxClass: "icheckbox_minimal",
            radioClass: "iradio_minimal"
        })
    })
}), $("table input").on("ifChecked", function() {
    checkState = "", $(this).parent().parent().parent().addClass("selected"), countChecked()
}), $("table input").on("ifUnchecked", function() {
    checkState = "", $(this).parent().parent().parent().removeClass("selected"), countChecked()
});
var checkState = "";
$(".bulk_action input").on("ifChecked", function() {
    checkState = "", $(this).parent().parent().parent().addClass("selected"), countChecked()
}), $(".bulk_action input").on("ifUnchecked", function() {
    checkState = "", $(this).parent().parent().parent().removeClass("selected"), countChecked()
}), $(".bulk_action input#check-all").on("ifChecked", function() {
    checkState = "all", countChecked()
}), $(".bulk_action input#check-all").on("ifUnchecked", function() {
    checkState = "none", countChecked()
}), $(document).ready(function() {
    $(".expand").on("click", function() {
        $(this).next().slideToggle(200), $expand = $(this).find(">:first-child"), "+" == $expand.text() ? $expand.text("-") : $expand.text("+")
    })
}), "undefined" != typeof NProgress && ($(document).ready(function() {
    NProgress.start()
}), $(window).load(function() {
    NProgress.done()
}));
var originalLeave = $.fn.popover.Constructor.prototype.leave;
$.fn.popover.Constructor.prototype.leave = function(a) {
    var c, d, b = a instanceof this.constructor ? a : $(a.currentTarget)[this.type](this.getDelegateOptions()).data("bs." + this.type);
    originalLeave.call(this, a), a.currentTarget && (c = $(a.currentTarget).siblings(".popover"), d = b.timeout, c.one("mouseenter", function() {
        clearTimeout(d), c.one("mouseleave", function() {
            $.fn.popover.Constructor.prototype.leave.call(b, b)
        })
    }))
}, $("body").popover({
    selector: "[data-popover]",
    trigger: "click hover",
    delay: {
        show: 50,
        hide: 400
    }
}), $(document).ready(function() {
   /* init_sparklines(), init_flot_chart(), init_sidebar(), init_wysiwyg(), init_InputMask(), init_JQVmap(), init_cropper(), init_knob(), init_IonRangeSlider(), init_ColorPicker(), init_TagsInput(), init_parsley(), init_daterangepicker(), init_daterangepicker_right(), init_daterangepicker_single_call(), init_daterangepicker_reservation(), init_SmartWizard(), init_EasyPieChart(), init_charts(), init_echarts(), init_morris_charts(), init_skycons(), init_select2(), init_validator(), init_DataTables(), init_chart_doughnut(), init_gauge(), init_PNotify(), init_starrr(), init_calendar(), init_compose(), init_CustomNotification(), init_autosize(), init_autocomplete()*/
   init_sidebar()
});


/*custom code added*/
/*used in listing search form*/
$("#search_reset").click(function() {
	$(".data-search-field" ).val("");
	setTimeout(function(){
		$("#search_form").submit();				
	}, 500);
	
});

/*used in listing page to select all items*/
function countSelected()
{
	var total=$(".check-row:checked").length;
	if(total >0)   $('#btnBulkDelete').prop('disabled', false);
	else	
	{
		$('#btnBulkDelete').prop('disabled', true);		
		$('#check-all').prop('checked', '');		
	}	
	
	total ? ($(".column-title").hide(), $(".bulk-actions").show(), $(".action-cnt").html(total + " Records Selected")) : ($(".column-title").show(), $(".bulk-actions").hide());
}

$('#check-all').on('change', function(event){
	if($('#check-all').is(":checked"))
	{
		$('.check-row').prop('checked', 'checked');
	}
	else
	{
		$('.check-row').prop('checked', '');
	}
	countSelected();
});

$('.check-row').on('change', function(event){
	countSelected();	
});

/*$('input').on('ifToggled', function(event){
	var total=$(".bulk_action input[name='table_records']:checked").length;
	if(total >0)   $('#btnBulkDelete').prop('disabled', false);
	else	$('#btnBulkDelete').prop('disabled', true);					
});	*/



/*password checking method*/
function vCheckPassword( v)
{
	 r = false;
	if ( v.length < 8 ) 
		return r;	
		
	var checkSpecial = /[*!@$#%&()^~{}]+/.test(v),
        checkUpper = /[A-Z]+/.test(v),
        checkLower = /[a-z]+/.test(v),
		checkDigit = /[0-9]+/.test(v),
		checkSpace = /\s/.test(v);
    
	if (checkUpper && checkLower && checkSpecial && checkDigit && !checkSpace) 
	{
		r = true;
	}

	return r;	
}

/*password checking method*/
function vCheckExtension( v)
{
	 r = false;
	if ( v.length < 3 ) 
		return r;	
		
        if ( v.length > 8 ) 
		return r;	
	        
	var 
		checkDigit = /[0-9]+/.test(v);
    
	if (checkDigit) 
	{
		r = true;
	}

	return r;	
}


/*password view*/
$('.btn_view_password').on('mousedown', function() {
	var password_field =$(this).attr("data-password-field");
	$("#"+password_field).attr("type", "text");
});
/*password view*/
$('.btn_view_password').on('mouseup', function() {
	var password_field =$(this).attr("data-password-field");
	$("#"+password_field).attr("type", "password");
});

/////////////open modal////////////////////
function openModal(modal_size, modal_header, modal_body, modal_footer)
{
	if(modal_size=='')
		modal_size ='sm'; 
	var modal_html = '<div class="modal fade" id="my-modal" tabindex="-1" role="dialog" aria-hidden="true">'+
		'<div class="modal-dialog modal-'+modal_size+'">'+
		  '<div class="modal-content">';
			if(modal_header !='')
			{
				modal_html += '<div class="modal-header">'+	modal_header+'</div>';
			}
			if(modal_body !='')
			{	
				modal_html +='<div class="modal-body">'+modal_body+'</div>';
			}
			if(modal_footer !='')
			{		
				modal_html +='<div class="modal-footer">'+modal_footer+'</div>';
			}	
		  modal_html +='</div>'+
		'</div>'+
	  '</div>';
	 $("#idMyModal").html(modal_html);
    $("#modalWindow").modal(); 
}


///////////////delete/////////////////
$("#btnBulkDelete").click(function(){
 
	var delete_id_array = [];
	
	$('input:checkbox.check-row:checked').each(function () {
	   var sThisVal = (this.checked ? $(this).val() : "");
	   if(sThisVal !='')
		{
			delete_id_array.push(sThisVal);
		} 
	});
   //console.log( JSON.stringify(delete_id_array) );
	if(delete_id_array.length >0)
	{	 
		
		var modal_body = '<h1 class="text-center"><i class="fa fa-exclamation-circle"></i></h1>'+
			  '<h4 class="text-center">Are you sure!</h4>'+
			  '<p class="text-center">You won\'t be able to revert this!</p>';
	
		var modal_footer = '<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>'+
				  '<button type="button" class="btn btn-danger" id="modal-btn-yes-bulk">Yes. delete it!</button>';
				  
		
		openModal('','',modal_body, modal_footer);
		$("#my-modal").modal('show');
	
		//yes confirm
		$("#modal-btn-yes-bulk").on("click", function(){
	  	
		var delete_action_url = $('#delete_action_url').val();
		var delete_parameter_two = $('#delete_parameter_two').val();
		
		var form = document.createElement("form");
		document.body.appendChild(form);
		form.method = "POST";
		
		if(delete_action_url === undefined || delete_action_url=='')
			form.action = window.location.href;
		else
		{
			form.action = BASE_URL + delete_action_url;
		}	
		
		var element2 = document.createElement("INPUT");         
		element2.name="action";
		element2.value = 'OkDeleteData';
		element2.type = 'hidden';
		form.appendChild(element2);
		
		var element3 = document.createElement("INPUT");         
		element3.name="delete_id";
		element3.value = JSON.stringify(delete_id_array);
		element3.type = 'hidden';
		form.appendChild(element3);
		
		if(delete_parameter_two === undefined || delete_parameter_two=='')
		{}
		else
		{
			var element4 = document.createElement("INPUT");         
			element4.name="delete_parameter_two";
			element4.value = delete_parameter_two;
			element4.type = 'hidden';
			form.appendChild(element4);	
		}
					
		form.submit();
		
		//alert("yes");
		$("#my-modal").modal('hide');
	});
		
			
		
  
	
	}

	//console.log(delete_id_array[0]);

	
  
});



function doConfirmDelete(delete_val,delete_action_url='', delete_type='')
{
	var delete_id_array = [];
	delete_id_array.push(delete_val);
	
	var modal_body = '<h1 class="text-center"><i class="fa fa-exclamation-circle"></i></h1>'+
			  '<h4 class="text-center">Are you sure!</h4>'+
			  '<p class="text-center">You won\'t be able to revert this!</p>';
	
	var modal_footer = '<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>'+
			  '<button type="button" class="btn btn-danger" id="modal-btn-yes-single">Yes. delete it!</button>';
			  
	
	openModal('','',modal_body, modal_footer);
	$("#my-modal").modal('show');
	$("#modal-btn-yes-single").on("click", function(){
		//alert("single");		
		
		var form = document.createElement("form");
		document.body.appendChild(form);
		form.method = "POST";
		
		if( delete_action_url=='')
			form.action = window.location.href;
		else
		{						
			form.action = BASE_URL + delete_action_url;
		}	
			
		var element2 = document.createElement("INPUT");         
		element2.name="action";
		element2.value = 'OkDeleteData';
		element2.type = 'hidden';
		form.appendChild(element2);
		
		
		var element3 = document.createElement("INPUT");         
		element3.name="delete_id";
		element3.value = JSON.stringify(delete_id_array);
		element3.type = 'hidden';
		form.appendChild(element3);
		
		
		if(delete_type=='')
		{}
		else
		{
			var element4 = document.createElement("INPUT");         
			element4.name="delete_parameter_two";
			element4.value = delete_type;
			element4.type = 'hidden';
			form.appendChild(element4);	
		}
		
		
		form.submit();
		
		//alert("yes");
		$("#my-modal").modal('hide');
		
		
			
	
	});
}

/*datatable */
function showDatatable(table_id, ignore_array, orderby_array)
{    	 
	$('head').append('<link rel="stylesheet" type="text/css" href="'+BASE_URL+'theme/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css">');
	
	$.getScript( BASE_URL+"theme/vendors/datatables.net/js/jquery.dataTables.min.js",true )
	.done(function( script, textStatus ) {
			$('#'+table_id).DataTable({
			searching: false,
			paging: false,
			bInfo : false,
			"aoColumnDefs" : [{
			   "bSortable" : false,
			   "aTargets" : ignore_array ,	
			 }],
			"order": [orderby_array],
		 });
	}); 
}
