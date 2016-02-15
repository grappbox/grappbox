/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP profile page content
*
*/
app.controller('profileController', ["$scope", "$http", "$rootScope", "$cookies", "Notification", function($scope, $http, $rootScope, $cookies, Notification) {

	/* ==================== INITIALIZATION ==================== */

	// Scope variables initialization
	$scope.data = { onLoad: true, userInformation: "", isValid: false };
	$scope.data.toUpdate = { firstname: "", lastname: "", birthday: "", avatar: "", email: "", phone: "", country: "", linkedin: "", viadeo: "", twitter: "" };

	// Get information for current user
	$http.get($rootScope.apiBaseURL + "/user/basicinformations/" + $cookies.get("USERTOKEN"))
	.then(function userInformationReceived(response) {
		$scope.data.userInformation = (response.data && Object.keys(response.data.data).length ? response.data.data : null);
		$scope.data.isValid = true;
		$scope.data.onLoad = false;

		// Bind existing data to view
		$scope.data.toUpdate.firstname = ($scope.data.userInformation.firstname != "" ? $scope.data.userInformation.firstname : "");
		$scope.data.toUpdate.lastname = ($scope.data.userInformation.lastname != "" ? $scope.data.userInformation.lastname : "");
		$scope.data.toUpdate.birthday = ($scope.data.userInformation.birthday != "" ? new Date($scope.data.userInformation.birthday) : "");
		$scope.data.toUpdate.email = ($scope.data.userInformation.email != "" ? $scope.data.userInformation.email : "");
		$scope.data.toUpdate.phone = ($scope.data.userInformation.phone != "" ? $scope.data.userInformation.phone : "");
		$scope.data.toUpdate.country = ($scope.data.userInformation.country != "" ? $scope.data.userInformation.country : "");
		$scope.data.toUpdate.linkedin = ($scope.data.userInformation.linkedin != "" ? $scope.data.userInformation.linkedin : "");
		$scope.data.toUpdate.viadeo = ($scope.data.userInformation.viadeo != "" ? $scope.data.userInformation.viadeo : "");
		$scope.data.toUpdate.twitter = ($scope.data.userInformation.twitter != "" ? $scope.data.userInformation.twitter : "");
	},
	function userInformationNotReceived(response) {
		$scope.data.userInformation = null;
		$scope.data.isValid = false;
		$scope.data.onLoad = false;

		if (response.data.info && response.data.info.return_code == "7.1.3")
			context.rootScope.onUserTokenError();
	});



	/* ==================== UPDATE OBJECT (PROFILE) ==================== */

	// "Update" button handler
	$scope.view_updateUserProfile = function() {

		Notification.info({ message: "Loading...", delay: 5000 });
		$http.put($rootScope.apiBaseURL + "/user/basicinformations/" + $cookies.get("USERTOKEN"), {
			data: {
				firstname: ($scope.data.toUpdate.firstname != "" ? $scope.data.toUpdate.firstname : null),
				lastname: ($scope.data.toUpdate.lastname != "" ? $scope.data.toUpdate.lastname : null),
				birthday: ($scope.data.toUpdate.birthday != "" ? $scope.data.toUpdate.birthday : null),
				avatar: ($scope.data.toUpdate.avatar.base64 != "" ? $scope.data.toUpdate.avatar.base64 : null),
				email: ($scope.data.toUpdate.email != "" ? $scope.data.toUpdate.email : null),
				phone: ($scope.data.toUpdate.phone != "" ? $scope.data.toUpdate.phone : null),
				country: ($scope.data.toUpdate.country != "" ? $scope.data.toUpdate.country : null),
				linkedin: ($scope.data.toUpdate.linkedin != "" ? $scope.data.toUpdate.linkedin : null),
				viadeo: ($scope.data.toUpdate.viadeo != "" ? $scope.data.toUpdate.viadeo : null),
				twitter: ($scope.data.toUpdate.twitter != "" ? $scope.data.toUpdate.twitter : null)
			}})
		.then(function userInformationUpdateSuccess(response) {
			Notification.success({ message: "Update success.", delay: 5000 });
		},
		function userInformationUpdateFailure(response) {
			if (response.data.info && response.data.info.return_code == "7.1.3")
				context.rootScope.onUserTokenError();
			else
				Notification.warning({ message: "An error occurred. Please try again.", delay: 5000 });
		})};

}]);