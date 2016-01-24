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
$(function() {

	// Auto page height (depending on current content)
	$(window).bind("load resize", function() {
		newWidth = (this.window.innernewWidth > 0) ? this.window.innernewWidth : this.screen.newWidth;
		offsetTop = 60;
		offsetBottom = 123;

		if (newWidth < 768) {
			$('div.navbar-collapse').addClass('collapse');
			offsetTop = 100;
		}
		else
			$('div.navbar-collapse').removeClass('collapse');

		newHeight = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
		newHeight = newHeight - offsetTop - offsetBottom;
		if (newHeight < 1)
			newHeight = 1;
		if (newHeight > offsetTop)
			$("#page-wrapper").css("min-height", newHeight + "px");
	});
});