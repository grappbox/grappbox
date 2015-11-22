/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP navigation scripts 
*
*/

/**
* Auto page height (depending on current content)
*
*/
$(function() {
	$(window).bind("load resize", function() {

		newWidth = (this.window.innernewWidth > 0) ? this.window.innernewWidth : this.screen.newWidth;
		offsetTop = 50;

		if (newWidth < 768) {
			$('div.navbar-collapse').addClass('collapse');
			offsetTop = 100;
		}
		else
			$('div.navbar-collapse').removeClass('collapse');

		newHeight = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
		newHeight = newHeight - offsetTop;

		if (newHeight < 1)
			newHeight = 1;

		if (newHeight > offsetTop)
			$("#page-wrapper").css("min-height", newHeight + "px");
	});
});
