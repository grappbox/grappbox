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
app.controller("WhiteboardController", ["$http", "$interval", "$location", "moment", "notificationFactory", "$q", "$rootScope", "$route", "$scope", "whiteboardObjectFactory", "whiteboardRenderFactory",
    function($http, $interval, $location, moment, notificationFactory, $q, $rootScope, $route, $scope, whiteboardObjectFactory, whiteboardRenderFactory) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { onLoad: true, valid: false, authorized: false };
  $scope.data = { id: $route.current.params.id, project_id: $route.current.params.project_id, name: "", creator: "" };
  $scope.whiteboard = { canvas: {}, objects: [], points: [], fullscreen: false, wrapper: "" };
  $scope.pull = { date: "", add: {}, delete: {}, interval: "", time: 2 };
  $scope.push = { date: "" };
  $scope.action = { resetTool: "", toggleFullscreen: "" };

  $scope.mouse = { start: { x: 0, y: 0 }, end: { x: 0, y: 0 }, pressed: false };
  $scope.text = { value: "", italic: false, bold: false, size: { label: "24 pt", value: "24" } };

  $scope.colors = [
    { name: "-None-", value: null },
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

  $scope.size = [
    { label: "8 pt", value: "8" },
    { label: "9 pt", value: "9" },
    { label: "10 pt", value: "10" },
    { label: "11 pt", value: "11" },
    { label: "12 pt", value: "12" },
    { label: "14 pt", value: "14" },
    { label: "18 pt", value: "18" },
    { label: "24 pt", value: "24" },
    { label: "30 pt", value: "30" },
    { label: "36 pt", value: "36" },
    { label: "48 pt", value: "48" },
    { label: "60 pt", value: "60" },
    { label: "72 pt", value: "72" },
    { label: "96 pt", value: "96" }
  ];

  $scope.thickness = [
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
    background: { name: "-None-", value: null },
    thickness: { label: "1 pt", value: "1" },
    tool: null
  };



  /* ==================== SETUP ==================== */

  // Routine definition (setup)
  // Handle page fullscreen changes
  var _onFullscreenChange = function() {
    if (document.webkitIsFullScreen || document.mozFullScreen || document.msFullscreenElement)
      $scope.whiteboard.fullscreen = true;
    else if (!document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement)
      $scope.whiteboard.fullscreen = false;
  };

  // Routine definition (setup)
  // Set whiteboard canvas and context
  var _setCanvas = function() {
    $scope.whiteboard.wrapper = document.getElementById("whiteboard-wrapper");

    document.addEventListener('webkitfullscreenchange', _onFullscreenChange(), false);
    document.addEventListener('mozfullscreenchange', _onFullscreenChange(), false);
    document.addEventListener('msfullscreenchange', _onFullscreenChange(), false);
    document.addEventListener('fullscreenchange', _onFullscreenChange(), false);

    $scope.whiteboard.canvas = document.getElementById("whiteboard-canvas");
    whiteboardRenderFactory.setCanvas($scope.whiteboard.canvas);
    whiteboardRenderFactory.setCanvasContext($scope.whiteboard.canvas.getContext("2d"));
    whiteboardRenderFactory.clearCanvasBuffer();
  };

  // Routine definition (setup)
  // Set whiteboard mouse handlers
  var _setMouseHandlers = function() {
    // Canvas default callback: mouse pressed
    $scope.whiteboard.canvas.onmousedown = function(event) {
      if ($scope.selected.tool) {
        $scope.mouse.pressed = true;
        $scope.mouse.start.x = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].x : event.offsetX);
        $scope.mouse.start.y = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].y : event.offsetY);
        $scope.mouse.end.x = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].x : event.offsetX);
        $scope.mouse.end.y = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].y : event.offsetY);
        $scope.whiteboard.points.push({ x: event.offsetX, y: event.offsetY, color: $scope.selected.color.value });
        if ($scope.selected.tool != "eraser")
          _renderObject(whiteboardObjectFactory.setRenderObject(0, $scope));
      }
    };

    // Canvas default callback: mouse drag
    $scope.whiteboard.canvas.onmousemove = function(event) {
      if ($scope.selected.tool) {
        if ($scope.mouse.pressed) {
          var last = $scope.whiteboard.points[$scope.whiteboard.points.length - 1];
          $scope.whiteboard.points.push({ x: event.offsetX, y: event.offsetY, color: $scope.selected.color.value });
          $scope.mouse.end.x = last.x;
          $scope.mouse.end.y = last.y;
          if ($scope.selected.tool != "eraser")
            _renderObject(whiteboardObjectFactory.setRenderObject(0, $scope));
        }
      }
    };

    // Canvas default callback: mouse release
    $scope.whiteboard.canvas.onmouseup = function() {
      if ($scope.selected.tool) {
        if ($scope.selected.tool != "eraser") {
          whiteboardRenderFactory.addToCanvasBuffer(whiteboardObjectFactory.setRenderObject(0, $scope));
          _push(whiteboardObjectFactory.convertToAPIObject($scope));
        }
        else
          _erase($scope);
        $scope.mouse.pressed = false;
        $scope.whiteboard.points = [];
        $scope.mouse.start.x = 0;
        $scope.mouse.start.y = 0;
        $scope.mouse.end.x = 0;
        $scope.mouse.start.y = 0;
      }
    };
  };



  /* ==================== ROUTINES (LOCAL) ==================== */
  
  // Routine definition (local)
  // Render/display canvas data using whiteboardRenderFactory
  var _renderObject = function(data) {
    if (data.tool === "line" || data.tool === "rectangle" || data.tool === "diamond" || data.tool === "ellipse")
      whiteboardRenderFactory.renderCanvasBuffer();
    whiteboardRenderFactory.renderObject(data);
  };

  // Routine definition (local)
  // Open whiteboard and get content for first use
  var _openWhiteboard = function() {
    var deferred = $q.defer();

    $http.get($rootScope.api.url + "/whiteboard/" + $scope.data.id, { headers: { 'Authorization': $rootScope.user.token }}).then(
      function onGetWhiteboardSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            _setCanvas();
            _setMouseHandlers();

            $scope.whiteboard.objects = [];
            whiteboardRenderFactory.clearCanvasBuffer();
            whiteboardRenderFactory.clearCanvas();

            $scope.data.name = response.data.data.name;
            $scope.data.creator = response.data.data.user.firstname + " " + response.data.data.user.lastname;
            $scope.pull.date = moment().format("YYYY-MM-DD HH:mm:ss");
            moment($scope.pull.date).subtract($scope.pull.time, 'seconds');

            if (response.data.data.content)
            angular.forEach(response.data.data.content, function(value, key) {
              var data = whiteboardObjectFactory.convertToLocalObject(value.id, value.object);
              this.whiteboard.objects.push(data);
              whiteboardRenderFactory.addToCanvasBuffer(data);
              whiteboardRenderFactory.renderObject(data);
            }, $scope);

            deferred.resolve();
            break;

            default:
            $scope.whiteboards.objects = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            deferred.reject();
            break;
          }
        }
        else {
          $scope.whiteboards.objects = null;
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
            $scope.whiteboards.objects = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = false;
            deferred.reject();
            break;

            case "10.3.4":
            $location.path("whiteboard/" + $route.  current.params.project_id);
            notificationFactory.warning("This whiteboard has been deleted.");
            deferred.reject();
            break;

            default:
            $scope.whiteboards.objects = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            deferred.reject();
            break;
          }
        }
        else {
          $scope.whiteboards.objects = null;
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
    $http.post($rootScope.api.url + "/whiteboard/draw/" + $scope.data.id,
      { data: { lastUpdate: $scope.pull.date }}, { headers: { 'Authorization': $rootScope.user.token }}).then(
      function onPostWhiteboardUpdateSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            $scope.pull.add = (response.data.data.add ? response.data.data.add : null);
            $scope.pull.delete = (response.data.data.delete ? response.data.data.delete : null);
            $scope.pull.date = moment().format("YYYY-MM-DD HH:mm:ss");
            moment($scope.pull.date).subtract($scope.pull.time, 'seconds');

            angular.forEach($scope.pull.add, function(value, key) {
              var data = whiteboardObjectFactory.convertToLocalObject(value.id, value.object);
              $scope.whiteboard.objects.push(data);
              whiteboardRenderFactory.addToCanvasBuffer(data);
              whiteboardRenderFactory.renderObject(data);
            });
            angular.forEach($scope.pull.delete, function(value, key) {
              for (i = 0; i < $scope.whiteboard.objects.length; ++i)
                if ($scope.whiteboard.objects[i].id == value.id || $scope.whiteboard.objects[i].id == 0 || $scope.whiteboard.objects[i].with <= 0 || $scope.whiteboard.objects[i].height <= 0) {
                  $scope.whiteboard.objects.splice(i, 1);
                  whiteboardRenderFactory.setCanvasBuffer($scope.whiteboard.objects);
                  whiteboardRenderFactory.renderCanvasBuffer();
                }
            });            
            break;

            default:
            $scope.whiteboards.objects = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            break;
          }
        }
        else {
          $scope.whiteboards.objects = null;
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
            $scope.whiteboards.objects = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = false;
            break;

            case "10.5.4":
            $location.path("whiteboard/" + $route.current.params.project_id);
            notificationFactory.warning("This whiteboard has been deleted.");
            break;

            default:
            $scope.whiteboards.objects = null;
            $scope.view.valid = false;
            $scope.view.onLoad = false;
            $scope.view.authorized = true;
            break;
          }
        }
        else {
          $scope.whiteboards.objects = null;
          $scope.view.valid = false;
          $scope.view.onLoad = false;
          $scope.view.authorized = true;
        }
      }
    );
  };

  // Routine definition (local)
  // Push whiteboard modifications
  var _push = function(object) {
    if (object)
      $http.put($rootScope.api.url + "/whiteboard/draw/" + $scope.data.id, { data: { object: object }}, { headers: { 'Authorization': $rootScope.user.token }}).then(
        function onWhiteboardPushSuccess(response) {
          if (response.data.info) {
            switch(response.data.info.return_code) {
              case "1.10.1":
              var data = whiteboardObjectFactory.convertToLocalObject(response.data.data.id, response.data.data.object);
              $scope.whiteboard.objects.push(data);
              whiteboardRenderFactory.addToCanvasBuffer(data);
              whiteboardRenderFactory.renderObject(data);            
              break;

              default:
              notificationFactory.error();
              break;
            }
          }
          else
            notificationFactory.error();
        },
        function onWhiteboardPushFail(response) {
          if (response.data.info) {
            switch(response.data.info.return_code) {
              case "10.4.3":
              $rootScope.onUserTokenError();
              break;

              case "10.4.9":
              notificationFactory.warning("You don't have sufficient rights to perform this operation.");
              break;

              default:
              notificationFactory.error();
              break;
            }
          }
          else
            notificationFactory.error();
        }
      );
  };

  // Routine definition (local)
  // Erase object
  var _erase = function(scope) {
    $http.delete($rootScope.api.url + "/whiteboard/object/" + $scope.data.id,
    { data: { data: { radius: 30, center: { x: (scope.mouse.start.x + scope.mouse.end.x) / 2, y: (scope.mouse.start.y + scope.mouse.end.y) / 2 }}},
    headers: { 'Authorization': $rootScope.user.token }}).then(
      function onWhiteboardEraseSuccess(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            for (i = 0; i < $scope.whiteboard.objects.length; ++i)
              if ($scope.whiteboard.objects[i].id == response.data.data.id || $scope.whiteboard.objects[i].id == 0 || $scope.whiteboard.objects[i].with <= 0 || $scope.whiteboard.objects[i].height <= 0) {
                $scope.whiteboard.objects.splice(i, 1);
                whiteboardRenderFactory.setCanvasBuffer($scope.whiteboard.objects);
                whiteboardRenderFactory.renderCanvasBuffer();
              }
            break;

            case "1.10.3":
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else
          notificationFactory.error();
      },
      function onWhiteboardEraseFail(response) {
        if (response.data.info) {
          switch(response.data.info.return_code) {
            case "10.4.3":
            $rootScope.onUserTokenError();
            break;

            case "10.4.9":
            notificationFactory.warning("You don\'t have sufficient rights to perform this operation.");
            break;

            default:
            notificationFactory.error();
            break;
          }
        }
        else
          notificationFactory.error();
      }
    );
  };


  /* ==================== SCOPE ROUTINES ==================== */

  // "Deselect" button handler
  $scope.action.resetTool = function() {
    $scope.selected.tool = null;
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



  /* ==================== EXECUTION ==================== */

  $scope.view.valid = true;
  $scope.view.onLoad = false;
  $scope.view.authorized = true;

  var openWhiteboard = _openWhiteboard();
  openWhiteboard.then(
    function onOpenWhiteboardSuccess() {
      $scope.pull.interval = $interval(_openWhiteboard, ($scope.pull.time * 1000));
    },
    function onOpenWhiteboardFail() { }
  );

  // Stop pull interval on route change
  $scope.$on('$destroy',function() {
    if($scope.pull.interval)
      $interval.cancel($scope.pull.interval);   
  });

}]);