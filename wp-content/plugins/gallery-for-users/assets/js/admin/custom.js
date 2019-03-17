/**
 * Hide menu items and change link for admin menu
 */
jQuery(document).ready(function($){
	$('#menu-posts-users_gallery').children('.menu-icon-users_gallery').attr('href', 'edit.php?post_type=users_gallery&page=users_gallery_settings');

	$('#menu-posts-users_gallery').find('a').each(function(){
		var content = $(this).text();
		if(content == 'All Gallery items' || content == 'Add New'){
			$(this).parent().remove();
		}
	});

	/**
	 * Enable color picker 
	 */
	$('.color_picker').wpColorPicker();
});
