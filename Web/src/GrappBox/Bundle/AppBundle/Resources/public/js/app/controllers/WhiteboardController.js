/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP whiteboard
app.controller("WhiteboardController", ["accessFactory", "$http", "$interval", "$location", "moment", "notificationFactory", "$q", "$rootScope", "$route", "$scope", "whiteboardObjectFactory", "whiteboardRenderFactory", "$uibModal",
    function(accessFactory, $http, $interval, $location, moment, notificationFactory, $q, $rootScope, $route, $scope, whiteboardObjectFactory, whiteboardRenderFactory, $uibModal) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { loaded: false, valid: false, authorized: false };
  $scope.route = { whiteboard_id: $route.current.params.id, project_id: $route.current.params.project_id };
  $scope.whiteboard = { objects: [], points: [], pull: {}, push: {}, name: "", creator: { id:"", firstname: "", lastname: "" }, date: "", delete: "" };
  $scope.canvas = { mouse: {}, element: "", wrapper: "", fullscreen: false, toggle: "", identifier: "" }
  $scope.tool = { colors: [], sizes: [], thicknesses: [], selected: {}, reset: "" }

  $scope.whiteboard.pull = { date: "", add: {}, delete: {}, interval: "", time: 2 };
  $scope.whiteboard.push = { date: "" };
  $scope.canvas.mouse = { start: { x: 0, y: 0 }, end: { x: 0, y: 0 }, pressed: false };

  $scope.tool.selected = {
    shape: { color: "", background: "", thickness: { label: "0.5 pt", value: "0.5" } },
    text: { value: "", italic: false, bold: false, size: { label: "11 pt", value: "11" } },
    name: null
  };

  $scope.tool.colors = [
    { name: "-None-", value: null , style: { "background-color": "none", "color": "#212121" } },
    { name: "Red", value: "#F44336", style: { "background-color": "#F44336", "color": "#fff" } },
    { name: "Pink", value: "#E91E63", style: { "background-color": "#E91E63", "color": "#fff" } },
    { name: "Purple", value: "#9C27B0", style: { "background-color": "#9C27B0", "color": "#fff" }  },
    { name: "Deep Purple", value: "#673AB7", style: { "background-color": "#673AB7", "color": "#fff" } },
    { name: "Indigo", value: "#3F51B5", style: { "background-color": "#3F51B5", "color": "#fff" } },
    { name: "Blue", value: "#2196F3", style: { "background-color": "#2196F3", "color": "#fff" } },
    { name: "Light Blue", value: "#03A9F4", style: { "background-color": "#03A9F4", "color": "#fff" } },
    { name: "Cyan", value: "#00BCD4", style: { "background-color": "#00BCD4", "color": "#fff" } },
    { name: "Teal", value: "#009688", style: { "background-color": "#009688", "color": "#fff" } },
    { name: "Green", value: "#4CAF50", style: { "background-color": "#4CAF50", "color": "#fff" } },
    { name: "Light Green", value: "#8BC34A", style: { "background-color": "#8BC34A", "color": "#fff" } },
    { name: "Lime", value: "#CDDC39", style: { "background-color": "#CDDC39", "color": "#212121" } },
    { name: "Yellow", value: "#FFEB3B", style: { "background-color": "#FFEB3B", "color": "#212121" }  },
    { name: "Amber", value: "#FFC107", style: { "background-color": "#FFC107", "color": "#212121" } },
    { name: "Orange", value: "#FF9800", style: { "background-color": "#FF9800", "color": "#fff" } },
    { name: "Deep Orange", value: "#FF5722", style: { "background-color": "#FF5722", "color": "#fff" } },
    { name: "Brown", value: "#795548", style: { "background-color": "#795548", "color": "#fff" } },
    { name: "Blue Grey", value: "#607D8B", style: { "background-color": "#607D8B", "color": "#fff" } },
    { name: "White", value: "#FFFFFF", style: { "background-color": "#FFFFFF", "color": "#212121" } },
    { name: "Grey 20%", value: "#EEEEEE", style: { "background-color": "#EEEEEE", "color": "#212121" } },
    { name: "Grey 40%", value: "#BDBDBD", style: { "background-color": "#BDBDBD", "color": "#212121" } },
    { name: "Grey 50%", value: "#9E9E9E", style: { "background-color": "#9E9E9E", "color": "#fff" } },
    { name: "Grey 60%", value: "#757575", style: { "background-color": "#757575", "color": "#fff" } },
    { name: "Grey 80%", value: "#424242", style: { "background-color": "#424242", "color": "#fff" } },
    { name: "Black", value: "#000000", style: { "background-color": "#000000", "color": "#fff" } }
  ];

  $scope.tool.sizes = [
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

  $scope.tool.thicknesses = [
    { label: "0.5 pt", value: "0.5" },
    { label: "1 pt", value: "1" },
    { label: "1.5 pt", value: "1.5" },
    { label: "2 pt", value: "2" },
    { label: "2.5 pt", value: "2.5" },
    { label: "3 pt", value: "3" },
    { label: "4 pt", value: "4" },
    { label: "5 pt", value: "5" }
  ];



  /* ==================== SETUP ==================== */

  // Routine definition (setup)
  // Handle page fullscreen changes
  var _onFullscreenChange = function() {
    if (document.webkitIsFullScreen || document.mozFullScreen || document.msFullscreenElement)
      $scope.canvas.fullscreen = true;
    else if (!document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement)
      $scope.canvas.fullscreen = false;
  };

  // Routine definition (setup)
  // Set whiteboard canvas and context
  var _setCanvas = function() {
    $scope.whiteboard.wrapper = document.getElementById("wrapper-" + $scope.canvas.identifier);

    document.addEventListener('webkitfullscreenchange', _onFullscreenChange(), false);
    document.addEventListener('mozfullscreenchange', _onFullscreenChange(), false);
    document.addEventListener('msfullscreenchange', _onFullscreenChange(), false);
    document.addEventListener('fullscreenchange', _onFullscreenChange(), false);

    $scope.canvas.element = document.getElementById("canvas-" + $scope.canvas.identifier);
    whiteboardRenderFactory.setCanvas($scope.canvas.element);
    whiteboardRenderFactory.setCanvasContext($scope.canvas.element.getContext("2d"));
    whiteboardRenderFactory.clearCanvasBuffer();
  };

  // Routine definition (setup)
  // Set whiteboard mouse handlers
  var _setMouseHandlers = function() {
    // Canvas default callback: mouse pressed
    $scope.canvas.element.onmousedown = function(event) {
      if ($scope.tool.selected.name) {
        $scope.canvas.mouse.pressed = true;
        if ($scope.tool.selected.name != "pencil") {
          $scope.canvas.mouse.start.x = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].x : event.offsetX);
          $scope.canvas.mouse.start.y = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].y : event.offsetY);
          $scope.canvas.mouse.end.x = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].x : event.offsetX);
          $scope.canvas.mouse.end.y = ($scope.whiteboard.points[0] ? $scope.whiteboard.points[0].y : event.offsetY);
        }
        $scope.whiteboard.points.push({ x: event.offsetX, y: event.offsetY, color: $scope.tool.selected.color });
        if ($scope.tool.selected.name != "eraser")
          _renderObject(whiteboardObjectFactory.setRenderObject(0, $scope.tool.selected, $scope.whiteboard.points, $scope.canvas.mouse));
      }
    };

    // Canvas default callback: mouse drag
    $scope.canvas.element.onmousemove = function(event) {
      if ($scope.tool.selected.name) {
        if ($scope.canvas.mouse.pressed) {
          var last = $scope.whiteboard.points[$scope.whiteboard.points.length - 1];
          $scope.whiteboard.points.push({ x: event.offsetX, y: event.offsetY, color: $scope.tool.selected.color });
          if ($scope.tool.selected.name != "pencil") {
            $scope.canvas.mouse.end.x = last.x;
            $scope.canvas.mouse.end.y = last.y;
          }
          if ($scope.tool.selected.name != "eraser")
            _renderObject(whiteboardObjectFactory.setRenderObject(0, $scope.tool.selected, $scope.whiteboard.points, $scope.canvas.mouse));
        }
      }
    };

    // Canvas default callback: mouse release
    $scope.canvas.element.onmouseup = function() {
      if ($scope.tool.selected.name) {
        if ($scope.tool.selected.name != "eraser") {
          whiteboardRenderFactory.addToCanvasBuffer(whiteboardObjectFactory.setRenderObject(0, $scope.tool.selected, $scope.whiteboard.points, $scope.canvas.mouse));
          _pushObject(whiteboardObjectFactory.convertToAPIObject($scope.tool.selected, $scope.whiteboard.points, $scope.canvas.mouse));
        }
        else
          _eraseObject($scope.canvas.mouse);
        $scope.canvas.mouse.pressed = false;
        $scope.whiteboard.points = [];
        $scope.canvas.mouse.start.x = 0;
        $scope.canvas.mouse.start.y = 0;
        $scope.canvas.mouse.end.x = 0;
        $scope.canvas.mouse.start.y = 0;
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

    $http.get($rootScope.api.url + "/whiteboard/" + $scope.route.whiteboard_id, { headers: { 'Authorization': $rootScope.user.token }}).then(
      function whiteboardOpened(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
          if (response.data.data.id == $scope.route.whiteboard_id) {
            switch(response.data.info.return_code) {
              case "1.10.1":
              _setCanvas();
              _setMouseHandlers();

              $scope.whiteboard.objects = [];
              whiteboardRenderFactory.clearCanvasBuffer();
              whiteboardRenderFactory.clearCanvas();

              $scope.whiteboard.name = response.data.data.name;
              $scope.whiteboard.creator.id = response.data.data.user.id;
              $scope.whiteboard.creator.firstname = response.data.data.user.firstname;
              $scope.whiteboard.creator.lastname = response.data.data.user.lastname;
              $scope.whiteboard.date = response.data.data.createdAt;
              $scope.whiteboard.pull.date = moment().format("YYYY-MM-DD HH:mm:ss");
              moment($scope.whiteboard.pull.date).subtract($scope.whiteboard.pull.time, 'seconds');

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
              $scope.view.loaded = true;
              $scope.view.authorized = true;
              deferred.reject();
              break;
            }
          }
        }
        else
          $rootScope.reject(true);
        return deferred.promise;
      },
      function whiteboardNotOpened(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
          if (response.data.data.id == $scope.route.whiteboard_id) {
            switch(response.data.info.return_code) {
              case "10.3.3":
              $rootScope.reject();
              deferred.reject();
              break;

              case "10.3.9":
              $scope.whiteboards.objects = null;
              $scope.view.valid = false;
              $scope.view.loaded = true;
              $scope.view.authorized = false;
              deferred.reject();
              break;

              case "10.3.4":
              $location.path("whiteboard/" + $scope.route.project_id);
              notificationFactory.warning("This whiteboard has been deleted.");
              deferred.reject();
              break;

              default:
              $scope.whiteboards.objects = null;
              $scope.view.valid = false;
              $scope.view.loaded = true;
              $scope.view.authorized = true;
              deferred.reject();
              break;
            }
          }
        }
        else
          $rootScope.reject(true);
        return deferred.promise;
      }
    );
    return deferred.promise;
  };

  // Routine definition (local)
  // Pull whiteboard modifications
  var _pullObjects = function() {
    $http.post($rootScope.api.url + "/whiteboard/draw/" + $scope.route.whiteboard_id,
      { data: { lastUpdate: $scope.whiteboard.pull.date }}, { headers: { 'Authorization': $rootScope.user.token }}).then(
      function objectsPulled(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
          switch(response.data.info.return_code) {
            case "1.10.1":
            $scope.whiteboard.pull.add = (response.data.data.add ? response.data.data.add : null);
            $scope.whiteboard.pull.delete = (response.data.data.delete ? response.data.data.delete : null);
            $scope.whiteboard.pull.date = moment().format("YYYY-MM-DD HH:mm:ss");
            moment($scope.whiteboard.pull.date).subtract($scope.whiteboard.pull.time, 'seconds');

            angular.forEach($scope.whiteboard.pull.add, function(value, key) {
              var data = whiteboardObjectFactory.convertToLocalObject(value.id, value.object);
              $scope.whiteboard.objects.push(data);
              whiteboardRenderFactory.addToCanvasBuffer(data);
              whiteboardRenderFactory.renderObject(data);
            });
            angular.forEach($scope.whiteboard.pull.delete, function(value, key) {
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
            $scope.view.loaded = true;
            $scope.view.authorized = true;
            break;
          }
        }
        else
          $rootScope.reject(true);
      },
      function objectsNotPulled(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
          switch(response.data.info.return_code) {
            case "10.5.3":
            $rootScope.reject();
            break;

            case "10.5.9":
            $scope.whiteboards.objects = null;
            $scope.view.valid = false;
            $scope.view.loaded = true;
            $scope.view.authorized = false;
            break;

            case "10.5.4":
            $location.path("whiteboard/" + $scope.route.project_id);
            notificationFactory.warning("This whiteboard has been deleted.");
            break;

            default:
            $scope.whiteboards.objects = null;
            $scope.view.valid = false;
            $scope.view.loaded = true;
            $scope.view.authorized = true;
            break;
          }
        }
        else
          $rootScope.reject(true);
      }
    );
  };

  // Routine definition (local)
  // Push whiteboard modifications
  var _pushObject = function(object) {
    if (object)
      $http.put($rootScope.api.url + "/whiteboard/draw/" + $scope.route.whiteboard_id, { data: { object: object }}, { headers: { 'Authorization': $rootScope.user.token }}).then(
        function objectPushed(response) {
          if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
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
        function objectNotPushed(response) {
          if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
            switch(response.data.info.return_code) {
              case "10.4.3":
              $rootScope.reject();
              break;

              case "10.4.9":
              notificationFactory.warning("You don't have permission to draw on this project's whiteboards.");
              break;

              default:
              notificationFactory.error();
              break;
            }
          }
          else
            $rootScope.reject(true);
        }
      );
  };

  // Routine definition (local)
  // Erase object
  var _eraseObject = function(mouse) {
    $http.delete($rootScope.api.url + "/whiteboard/object/" + $scope.route.whiteboard_id,
    { data: { data: { radius: 30, center: { x: (mouse.start.x + mouse.end.x) / 2, y: (mouse.start.y + mouse.end.y) / 2 }}},
    headers: { 'Authorization': $rootScope.user.token }}).then(
      function objectErased(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
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
      function objectNotErased(response) {
        if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
          switch(response.data.info.return_code) {
            case "10.4.3":
            $rootScope.reject();
            break;

            case "10.4.9":
            notificationFactory.warning("You don\'t have permission to delete objects.");
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
  $scope.tool.reset = function() {
    $scope.tool.selected.name = null;
    $scope.tool.selected.text.italic = false;
    $scope.tool.selected.text.bold = false;
  };

  // "Fullscreen" button handler
  $scope.canvas.toggle = function(requestedState) {
    if (requestedState) {
      if ($scope.whiteboard.wrapper.requestFullscreen)
        $scope.whiteboard.wrapper.requestFullscreen();
      else if ($scope.whiteboard.wrapper.webkitRequestFullscreen)
        $scope.whiteboard.wrapper.webkitRequestFullscreen();
      else if ($scope.whiteboard.wrapper.mozRequestFullScreen)
        $scope.whiteboard.wrapper.mozRequestFullScreen();
      else if ($scope.whiteboard.wrapper.msRequestFullscreen)
        $scope.whiteboard.wrapper.msRequestFullscreen();
      $scope.canvas.fullscreen = true;
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
      $scope.canvas.fullscreen = false;
    }
  };



  /* ==================== EXECUTION ==================== */

  $scope.view.valid = true;
  $scope.view.loaded = true;
  $scope.view.authorized = true;

  accessFactory.projectAvailable();
  accessFactory.whiteboardAvailable();

  var openWhiteboard = _openWhiteboard();
  openWhiteboard.then(
    function whiteboardOpened() {
      $scope.canvas.identifier = Math.floor((Math.random() * 4096) + 1);
      $scope.whiteboard.pull.interval = $interval(_openWhiteboard, ($scope.whiteboard.pull.time * 1000));
    },
    function whiteboardNotOpened() { }
  );

  // Stop pull interval on route change
  $scope.$on('$destroy', function() {
    if ($scope.whiteboard.pull.interval)
      $interval.cancel($scope.whiteboard.pull.interval);
    whiteboardRenderFactory.clearCanvasBuffer();
    whiteboardRenderFactory.clearCanvas();
  });

  $scope.$on('$locationChangeStart', function() {
    if ($scope.whiteboard.pull.interval)
      $interval.cancel($scope.whiteboard.pull.interval);
    whiteboardRenderFactory.clearCanvasBuffer();
    whiteboardRenderFactory.clearCanvas();
  });

  $scope.$on('$routeChangeStart', function() {
    if ($scope.whiteboard.pull.interval)
      $interval.cancel($scope.whiteboard.pull.interval);
    whiteboardRenderFactory.clearCanvasBuffer();
    whiteboardRenderFactory.clearCanvas();
  });



  /* ==================== DELETE WHITEBOARD ==================== */

  // "Delete whiteboard" button handler
  $scope.whiteboard.delete = function() {
    if ($scope.canvas.fullscreen)
      $scope.canvas.toggle(false);
    var whiteboardDeletion = $uibModal.open({ animation: true, size: "lg", backdrop: "static", templateUrl: "whiteboardDeletion.html", controller: "WhiteboardDeletionController" });
    whiteboardDeletion.result.then(
      function whiteboardDeletionConfirmed(data) {
        if ($scope.whiteboard.pull.interval)
          $interval.cancel($scope.whiteboard.pull.interval);
        $http.delete($rootScope.api.url + "/whiteboard/" + $scope.route.whiteboard_id, { headers: { 'Authorization': $rootScope.user.token }}).then(
          function whiteboardDeleted(response) {
            $location.path("whiteboard/" + $scope.route.project_id);            
            notificationFactory.success("Whiteboard successfully deleted.");
          },
          function whiteboardNotDeleted(response) {
            if (response && response.data && response.data.info && response.data.info.return_code && response.data.data) {
              switch(response.data.info.return_code) {
                case "10.6.3":
                $rootScope.reject();
                break;

                case "10.6.9":
                notificationFactory.warning("You don\'t have permission to delete whiteboards.");
                break;

                default:
                notificationFactory.error();
                break;
              }
            }
            else
              $rootScope.reject(true);
          }
        ),
        function whiteboardDeletionCancelled() { }
      }
    );
  };

}]);



// Controller definition (from view)
// Confirmation prompt for whiteboard deletion.
app.controller("WhiteboardDeletionController", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {

  $scope.whiteboardDeletionConfirmed = function() { $uibModalInstance.close(); };
  $scope.whiteboardDeletionCancelled = function() { $uibModalInstance.dismiss(); };
}]);