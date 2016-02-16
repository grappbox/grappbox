/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP calendar page content
*
*/
app.controller('calendarController', ["$scope", function($scope) {

	/* ==================== INITIALIZATION ==================== */

  $scope.alertList = [];
  $scope.alertList.push({
    type: 'warning',
    message: 'This section is under construction.'
  });

	// Scope variables initialization
	$scope.data = { onLoad: false, calendar: "", isValid: false };

}]);