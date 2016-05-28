/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP additional scripts definition
*
*/
(function($) {

	// Auto page height (depending on page content)
	$(window).bind("load resize", function() {
		newHeight = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height);
		$("#page-content-wrapper").css("min-height", (newHeight < 1 ? 1 : newHeight) + "px");
	});
	
})(jQuery);