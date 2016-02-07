/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP whiteboard page content
*
*/
app.controller('whiteboardController', ['$scope', '$http', '$routeParams', 'drawFactory', function($scope, $http, $routeParams, drawFactory) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.id = { project: $routeParams.project_id, whiteboard: $routeParams.id };
  $scope.selectedTool = "pencil";

  $scope.draw = { canvasPoints: [], mouseStartPosition: { x: 0, y: 0 }, mouseEndPosition: { x: 0, y: 0 } };
  $scope.text = { textValue: "", isItalicEnabled: false, isBoldEnabled: false };

  $scope.color = {
    availableColors: [ { name: '-None-', value: 'none' }, { name: 'Red', value: '#F44336' }, { name: 'Pink', value: '#E91E63' }, { name: 'Purple', value: '#9C27B0' },
                       { name: 'Deep Purple', value: '#673AB7' }, { name: 'Indigo', value: '#3F51B5' }, { name: 'Blue', value: '#2196F3' }, { name: 'Light Blue', value: '#03A9F4' },
                       { name: 'Cyan', value: '#00BCD4' }, { name: 'Teal', value: '#009688' }, { name: 'Green', value: '#4CAF50' }, { name: 'Light Green', value: '#8BC34A' },
                       { name: 'Lime', value: '#CDDC39' }, { name: 'Yellow', value: '#FFEB3B' }, { name: 'Amber', value: '#FFC107' }, { name: 'Orange', value: '#FF9800' },
                       { name: 'Deep Orange', value: '#FF5722' }, { name: 'Brown', value: '#795548' }, { name: 'Blue Grey', value: '#607D8B' }, { name: 'White', value: '#FFFFFF' },
                       { name: 'Grey 20%', value: '#EEEEEE' }, { name: 'Grey 40%', value: '#BDBDBD' }, { name: 'Grey 50%', value: '#9E9E9E' }, { name: 'Grey 60%', value: '#757575' },
                       { name: 'Grey 80%', value: '#424242' }, { name: 'Black', value: '#000000' } ],
    selectedDrawColor: { name: 'Black', value: '#000000' },
    selectedFillColor: { name: '-None-', value: 'none' } };

  $scope.line = {
    availableWidths: [ { label: '0.5 pt', value: '0.5' }, { label: '1 pt', value: '1' }, { label: '1.5 pt', value: '1.5' }, { label: '2 pt', value: '2' },
                       { label: '2.5 pt', value: '2.5' }, { label: '3 pt', value: '3' }, { label: '4 pt', value: '4' }, { label: '5 pt', value: '5' } ],
    selectedLineWidth: { label: '1 pt', value: '1' } };


  // Routine definition
  // Create/compile canvasData to render
  var createRenderObject = function() {
    var canvasData = {};

    switch ($scope.selectedTool) {
      case "pencil":
      canvasData = { tool: "pencil", lineWidth: Number($scope.line.selectedLineWidth.value), drawColor: $scope.color.selectedDrawColor.value, points: $scope.draw.canvasPoints };
      break;

      case "line":
      canvasData = { tool: "line", lineWidth: Number($scope.line.selectedLineWidth.value), drawColor: $scope.color.selectedDrawColor.value,
        startX: $scope.draw.mouseStartPosition.x, startY: $scope.draw.mouseStartPosition.y, endX: $scope.draw.mouseEndPosition.x, endY: $scope.draw.mouseEndPosition.y };
      break;

      case "rectangle":
      canvasData = { tool: "rectangle", lineWidth: Number($scope.line.selectedLineWidth.value), drawColor: $scope.color.selectedDrawColor.value,
        fillColor: $scope.color.selectedFillColor.value, startX: $scope.draw.mouseStartPosition.x, startY: $scope.draw.mouseStartPosition.y, fillWidth: $scope.draw.mouseEndPosition.x - $scope.draw.mouseStartPosition.x,
        fillHeight: $scope.draw.mouseEndPosition.y - $scope.draw.mouseStartPosition.y };
      break;

      case "circle":
      canvasData = { tool: "circle", lineWidth: Number($scope.line.selectedLineWidth.value), drawColor: $scope.color.selectedDrawColor.value,
        fillColor: $scope.color.selectedFillColor.value, startX: $scope.draw.mouseStartPosition.x, startY: $scope.draw.mouseStartPosition.y,
        fillRadius: (Math.abs($scope.draw.mouseEndPosition.x - $scope.draw.mouseStartPosition.x) + (Math.abs($scope.draw.mouseEndPosition.y - $scope.draw.mouseStartPosition.y)) / 2) };
      break;

      case "text":
      canvasData = { tool: "text", font: '32pt Roboto Condensed', isItalicEnabled: $scope.text.isItalicEnabled, isBoldEnabled: $scope.text.isBoldEnabled,
        content: $scope.text.textValue, startX: $scope.draw.mouseStartPosition.x, startY: $scope.draw.mouseStartPosition.y, drawColor: $scope.color.selectedDrawColor.value };
      break;

      default:
      canvasData = {};
      break;
    }

    return canvasData;
  };

  // Routine definition
  // Render/display canvasData using drawFactory
  var renderPath = function(data) {
    if ($scope.selectedTool === "rectangle" || $scope.selectedTool === "line" || $scope.selectedTool === "circle")
      drawFactory.renderAll();
    drawFactory.render(data);
  };




  /* ==================== START ==================== */

  // Routine definition
  // Handle 'Undo' button
  $scope.undoLastCanvasAction = function() {
    drawFactory.undoLastCanvasAction();
    drawFactory.renderAll();

    $scope.draw.canvasPoints = [];
    $scope.draw.mouseStartPosition.x = 0;
    $scope.draw.mouseStartPosition.y = 0;
    $scope.draw.mouseEndPosition.x = 0;
    $scope.draw.mouseEndPosition.y = 0;
  };

  // Routine definition
  // Handle 'Expand' button
  $scope.expandWhiteboard = function() { angular.element(document.querySelector('#app-wrapper')).toggleClass('hide-menu'); };


  // START
  // Set whiteboard canvas and controls
  $scope.setWhiteboard = function() {
    var canvas = "";
    var isMousePressed = false;

    canvas = document.getElementById("whiteboard-canvas");
    drawFactory.setCanvasContext(canvas.getContext("2d"));

    // Canvas default callback: mouse pressed
    canvas.onmousedown = function(eventPosition) {
      var canvasData;

      isMousePressed = true;
      $scope.draw.canvasPoints.push({ toolPositionX: eventPosition.offsetX, toolPositionY: eventPosition.offsetY, drawColor: $scope.color.selectedDrawColor.value });
      $scope.draw.mouseStartPosition.x = $scope.draw.canvasPoints[0].toolPositionX;
      $scope.draw.mouseStartPosition.y = $scope.draw.canvasPoints[0].toolPositionY;
      $scope.draw.mouseEndPosition.x = $scope.draw.canvasPoints[0].toolPositionX;
      $scope.draw.mouseEndPosition.y = $scope.draw.canvasPoints[0].toolPositionY;
      
      canvasData = createRenderObject();
      renderPath(canvasData);
    };

    // Canvas default callback: mouse drag
    canvas.onmousemove = function(eventPosition) {
      var lastPoint;
      var canvasData;

      if (isMousePressed) {
        lastPoint = $scope.draw.canvasPoints[$scope.draw.canvasPoints.length - 1];
        $scope.draw.canvasPoints.push({ x: eventPosition.offsetX, y: eventPosition.offsetY, drawColor: $scope.color.selectedDrawColor.value });
        $scope.draw.mouseEndPosition.x = lastPoint.x;
        $scope.draw.mouseEndPosition.y = lastPoint.y;
        
        canvasData = createRenderObject();
        renderPath(canvasData);
      }
    };

    // Canvas default callback: mouse release
    canvas.onmouseup = function() {
      var canvasData;

      isMousePressed = false;
      canvasData = createRenderObject();
      drawFactory.addToCanvasBuffer(canvasData);

      $scope.draw.canvasPoints = [];
      $scope.draw.mouseStartPosition.x = 0;
      $scope.draw.mouseStartPosition.y = 0;
      $scope.draw.mouseEndPosition.x = 0;
      $scope.draw.mouseEndPosition.y = 0;
    };
  };

}]);



/**
* Controller definition
* APP whiteboard draw factory
*
*/
app.factory("drawFactory", function() {
  var canvas = document.getElementById("whiteboard-canvas");
  var canvasContext;
  var canvasBuffer = [];

  // Routine definition
  // Free-hand draw rendering
  var renderPencil = function(data) {
    if (data.drawColor != "none") {
      canvasContext.beginPath();
      canvasContext.strokeStyle = data.drawColor;
      canvasContext.lineWidth = data.lineWidth;
      canvasContext.lineCap = 'round';
      canvasContext.moveTo(data.points[0].x, data.points[0].y);

      for (var i = 0; i < data.points.length; ++i) {
        canvasContext.lineTo(data.points[i].x, data.points[i].y);
      }
      canvasContext.stroke();
    }
  };

  // Routine definition
  // Line rendering
  var renderLine = function(data) {
    if (data.drawColor != "none") {
      canvasContext.beginPath();
      canvasContext.strokeStyle = data.drawColor;
      canvasContext.lineWidth = data.lineWidth;
      canvasContext.lineCap = 'round';
      canvasContext.moveTo(data.startX, data.startY);
      canvasContext.lineTo(data.endX, data.endY);
      canvasContext.stroke();
    }
  };

  // Routine definition
  // Rectangle rendering
  var renderRectangle = function(data) {
    canvasContext.beginPath();
    canvasContext.rect(data.startX, data.startY, data.fillWidth, data.fillHeight);

    if (data.drawColor != "none") {
      canvasContext.lineWidth = data.lineWidth;
      canvasContext.strokeStyle = data.drawColor;
      canvasContext.stroke();
    }
    if (data.fillColor != "none") {
      canvasContext.fillStyle = data.fillColor;
      canvasContext.fill();
    }
  };

  // Routine definition
  // Circle rendering
  var renderCircle = function(data) {
    canvasContext.beginPath();
    canvasContext.arc(data.startX, data.startY, data.fillRadius, 0, Math.PI * 2, false);

    if (data.drawColor != "none") {
      canvasContext.lineWidth = data.lineWidth;
      canvasContext.strokeStyle = data.drawColor;
      canvasContext.stroke();
    }
    if (data.fillColor != "none") {
      canvasContext.fillStyle = data.fillColor;
      canvasContext.fill();
    }
  };

  // Routine definition
  // Text rendering
  var renderText = function(data) {
    if (data.drawColor != "none") {
      canvasContext.beginPath();
      canvasContext.font = (data.isItalicEnabled ? "italic " : '') + (data.isBoldEnabled ? "bold " : '') + data.font;
      canvasContext.fillStyle = data.drawColor;
      canvasContext.fillText(data.content, data.startX, data.startY);
      canvasContext.stroke();
    }
  };

  // Routine definition
  // Rendering HUB
  var renderAll = function() {
    canvasContext.clearRect(0, 0, canvas.width, canvas.height);

    for (var i = 0; i < canvasBuffer.length; ++i) {
      switch (canvasBuffer[i].tool) {
        case "pencil":
        renderPencil(canvasBuffer[i]);
        break;
        case "rectangle":
        renderRectangle(canvasBuffer[i]);
        break;
        case "circle":
        renderCircle(canvasBuffer[i]);
        break;
        case "line":
        renderLine(canvasBuffer[i]);
        break;
        case "text":
        renderText(canvasBuffer[i]);
        break;
      }
    }
  };

  // Routine access (return)
  // Give access to built-in routines
  return {
    addToCanvasBuffer: function(data) { canvasBuffer.push(data); },

    renderAll: function() { renderAll(); },

    setCanvasContext: function(context) { canvasContext = context; },

    undoLastCanvasAction: function() { canvasBuffer.pop(); },

    render: function(data) {
      switch (data.tool) {
        case "pencil":
        renderPencil(data);
        break;

        case "rectangle":
        renderRectangle(data);
        break;
        
        case "circle":
        renderCircle(data);
        break;
        
        case "line":
        renderLine(data);
        break;
        
        case "text":
        renderText(data);
        break;
      };
    }
  };

});