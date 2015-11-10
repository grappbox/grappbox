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
  var canvasContext;
  var canvasPoints = [];
  var canvasColorValues = [];
  var whiteboardLineWidth;

  var mouseStartPosition;
  var mouseEndPosition;
  var isMousePressed;

  var lineColorIndex = 0;
  var fillColorIndex = 0;

  var createRenderObject;
  var renderPath;

  /* Create/compile canvasData to render */
  var createRenderObject = function() {
    var canvasPointsLength = canvasPoints.length;
    var canvasData = "";

    switch ($scope.whiteboardTools) {
      case "pencil":
        canvasData = {
        toolName: "pencil",
        toolLineWidth: Number($scope.whiteboardLineWidth),
        toolPoints: canvasPoints,
        toolColor: canvasColorValues[lineColorIndex] };
      break;

      case "line":
        canvasData = {
        toolName: "line",
        toolLineColor: canvasColorValues[lineColorIndex],
        toolLineWidth: Number($scope.whiteboardLineWidth),
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolEndX: mouseEndPosition.x,
        toolEndY: mouseEndPosition.y };
      break;

      case "rectangle":
        canvasData = {
        toolName: "rectangle",
        toolLineColor: canvasColorValues[lineColorIndex],
        toolFillColor: canvasColorValues[fillColorIndex],
        toolLineWidth: Number($scope.whiteboardLineWidth),
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolWidth: mouseEndPosition.x - mouseStartPosition.x,
        toolHeight: mouseEndPosition.y - mouseStartPosition.y,
        toolIsFillShapeEnabled: $scope.isFillShapeEnabled };
      break;

      case "circle":
        canvasData = {
        toolName: "circle",
        toolLineColor: canvasColorValues[lineColorIndex],
        toolFillColor: canvasColorValues[fillColorIndex],
        toolLineWidth: Number($scope.whiteboardLineWidth),
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolRadius: (Math.abs(mouseEndPosition.x - mouseStartPosition.x) + (Math.abs(mouseEndPosition.y - mouseStartPosition.y)) / 2),
        toolIsFillShapeEnabled: $scope.isFillShapeEnabled };
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
  $scope.whiteboardLineWidth = "0.5";
  $scope.whiteboardDrawType = "line";
  $scope.lineColor = canvasColorValues[lineColorIndex];
  $scope.fillColor = canvasColorValues[fillColorIndex];
  $scope.isFillShapeEnabled = false;

  /* Initialize whiteboard canvas and controls, set default values */
  $scope.initializeWhiteboardControls = function() {
    canvas = document.getElementById("whiteboard-canvas");
    canvasContext = canvas.getContext("2d");
    whiteboardRendererFactory.setCanvasContext(canvasContext);

    canvasPoints = [];
    canvasColorValues = ['#000000', "#F44336",
                         "#E91E63", "#9C27B0",
                         "#673AB7", "#3F51B5",
                         "#2196F3", "#03A9F4",
                         "#00BCD4", "#009688",
                         "#4CAF50", "#8BC34A",
                         "#CDDC39", "#FFEB3B",
                         "#FFC107", "#FF9800",
                         "#FF5722", "#795548",
                         "#607D8B", "#FFFFFF",
                         "#EEEEEE", "#BDBDBD",
                         "#9E9E9E", "#757575",
                         "#424242", "#000000"];

    whiteboardLineWidth = 0.5;

    lineColorIndex = 0;
    fillColorIndex = 0;
    isMousePressed = false;
    whiteboardDrawType = "line";
    mouseStartPosition = { x: 0, y: 0 };
    mouseEndPosition = { x: 0, y: 0 };

    /* Canvas default callback: mouse pressed */
    canvas.onmousedown = function(eventPosition) {
      var canvasData;

      canvasPoints.push({
        toolPositionX: eventPosition.offsetX,
        toolPositionY: eventPosition.offsetY,
        toolColor: canvasColorValues[lineColorIndex]
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

      if (isMousePressed)
      {
        x = eventPosition.offsetX;
        y = eventPosition.offsetY;

        canvasPoints.push({
          x: x,
          y: y,
          toolColor: canvasColorValues[lineColorIndex]
        });

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

  /* Handle color changes */
  $scope.setWhiteboardColor = function(color) {
    switch ($scope.whiteboardDrawType) {
      case "line":
      lineColorIndex = color;
      $scope.lineColor = canvasColorValues[lineColorIndex];
      break;

      case "fill":
      fillColorIndex = color;
      $scope.fillColor = canvasColorValues[fillColorIndex];
      break;

      default:
      lineColorIndex = color;
      $scope.lineColor = canvasColorValues[lineColorIndex];
      break;
    }
  };

  /* Handle draw type changes */
  $scope.setWhiteboardDrawType = function(drawType) {
    $scope.whiteboardDrawType = drawType;
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
