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
  var canvasPoints;
  var canvasColorsValue;
  var canvasColorsArray = [];
  var canvasLineWidth;

  var mouseStartPosition;
  var mouseEndPosition;
  var isMousePressed;

  var lineColorNumber;
  var fillColorNumber;

  var createRenderObject;
  var renderPath;

  /* Create/compile canvasData to render */
  var createRenderObject = function() {
    var canvasPointsLength = canvasPoints.length;
    var canvasData = "";

    switch ($scope.whiteboardTool)
    {
      case "pencil":
        canvasData = {
        toolName: "pencil",
        toolLineWidth: canvasLineWidth,
        toolPoints: canvasPoints,
        toolColor: canvasColorsValue[lineColorNumber] };
      break;

      case "line":
        canvasData = {
        toolName: "line",
        toolLineColor: canvasColorsValue[lineColorNumber],
        toolLineWidth: canvasLineWidth,
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolEndX: mouseEndPosition.x,
        toolEndY: mouseEndPosition.y };
      break;

      case "rectangle":
        canvasData = {
        toolName: "rectangle",
        toolLineColor: canvasColorsValue[lineColorNumber],
        toolFillColor: canvasColorsValue[fillColorNumber],
        toolLineWidth: canvasLineWidth,
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolWidth: mouseEndPosition.x - mouseStartPosition.x,
        toolHeight: mouseEndPosition.y - mouseStartPosition.y,
        toolFillShape: $scope.fillShape };
      break;

      case "circle":
        canvasData = {
        toolName: "circle",
        toolLineColor: canvasColorsValue[lineColorNumber],
        toolFillColor: canvasColorsValue[fillColorNumber],
        toolLineWidth: canvasLineWidth,
        toolStartX: mouseStartPosition.x,
        toolStartY: mouseStartPosition.y,
        toolRadius: (Math.abs(mouseEndPosition.x - mouseStartPosition.x) + (Math.abs(mouseEndPosition.y - mouseStartPosition.y)) / 2),
        toolFillShape: $scope.fillShape };
      break;

      default:
        canvasData = {};
      break;
    }

    return canvasData;
  };

  /* Render/display canvasData using whiteboardRendererFactory */
  var renderPath = function(data) {
  if ($scope.whiteboardTool === "rectangle" || $scope.whiteboardTool === "line" || $scope.whiteboardTool === "circle")
    whiteboardRendererFactory.renderAll();
    whiteboardRendererFactory.render(data);
  };



  /*
  * SCOPE FUNCTIONS
  */

  /* Scope variables default values */
  $scope.whiteboardTool = "pencil";
  $scope.lineColorCss = "black";
  $scope.fillColorCss = "black";
  $scope.fillShape = false;
  $scope.colorTarget = "line";

  /* Initialize whiteboard canvas and controls, set default values */
  $scope.initializeWhiteboardControls = function()
  {
    canvas = document.getElementById("whiteboard-canvas");
    canvasContext = canvas.getContext("2d");
    whiteboardRendererFactory.setCanvasContext(canvasContext);

    canvasPoints = [];
    canvasColorsArray = ['black'];
    canvasColorsValue = ['#000000'];
    canvasLineWidth = 0.5;

    lineColorNumber = 1;
    fillColorNumber = 1;
    isMousePressed = false;
    mouseStartPosition = { x: 0, y: 0 };
    mouseEndPosition = { x: 0, y: 0 };

    /* Canvas default callback: mouse pressed */
    canvas.onmousedown = function(eventPosition)
    {
      var canvasData;

      canvasPoints.push({
        toolPositionX: eventPosition.offsetX,
        toolPositionY: eventPosition.offsetY,
        toolColor: canvasColorsValue[lineColorNumber]
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
    canvas.onmousemove = function(eventPosition)
    {
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
          toolColor: canvasColorsValue[lineColorNumber]
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
  $scope.selectColor = function(color) {
    lineColorNumber = color;
    $scope.lineColorCss = canvasColorsArray[lineColorNumber];

    fillColorNumber = color;
    $scope.fillColorCss = canvasColorsArray[fillColorNumber];
  };

  /* Handle line width changes */
  $scope.changelineWidth = function(size) {
    canvasLineWidth = Number(size);
  };

  /* Handle 'Undo' button */
  $scope.undoCanvasAction = function() {
    whiteboardRendererFactory.undoCanvasAction();
    whiteboardRendererFactory.renderAll();

    canvasPoints = [];
    mouseStartPosition.x = 0;
    mouseStartPosition.y = 0;
    mouseEndPosition.x = 0;
    mouseEndPosition.y = 0;
  };

  /* Handle 'Expand' button */
  $scope.enableFullScreen = function() {
    angular.element(document.querySelector('#app-wrapper')).toggleClass('hide-menu');
  };

}]);
