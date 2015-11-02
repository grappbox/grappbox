/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

var initializeWhiteboardControls_function = function(whiteboardRendererFactory)
{
  var offset;

  colorArray = ['black'];
  colorValue = ['#000000'];

  canvas = document.getElementById("whiteboard-canvas");
  context = canvas.getContext("2d");
  whiteboardRendererFactory.setContext(context);
  offset = 0;
  points = [];
  lineColorNumber = 1;
  fillColorNumber = 1;
  mouseDown = false;
  startPos = { x: 0, y: 0 };
  endPos = { x: 0, y: 0 };

  canvas.onmousedown = function(eventPosition)
  {
    var data;

    points.push({
      x: eventPosition.offsetX,
      y: eventPosition.offsetY,
      color: colorValue[lineColorNumber]
    });

    mouseDown = true;

    startPos.x = points[0].x;
    startPos.y = points[0].y;
    endPos.x = points[0].x;
    endPos.y = points[0].y;

    data = createRenderObject();
    renderPath(data);
  };

  canvas.onmousemove = function(eventPosition)
  {
    var x;
    var y;
    var lastPoint;
    var data;

    if (mouseDown)
    {
      x = eventPosition.offsetX;
      y = eventPosition.offsetY;

      points.push({
        x: x,
        y: y,
        color: colorValue[lineColorNumber]
      });

      lastPoint = points[points.length - 1];
      endPos.x = lastPoint.x;
      endPos.y = lastPoint.y;

      data = createRenderObject();
      renderPath(data);    
    }

    console.log(canvas.offsetLeft);
    console.log(canvas.offsetTop);

  };

  canvas.onmouseup = function(e) {
    var data;

    mouseDown = false;
    data = createRenderObject();
    whiteboardRendererFactory.addToCanvasBuffer(data);

    points = [];
    startPos.x = 0;
    startPos.y = 0;
    endPos.x = 0;
    endPos.y = 0;
  };

};
