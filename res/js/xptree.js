jQuery(document).ready(function($) {
	$(".titlewrap").dblclick(function() {
		alert('doubleclick');
		// check, if there is a node if yes, do the request to expand it, if not do nothing
		return false;
	});
	$(".titlewrap").click(function() {
		//alert('click');
		// wait for dbl click, if not do the request to select this folder
		return false;
	});
});