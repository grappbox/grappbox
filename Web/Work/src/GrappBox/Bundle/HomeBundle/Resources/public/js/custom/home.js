/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* HOME scripts definition
*
*/
$(document).ready(function() {

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


    // Active links management
    var linksChildren = $("nav li").children();
    var linksArray = [];

    for (var i = 0; i < linksChildren.length; ++i) {
        var eachLink = $(linksChildren[i]).attr('href');
        if (eachLink && eachLink.length > 1 && eachLink.indexOf('#') !== -1)
            linksArray.push(eachLink);
    }

    $(window).scroll(function() {
        var windowsPosition = $(window).scrollTop();
        var windowsHeight = $(window).height();
        var documentHeight = $(document).height();

        for (var i = 0; i < linksArray.length; ++i) {
            var linkID = linksArray[i];
            var linkContainerPosition = $(linkID).offset().top - 60;
            var linkContainerHeight = $(linkID).height();

            if (windowsPosition >= linkContainerPosition && windowsPosition <= (linkContainerPosition + linkContainerHeight))
                $("a[href='" + linkID + "']").addClass("active");
            else
                $("a[href='" + linkID + "']").removeClass("active");
        }

        if (windowsPosition + windowsHeight == documentHeight) {
            if (!$("nav li:last-child a").hasClass("active")) {
                var activeLink = $(".active").attr("href");
                $("a[href='" + activeLink + "']").removeClass("active");
                $("nav li:last-child a").addClass("active");
            }
        }
    });


    // Naviagation bar behavior on scroll
    $(window).bind('scroll', function() {
        var navigationBarHeight = $(window).height() - 60;
        if ($(window).scrollTop() > navigationBarHeight)
            $('nav').addClass('fixed');
        else
            $('nav').removeClass('fixed');
    });


    // Login modal behavior depending on URL parameters
    function matchLoginModalURL() {
        if (window.location.hash == "#login")
            $('#app-modal').modal('show');
    }

    matchLoginModalURL();

    $(window).bind('hashchange', function() {
        matchLoginModalURL();
    });

    $('#app-modal').on('shown.bs.modal', function () {
        window.location.hash = "#login";
        $('#form_email').focus();
        getLoginState();
    });
    $('#app-modal').on('hide.bs.modal', function () {
        $('#form_alert').removeClass('show');
    });


    // Login modal behavior depending on server cookies
    function getLoginState() {
        var loginState = Cookies.get("LASTLOGINMESSAGE");

        if (loginState != undefined && loginState != sha512('_success')) {
            switch(loginState) {
                case sha512("_BADLOGIN"):
                $('#form_alert p').text("Sorry, GrappBox didn't recognize the email you entered.");
                break;

                case sha512("_BADPASSWORD"):
                $('#form_alert p').text("The email and password you entered don't match.");
                break;

                case sha512("_DENIED"):
                $('#form_alert p').text("Sign in to continue to Grappbox.");
                break;

                default:
                break;
            }
            Cookies.remove("LASTLOGINMESSAGE", { path: '/' });
            Cookies.remove("USERTOKEN", { path: '/' });

            $('#form_alert').addClass('show');
        }
    }

});