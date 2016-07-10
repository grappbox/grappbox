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
app.controller("whiteboardController", ["$rootScope", "$scope", "$route", "whiteboardFactory", "$http", "$location", "Notification", function($rootScope, $scope, $route, whiteboardFactory, $http, $location, Notification) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { onLoad: true, valid: false, authorized: false };
  $scope.data = { id: $route.current.params.id, project_id: $route.current.params.project_id, name: "", creator: "" };
  $scope.whiteboard = { canvas: {}, objects: {}, points: [] };
  $scope.action = { setWhiteboard: "", undoLastAction: "", resetTool: "" };

  $scope.mouse = { start: { x: 0, y: 0 }, end: { x: 0, y: 0 }, pressed: false };
  $scope.text = { value: "", italic: false, bold: false, font: { label: "24", value: "24pt" } };

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

  $scope.fonts = [
    { label: "8 pt", value: "8pt" },
    { label: "9 pt", value: "9pt" },
    { label: "10 pt", value: "10pt" },
    { label: "11 pt", value: "11pt" },
    { label: "12 pt", value: "12pt" },
    { label: "14 pt", value: "14pt" },
    { label: "18 pt", value: "18pt" },
    { label: "24 pt", value: "24pt" },
    { label: "30 pt", value: "30pt" },
    { label: "36 pt", value: "36pt" },
    { label: "48 pt", value: "48pt" },
    { label: "60 pt", value: "60pt" },
    { label: "72 pt", value: "72pt" },
    { label: "96 pt", value: "96pt" }
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
    tool: "none"
  };



  /* ==================== LOCAL ROUTINES ==================== */

  // Routine definition (local)
  // Create new pencil render object
  var _newPencil = function(size, color, points) {
    return data = {
      tool: "pencil",
      size: Number(size),
      color: color,
      points: points
    };
  };

  // Routine definition (local)
  // Create new line render object
  var _newLine = function(size, color, start_x, start_y, end_x, end_y) {
    return data = {
      tool: "line",
      size: Number(size),
      color: color,
      start_x: start_x,
      start_y: start_y,
      end_x: end_x,
      end_y: end_y
    };
  };

  // Routine definition (local)
  // Create new rectangle render object
  var _newRectange = function(size, color, fill, start_x, start_y, end_x, end_y) {
    return data = {
      tool: "rectangle",
      size: Number(size),
      color: color,
      fill: fill,
      start_x: start_x,
      start_y: start_y,
      height: end_y - start_y,
      width: end_x - start_x
    };
  };

  // Routine definition (local)
  // Create new diamond render object
  var _newDiamond = function(size, color, fill, start_x, start_y, end_x, end_y) {
    return data = {
      tool: "diamond",
      size: Number(size),
      color: color,
      fill: fill,
      start_x: start_x,
      start_y: start_y,
      end_x: end_x,
      end_y: end_y,
      height: end_y - start_y,
      width: end_x - start_x
    };
  };

  // Routine definition (local)
  // Create new ellipse render object
  var _newEllipse = function(size, color, fill, start_x, start_y, end_x, end_y) {
    return data = {
      tool: "ellipse",
      size: Number(size),
      color: color,
      fill: fill,
      start_x: start_x,
      start_y: start_y,
      radius_x: (Math.abs(end_x - start_x)),
      radius_y: (Math.abs(end_y - start_y))
    };
  };

  // Routine definition (local)
  // Create new text render object
  var _newText = function(font, italic, bold, value, start_x, start_y, color) {
    return data = {
      tool: "text",
      font: font + " Roboto",
      italic: italic,
      bold: bold,
      value: value,
      start_x: start_x,
      start_y: start_y,
      color: color
    };
  };

  // Routine definition (local)
  // Create/compile canvas data to render (from user input)
  var _setRenderObject = function() {
    switch ($scope.selected.tool) {
      case "pencil":
      var data = _newPencil($scope.selected.size.value, $scope.selected.color.value, $scope.whiteboard.points);
      break;

      case "line":
      var data = _newLine($scope.selected.size.value, $scope.selected.color.value, $scope.mouse.start.x, $scope.mouse.start.y, $scope.mouse.end.x, $scope.mouse.end.y);
      break;

      case "rectangle":
      var data = _newRectange($scope.selected.size.value, $scope.selected.color.value, $scope.selected.fill.value, $scope.mouse.start.x, $scope.mouse.start.y, $scope.mouse.end.x, $scope.mouse.end.y);
      break;

      case "diamond":
      var data = _newDiamond($scope.selected.size.value, $scope.selected.color.value, $scope.selected.fill.value, $scope.mouse.start.x, $scope.mouse.start.y, $scope.mouse.end.x, $scope.mouse.end.y);
      break;

      case "ellipse":
      var data = _newEllipse($scope.selected.size.value, $scope.selected.color.value, $scope.selected.fill.value, $scope.mouse.start.x, $scope.mouse.start.y, $scope.mouse.end.x, $scope.mouse.end.y);
      break;

      case "text":
      var data = _newText($scope.text.font.value, $scope.text.italic, $scope.text.bold, $scope.text.value, $scope.mouse.start.x, $scope.mouse.start.y, $scope.selected.color.value);
      break;

      default:
      var data = {};
      break;
    }

    return data;
  };

  // Routine definition (local)
  // Create/compile canvas data to render (from API)
  var _setRenderObjectFromAPI = function(object) {
    switch (object.type) {
      case "HANDWRITE":
      var data = _newPencil(object.lineWeight, object.color, object.points);
      break;

      case "LINE":
      var data = _newLine(object.lineWeight, object.color, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
      break;

      case "RECTANGLE":
      var data = _newRectange(object.lineWeight, object.color, object.background, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
      break;

      case "DIAMOND":
      var data = _newDiamond(object.lineWeight, object.color, object.background, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
      break;

      case "ELLIPSE":
      var data = _newEllipse(object.lineWeight, object.color, object.background, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
      break;

      case "TEXT":
      var data = _newText(object.size, object.isItalic, object.isBold, object.text, object.positionStart.x, object.positionStart.y, object.color);
      break;

      default:
      var data = {};
      break;
    }

    return data;
  };

  // Routine definition (local)
  // Render/display canvas data using whiteboardFactory
  var _renderObject = function(data) {
    if (data.tool === "line" || data.tool === "rectangle" || data.tool === "diamond" || data.tool === "ellipse")
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
      if ($scope.mouse.pressed) {
        var last = $scope.whiteboard.points[$scope.whiteboard.points.length - 1];
        $scope.whiteboard.points.push({
          x: event.offsetX,
          y: event.offsetY,
          color: $scope.selected.color.value
        });
        $scope.mouse.end.x = last.x;
        $scope.mouse.end.y = last.y;
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

  // Routine definition (local)
  // Open whiteboard and get content for first use
  var _openWhiteboard = function() {
    $http.get($rootScope.api.url + "/whiteboard/open/" + $rootScope.user.token + "/" + $scope.data.id).then(
      function onGetWhiteboardSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            $scope.data.objects = (response.data.data.content ? response.data.data.content : null);
            $scope.data.name = response.data.data.name;
            $scope.data.creator = response.data.data.user.firstname + " " + response.data.data.user.lastname;

            $scope.action.setWhiteboard();
            angular.forEach($scope.data.objects, function(value, key) {
              _renderObject(_setRenderObjectFromAPI(value.object));
            });
            break;

            default:
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            break;
          }
        }
        else {
          $scope.whiteboards.list = null;
          $scope.view.valid = false;
          $scope.view.onLoad = false;
          $scope.view.authorized = true;
        }
      },
      function onGetWhiteboardFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "10.3.3":
            $rootScope.onUserTokenError();
            break;

            case "10.3.9":
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = false;
            break;

            case "10.3.4":
            $location.path("whiteboard/" + $route.current.params.project_id);
            Notification.warning({ title: "Whiteboard", message: "This whiteboard has been deleted.", delay: 4500 });
            break;

            default:
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            break;
          }
        }
        else {
          $scope.whiteboards.list = null;
          $scope.view.valid = false;
          $scope.view.onLoad = false;
          $scope.view.authorized = true;
        }
      }
    );
  };

  // Routine definition (local)
  // Pull whiteboard modifications
  var _pull = function() {

  };

  // Routine definition (local)
  // Push whiteboard modifications
  var _push = function() {

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

  // "Deselect" button handler
  $scope.action.resetTool = function() {
    $scope.selected.tool = "none";
    $scope.text.italic = false;
    $scope.text.bold = false;
  };

  // Initialize whiteboard on launch
  $scope.action.setWhiteboard = function() {
    _setCanvas();
    _setMouseHandlers();
  };



  /* ==================== EXECUTION ==================== */

  $scope.view.valid = true;
  $scope.view.onLoad = false;
  $scope.view.authorized = true;

  _openWhiteboard();


}]);