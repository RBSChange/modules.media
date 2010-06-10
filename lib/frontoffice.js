jQuery(document).ready(function() {
	jQuery('a.lightbox').fancybox();
	// or using lightBox : jQuery('a.lightbox').lightBox();
	if (document.all)
	{
		jQuery('img[src$=.png],div').ifixpng();
	}
	});