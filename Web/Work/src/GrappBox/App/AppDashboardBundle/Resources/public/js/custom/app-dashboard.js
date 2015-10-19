/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 */

var app = angular.module('grappbox', ['ngRoute']).config(function($interpolateProvider) {
        $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
    });

app.controller('teamOccupationController', function($scope, $http) {
    $http.get('../resources/_temp/team-occupation.json').success(function(data) {
        $scope.teamOccupationList = data;
    });
});

app.controller('nextMeetingsController', function($scope, $http) {
    $http.get('../resources/_temp/next-meetings.json').success(function(data) {
        $scope.nextMeetingsList = data;
    });
});

app.controller('globalProgressController', function($scope, $http) {
    $http.get('../resources/_temp/global-progress.json').success(function(data) {
        $scope.globalProgressList = data;
    });
});
