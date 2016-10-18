import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

Item {
    id: mainItem

    property int mode
    property bool bold
    property bool italic
    property string text
    property int sizeText
    property string colorBorder: "#000000"
    property string colorFill: "Translucent"
    property double tick: 1
    property double minimumDistHandWrite: 0.1

    Flickable {
        id: flickableMain
        anchors.fill: parent
        interactive: false

        contentHeight: canvasMain.height
        contentWidth: canvasMain.width

        Canvas {
            id: canvasMain
            width: 4096
            height: 4096

            property int currentMode: -1

            property var context

            property double startX
            property double startY
            property double endX
            property double endY

            property var tabHandWrite: []

            property var objectsAdded: []

            function drawLine(startX, startY, endX, endY, tick, colorBorder, colorFill) {
                context.strokeStyle = colorBorder;
                context.lineWidth = tick;
                context.beginPath();
                context.moveTo(startX, startY);
                context.lineTo(endX, endY);
                context.stroke();
                context.closePath();
            }

            function drawRect(startX, startY, endX, endY, tick, colorBorder, colorFil) {
                context.strokeStyle = colorBorder;
                context.lineWidth = tick;
                context.beginPath();
                if (colorFil !== "Translucent")
                {
                    context.fillStyle = colorFil;
                    context.fillRect(Math.min(startX, endX), Math.min(startY, endY), Math.abs(startX - endX), Math.abs(startY - endY));
                }
                context.rect(Math.min(startX, endX), Math.min(startY, endY), Math.abs(startX - endX), Math.abs(startY - endY));
                context.stroke();
            }

            function drawCircle(startX, startY, endX, endY, tick, colorBorder, colorFil) {
                context.strokeStyle = colorBorder;
                context.lineWidth = tick;
                context.beginPath();
                context.fillStyle = colorFil;
                context.ellipse(Math.min(startX, endX), Math.min(startY, endY), Math.abs(startX - endX), Math.abs(startY - endY));
                if (colorFil !== "Translucent")
                {
                    context.fillStyle = colorFil;
                    context.fill();
                }
                context.stroke();
            }

            function drawDiamond(startX, startY, endX, endY, tick, colorBorder, colorFil) {
                context.strokeStyle = colorBorder;
                context.lineWidth = tick;
                context.beginPath();
                var realStX = Math.min(startX, endX);
                var realStY = Math.min(startY, endY);
                var realEdX = Math.max(startX, endX);
                var realEdY = Math.max(startY, endY);
                var realWidth = realEdX - realStX;
                var realHeight = realEdY - realStY;
                context.moveTo(startX + realWidth / 2, startY);
                context.lineTo(endX, startY + realHeight / 2);
                context.lineTo(startX + realWidth / 2, endY);
                context.lineTo(startX, startY + realHeight / 2);
                context.lineTo(startX + realWidth / 2, startY);
                if (colorFil !== "Translucent")
                {
                    context.fillStyle = colorFil;
                    context.fill();
                }
                context.stroke();
            }

            function drawHand(arrayPoint, tick, colorBorder) {
                context.strokeStyle = colorBorder
                context.lineWidth = tick
                context.beginPath();
                context.moveTo(arrayPoint[0].x, arrayPoint[0].y)
                for (var i = 1; i < arrayPoint.length; ++i) {
                    context.lineTo(arrayPoint[i].x, arrayPoint[i].y);
                }
                context.stroke();
            }

            function drawText(x, y, text, color, size, bold, italic) {
                context.font = (bold ? "bold " : "") + (italic ? "italic " : "") + size + "px sans-serif";
                context.strokeStyle = color;
                context.fillStyle = color;
                context.lineWidth = 1;
                context.beginPath();
                context.text(text, x, y);
                context.fill();
                context.stroke();
            }

            onPaint: {
                context = getContext("2d");
                context.clearRect(0, 0, width, height);
                context.fillStyle = "#FFFFFF";
                context.fillRect(0, 0, width, height);
                switch (currentMode)
                {
                case 1:
                    drawHand(tabHandWrite, tick, colorBorder);
                    break;
                case 2:
                    drawLine(startX, startY, endX, endY, tick, colorBorder, colorFill);
                    break;
                case 3:
                    drawRect(startX, startY, endX, endY, tick, colorBorder, colorFill);
                    break;
                case 4:
                    drawCircle(startX, startY, endX, endY, tick, colorBorder, colorFill);
                    break;
                case 5:
                    drawDiamond(startX, startY, endX, endY, tick, colorBorder, colorFill);
                    break;
                case 6:
                    break;
                case 7:
                    drawText(endX, endY, text, colorBorder, sizeText, bold, italic);
                    break;
                }
                for (var i = 0; i < objectsAdded.length; ++i) {
                    var obj = objectsAdded[i];
                    switch (obj.type)
                    {
                    case 1:
                        drawHand(obj.arrayPoint, obj.tickness, obj.borderCol);
                        break;
                    case 2:
                        drawLine(obj.begin.x, obj.begin.y, obj.end.x, obj.end.y, obj.tickness, obj.borderCol, obj.fillCol);
                        break;
                    case 3:
                        drawRect(obj.begin.x, obj.begin.y, obj.end.x, obj.end.y, obj.tickness, obj.borderCol, obj.fillCol);
                        break;
                    case 4:
                        drawCircle(obj.begin.x, obj.begin.y, obj.end.x, obj.end.y, obj.tickness, obj.borderCol, obj.fillCol);
                        break;
                    case 5:
                        drawDiamond(obj.begin.x, obj.begin.y, obj.end.x, obj.end.y, obj.tickness, obj.borderCol, obj.fillCol);
                        break;
                    case 6:
                        break;
                    case 7:
                        drawText(obj.end.x, obj.end.y, obj.text, obj.borderCol, obj.sizeText, obj.boldM, obj.italicM);
                        break;
                    }
                }
            }

            MouseArea {
                anchors.fill: parent

                onPressed: {
                    canvasMain.currentMode = mode
                    canvasMain.startX = mouseX
                    canvasMain.startY = mouseY
                    canvasMain.endX = mouseX
                    canvasMain.endY = mouseY
                    if (mode == 1)
                    {
                        canvasMain.tabHandWrite = [{x: mouseX, y: mouseY}]
                    }
                }

                onPositionChanged: {
                    canvasMain.endX = mouseX
                    canvasMain.endY = mouseY
                    if (canvasMain.currentMode == 1) {
                        var lastObj = canvasMain.tabHandWrite[canvasMain.tabHandWrite.length - 1];
                        if (Math.sqrt(Math.pow((mouseX - lastObj.x), 2), Math.pow((mouseY - lastObj.y), 2)) >= minimumDistHandWrite)
                        {
                            canvasMain.tabHandWrite.push({x: mouseX, y: mouseY})
                        }
                        canvasMain.requestPaint()
                    }
                    else
                        canvasMain.requestPaint()
                }

                onReleased: {
                    canvasMain.currentMode = -1
                    var newObj = {type: mode,
                                    begin: {x: canvasMain.startX,
                                            y: canvasMain.startY},
                                    end: {x: mouseX,
                                          y: mouseY},
                                    text: mainItem.text,
                                    sizeText: mainItem.sizeText,
                                    arrayPoint: canvasMain.tabHandWrite.slice(),
                                    borderCol: colorBorder,
                                    fillCol: colorFill,
                                    tickness: tick,
                                    boldM: bold,
                                    italicM: italic}
                    canvasMain.tabHandWrite = []
                    canvasMain.objectsAdded.push(newObj)
                    canvasMain.requestPaint()
                }
            }
        }
    }

    Scrollbar {
        flickableItem: flickableMain
    }
}

