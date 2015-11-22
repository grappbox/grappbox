/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* SHOWCASE navigation scripts 
*
*/
$(document).ready(function() {

    /**
    * Smooth scroll to anchor
    *
    */
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


    /**
    * Active links management
    *
    */
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


    /**
    * Naviagation bar behavior on scroll
    *
    */
    $(window).bind('scroll', function() {
        var navigationBarHeight = $(window).height() - 60;

        if ($(window).scrollTop() > navigationBarHeight)
            $('nav').addClass('fixed');
        else
            $('nav').removeClass('fixed');
    });


    /**
    * Login modal behavior depending on URL parameters
    *
    */
    function getURLParameters() {
        var hashesArray = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        var parametersArray = [];
        var hash;

        for (var i = 0; i < hashesArray.length; ++i) {
            hash = hashesArray[i].split('=');
            parametersArray.push(hash[0]);
            parametersArray[hash[0]] = hash[1];
        }

        return parametersArray;
    }

    function matchLoginModalURL() {
        var URLParameters = getURLParameters();

        if (window.location.hash == "#login" || ((URLParameters.indexOf("return") > -1 && URLParameters.return == "login")))
            $('#app-modal').modal('show');
    }

    matchLoginModalURL();

    $(window).bind('hashchange', function() {
        matchLoginModalURL();
    });

});
