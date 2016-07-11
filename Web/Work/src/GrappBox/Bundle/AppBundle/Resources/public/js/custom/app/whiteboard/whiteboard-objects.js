/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Controller definition
* APP whiteboard objects factory
*
*/
app.factory("objectFactory", function() {

	/* ==================== ROUTINES (TO WEB CANVAS) ==================== */

	// Routine definition
	// Create new pencil render object (from Web canvas)
	var _newPencil = function(size, color, points) {
	  return { tool: "pencil", size: Number(size), color: (!color ? "none" : color), points: points };
	};

	// Routine definition
	// Create new line render object (from Web canvas)
	var _newLine = function(size, color, start_x, start_y, end_x, end_y) {
	  return { tool: "line", size: Number(size), color: (!color ? "none" : color), start_x: start_x, start_y: start_y, end_x: end_x, end_y: end_y };
	};

	// Routine definition
	// Create new rectangle render object (from Web canvas)
	var _newRectange = function(size, color, fill, start_x, start_y, end_x, end_y) {
	  return { tool: "rectangle", size: Number(size), color: (!color ? "none" : color), fill: (!fill ? "none" : fill), start_x: start_x, start_y: start_y, height: end_y - start_y, width: end_x - start_x };
	};

	// Routine definition
	// Create new diamond render object (from Web canvas)
	var _newDiamond = function(size, color, fill, start_x, start_y, end_x, end_y) {
	  return { tool: "diamond", size: Number(size), color: (!color ? "none" : color), fill: (!fill ? "none" : fill), start_x: start_x, start_y: start_y, end_x: end_x, end_y: end_y, height: end_y - start_y, width: end_x - start_x };
	};

	// Routine definition
	// Create new ellipse render object (from Web canvas)
	var _newEllipse = function(size, color, fill, start_x, start_y, end_x, end_y) {
	  return { tool: "ellipse", size: Number(size), color: (!color ? "none" : color), fill: (!fill ? "none" : fill), start_x: start_x, start_y: start_y, radius_x: (Math.abs((end_x - start_x) / 2)), radius_y: (Math.abs((end_y - start_y)  / 2)) };
	};

	// Routine definition
	// Create new text render object (from Web canvas)
	var _newText = function(font, italic, bold, value, start_x, start_y, color) {
	  return { tool: "text", font: font + "pt Roboto", italic: italic, bold: bold, value: value, start_x: start_x, start_y: start_y, color: (!color ? "none" : color) };
	};

	// Routine definition
	// Create/compile canvas data to render (from Web canvas)
	var _setRenderObject = function(scope) {
		var data = {};

	  switch (scope.selected.tool) {
	    case "pencil":
	    data = _newPencil(scope.selected.size.value, scope.selected.color.value, scope.whiteboard.points);
	    break;

	    case "line":
	    data = _newLine(scope.selected.size.value, scope.selected.color.value, scope.mouse.start.x, scope.mouse.start.y, scope.mouse.end.x, scope.mouse.end.y);
	    break;

	    case "rectangle":
	    data = _newRectange(scope.selected.size.value, scope.selected.color.value, scope.selected.fill.value, scope.mouse.start.x, scope.mouse.start.y, scope.mouse.end.x, scope.mouse.end.y);
	    break;

	    case "diamond":
	    data = _newDiamond(scope.selected.size.value, scope.selected.color.value, scope.selected.fill.value, scope.mouse.start.x, scope.mouse.start.y, scope.mouse.end.x, scope.mouse.end.y);
	    break;

	    case "ellipse":
	    data = _newEllipse(
	    	scope.selected.size.value, scope.selected.color.value, scope.selected.fill.value,
	      (scope.mouse.start.x < scope.mouse.end.x ? scope.mouse.start.x : scope.mouse.end.x),
	      (scope.mouse.start.y < scope.mouse.end.y ? scope.mouse.start.y : scope.mouse.end.y),
	      (scope.mouse.start.x > scope.mouse.end.x ? scope.mouse.start.x : scope.mouse.end.x),
	      (scope.mouse.start.y > scope.mouse.end.y ? scope.mouse.start.y : scope.mouse.end.y));
	    break;

	    case "text":
	    data = _newText(scope.text.font.value, scope.text.italic, scope.text.bold, scope.text.value, scope.mouse.start.x, scope.mouse.start.y, scope.selected.color.value);
	    break;

	    default:
	    data = {};
	    break;
	  }

	  return data;
	};



	/* ==================== ROUTINES (FROM API) ==================== */

	// Routine definition
	// Create/compile canvas data to render (from API)
	var _setRenderObjectFromAPI = function(object) {
		var data = {};

	  switch (object.type) {
	    case "HANDWRITE":
	    data = _newPencil(object.lineweight, object.color, object.points);
	    break;

	    case "LINE":
	    data = _newLine(object.lineweight, object.color, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
	    break;

	    case "RECTANGLE":
	    data = _newRectange(object.lineweight, object.color, object.background, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
	    break;

	    case "DIAMOND":
	    data = _newDiamond(
	      object.lineweight, object.color, object.background,
	      (object.positionStart.x < object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y < object.positionEnd.y ? object.positionStart.y : object.positionEnd.y),
	      (object.positionStart.x > object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y > object.positionEnd.y ? object.positionStart.y : object.positionEnd.y));
	    break;

	    case "ELLIPSE":
	    data = _newEllipse(
	      object.lineweight, object.color, object.background,
	      (object.positionStart.x < object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y < object.positionEnd.y ? object.positionStart.y : object.positionEnd.y),
	      (object.positionStart.x > object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y > object.positionEnd.y ? object.positionStart.y : object.positionEnd.y));
	    break;

	    case "TEXT":
	    data = _newText(object.size, object.isItalic, object.isBold, object.text, object.positionStart.x, object.positionStart.y, object.color);
	    break;

	    default:
	    data = {};
	    break;
	  }

	  return data;
	};



	/* ==================== ROUTINES (TO API) ==================== */

	// Routine definition
	// Convert pencil render object to API draw object
	var _newPencilToAPI = function(size, color, points) {
	  return { type: "HANDWRITE", lineweight: Number(size), color: color, points: points };
	};

	// Routine definition
	// Convert line render object to API draw object
	var _newLineToAPI = function(size, color, start_x, start_y, end_x, end_y) {
	  return { type: "LINE", lineweight: Number(size), color: color, positionStart: { x: start_x, y: start_y }, positionEnd: { x: end_x, y: end_y } };
	};

	// Routine definition
	// Convert rectangle render object to API draw object
	var _newRectangeToAPI = function(size, color, fill, start_x, start_y, end_x, end_y) {
	  return { type: "RECTANGLE", lineweight: Number(size), color: color, background: (fill == "none" ? null : fill), positionStart: { x: start_x, y: start_y }, positionEnd: { x: end_x, y: end_y } };
	};

	// Routine definition
	// Convert diamond render object to API draw object
	var _newDiamondToAPI = function(size, color, fill, start_x, start_y, end_x, end_y) {
	  return { type: "DIAMOND", lineweight: Number(size), color: color, background: (fill == "none" ? null : fill), positionStart: { x: start_x, y: start_y }, positionEnd: { x: end_x, y: end_y } };
	};

	// Routine definition
	// Convert ellipse render object to API draw object
	var _newEllipseToAPI = function(size, color, fill, start_x, start_y, end_x, end_y) {
	  return { type: "ELLIPSE", lineweight: Number(size), color: color, background: (fill == "none" ? null : fill), positionStart: { x: start_x, y: start_y },  positionEnd: { x: end_x, y: end_y }, radius: { x: (Math.abs((end_x - start_x) / 2)), y: (Math.abs((end_y - start_y)  / 2)) } };
	};

	// Routine definition
	// Convert text render object to API draw object
	var _newTextToAPI = function(font, italic, bold, value, start_x, start_y, color) {
	  return { type: "TEXT", size: font, isItalic: italic, isBold: bold, text: value, positionStart: { x: start_x, y: start_y }, color: color };
	};

	// Routine definition
	// Convert/compile canvas data to render (to API)
	var _setRenderObjectToAPI = function(scope) {
		var data = {};

	  switch (scope.selected.tool) {
	    case "pencil":
	    data = _newPencilToAPI(scope.selected.size.value, scope.selected.color.value, scope.whiteboard.points);
	    break;

	    case "line":
	    data = _newLineToAPI(scope.selected.size.value, scope.selected.color.value, scope.mouse.start.x, scope.mouse.start.y, scope.mouse.end.x, scope.mouse.end.y);
	    break;

	    case "rectangle":
	    data = _newRectangeToAPI(scope.selected.size.value, scope.selected.color.value, scope.selected.fill.value, scope.mouse.start.x, scope.mouse.start.y, scope.mouse.end.x, scope.mouse.end.y);
	    break;

	    case "diamond":
	    data = _newDiamondToAPI(scope.selected.size.value, scope.selected.color.value, scope.selected.fill.value, scope.mouse.start.x, scope.mouse.start.y, scope.mouse.end.x, scope.mouse.end.y);
	    break;

	    case "ellipse":
	    data = _newEllipseToAPI(scope.selected.size.value, scope.selected.color.value, scope.selected.fill.value, scope.mouse.start.x, scope.mouse.start.y, scope.mouse.end.x, scope.mouse.end.y);
	    break;

	    case "text":
	    data = _newTextToAPI(scope.text.font.value, scope.text.italic, scope.text.bold, scope.text.value, scope.mouse.start.x, scope.mouse.start.y, scope.selected.color.value);
	    break;

	    default:
	    data = {};
	    break;
	  }

	  return data;
	};



	/* ==================== ROUTINES (TO STORE) ==================== */

	// Routine definition
	// Convert new pencil render object (to storage)
	var _newPencilToStore = function(id, size, color, points) {
	  return { tool: "pencil", size: Number(size), color: (!color ? "none" : color), points: points };
	};

	// Routine definition
	// Convert new line render object (to storage)
	var _newLineToStore = function(id, size, color, start_x, start_y, end_x, end_y) {
	  return { tool: "line", size: Number(size), color: (!color ? "none" : color), start_x: start_x, start_y: start_y, end_x: end_x, end_y: end_y };
	};

	// Routine definition
	// Convert new rectangle render object (to storage)
	var _newRectangeToStore = function(id, size, color, fill, start_x, start_y, end_x, end_y) {
	  return { tool: "rectangle", size: Number(size), color: (!color ? "none" : color), fill: (!fill ? "none" : fill), start_x: start_x, start_y: start_y, height: end_y - start_y, width: end_x - start_x };
	};

	// Routine definition
	// Convert new diamond render object (to storage)
	var _newDiamondToStore = function(id, size, color, fill, start_x, start_y, end_x, end_y) {
	  return { tool: "diamond", size: Number(size), color: (!color ? "none" : color), fill: (!fill ? "none" : fill), start_x: start_x, start_y: start_y, end_x: end_x, end_y: end_y, height: end_y - start_y, width: end_x - start_x };
	};

	// Routine definition
	// Convert new ellipse render object (to storage)
	var _newEllipseToStore = function(id, size, color, fill, start_x, start_y, end_x, end_y) {
	  return { tool: "ellipse", size: Number(size), color: (!color ? "none" : color), fill: (!fill ? "none" : fill), start_x: start_x, start_y: start_y, radius_x: (Math.abs((end_x - start_x) / 2)), radius_y: (Math.abs((end_y - start_y)  / 2)) };
	};

	// Routine definition
	// Convert new text render object (to storage)
	var _newTextToStore = function(id, font, italic, bold, value, start_x, start_y, color) {
	  return { tool: "text", font: font + "pt Roboto", italic: italic, bold: bold, value: value, start_x: start_x, start_y: start_y, color: (!color ? "none" : color) };
	};

	// Routine definition
	// Create/compile canvas data to render (from API)
	var _setRenderObjectToStore = function(id, object) {
		var data = {};

	  switch (object.type) {
	    case "HANDWRITE":
	    data = _newPencilToStore(id, object.lineweight, object.color, object.points);
	    break;

	    case "LINE":
	    data = _newLineToStore(id, object.lineweight, object.color, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
	    break;

	    case "RECTANGLE":
	    data = _newRectangeToStore(id, object.lineweight, object.color, object.background, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
	    break;

	    case "DIAMOND":
	    data = _newDiamondToStore(
	    	id, object.lineweight, object.color, object.background,
	      (object.positionStart.x < object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y < object.positionEnd.y ? object.positionStart.y : object.positionEnd.y),
	      (object.positionStart.x > object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y > object.positionEnd.y ? object.positionStart.y : object.positionEnd.y));
	    break;

	    case "ELLIPSE":
	    data = _newEllipseToStore(
	    	id, object.lineweight, object.color, object.background,
	      (object.positionStart.x < object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y < object.positionEnd.y ? object.positionStart.y : object.positionEnd.y),
	      (object.positionStart.x > object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y > object.positionEnd.y ? object.positionStart.y : object.positionEnd.y));
	    break;

	    case "TEXT":
	    data = _newTextToStore(id, object.size, object.isItalic, object.isBold, object.text, object.positionStart.x, object.positionStart.y, object.color);
	    break;

	    default:
	    data = {};
	    break;
	  }

	  return data;
	};


	/* ==================== EXECUTION ==================== */

  // Give access to built-in routines
  return {
    setRenderObject: function(data) {
      return _setRenderObject(data);
    },

    setRenderObjectFromAPI: function(data) {
      return _setRenderObjectFromAPI(data);
    },

    setRenderObjectToAPI: function(data) {
    	return _setRenderObjectToAPI(data);
    },

    setRenderObjectToStore: function(id, data) {
    	return _setRenderObjectToStore(id, data);
    }
  };

});