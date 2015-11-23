/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Whiteboard controller
*
*/

app.controller('whiteboardController', ['$scope', '$http', '$routeParams', 'whiteboardRendererFactory', function($scope, $http, $routeParams, whiteboardRendererFactory) {
  $scope.whiteboardID = $routeParams.id;

  $http.get('../resources/_temp/whiteboards.json').success(function(data) {
    $scope.whiteboardsListContent = data;
  });


  /*
  * CANVAS FUNCTIONS
  */

  /* Canvas-related variables */
  var canvas;
  var canvasData;
  var canvasContext;
  var canvasPoints = [];
  var canvasColorValues = [];

  var textMode = false;

  var mouseStartPosition;
  var mouseEndPosition;
  var isMousePressed;

  var lineColorIndex = 0;
  var fillColorIndex = 0;

  var createRenderObject;
  var renderPath;

  /* Create/compile canvasData to render */
  var createRenderObject = function() {
    canvasData = {};

    switch ($scope.whiteboardTools) {
      case "pencil":
      canvasData = {
        toolName: "pencil",
        toolLineWidth: Number($scope.selectedLineWidth),
        toolPoints: canvasPoints,
        toolColor: $scope.color.selectedDrawColor.value
      };
      break;

      case "line":
      canvasData = {
        toolName: "line",
        toolDrawColor: $scope.color.selectedDrawColor.value,
        toolLineWidth: Number($scope.selectedLineWidth),
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolEndX: mouseEndPosition.x,
        toolEndY: mouseEndPosition.y
      };
      break;

      case "rectangle":
      canvasData = {
        toolName: "rectangle",
        toolDrawColor: $scope.color.selectedDrawColor.value,
        toolFillColor: $scope.color.selectedFillColor.value,
        toolLineWidth: Number($scope.selectedLineWidth),
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolWidth: mouseEndPosition.x - mouseStartPosition.x,
        toolHeight: mouseEndPosition.y - mouseStartPosition.y,
        toolIsFillShapeEnabled: $scope.isFillModeEnabled
      };
      break;

      case "circle":
      canvasData = {
        toolName: "circle",
        toolDrawColor: $scope.color.selectedDrawColor.value,
        toolFillColor: $scope.color.selectedFillColor.value,
        toolLineWidth: Number($scope.selectedLineWidth),
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolRadius: (Math.abs(mouseEndPosition.x - mouseStartPosition.x) + (Math.abs(mouseEndPosition.y - mouseStartPosition.y)) / 2),
        toolIsFillShapeEnabled: $scope.isFillModeEnabled
      };
      break;

      case "text":
      canvasData = {
        toolName: "text",
        toolFont: '40pt Roboto Condensed',
        toolIsItalic: $scope.isItalicEnabled,
        toolIsBold: $scope.isBoldEnabled,
        toolContent: $scope.drawTextValue,
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolDrawColor: $scope.color.selectedDrawColor.value
      };
      break;

      default:
      canvasData = {};
      break;
    }

    return canvasData;
  };

  /* Render/display canvasData using whiteboardRendererFactory */
  var renderPath = function(data) {
    if ($scope.whiteboardTools === "rectangle" || $scope.whiteboardTools === "line" || $scope.whiteboardTools === "circle")
      whiteboardRendererFactory.renderAll();
    whiteboardRendererFactory.render(data);
  };



  /*
  * SCOPE FUNCTIONS
  */

  /* Scope variables default values */
  $scope.whiteboardTools = "pencil";
  $scope.selectedLineWidth = "0.5";
  $scope.isFillModeEnabled = false;

  $scope.color = {
    availableColors: [
    { name: 'Red', value: '#F44336' },
    { name: 'Pink', value: '#E91E63' },
    { name: 'Purple', value: '#9C27B0' },
    { name: 'Deep Purple', value: '#673AB7' },
    { name: 'Indigo', value: '#3F51B5' },
    { name: 'Blue', value: '#2196F3' },
    { name: 'Light Blue', value: '#03A9F4' },
    { name: 'Cyan', value: '#00BCD4' },
    { name: 'Teal', value: '#009688' },
    { name: 'Green', value: '#4CAF50' },
    { name: 'Light Green', value: '#8BC34A' },
    { name: 'Lime', value: '#CDDC39' },
    { name: 'Yellow', value: '#FFEB3B' },
    { name: 'Amber', value: '#FFC107' },
    { name: 'Orange', value: '#FF9800' },
    { name: 'Deep Orange', value: '#FF5722' },
    { name: 'Brown', value: '#795548' },
    { name: 'Blue Grey', value: '#607D8B' },
    { name: 'White', value: '#FFFFFF' },
    { name: 'Grey 20%', value: '#EEEEEE' },
    { name: 'Grey 40%', value: '#BDBDBD' },
    { name: 'Grey 50%', value: '#9E9E9E' },
    { name: 'Grey 60%', value: '#757575' },
    { name: 'Grey 80%', value: '#424242' },
    { name: 'Black', value: '#000000' },

    ],
    selectedDrawColor: { name: 'Black', value: '#000000' },
    selectedFillColor: { name: 'Black', value: '#000000' },    
  };


  /* Initialize whiteboard canvas and controls, set default values */
  $scope.initializeWhiteboardControls = function() {

    canvas = document.getElementById("whiteboard-canvas");
    canvasContext = canvas.getContext("2d");
    whiteboardRendererFactory.setCanvasContext(canvasContext);

    canvasPoints = [];

    isMousePressed = false;

    mouseStartPosition = { x: 0, y: 0 };
    mouseEndPosition = { x: 0, y: 0 };

    /* Canvas default callback: mouse pressed */
    canvas.onmousedown = function(eventPosition) {
      var canvasData;

      canvasPoints.push({
        toolPositionX: eventPosition.offsetX,
        toolPositionY: eventPosition.offsetY,
        toolColor: $scope.color.selectedDrawColor.value
      });

      isMousePressed = true;

      mouseStartPosition.x = canvasPoints[0].toolPositionX;
      mouseStartPosition.y = canvasPoints[0].toolPositionY;
      mouseEndPosition.x = canvasPoints[0].toolPositionX;
      mouseEndPosition.y = canvasPoints[0].toolPositionY;

      canvasData = createRenderObject();
      renderPath(canvasData);
    };

    /* Canvas default callback: mouse drag */
    canvas.onmousemove = function(eventPosition) {
      var x;
      var y;
      var lastPoint;
      var canvasData;

      if (isMousePressed) {
        x = eventPosition.offsetX;
        y = eventPosition.offsetY;

        canvasPoints.push({ x: x, y: y, toolColor: $scope.color.selectedDrawColor.value });

        lastPoint = canvasPoints[canvasPoints.length - 1];
        mouseEndPosition.x = lastPoint.x;
        mouseEndPosition.y = lastPoint.y;

        canvasData = createRenderObject();
        renderPath(canvasData);
      }
    };

    /* Canvas default callback: mouse release */
    canvas.onmouseup = function() {
      var canvasData;

      isMousePressed = false;
      canvasData = createRenderObject();
      whiteboardRendererFactory.addToCanvasBuffer(canvasData);

      canvasPoints = [];
      mouseStartPosition.x = 0;
      mouseStartPosition.y = 0;
      mouseEndPosition.x = 0;
      mouseEndPosition.y = 0;
    };
  };

  /* Handle 'Undo' button */
  $scope.undoLastCanvasAction = function() {
    whiteboardRendererFactory.undoLastCanvasAction();
    whiteboardRendererFactory.renderAll();

    canvasPoints = [];
    mouseStartPosition.x = 0;
    mouseStartPosition.y = 0;
    mouseEndPosition.x = 0;
    mouseEndPosition.y = 0;
  };

  /* Handle 'Expand' button */
  $scope.expandWhiteboard = function() {
    angular.element(document.querySelector('#app-wrapper')).toggleClass('hide-menu');
  };

}]);
