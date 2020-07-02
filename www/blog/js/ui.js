$(document).ready(function() {
	// Menu
	$('#mainmenu label.togglebars').click(function(){
		$('nav.menu>ul').toggleClass("mobileshow");
	});
	$('#mainmenu li a').click(function(){
		$(this).parent().toggleClass("mobileshow");
	});
	$('#mainmenu li.mobilehide').click(function(){
		$('#mainmenu li, #mainmenu ul').removeClass("mobileshow");
	});
	jQuery(document).click(function(e){
		var menu = $('#mainmenu');
		if (e.target.id != menu.attr('id') && !menu.has(e.target).length) {
			$('#mainmenu li, #mainmenu ul').removeClass("mobileshow");
		}
	});
});