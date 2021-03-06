jQuery(document).ready(function($){

	$('.wpfm_files_grid .wpfm_file_box').each( function(index, key) {
        var icon_id = $(this).find('.view-icon').attr('id');
        var modal_id = '#'+icon_id;
        var modal_target = $(this).find('.view-icon').data('target');
        
        // alert('load cs');
        
        $(modal_id).animatedModal({
    		modalTarget:modal_target,
            animatedIn:'lightSpeedIn',
            animatedOut:'bounceOutDown',
            color:'#fff',
            opacityIn: '1'
    	});

    });


    $('table .column-detail').each( function(index, key) {
        var icon_id = $(this).find('.view-icon').attr('id');
        var modal_id = '#'+icon_id;
        var modal_target = $(this).find('.view-icon').data('target');
        
        $(modal_id).animatedModal({
            modalTarget:modal_target,
            animatedIn:'lightSpeedIn',
            animatedOut:'bounceOutDown',
            color:'#fff',
        });
    });

    $('.wpfm-modal-content .col-md-2').each( function(index, key) {
        var icon_id = $(this).find('.view-icon').data('modal_id');
        var modal_id = '.'+icon_id;
        var modal_target = $(this).find('.view-icon').data('target');
        
        $(modal_id).animatedModal({
            modalTarget:modal_target,
            animatedIn:'lightSpeedIn',
            animatedOut:'bounceOutDown',
            color:'#fff',
        });
    });
});