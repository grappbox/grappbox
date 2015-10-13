/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 */

var app = angular.module('grappbox', []).config(function($interpolateProvider) {
        $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
    });

app.controller('teamOccupationController', function($scope, $http) {
    $http.get('../temporary/team-occupation.json').success(function(data) {
        $scope.teamOccupationList = data;
    });
});

app.controller('nextMeetingsController', function($scope, $http) {
    $http.get('../temporary/next-meetings.json').success(function(data) {
        $scope.nextMeetingsList = data;
    });
});

app.controller('globalProgressController', function($scope, $http) {
    $http.get('../temporary/global-progress.json').success(function(data) {
        $scope.globalProgressList = data;
    });
});
