/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP whiteboard page content
*
*/
app.controller("whiteboardController", ["$scope", "$http", "$route", "whiteboardFactory", function($scope, $http, $route, whiteboardFactory) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { onLoad: true, valid: false, authorized: false };
  $scope.whiteboard = { id: $route.current.params.id, project_id: $route.current.params.project_id, canvas: {}, points: [] };
  $scope.action = { undoLastAction: "", setWhiteboard: "" };

  $scope.mouse = { start: { x: 0, y: 0 }, end: { x: 0, y: 0 }, pressed: false };
  $scope.text = { value: "", italic: false, bold: false };

  $scope.colors = [
    { name: "-None-", value: "none" },
    { name: "Red", value: "#F44336" },
    { name: "Pink", value: "#E91E63" },
    { name: "Purple", value: "#9C27B0" },
    { name: "Deep Purple", value: "#673AB7" },
    { name: "Indigo", value: "#3F51B5" },
    { name: "Blue", value: "#2196F3" },
    { name: "Light Blue", value: "#03A9F4" },
    { name: "Cyan", value: "#00BCD4" },
    { name: "Teal", value: "#009688" },
    { name: "Green", value: "#4CAF50" },
    { name: "Light Green", value: "#8BC34A" },
    { name: "Lime", value: "#CDDC39" },
    { name: "Yellow", value: "#FFEB3B" },
    { name: "Amber", value: "#FFC107" },
    { name: "Orange", value: "#FF9800" },
    { name: "Deep Orange", value: "#FF5722" },
    { name: "Brown", value: "#795548" },
    { name: "Blue Grey", value: "#607D8B" },
    { name: "White", value: "#FFFFFF" },
    { name: "Grey 20%", value: "#EEEEEE" },
    { name: "Grey 40%", value: "#BDBDBD" },
    { name: "Grey 50%", value: "#9E9E9E" },
    { name: "Grey 60%", value: "#757575" },
    { name: "Grey 80%", value: "#424242" },
    { name: "Black", value: "#000000" }
  ];

  $scope.sizes = [
    { label: "0.5 pt", value: "0.5" },
    { label: "1 pt", value: "1" },
    { label: "1.5 pt", value: "1.5" },
    { label: "2 pt", value: "2" },
    { label: "2.5 pt", value: "2.5" },
    { label: "3 pt", value: "3" },
    { label: "4 pt", value: "4" },
    { label: "5 pt", value: "5" }
  ];

  $scope.selected = {
    color: { name: "Black", value: "#000000" },
    fill: { name: "-None-", value: "none" },
    size: { label: "1 pt", value: "1" },
    tool: "pencil"
  };



  /* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
  // Create/compile canvasData to render
  var _setRenderObject = function() {
    var data = {};

    switch ($scope.selected.tool) {
      case "pencil":
      data = {
        tool: "pencil",
        size: Number($scope.selected.size.value),
        color: $scope.selected.color.value,
        points: $scope.whiteboard.points
      };
      break;

      case "line":
      data = {
        tool: "line",
        size: Number($scope.selected.size.value),
        color: $scope.selected.color.value,
        start_x: $scope.mouse.start.x,
        start_y: $scope.mouse.start.y,
        end_x: $scope.mouse.end.x,
        end_y: $scope.mouse.end.y
      };
      break;

      case "rectangle":
      data = {
        tool: "rectangle",
        size: Number($scope.selected.size.value),
        color: $scope.selected.color.value,
        fill: $scope.selected.fill.value,
        start_x: $scope.mouse.start.x,
        start_y: $scope.mouse.start.y,
        height: $scope.mouse.end.y - $scope.mouse.start.y,
        width: $scope.mouse.end.x - $scope.mouse.start.x
      };
      break;

      case "diamond":
      data = {
        tool: "diamond",
        size: Number($scope.selected.size.value),
        color: $scope.selected.color.value,
        fill: $scope.selected.fill.value,
        start_x: $scope.mouse.start.x,
        start_y: $scope.mouse.start.y,
        end_x: $scope.mouse.end.x,
        end_y: $scope.mouse.end.y,
        height: $scope.mouse.end.y - $scope.mouse.start.y,
        width: $scope.mouse.end.x - $scope.mouse.start.x
      };
      break;

      case "ellipse":
      data = {
        tool: "ellipse",
        size: Number($scope.selected.size.value),
        color: $scope.selected.color.value,
        fill: $scope.selected.fill.value,
        start_x: $scope.mouse.start.x,
        start_y: $scope.mouse.start.y,
        radius_x: (Math.abs($scope.mouse.end.x - $scope.mouse.start.x)),
        radius_y: (Math.abs($scope.mouse.end.y - $scope.mouse.start.y))
      };
      break;

      case "text":
      data = {
        tool: "text",
        font: "32pt Roboto Condensed",
        italic: $scope.text.italic,
        bold: $scope.text.bold,
        value: $scope.text.value,
        start_x: $scope.mouse.start.x,
        start_y: $scope.mouse.start.y,
        color: $scope.selected.color.value
      };
      break;

      default:
      data = {};
      break;
    }

    return data;
  };

  // Routine definition (local)
  // Render/display canvas data using whiteboardFactory
  var _renderObject = function(data) {
    if ($scope.selected.tool === "line" || $scope.selected.tool === "rectangle" || $scope.selected.tool === "diamond" || $scope.selected.tool === "ellipse")
      whiteboardFactory.renderCanvasBuffer();
    whiteboardFactory.renderObject(data);
  };

  // Routine definition (local)
  // Set whiteboard canvas and context
  var _setCanvas = function() {
    $scope.whiteboard.canvas = document.getElementById("whiteboard-canvas");
    whiteboardFactory.setCanvas($scope.whiteboard.canvas);
    whiteboardFactory.setCanvasContext($scope.whiteboard.canvas.getContext("2d"));
  };

  // Routine definition (local)
  // Set whiteboard mouse handlers
  var _setMouseHandlers = function() {
    // Canvas default callback: mouse pressed
    $scope.whiteboard.canvas.onmousedown = function(event) {
      $scope.mouse.pressed = true;
      $scope.mouse.start.x = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].x : event.offsetX);
      $scope.mouse.start.y = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].y : event.offsetY);
      $scope.mouse.end.x = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].x : event.offsetX);
      $scope.mouse.end.y = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].y : event.offsetY);

      $scope.whiteboard.points.push({
        x: event.offsetX,
        y: event.offsetY,
        color: $scope.selected.color.value
      });
      _renderObject(_setRenderObject());
    };

    // Canvas default callback: mouse drag
    $scope.whiteboard.canvas.onmousemove = function(event) {
      var last = "";

      if ($scope.mouse.pressed) {
        last = $scope.whiteboard.points[$scope.whiteboard.points.length - 1];
        $scope.whiteboard.points.push({
          x: event.offsetX,
          y: event.offsetY,
          color: $scope.selected.color.value
        });
        $scope.mouse.end.x = last.x;
        $scope.mouse.start.y = last.y;
        _renderObject(_setRenderObject());
      }
    };

    // Canvas default callback: mouse release
    $scope.whiteboard.canvas.onmouseup = function() {
      $scope.mouse.pressed = false;
      whiteboardFactory.addToCanvasBuffer(_setRenderObject());
      $scope.whiteboard.points = [];
      $scope.mouse.start.x = 0;
      $scope.mouse.start.y = 0;
      $scope.mouse.end.x = 0;
      $scope.mouse.start.y = 0;
    };
  };



  /* ==================== SCOPE ROUTINES ==================== */

  // "Undo" button handler
  $scope.action.undoLastAction = function() {
    whiteboardFactory.undoLastAction();
    whiteboardFactory.renderCanvasBuffer();

    $scope.whiteboard.points = [];
    $scope.mouse.start.x = 0;
    $scope.mouse.start.y = 0;
    $scope.mouse.end.x = 0;
    $scope.mouse.end.y = 0;
  };

  // Initialize whiteboard on launch
  $scope.action.setWhiteboard = function() {
    _setCanvas();
    _setMouseHandlers();
  };



  /* ==================== EXECUTION ==================== */

  // TEMP
  $scope.view.authorized = true;
  $scope.view.valid = true;
  $scope.view.onLoad = false;

}]);