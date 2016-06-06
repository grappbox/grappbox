/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* GRAPPBOX
* APP menu controller definition
*
*/
app.controller("app_asideMenu", ["$scope", "$uibModalInstance", "routeList", "iconList", function($scope, $uibModalInstance, routeList, iconList) {

  $scope.routeList = routeList;
  $scope.iconList = iconList;
  $scope.app_closeAsideMenu = function() { $uibModalInstance.close(); };
}]);



/**
* GRAPPBOX
* APP additional scripts definition
*
*/
(function($) {

  // Auto page height (depending on page content)
  $(window).bind("load resize", function() {
    newHeight = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height);
    $("#app-wrapper").css("min-height", (newHeight < 1 ? 1 : newHeight) + "px");
  });
  
})(jQuery);