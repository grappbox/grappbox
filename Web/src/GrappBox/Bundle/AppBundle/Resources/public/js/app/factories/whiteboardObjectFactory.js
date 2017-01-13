/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Factory definition
// APP whiteboard objects creation
app.factory("whiteboardObjectFactory", function() {

	/* ==================== ROUTINES (LOCAL OBJECT) ==================== */

	// Routine definition
	// Create new pencil render object
	var _newPencil = function(id, color, thickness, points) {
	  return { id: id, tool: "pencil", color: color, thickness: Number(thickness), points: points };
	};

	// Routine definition
	// Create new line render object
	var _newLine = function(id, color, background, thickness, start_x, start_y, end_x, end_y) {
	  return { id: id, tool: "line", color: color, background: background, thickness: Number(thickness), start: { x: start_x, y: start_y }, end: { x: end_x, y: end_y } };
	};

	// Routine definition
	// Create new rectangle render object
	var _newRectange = function(id, color, background, thickness, start_x, start_y, end_x, end_y) {
	  return { id: id, tool: "rectangle", color: color, background: background, thickness: Number(thickness), start: { x: start_x, y: start_y }, end: { x: end_x, y: end_y }, height: end_y - start_y, width: end_x - start_x };
	};

	// Routine definition
	// Create new diamond render object
	var _newDiamond = function(id, color, background, thickness, start_x, start_y, end_x, end_y) {
	  return { id: id, tool: "diamond", color: color, background: background, thickness: Number(thickness), start: { x: start_x, y: start_y }, end: { x: end_x, y: end_y }, height: end_y - start_y, width: end_x - start_x };
	};

	// Routine definition
	// Create new ellipse render object
	var _newEllipse = function(id, color, background, thickness, start_x, start_y, end_x, end_y) {
	  return { id: id, tool: "ellipse", color: color, background: background, thickness: Number(thickness), start: { x: start_x, y: start_y }, end: { x: end_x, y: end_y }, radius: { x: Math.abs((end_x - start_x) / 2), y: Math.abs((end_y - start_y)  / 2) } };
	};

	// Routine definition
	// Create new text render object
	var _newText = function(id, color, start_x, start_y, end_x, end_y, value, italic, bold, size) {
	  return { id: id, tool: "text", color: color, start: { x: start_x, y: start_y }, end: { x: end_x, y: end_y }, value: value, italic: italic, bold: bold, size: size + "pt Roboto" };
	};

	// Routine definition
	// Compile canvas data into a local render object
	var _setRenderObject = function(id, tool, points, mouse) {
		var data = {};

	  switch (tool.name) {
	    case "pencil":
	    data = _newPencil(id, tool.shape.color, tool.shape.thickness.value, points);
	    break;

	    case "line":
	    data = _newLine(id, tool.shape.color, tool.shape.background, tool.shape.thickness.value, mouse.start.x, mouse.start.y, mouse.end.x, mouse.end.y);
	    break;

	    case "rectangle":
	    data = _newRectange(id, tool.shape.color, tool.shape.background, tool.shape.thickness.value, mouse.start.x, mouse.start.y, mouse.end.x, mouse.end.y);
	    break;

	    case "diamond":
	    data = _newDiamond(id, tool.shape.color, tool.shape.background, tool.shape.thickness.value, mouse.start.x, mouse.start.y, mouse.end.x, mouse.end.y);
	    break;

	    case "ellipse":
	    data = _newEllipse(
	    	id, tool.shape.color, tool.shape.background, tool.shape.thickness.value,
	      (mouse.start.x < mouse.end.x ? mouse.start.x : mouse.end.x),
	      (mouse.start.y < mouse.end.y ? mouse.start.y : mouse.end.y),
	      (mouse.start.x > mouse.end.x ? mouse.start.x : mouse.end.x),
	      (mouse.start.y > mouse.end.y ? mouse.start.y : mouse.end.y));
	    break;

	    case "text":
	    data = _newText(id, tool.shape.color, mouse.start.x, mouse.start.y, mouse.end.x, mouse.end.y, tool.text.value, tool.text.italic, tool.text.bold, tool.text.size.value);
	    break;

	    default:
	    data = {};
	    break;
	  }

	  return data;
	};



	/* ==================== ROUTINES (TO LOCAL OBJECT) ==================== */

	// Routine definition
	// Convert an API render object to a local render object
	var _convertToLocalObject = function(id, object) {
		var data = {};

	  switch (object.type) {
	    case "HANDWRITE":
	    data = _newPencil(id, object.color, object.lineWeight, object.points);
	    break;

	    case "LINE":
	    data = _newLine(id, object.color, object.background, object.lineWeight, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
	    break;

	    case "RECTANGLE":
	    data = _newRectange(id, object.color, object.background, object.lineWeight, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
	    break;

	    case "DIAMOND":
	    data = _newDiamond(id, object.color, object.background, object.lineWeight, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y);
	    break;

	    case "ELLIPSE":
	    data = _newEllipse(
	    	id, object.color, object.background, object.lineWeight,
	      (object.positionStart.x < object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y < object.positionEnd.y ? object.positionStart.y : object.positionEnd.y),
	      (object.positionStart.x > object.positionEnd.x ? object.positionStart.x : object.positionEnd.x),
	      (object.positionStart.y > object.positionEnd.y ? object.positionStart.y : object.positionEnd.y));
	    break;

	    case "TEXT":
	    data = _newText(id, object.color, object.positionStart.x, object.positionStart.y, object.positionEnd.x, object.positionEnd.y, object.text, object.isItalic, object.isBold, object.size);
	    break;

	    default:
	    data = {};
	    break;
	  }

	  return data;
	};



	/* ==================== ROUTINES (API OBJECT) ==================== */

	// Routine definition
	// Convert pencil render object to API draw object
	var _newPencil_API = function(color, thickness, points) {
	  return { type: "HANDWRITE", lineWeight: Number(thickness), color: color, points: points };
	};

	// Routine definition
	// Convert line render object to API draw object
	var _newLine_API = function(color, background, thickness, start_x, start_y, end_x, end_y) {
	  return { type: "LINE", lineWeight: Number(thickness), color: color, positionStart: { x: start_x, y: start_y }, positionEnd: { x: end_x, y: end_y } };
	};

	// Routine definition
	// Convert rectangle render object to API draw object
	var _newRectange_API = function(color, background, thickness, start_x, start_y, end_x, end_y) {
	  return { type: "RECTANGLE", lineWeight: Number(thickness), color: color, background: background, positionStart: { x: Math.abs(start_x), y: Math.abs(start_y) }, positionEnd: { x: Math.abs(end_x), y: Math.abs(end_y) } };
	};

	// Routine definition
	// Convert diamond render object to API draw object
	var _newDiamond_API = function(color, background, thickness, start_x, start_y, end_x, end_y) {
	  return { type: "DIAMOND", lineWeight: Number(thickness), color: color, background: background, positionStart: { x: start_x, y: start_y }, positionEnd: { x: end_x, y: end_y } };
	};

	// Routine definition
	// Convert ellipse render object to API draw object
	var _newEllipse_API = function(color, background, thickness, start_x, start_y, end_x, end_y) {
	  return { type: "ELLIPSE", lineWeight: Number(thickness), color: color, background: background, positionStart: { x: start_x, y: start_y }, positionEnd: { x: end_x, y: end_y },
	  				 radius: { x: (Math.abs((end_x - start_x) / 2) != 0 ? Math.abs((end_x - start_x) / 2) : 1), y: (Math.abs((end_y - start_y)  / 2) != 0 ? Math.abs((end_y - start_y)  / 2) : 1) } };
	};

	// Routine definition
	// Convert text render object to API draw object
	var _newText_API = function(color, start_x, start_y, end_x, end_y, value, italic, bold, size) {
	  return { type: "TEXT", size: size, isItalic: italic, isBold: bold, text: value, positionStart: { x: start_x, y: start_y }, positionEnd: { x: end_x, y: end_y }, color: color };
	};

	// Routine definition
	// Convert/compile canvas data to render (to API)
	var _convertToAPIObject = function(tool, points, mouse) {
		var data = {};

	  switch (tool.name) {
	    case "pencil":
	    data = _newPencil_API(tool.shape.color, tool.shape.thickness.value, points);
	    break;

	    case "line":
	    data = _newLine_API(tool.shape.color, tool.shape.background, tool.shape.thickness.value, mouse.start.x, mouse.start.y, mouse.end.x, mouse.end.y);
	    break;

	    case "rectangle":
	    data = _newRectange_API(tool.shape.color, tool.shape.background, tool.shape.thickness.value, mouse.start.x, mouse.start.y, mouse.end.x, mouse.end.y);
	    break;

	    case "diamond":
	    data = _newDiamond_API(tool.shape.color, tool.shape.background, tool.shape.thickness.value, mouse.start.x, mouse.start.y, mouse.end.x, mouse.end.y);
	    break;

	    case "ellipse":
	    data = _newEllipse_API(
	    	tool.shape.color, tool.shape.background, tool.shape.thickness.value,
	      (mouse.start.x < mouse.end.x ? mouse.start.x : mouse.end.x),
	      (mouse.start.y < mouse.end.y ? mouse.start.y : mouse.end.y),
	      (mouse.start.x > mouse.end.x ? mouse.start.x : mouse.end.x),
	      (mouse.start.y > mouse.end.y ? mouse.start.y : mouse.end.y));
	    break;

	    case "text":
	    data = _newText_API(tool.shape.color, mouse.start.x, mouse.start.y, mouse.end.x, mouse.end.y, tool.text.value, tool.text.italic, tool.text.bold, tool.text.size.value);
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
    setRenderObject: function(id, tool, points, mouse) {
      return _setRenderObject(id, tool, points, mouse);
    },

    convertToLocalObject: function(id, object) {
      return _convertToLocalObject(id, object);
    },

    convertToAPIObject: function(tool, points, mouse) {
    	return _convertToAPIObject(tool, points, mouse);
    }
  };

});