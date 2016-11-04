/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Factory definition
// APP whiteboard render
app.factory("whiteboardRenderFactory", function() {

	/* ==================== INITIALIZATION ==================== */

	// Factory variables initialization
  var canvas = "";
  var canvasContext = "";
  var canvasBuffer = [];



  /* ==================== ROUTINES ==================== */

  // Routine definition
  // Free-hand draw rendering
  var _renderPencil = function(data) {
    if (data.color) {
      canvasContext.beginPath();
      canvasContext.strokeStyle = data.color;
      canvasContext.lineWidth = data.thickness;
      canvasContext.lineCap = "round";
      canvasContext.moveTo(data.points[0].x, data.points[0].y);

      for (var i = 0; i < data.points.length; ++i) {
        canvasContext.lineTo(data.points[i].x, data.points[i].y);
      }
      canvasContext.stroke();
    }
  };

  // Routine definition
  // Line rendering
  var _renderLine = function(data) {
    if (data.color) {
      canvasContext.beginPath();
      canvasContext.strokeStyle = data.color;
      canvasContext.lineWidth = data.thickness;
      canvasContext.lineCap = "round";
      canvasContext.moveTo(data.start.x, data.start.y);
      canvasContext.lineTo(data.end.x, data.end.y);
      canvasContext.stroke();
    }
  };

  // Routine definition
  // Rectangle rendering
  var _renderRectangle = function(data) {
    canvasContext.beginPath();
    canvasContext.rect(data.start.x, data.start.y, data.width, data.height);

    if (data.color) {
      canvasContext.lineWidth = data.thickness;
      canvasContext.strokeStyle = data.color;
      canvasContext.stroke();
    }
    if (data.background) {
      canvasContext.fillStyle = data.background;
      canvasContext.fill();
    }
  };

  // Routine definition
  // Diamond rendering
  var _renderDiamond = function(data) {
    canvasContext.beginPath();
    canvasContext.moveTo(data.start.x, data.start.y + (data.height / 2));
    canvasContext.lineTo(data.start.x + (data.width / 2), data.start.y);
    canvasContext.lineTo(data.end.x, data.start.y + (data.height / 2));
    canvasContext.lineTo(data.start.x + (data.width / 2), data.end.y);
    canvasContext.closePath();

    if (data.color) {
      canvasContext.lineWidth = data.thickness;
      canvasContext.strokeStyle = data.color;
      canvasContext.stroke();
    }
    if (data.background) {
      canvasContext.fillStyle = data.background;
      canvasContext.fill();
    }
  };

  // Routine definition
  // Ellipse rendering
  var _renderEllipse = function(data) {
    canvasContext.beginPath();
    canvasContext.ellipse(data.start.x + data.radius.x, data.start.y + data.radius.y, data.radius.x, data.radius.y, 0, 0, Math.PI * 2);

    if (data.color) {
      canvasContext.lineWidth = data.thickness;
      canvasContext.strokeStyle = data.color;
      canvasContext.stroke();
    }
    if (data.background) {
      canvasContext.fillStyle = data.background;
      canvasContext.fill();
    }
  };

  // Routine definition
  // Text rendering
  var _renderText = function(data) {
    if (data.color) {
      canvasContext.beginPath();
      canvasContext.font = (data.italic ? "italic " : "") + (data.bold ? "bold " : "") + data.size;
      canvasContext.fillStyle = data.color;
      canvasContext.fillText(data.value, data.start.x, data.start.y);
      canvasContext.stroke();
    }
  };

  // Routine definition
  // Rendering HUB
  var _renderCanvasBuffer = function() {
    canvasContext.clearRect(0, 0, canvas.width, canvas.height);

    for (var i = 0; i < canvasBuffer.length; ++i) {
      switch (canvasBuffer[i].tool) {
        case "pencil":
        _renderPencil(canvasBuffer[i]);
        break;

        case "rectangle":
        _renderRectangle(canvasBuffer[i]);
        break;
        
        case "diamond":
        _renderDiamond(canvasBuffer[i]);
        break;        
        
        case "ellipse":
        _renderEllipse(canvasBuffer[i]);
        break;
        
        case "line":
        _renderLine(canvasBuffer[i]);
        break;
        
        case "text":
        _renderText(canvasBuffer[i]);
        break;
      }
    }
  };



  /* ==================== EXECUTION ==================== */

  // Give access to built-in routines
  return {
    setCanvas: function(newCanvas) {
      canvas = newCanvas;
    },

    clearCanvas: function() {
      canvasContext.clearRect(0, 0, canvas.width, canvas.height);
    },

    setCanvasContext: function(newCanvasContext) {
      canvasContext = newCanvasContext;
    },

    setCanvasBuffer: function(newCanvasBuffer) {
      canvasBuffer = newCanvasBuffer;
    },

    addToCanvasBuffer: function(data) {
    	canvasBuffer.push(data);
    },

    clearCanvasBuffer: function() {
      canvasBuffer = [];
    },

    renderCanvasBuffer: function() {
    	_renderCanvasBuffer();
    },

    renderObject: function(data) {
      switch (data.tool) {
        case "pencil":
        _renderPencil(data);
        break;

        case "rectangle":
        _renderRectangle(data);
        break;

        case "diamond":
        _renderDiamond(data);
        break;
        
        case "ellipse":
        _renderEllipse(data);
        break;
        
        case "line":
        _renderLine(data);
        break;
        
        case "text":
        _renderText(data);
        break;
      };
    }
  };

});