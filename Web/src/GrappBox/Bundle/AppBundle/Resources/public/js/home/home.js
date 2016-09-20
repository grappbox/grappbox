/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* =============================================== */
/* ==================== ANCHORS ================== */
/* =============================================== */

$(document).ready(function() {
  "use strict";

  // Smooth scroll to anchor
  $('a[href^="#"]').on('click', function(clickEvent) {
    clickEvent.preventDefault();

    var target = this.hash;
    var $target = $(target);
    if (target.length > 1)
      $('html, body').stop().animate({
        'scrollTop': $target.offset().top - 55
      }, 900, 'swing', function() {
        window.location.hash = target;
      });
  });

  // Hide navbar on page top
  $(window).scroll(function () {
    if ($(this).scrollTop() > 200)
      $('.navbar').fadeIn();
    else
      $('.navbar').fadeOut();
  });



/* ================================================ */
/* ==================== PARALLAX ================== */
/* ================================================ */

  $('#home').parallax("100%", 0.3);



/* ======================================================= */
/* ==================== MATERIAL DESIGN ================== */
/* ======================================================= */

  $.material.init();

});