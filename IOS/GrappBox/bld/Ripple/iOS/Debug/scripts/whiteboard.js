var canvas = new fabric.Canvas('canvas', { isDrawingMode: false });
canvas.selection = false;
fabric.Object.prototype.selectable = false;

var StartDrawing = function()
{
    canvas.isDrawingMode = true;
}

var StopDrawing = function()
{
    canvas.isDrawingMode = false;
    canvas.item(0).selectable = false;
}

var DrawRect = function ()
{
    var mouse_pos = { x: 0, y: 0 };

    canvas.isDrawingMode = false;
    canvas.observe('mouse:down', function (e) {
        mouse_pos = canvas.getPointer(e.e);
        canvas.add(new fabric.Rect({
            top: mouse_pos.y,
            left: mouse_pos.x,
            width: 60,
            height: 70,
            fill: 'red',
            selectable: false,
            evented: false
        }));
        canvas.off('mouse:down');
    });
};

var DrawCircle = function ()
{
    var mouse_pos = { x: 0, y: 0 };

    canvas.isDrawingMode = false;
    canvas.observe('mouse:down', function (e) {
        mouse_pos = canvas.getPointer(e.e);
        canvas.add(new fabric.Circle({
            top: mouse_pos.y,
            left: mouse_pos.x,
            radius: 30,
            fill: 'red',
            selectable: false,
            evented: false
        }));
        canvas.off('mouse:down');
    });
};

var DrawLine = function () {
    canvas.isDrawingMode = false;

    canvas.observe('mouse:down', function (e) { LineMouseDown(e); });
    canvas.observe('mouse:up', function (e) { LineMouseUp(e); });
    canvas.observe('mouse:move', function (e) { LineMouseMove(e); });

    var Started = false;
    var StartX = 0;
    var StartY = 0;

    function LineMouseDown(e) {
        var Mouse = canvas.getPointer(e.e);

        Started = true;
        StartX = Mouse.x;
        StartY = Mouse.y;
        canvas.off('mouse:down');
    }

    function LineMouseMove(e) {
        if (!Started)
            return (false);
        canvas.off('mouse:move');
    }

    function LineMouseUp(e) {
        if (Started) {
            var Mouse = canvas.getPointer(e.e);

            canvas.add(new fabric.Line([StartX, StartY, Mouse.x, Mouse.y],
                {
                    stroke: "#000000",
                    strokeWidth: 2,
                    selectable: false,
                    evented: false
                }));
            canvas.renderAll();
            canvas.calcOffset();

            Started = false;
            canvas.off('mouse:up');
        }
    }
};