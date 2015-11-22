/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
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

  var renderPencil = function(data) {
    canvasContext.beginPath();

    canvasContext.strokeStyle = data.toolColor;
    canvasContext.lineWidth = data.toolLineWidth;
    canvasContext.lineCap = 'round';
    canvasContext.moveTo(data.toolPoints[0].x, data.toolPoints[0].y);

    for (var i = 0; i < data.toolPoints.length; ++i) {
      canvasContext.lineTo(data.toolPoints[i].x, data.toolPoints[i].y);
    }

    canvasContext.stroke();
  };

  var renderLine = function(data) {
    canvasContext.beginPath();

    canvasContext.strokeStyle = data.toolLineColor;
    canvasContext.lineWidth = data.toolLineWidth;
    canvasContext.lineCap = 'round';
    canvasContext.moveTo(data.toolStartX, data.toolStartY);
    canvasContext.lineTo(data.toolEndX, data.toolEndY);

    canvasContext.stroke();
  };

  var renderRectangle = function(data) {
    canvasContext.beginPath();

    canvasContext.strokeStyle = data.toolLineColor;
    canvasContext.fillStyle = data.toolFillColor;
    canvasContext.lineWidth = data.toolLineWidth;
    canvasContext.rect(data.toolStartX, data.toolStartY, data.toolWidth, data.toolHeight);

    if (data.toolIsFillShapeEnabled)
      canvasContext.fill();

    canvasContext.stroke();
  };

  var renderCircle = function(data) {
    canvasContext.beginPath();

    canvasContext.strokeStyle = data.toolLineColor;
    canvasContext.fillStyle = data.toolFillColor;
    canvasContext.lineWidth = data.toolLineWidth;
    canvasContext.arc(data.toolStartX, data.toolStartY, data.toolRadius, 0, Math.PI * 2, false);

    if (data.toolIsFillShapeEnabled)
      canvasContext.fill();

    canvasContext.stroke();
  };

  var renderText = function(data) {
    canvasContext.beginPath();

    canvasContext.strokeStyle = data.toolLineColor;
    canvasContext.fillText(data.toolContent, data.toolStartX, data.toolStartY);

    canvasContext.stroke();
  };


  var renderAll = function() {
    canvasContext.clearRect(0, 0, canvas.width, canvas.height);

    for (var i = 0; i < canvasBuffer.length; ++i) {
      switch (canvasBuffer[i].toolName) {
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
      switch (data.toolName) {
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

    undoLastCanvasAction: function () {
      canvasBuffer.pop();   
    }
  };

});
