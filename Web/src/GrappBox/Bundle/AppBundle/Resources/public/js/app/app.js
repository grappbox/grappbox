/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ==================================================== */
/* ==================== DEFINITION ==================== */
/* ==================================================== */

// APP DEFINITION
var app = angular.module("grappbox", [
  "ngRoute",
  "ngCookies",
  "ngAnimate",
  "ngTagsInput",
  "base64",
  "naif.base64",
  "mwl.calendar",
  "ui.bootstrap",
  "ui-notification",
  "LocalStorageModule",
  "ui.tree",
  "ng-drag-scroll",
  "ang-drag-drop",
  "chart.js",
  "gantt",
  "gantt.movable",
  "gantt.tooltips",
  "gantt.progress",
  "gantt.table",
  "gantt.tree",
  "gantt.groups",
  "gantt.dependencies",
  "gantt.resizeSensor"
]);



/* ======================================================= */
/* ==================== CONFIGURATION ==================== */
/* ======================================================= */

// Production compilation settings
// app.config(["$compileProvider", function($compileProvider) {
//  $compileProvider.debugInfoEnabled(false);
// }]);

// TWIG template conflict fix
app.config(["$interpolateProvider", function($interpolateProvider) {
  $interpolateProvider.startSymbol("{[{").endSymbol("}]}");
}]);

// Cross-domain URLs calls fix
app.config(["$httpProvider", function($httpProvider) {
  $httpProvider.useApplyAsync(true);
  $httpProvider.defaults.useXDomain = true;
  delete $httpProvider.defaults.headers.common["X-Requested-With"];
}]);

// Local storage settings
app.config(["localStorageServiceProvider", function(localStorageServiceProvider) {
  localStorageServiceProvider
    .setPrefix("grappbox.")
    .setStorageCookie(30, "/")
    .setStorageType("localStorage")
    .setNotify(true, true);
}]);

// Bootstrap notifications settings
app.config(["NotificationProvider", function(NotificationProvider) {
  NotificationProvider.setOptions({ positionX: "center", positionY: "bottom" });
}]);

// ChartJS
app.config(["ChartJsProvider", function(ChartJsProvider) {
  ChartJsProvider.setOptions({
    legend: true,
    tooltipFontFamily: "'Roboto', 'Helvetica Neue', 'Segoe UI', 'Oxygen', 'Ubuntu', 'Cantarell', 'Open Sans', sans-serif",
    tooltipFontSize: 14,
    tooltipFontStyle: "normal",
    tooltipFontColor: "#fff",
    tooltipTitleFontFamily: "'Roboto', 'Helvetica Neue', 'Segoe UI', 'Oxygen', 'Ubuntu', 'Cantarell', 'Open Sans', sans-serif",
    tooltipTitleFontSize: 14,
    tooltipTitleFontStyle: "bold",
    tooltipTitleFontColor: "#fff",
    tooltipYPadding: 10,
    tooltipXPadding: 10,
    tooltipCornerRadius: 0,
    tooltipXOffset: 10 
  });
}]);



/* ======================================================= */
/* ==================== MATERIAL DESIGN ================== */
/* ======================================================= */

// jQuery
$(document).ready(function() {
  $.material.init();
});