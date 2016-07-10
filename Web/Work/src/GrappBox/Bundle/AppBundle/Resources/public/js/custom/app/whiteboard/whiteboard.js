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
app.controller("whiteboardController", ["$rootScope", "$scope", "$route", "whiteboardFactory", "$http", "$location", "Notification", "$q", "$interval",
    function($rootScope, $scope, $route, whiteboardFactory, $http, $location, Notification, $q, $interval) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { onLoad: true, valid: false, authorized: false };
  $scope.data = { id: $route.current.params.id, project_id: $route.current.params.project_id, name: "", creator: "" };
  $scope.whiteboard = { canvas: {}, objects: {}, points: [], fullscreen: false, wrapper: "" };
  $scope.pull = { date: "", add: "", delete: "" };
  $scope.action = { setWhiteboard: "", undoLastAction: "", resetTool: "", toggleFullscreen: "" };

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
      color: (!color ? "none" : color),
      points: points
    };
  };

  // Routine definition (local)
  // Create new line render object
  var _newLine = function(size, color, start_x, start_y, end_x, end_y) {
    return data = {
      tool: "line",
      size: Number(size),
      color: (!color ? "none" : color),
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
      color: (!color ? "none" : color),
      fill: (!fill ? "none" : fill),
      start_x: start_x,
      start_y: start_y,
      height: end_y - start_y,
      width: end_x - start_x
    };
  };

  // Routine definition (local)
  // Create new diamond render object
  var _newDiamond = function(API, size, color, fill, start_x, start_y, end_x, end_y) {
    return data = {
      API: API,
      tool: "diamond",
      size: Number(size),
      color: (!color ? "none" : color),
      fill: (!fill ? "none" : fill),
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
  var _newEllipse = function(API, size, color, fill, start_x, start_y, end_x, end_y) {
    return data = {
      API: API,
      tool: "ellipse",
      size: Number(size),
      color: (!color ? "none" : color),
      fill: (!fill ? "none" : fill),
      start_x: start_x,
      start_y: start_y,
      radius_x: (Math.abs((end_x - start_x) / 2)),
      radius_y: (Math.abs((end_y - start_y)  / 2))
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
      color: (!color ? "none" : color)
    };
  };

  // Routine definition (local)
  // Create/compile canvas data to render (from user input)
  var _setRenderObject = function() {
    switch ($scope.selected.tool) {
      case "pencil":
      var data = _newPencil(
        $scope.selected.size.value,
        $scope.selected.color.value,
        $scope.whiteboard.points
        );
      break;

      case "line":
      var data = _newLine(
        $scope.selected.size.value,
        $scope.selected.color.value,
        $scope.mouse.start.x,
        $scope.mouse.start.y,
        $scope.mouse.end.x,
        $scope.mouse.end.y
        );
      break;

      case "rectangle":
      var data = _newRectange(
        $scope.selected.size.value,
        $scope.selected.color.value,
        $scope.selected.fill.value,
        $scope.mouse.start.x,
        $scope.mouse.start.y,
        $scope.mouse.end.x,
        $scope.mouse.end.y
        );
      break;

      case "diamond":
      var data = _newDiamond(
        false,
        $scope.selected.size.value,
        $scope.selected.color.value,
        $scope.selected.fill.value,
        $scope.mouse.start.x,
        $scope.mouse.start.y,
        $scope.mouse.end.x,
        $scope.mouse.end.y
        );
      break;

      case "ellipse":
      var data = _newEllipse(
        false,
        $scope.selected.size.value,
        $scope.selected.color.value,
        $scope.selected.fill.value,
        $scope.mouse.start.x,
        $scope.mouse.start.y,
        $scope.mouse.end.x,
        $scope.mouse.end.y
        );
      break;

      case "text":
      var data = _newText(
        $scope.text.font.value,
        $scope.text.italic,
        $scope.text.bold,
        $scope.text.value,
        $scope.mouse.start.x,
        $scope.mouse.start.y,
        $scope.selected.color.value
        );
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
      var data = _newPencil(
        object.lineweight,
        object.color,
        object.points
        );
      break;

      case "LINE":
      var data = _newLine(
        object.lineweight,
        object.color,
        object.positionStart.x,
        object.positionStart.y,
        object.positionEnd.x,
        object.positionEnd.y
        );
      break;

      case "RECTANGLE":
      var data = _newRectange(
        object.lineweight,
        object.color,
        object.background,
        object.positionStart.x,
        object.positionStart.y,
        object.positionEnd.x,
        object.positionEnd.y
        );
      break;

      case "DIAMOND":
      var data = _newDiamond(
        true,
        object.lineweight,
        object.color,
        object.background,
        (object.positionStart.x < object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
        (object.positionStart.y < object.positionEnd.y ? object.positionStart.y : object.positionEnd.y),
        (object.positionStart.x > object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
        (object.positionStart.y > object.positionEnd.y ? object.positionStart.y : object.positionEnd.y)
        );
      break;

      case "ELLIPSE":
      var data = _newEllipse(
        true,
        object.lineweight,
        object.color,
        object.background,
        (object.positionStart.x < object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
        (object.positionStart.y < object.positionEnd.y ? object.positionStart.y : object.positionEnd.y),
        (object.positionStart.x > object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
        (object.positionStart.y > object.positionEnd.y ? object.positionStart.y : object.positionEnd.y)
        );
      break;

      case "TEXT":
      var data = _newText(
        object.size,
        object.isItalic,
        object.isBold,
        object.text,
        object.positionStart.x,
        object.positionStart.y,
        object.color
        );
      break;

      default:
      var data = {};
      break;
    }

    return data;
  };

  // Routine definition (local)
  // Render/display canvas data using whiteboardFactory (from user input)
  var _renderObject = function(data) {
    if (data.tool === "line" || data.tool === "rectangle" || data.tool === "diamond" || data.tool === "ellipse")
      whiteboardFactory.renderCanvasBuffer();
    whiteboardFactory.renderObject(data);
  };

  // Routine definition (local)
  // Render/display canvas data using whiteboardFactory (from API)
  var _renderObjectFromAPI = function(data) {
    whiteboardFactory.renderObject(data);
  };

  // Routine definition (local)
  // Handle page fullscreen changes
  var _onFullscreenChange = function() {
    if (document.webkitIsFullScreen || document.mozFullScreen || document.msFullscreenElement)
      $scope.whiteboard.fullscreen = true;
    else if (!document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement)
      $scope.whiteboard.fullscreen = false;
  };

  // Routine definition (local)
  // Set whiteboard canvas and context
  var _setCanvas = function() {
    $scope.whiteboard.wrapper = document.getElementById("whiteboard-wrapper");

    document.addEventListener('webkitfullscreenchange', _onFullscreenChange(), false);
    document.addEventListener('mozfullscreenchange', _onFullscreenChange(), false);
    document.addEventListener('msfullscreenchange', _onFullscreenChange(), false);
    document.addEventListener('fullscreenchange', _onFullscreenChange(), false);

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
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/whiteboard/open/" + $rootScope.user.token + "/" + $scope.data.id).then(
      function onGetWhiteboardSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            $scope.data.objects = (response.data.data.content ? response.data.data.content : null);
            $scope.data.name = response.data.data.name;
            $scope.data.creator = response.data.data.user.firstname + " " + response.data.data.user.lastname;
            $scope.pull.date = new Date();

            $scope.action.setWhiteboard();
            angular.forEach($scope.data.objects, function(value, key) {
              var data = _setRenderObjectFromAPI(value.object);
              whiteboardFactory.addToCanvasBuffer(data);
              _renderObjectFromAPI(data);
            });
            deferred.resolve();
            break;

            default:
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            deferred.reject();
            break;
          }
        }
        else {
          $scope.whiteboards.list = null;
          $scope.view.valid = false;
          $scope.view.onLoad = false;
          $scope.view.authorized = true;
          deferred.reject();
        }
        return deferred.promise;
      },
      function onGetWhiteboardFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "10.3.3":
            $rootScope.onUserTokenError();
            deferred.reject();
            break;

            case "10.3.9":
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = false;
            deferred.reject();
            break;

            case "10.3.4":
            $location.path("whiteboard/" + $route.current.params.project_id);
            Notification.warning({ title: "Whiteboard", message: "This whiteboard has been deleted.", delay: 4500 });
            deferred.reject();
            break;

            default:
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            deferred.reject();
            break;
          }
        }
        else {
          $scope.whiteboards.list = null;
          $scope.view.valid = false;
          $scope.view.onLoad = false;
          $scope.view.authorized = true;
          deferred.reject();
        }
        return deferred.promise;
      }
    );
    return deferred.promise;
  };

  // Routine definition (local)
  // Pull whiteboard modifications
  var _pull = function() {
    $http.post($rootScope.api.url + "/whiteboard/pulldraw/" + $scope.data.id,
      { data: { token: $rootScope.user.token, lastUpdate: $scope.pull.date }}).then(
      function onPostWhiteboardUpdateSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            $scope.pull.add = (response.data.data.add ? response.data.data.add : null);

            angular.forEach($scope.pull.add, function(value, key) {
              var data = _setRenderObjectFromAPI(value.object);
              whiteboardFactory.addToCanvasBuffer(data);
              _renderObjectFromAPI(data);
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
      function onPostWhiteboardUpdateFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "10.5.3":
            $rootScope.onUserTokenError();
            break;

            case "10.5.9":
            $scope.whiteboards.list = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = false;
            break;

            case "10.5.4":
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

  // "Fullscreen" button handler
  $scope.action.toggleFullscreen = function() {
    if (!$scope.whiteboard.fullscreen) {
      if ($scope.whiteboard.wrapper.requestFullscreen)
        $scope.whiteboard.wrapper.requestFullscreen();
      else if ($scope.whiteboard.wrapper.webkitRequestFullscreen)
        $scope.whiteboard.wrapper.webkitRequestFullscreen();
      else if ($scope.whiteboard.wrapper.mozRequestFullScreen)
        $scope.whiteboard.wrapper.mozRequestFullScreen();
      else if ($scope.whiteboard.wrapper.msRequestFullscreen)
        $scope.whiteboard.wrapper.msRequestFullscreen();
      $scope.whiteboard.fullscreen = true;
    }
    else {
      if (document.exitFullscreen)
        document.exitFullscreen();
      else if (document.webkitExitFullscreen)
        document.webkitExitFullscreen();
      else if (document.mozCancelFullScreen)
        document.mozCancelFullScreen();
      else if (document.msExitFullscreen)
        document.msExitFullscreen();
      $scope.whiteboard.fullscreen = false;
    }
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

  var openWhiteboard = _openWhiteboard();
  openWhiteboard.then(
    function onOpenWhiteboardSuccess() {
      $interval(_pull , 1000);
    },
    function onOpenWhiteboardFail() {
    }
  );


}]);