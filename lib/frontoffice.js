$(document).ready(function() {
	$('a.lightbox').fancybox();
	// or using lightBox : $('a.lightbox').lightBox();
	if (document.all)
	{
		$('img[src$=.png],div').ifixpng();
	}
	});