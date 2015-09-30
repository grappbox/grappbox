$(document).ready(function()
{
    $('a[href^="#"]').on('click',function (e)
    {
        e.preventDefault();

        var target = this.hash;
        var $target = $(target);

        $('html, body').stop().animate({
            'scrollTop': $target.offset().top - 50;
        }, 700, 'swing');
    });

    var aChildren = $("nav li").children();
    var aArray = [];
    for (var i=0; i < aChildren.length; i++)
    {    
        var aChild = aChildren[i];
        var ahref = $(aChild).attr('href');
        
        if (ahref.indexOf('#') !== -1)
            { aArray.push(ahref); }
    }

    $(window).scroll(function()
    {
        var windowPos = $(window).scrollTop();
        var windowHeight = $(window).height();
        var docHeight = $(document).height();

        for (var i=0; i < aArray.length; i++)
        {
            var theID = aArray[i];
            var divPos = $(theID).offset().top - 50;
            var divHeight = $(theID).height();
            if (windowPos >= divPos && windowPos < (divPos + divHeight))
                {
                    $("a[href='" + theID + "']").addClass("nav-active");
                }
            else
                {
                    $("a[href='" + theID + "']").removeClass("nav-active");
                }
        }

        if(windowPos + windowHeight == docHeight)
        {
            if (!$("nav li:last-child a").hasClass("nav-active"))
            {
                var navActiveCurrent = $(".nav-active").attr("href");
                $("a[href='" + navActiveCurrent + "']").removeClass("nav-active");
                $("nav li:last-child a").addClass("nav-active");
            }
        }
    });
});
