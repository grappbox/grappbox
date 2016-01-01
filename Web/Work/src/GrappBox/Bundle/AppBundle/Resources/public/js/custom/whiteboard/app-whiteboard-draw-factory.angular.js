/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP whiteboard data (factory)
*
*/
app.factory("whiteboardRendererFactory", function() {
  var canvasContext;
  var canvasBuffer = [];

  var renderPencil;
  var renderLine;
  var renderRectangle;
  var renderCircle;
  var renderAll;

  var canvas = document.getElementById("whiteboard-canvas");

  // Free-hand drawing
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

  // Line
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

  // Rectangle
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

  // Circle
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

  // Text
  var renderText = function(data) {
    if (data.drawColor != "none") {
      canvasContext.beginPath();
      canvasContext.font = (data.isItalicEnabled ? "italic " : '') + (data.isBoldEnabled ? "bold " : '') + data.font;
      canvasContext.fillStyle = data.drawColor;
      canvasContext.fillText(data.content, data.startX, data.startY);
      canvasContext.stroke();
    }
  };

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

  return {
    addToCanvasBuffer: function(data) {
      canvasBuffer.push(data);
    },

    renderAll: function() {
      renderAll();
    },

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
      }
    },

    setCanvasContext: function(context) {
      canvasContext = context;
    },

    undoLastCanvasAction: function() {
      canvasBuffer.pop();
    }
  };

});
