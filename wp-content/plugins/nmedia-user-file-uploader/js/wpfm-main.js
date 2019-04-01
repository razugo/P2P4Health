jQuery(function($){

	// Loading files on load
	var group_id = 0;
	WPFM.renderFiles( group_id );

	$(document).on('mixLoad', function(e, state){
		// Nothing ...
	});
	/*
	 show and hide the meta edit form or meta detail
	*/
	$(document).on('click','.edit-meta-btn', function(e, state){
		$(this).closest('.meta-info').hide();
		$(this).closest('.meta-info').siblings('.meta-edit-from').show();		
	});
	$(document).on('click','.go-to-meta-info-btn', function(e, state){
		$(this).closest('.meta-edit-from').hide();
		$(this).closest('.meta-edit-from').siblings('.meta-info').show();		
	});
	
	/*
	**** end ---
	*/
	$(document).on('wpfm_files_rendered', function(e) {

		add_mixitup();
		// setting directory to root for BC
		WPFM.resetBC();
      	WPFM.currentDir = {id:'0',title:''};
		WPFM.dir_selected();

		add_modal_on_file();
	});

    $(document).on('wpfm_dir_changed', function(e){
    	
		// Nothing ...
    });
    
    // After directory created
    $(document).on('wpfm_after_dir_created', function(e) {
        
        // Loading files on load
		window.location.reload(false); 
		
    });
    
    // After file saved
    $(document).on('wpfm_after_file_saved', function(e) {
        
        // Loading files on load
		window.location.reload(false);
		// group_id = 0;
		// WPFM.refreshFiles(group_id);
    });
    
    // After file/directory deleted
    $(document).on('wpfm_after_item_deleted', function(e) {
        
        // Loading files on load
		window.location.reload(false); 
    });
    
    // file meta save on single file 
    $(document).on('submit','.save-meta-frm', function(e) {
	    event.preventDefault();

	    var modal = $(this).closest('.frizi-modal');
   		$(modal).modal('hide');
   		
	    var data = $(this).serialize();

	    data = 'action=wpfm_file_meta_update&'+data;
	    // console.log(data);
	    $.post( wpfm_vars.ajaxurl, data, function(resp) {
	    	// console.log(resp);
			swal(resp);

			var form_data = modal.find("#text_id").val;
			// set the values of meta info pannel
			$.each( form_data, function( i, key ) {
			 	// $( "#" + val ).text( "Mine is " + val + "." );
			 	var input_val = $(this).find('input').val();
				console.log(input_val);
			});
		}).fail(function() {

		    alert( "error" );
		});
	});
	
	// sending file in email
	$('.wpfm-send-file-in-email').on('submit', function(e) {
	    
	    e.preventDefault();

	    $('.wpfm-sending-file').show();
   		
	    var data = $(this).serialize();

	    // console.log(data);
	    $.post( wpfm_vars.ajaxurl, data, function(resp) {
	    	
			WPFM.alert(resp.message, resp.status);

		}, 'json');
	});

    // On dir change
    $('#wpfm-main-wrapper').on('click', '.wpfm-dir', function(e) {
    	
    	$('#wpfm-del-dir-btn').show();

    	var dir_id = $(this).data('node_id');
    	var dir_title = $(this).data('title');
    	var parent_id = '.parent-'+dir_id;
    	
    	// making serting and searching do some code
    	// when clicking on directroy, first remove all 'mix' classes form '.wpfm_file_box'
    	// then add 'mix' class only those '.wpfm_file_box' div's which is displayed
    	$('.wpfm_file_box').removeClass('mix');
    	$(parent_id).addClass('mix');
    	// end sorting and searching section

		WPFM.currentDir = {id: dir_id,title:dir_title};
		WPFM.dir_selected();
    	
    });
    
    // Breadcrumb item click
    $('#wpfm-main-wrapper').on('click', '.wpfm-bc-item', function(e) {
    	
    	var dir_id		= $(this).data('node_id');
    	var dir_title	= $(this).data('title');
    	console.log(dir_title);
    	WPFM.currentDir = {id:dir_id,title:dir_title};
    	
    	WPFM.dir_selected( 'bc-removed' );

    	// for making search and sorting 
    	// first remove class of mix form all file div's
    	// then add class mix to reqired or displayed file div's
    	$('.wpfm_file_box').removeClass('mix');
    	var files = '.parent-'+ dir_id;
    	$(files).addClass('mix');
    	// end sections for soring ans searching

    	var bcCount = WPFM.wpfm_bc.length;
    	var bcIndex = 0;
    	for(var i=0; i<bcCount; i++) {
    		
    		var bcItem = WPFM.wpfm_bc[i];
    		if( dir_id === bcItem.id ) {
    			bcIndex = i;
    		    // console.log('removed '+bcItem.title);
    		}
    	}
    	
    	var currentIndex = bcIndex+1;
    	var extraItems = bcCount - currentIndex;
		WPFM.wpfm_bc.splice(currentIndex, extraItems);
    	WPFM.updateBC();
    });
    
	// 
	$('#wpfm-create-dir-option-btn').on('click', function(){
		$('.wpfm-new-select-wrapper').hide();
		$(this).hide();
		$('.wpfm-dir-create-wrapper').show('200');
	});

	$('.wpfm-cancle-btn').on('click', function(){
		WPFM.reset_upload_area();
	});

	$('#wpfm-dirname').on('keyup', function(){
		
		var value = $(this).val();
		if (value.length != 0) {
			$('#wpfm-dir-created-btn').removeAttr("disabled");
		}else{
			$('#wpfm-dir-created-btn').attr('disabled','disabled');
		};
	});
	
	
	// Creating new directory
	$('#wpfm-dir-created-btn').on('click', function(){
		var dir_name 		= $('#wpfm-dirname').val();
		var dir_description = $('#wpfm-description').val();
		var shortcode_groups= $('#shortcode_groups').val();
	
		var data = {
			"action" 			: "wpfm_create_directory",
			"dir_name" 			: dir_name,
			"directory_detail"	: dir_description,
			"shortcode_groups"	: shortcode_groups,
			"parent_id"			: WPFM.get_selected_dir()
		};
		
		WPFM.createDirectory(data);
		
	});

	$(document).on('click','.file-edit-btn', function(event){
		
		$(this).closest('.wpfm-modal-content').find('.title_dec_adit_wrapper').toggle();
	});

	$(document).on('click','.file-title-dec-cancel-adit-btn', function(event){
		$(this).closest('.title_dec_adit_wrapper').hide();
	});

	$(document).on('click','.del-file-btn', function(event){
		event.preventDefault();
	  	var file_id 	= $(this).data('id');
	  	
		swal({
		  title: "Are you sure?",
		  icon: "warning",
		  buttons: true,
		  dangerMode: true
		}).then( function(willDelete){
		  if (willDelete) {
		  	
			WPFM.delete_file( file_id );
		  }else {
		  	$('html').css('overflow', 'visible')
		  	$('body').css('overflow', 'visible')
		  }
		});
		
	});

	
	$(document).on('click','.file-title-dec-save-btn', function(event){
		event.preventDefault();
		var ajax_url 		= $('#ajax_url').val();
		var file_id 		= $(this).siblings('.file-title').data("id");
		var file_title 		= $(this).siblings('.file-title').val();
		var file_descrip 	= $(this).siblings('.file-description').val();
		var data = {
			'action' : 'wpfm_edit_file_title_desc',
			'file_id' : file_id,
			'file_title' : file_title,
			'file_content' : file_descrip,
		}

		$.post( wpfm_vars.ajaxurl, data, function(resp) {
		
			swal(resp, "File update successfuly", "success");
			location.reload();

		}).fail(function() {	
			swal('error', "File not update", "error");
		});

	});

	$('#wpfm-save-file-btn').on('click', function(event){
		
		event.preventDefault();
		// alert("working");
		var new_files 	= $('.wpfm-new-uploaded-files :input').serialize();
		var wpfm_nonce = $('#wpfm_save_nonce').val();
		var groups = $('#shortcode_groups').val();
		var data = new_files+"&action=wpfm_save_file_data&wpfm_save_nonce="+wpfm_nonce+"&shortcode_groups="+groups;
		data	+= '&parent_id='+WPFM.get_selected_dir();
		// console.log(data);
		WPFM.saveFiles( data );
		
	});
	
	$(document).on('change', '#wpfm_sorted_by', function(event){
		var orederby = $(this).val();
		var oreder= $('input[type=radio][name=wpfm_sortorder]').val();
		$(".wpfm_files_grid").mixItUp('sort', orederby+':'+oreder +' '+ 'title:'+oreder );
	});
	$(document).on('change', 'input[type=radio][name=wpfm_sortorder]', function(event){
		var oreder = $(this).val();
		var orederby = $("#wpfm_sorted_by").val();
		console.log(orederby , oreder)
		$(".wpfm_files_grid").mixItUp('sort', orederby+':'+oreder );
	});

	$(document).on('change', '#group-filter', function(event){
		var filterby = $(this).val();
		var $matching_groups = $();
		console.log(filterby);
		if (filterby == 'all') {
			$(".wpfm_files_grid").mixItUp( 'filter', 'all' );
		}else{

			$( '.mix').each(function() {
				$this = $("this");
		          
		           // add item to be filtered out if input text matches items inside the title   
		        if( $(this).hasClass( filterby ) ) {
		            $matching_groups = $matching_groups.add(this);
		        }
		    });
			$(".wpfm_files_grid").mixItUp( 'filter', $matching_groups );
		};
	});
	$(document).on('keyup', '#search_files', function(event){
    // Delay function invoked to make sure user stopped typing

		var inputText;
		var $matching = $();
		inputText = $("#search_files").val().toLowerCase();
      
      // Check to see if input field is empty
		if ((inputText.length) > 0) {            
			$( '.mix').each(function() {
				$this = $("this");
		          
		           // add item to be filtered out if input text matches items inside the title   
		        if($(this).children('.file_title').text().toLowerCase().match(inputText)) {
		            $matching = $matching.add(this);
		        }
		        else {
		            // removes any previously matched item
		            $matching = $matching.not(this);
		        }
		    });
		    $(".wpfm_files_grid").mixItUp('filter', $matching);
		}

		else {
			// resets the filter to show all item if input is empty
			$(".wpfm_files_grid").mixItUp('filter', 'all');
		}
	});
	
});

function add_mixitup(){

	var mix = jQuery('.wpfm_files_grid').mixItUp();
}

function add_modal_on_file(){
	
	jQuery('.wpfm_file_box').find('.view-icon').animatedModal({
		animatedIn:'lightSpeedIn',
        animatedOut:'bounceOutDown',
        color:'#fff',
	});
}
