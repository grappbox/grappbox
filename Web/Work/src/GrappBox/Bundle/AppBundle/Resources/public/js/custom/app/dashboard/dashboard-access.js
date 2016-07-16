/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ========================================================== */
/* ==================== DASHBOARD ACCESS ==================== */
/* ========================================================== */

// Routine definition
// APP dashboard redirect
var isProjectSelected = function($rootScope, $q, localStorageService, $location, $base64, Notification) {
	var deferred = $q.defer();
  
  if (localStorageService.get("HAS_PROJECT")) {
    if (!localStorageService.get("PROJECT_ID")) {
      Notification.warning({ title: "Dashboard", message: "Someting is wrong with GrappBox. Please try again.", delay: 3000 });
      $rootScope.project.switch();
    }
    else
      $location.path("/dashboard/" + $base64.decode(localStorageService.get("PROJECT_ID")));
  }
  deferred.resolve();
};

// "isProjectSelected" routine injection
isProjectSelected["$inject"] = ["$rootScope", "$q", "localStorageService", "$location", "$base64", "Notification"];