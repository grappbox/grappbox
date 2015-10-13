/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 */

angular.module('app-dashboard', []).config(function($interpolateProvider) { $interpolateProvider.startSymbol('{[{').endSymbol('}]}'); });

var app = angular.module('app-dashboard', []);
app.controller('app-dashboard-controller', function($scope) { });
