jQuery(function($) {	
	
	$("#filemanager-tabs").tabs();

	$(".wpfm-settings-form-filemanager").submit(function(event){
		event.preventDefault();
		var form_data = $(this).serialize();
		$(".loading-image").show();
		$(".nm-saving-settings").html("");
		$.post(ajaxurl, form_data, function(resp){
			$(".loading-image").hide();
			$(".nm-saving-settings").html(resp);
		});
	});
	
	$('.file-edit-btn').on('click', function(event){
		event.preventDefault();
		$(this).closest('.wpfm-modal-content').find('.title_dec_adit_wrapper').toggle();
	});

	$(document).on('click','.file-title-dec-save-btn', function(event){
		event.preventDefault();

		var file_id 		= $(this).siblings('.file-title').data("id");
		var file_title 		= $(this).siblings('.file-title').val();
		var file_descrip 	= $(this).siblings('.file-description').val();
		var data = {
			'action' : 'wpfm_edit_file_title_desc',
			'file_id' : file_id,
			'file_title' : file_title,
			'file_content' : file_descrip,
		}
		// console.log(data);
		$.post( ajaxurl, data, function(resp) {
			swal(resp);
			location.reload();

		}).fail(function() {
		    swal( "error" );
		});

	});
	/*
	** js for file meta
	*/

	// hide or show all fields in table with minus and plus icon
	$('#postcustoms > h3').on('click', function(event){
		
		if ( $(this).children('span.dashicons').hasClass('dashicons-plus') ) {
			$("#form-meta-setting ul li").find('table').slideDown();
			$(this).children('span.dashicons').removeClass('dashicons-plus').addClass('dashicons-minus');
		}else if($(this).children('span.dashicons').hasClass('dashicons-minus')){
			$("#form-meta-setting ul li").find('table').slideUp();
			$(this).children('span.dashicons').removeClass('dashicons-minus').addClass('dashicons-plus');
		};
	});

	// click on del button filed deleted
	$('#form-meta-setting').on('click','.dashicons-trash', function(event){
		var list_item = $(this).closest('li');
		$("#remove-meta-confirm").dialog("open");
		meta_removed = $(list_item);
		event.preventDefault();
	});

	// click on copy button and copy field
	$('#form-meta-setting').on('click','.dashicons-image-rotate-right', function(event){
		var list_item = $(this).closest('li');
		list_item.clone(true).insertAfter(list_item);
	});

	$('#form-meta-setting').on('click', '.dashicons-image-flip-vertical', function(e) {
		$(this).closest('li').find("table").slideToggle(300);
	});

	$("#form-meta-setting ul").sortable({
		revert : true,
		start : function(event, ui) {
			
			$(ui.helper).addClass("ui-helper");
            
			$(ui.helper.context).find('h3,span').show();
		},
		stop : function(event, ui) {
			// only attach click event when dropped from right panel
			if (ui.originalPosition.left > 20) {
				$(ui.item).find(".dashicons-image-flip-vertical").click(function(e) {
					$(this).parent('.inputdata').find("table").slideToggle(300);
				});
			}
		}
	});
	$("#nm-input-types li").draggable({
		connectToSortable : "#form-meta-setting ul#file-meta-input-holder",
		helper : "clone",
		revert : "invalid",
		start: function(event, ui){
			// ui.helper.width('100%');
			// ui.helper.height('auto');
			ui.helper.addClass("ui-helper");

			$("#form-meta-setting ul li").find('table').slideUp();
		},
		stop : function(event, ui) {
			console.log('stop end');
			$(ui.helper).find('table').slideDown( 'slow' );
			$('.ui-sortable .ui-draggable').removeClass('input-type-item').find('div').addClass('inputdata');
			ui.helper.width('98%');
			ui.helper.height('auto');
			ui.helper.find('.top-heading-text').addClass("newcalss");
			ui.helper.find('.top-heading-text').css({
				"background-color" : '#d7d7d7',
			});
			ui.helper.css({
				'margin' : '10px',
			});
		}
	});


	// =========== remove dialog ===========
	$("#remove-meta-confirm").dialog({
		resizable : false,
		height : 160,
		autoOpen : false,
		modal : true,
		buttons : {
			Remove : function() {
				$(this).dialog("close");
				meta_removed.remove();
			},
			Cancel : function() {
				$(this).dialog("close");
			}
		}
	});

	$('.save-meta-frm').on('submit', function(e) {
	    event.preventDefault();
	    // alert('saved');
	    var modal = $(this).closest('.modal');
   		$(modal).modal('hide');
   		
	    var data = $(this).serialize();
	    data = 'action=wpfm_file_meta_update&'+data;

	    $.post( ajaxurl, data, function(resp) {

			swal(resp);
		}).fail(function() {

		    alert( "error" );
		});
	});
});

