jQuery(document).ready(function($) {
	$(".titlewrap").dblclick(function() {
		alert('doubleclick');
		return false;
	});
	$(".titlewrap").click(function() {
		//alert('click');
		// 
		return false;
	});
});