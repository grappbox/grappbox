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
  "ang-drag-drop",
  "gantt",
  //"gantt.sortable",
  "gantt.movable",
  //"gantt.drawtask",
  "gantt.tooltips",
  //"gantt.bounds",
  "gantt.progress",
  "gantt.table",
  "gantt.tree",
  "gantt.groups",
  "gantt.dependencies",
  //"gantt.overlap",
  "gantt.resizeSensor"//,
  //"mgcrea.ngStrap"
]);



/* ======================================================= */
/* ==================== CONFIGURATION ==================== */
/* ======================================================= */

// Production compilation settings
app.config(["$compileProvider", function($compileProvider) {
  $compileProvider.debugInfoEnabled(false);
}]);

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



/* ======================================================= */
/* ==================== MATERIAL DESIGN ================== */
/* ======================================================= */

// jQuery
$(document).ready(function() {
  $.material.init();
});