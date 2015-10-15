var canvas = new fabric.Canvas('canvas', { isDrawingMode: false });
canvas.selection = false;
fabric.Object.prototype.selectable = false;

function Whiteboard() {
}

Whiteboard.prototype.StartDrawing = function () {
    canvas.isDrawingMode = true;
}



Whiteboard.prototype.DrawRect = function () {
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
}

Whiteboard.prototype.DrawCircle = function () {
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
}

Whiteboard.prototype.DrawLine = function () {
    canvas.isDrawingMode = false;

    canvas.observe('mouse:down', function (e) { LineMouseDown(e); });
    canvas.observe('mouse:up', function (e) { LineMouseUp(e); });
    //canvas.observe('mouse:move', function (e) { LineMouseMove(e); });

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
}

Whiteboard.prototype.LoadSVG = function () {
    //var svg = new String('<?xml version="1.0" encoding="UTF-8" standalone="no" ?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
    //<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="300" height="300" xml:space="preserve"><desc>Created with Fabric.js 1.5.0</desc><defs></defs><path d="M 108.5 69 Q 108.5 69 109 69 Q 109.5 69 108.75 69 Q 108 69 107.5 69 Q 107 69 106.5 69 Q 106 69 105.5 69 Q 105 69 105 69.5 Q 105 70 105 70.5 Q 105 71 104.5 71.5 Q 104 72 104 73 Q 104 74 103.5 74.5 Q 103 75 103 75.5 Q 103 76 125 114 Q 147 152 148.5 152.5 Q 150 153 151.5 153.5 Q 153 154 154.5 154 Q 156 154 158 154.5 Q 160 155 161 155 Q 162 155 163.5 155 Q 165 155 166 155 Q 167 155 168.5 155 Q 170 155 171 154.5 Q 172 154 173 154 Q 174 154 181 148.5 Q 188 143 189 142.5 Q 190 142 190.5 141 Q 191 140 192 138.5 Q 193 137 193.5 136.5 Q 194 136 195 135 Q 196 134 196 132.5 Q 196 131 197 129.5 Q 198 128 198.5 127 Q 199 126 199.5 125 Q 200 124 200.5 122.5 Q 201 121 202.5 117 Q 204 113 204.5 111.5 Q 205 110 205 108.5 Q 205 107 205 105.5 Q 205 104 205 103 Q 205 102 205.5 100.5 Q 206 99 206 94 Q 206 89 206 87.5 Q 206 86 205.5 85 Q 205 84 205 83 Q 205 82 204.5 81 Q 204 80 204 79 Q 204 78 202.5 75 Q 201 72 200 71 Q 199 70 198.5 69 Q 198 68 197 67.5 Q 196 67 195.5 66 Q 195 65 194 64.5 Q 193 64 190.5 62 Q 188 60 186.5 59.5 Q 185 59 183.5 58 Q 182 57 181 56.5 Q 180 56 178.5 56 Q 177 56 175.5 56 Q 174 56 172.5 55.5 Q 171 55 167.5 55 Q 164 55 162.5 55 Q 161 55 159.5 55 Q 158 55 156 55 Q 154 55 152.5 55.5 Q 151 56 148.5 56 Q 146 56 144.5 56.5 Q 143 57 137 60 Q 131 63 129 64.5 Q 127 66 125.5 67 Q 124 68 122 69.5 Q 120 71 118.5 72.5 Q 117 74 111.5 81 Q 106 88 105.5 89.5 Q 105 91 104 93.5 Q 103 96 102 98 Q 101 100 100.5 101.5 Q 100 103 100 105.5 Q 100 108 99.5 113.5 Q 99 119 99 121 Q 99 123 99 124.5 Q 99 126 99 128 Q 99 130 99.5 132.5 Q 100 135 100.5 136.5 Q 101 138 102 140 Q 103 142 105.5 148 Q 108 154 109 155.5 Q 110 157 111 158.5 Q 112 160 113.5 162 Q 115 164 116 165 Q 117 166 118.5 168 Q 120 170 123.5 174 Q 127 178 128.5 178.5 Q 130 179 131.5 180.5 Q 133 182 134.5 182.5 Q 136 183 137 184.5 Q 138 186 139.5 186 Q 141 186 142.5 187 Q 144 188 146.5 189 Q 149 190 150 190.5 Q 151 191 152 191.5 Q 153 192 154 192 Q 155 192 156.5 192 Q 158 192 159 192 Q 160 192 161 192 Q 162 192 164.5 192 Q 167 192 167.5 191.5 Q 168 191 169 191 Q 170 191 170.5 190.5 Q 171 190 171.5 189.5 Q 172 189 174 186.5 Q 176 184 176 183 Q 176 182 176.5 180.5 Q 177 179 177 178 Q 177 177 177.5 176 Q 178 175 178 174 Q 178 173 177.5 169 Q 177 165 177 163.5 Q 177 162 176.5 160.5 Q 176 159 175.5 158 Q 175 157 174.5 155.5 Q 174 154 173 152.5 Q 172 151 170.5 149 Q 169 147 166 142 Q 163 137 162 136 Q 161 135 159.5 133 Q 158 131 157 130 Q 156 129 154.5 127.5 Q 153 126 151.5 125 Q 150 124 147 121.5 Q 144 119 142 118 Q 140 117 139 116.5 Q 138 116 136.5 115 Q 135 114 127 112 Q 119 110 117.5 109.5 Q 116 109 114.5 109 Q 113 109 111.5 109 Q 110 109 101.5 109.5 Q 93 110 92 110.5 Q 91 111 89.5 111.5 Q 88 112 87 112.5 Q 86 113 85 113.5 Q 84 114 83.5 114.5 Q 83 115 82 115.5 Q 81 116 79.5 118 Q 78 120 77.5 121 Q 77 122 76.5 123 Q 76 124 75.5 125 Q 75 126 75 126.5 Q 75 127 75 128 Q 75 129 74.5 132.5 Q 74 136 74 138 Q 74 140 74 140.5 Q 74 141 74 142.5 Q 74 144 74 145 Q 74 146 74 147 Q 74 148 74.5 148.5 Q 75 149 76 151 Q 77 153 77 154 Q 77 155 77.5 155.5 Q 78 156 78.5 157 Q 79 158 81.5 161.5 L 84 165" style="stroke: rgb(0, 0, 0); stroke-width: 1; stroke-dasharray: ; stroke-linecap: round; stroke-linejoin: round; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(140 123.5) translate(-140, -123.5) " stroke-linecap="round" />
    //<circle cx="0" cy="0" r="30" style="stroke: none; stroke-width: 1; stroke-dasharray: ; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: red; fill-rule: nonzero; opacity: 1;" transform="translate(148 215) "/>
    //<rect x="-30" y="-35" rx="0" ry="0" width="60" height="70" style="stroke: none; stroke-width: 1; stroke-dasharray: ; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: red; fill-rule: nonzero; opacity: 1;" transform="translate(247 256)"/>
    //<line x1="78.5" y1="-73" x2="-78.5" y2="73" style="stroke: #000000; stroke-width: 2; stroke-dasharray: ; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;" transform="translate(153.5 177)"/>
    //</svg>');
    ////canvas.loadSVGFromString(svg);
    //fabric.loadSVGFromString(svg, function (objects, options) {
    //    var obj = fabric.util.groupSVGElements(objects, options);
    //    canvas.add(obj).renderAll();
    //});
}

Whiteboard.prototype.ToSVG = function () {
    console.log(canvas.toSVG());
}

whiteboard = new Whiteboard();