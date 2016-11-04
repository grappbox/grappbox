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
    property string colorFill: null
    property string colorText: "#000000"
    property double tick: 0.5
    property double minimumDistHandWrite: 0.1

    property WhiteboardModel whiteModel

    function refreshWhiteboard()
    {
        canvasMain.requestPaint();
    }

    function removeObjectTmp(id)
    {
        console.log("Remove object ", id, " before length : ", canvasMain.objectsAdded.length)
        canvasMain.objectsAdded.splice(id, 1);
        console.log("After length : ", canvasMain.objectsAdded.length)
        refreshWhiteboard();
    }

    signal pushObject(var obj, var id)

    function getAPIObjectFormat(obj) {
        switch (obj.type)
        {
        case 1: //HANDWRITE
            var ret = {
                type: "HANDWRITE",
                lineWeight: obj.tickness,
                points: obj.arrayPoint,
                color: obj.borderCol
            }
            return ret
        case 2: //LINE
            var ret = {
                type: "LINE",
                lineWeight: obj.tickness,
                color: obj.borderCol,
                positionStart: obj.begin,
                positionEnd: obj.end
            }
            return ret
        case 3: //RECTANGLE
            var ret = {
                type: "RECTANGLE",
                lineWeight: obj.tickness,
                color: obj.borderCol,
                background: obj.fillCol,
                positionStart: obj.begin,
                positionEnd: obj.end
            }
            return ret
        case 4: //ELLIPSE
            var ret = {
                type: "ELLIPSE",
                color: obj.borderCol,
                background: obj.fillCol,
                lineWeight: obj.tickness,
                positionStart: obj.begin,
                positionEnd: obj.end,
                radius: {
                    x: Math.abs(obj.begin.x - obj.end.x),
                    y: Math.abs(obj.begin.y - obj.end.y)
                }
            }
            return ret
        case 5: //DIAMOND
            var ret = {
                type: "DIAMOND",
                lineWeight: obj.tickness,
                color: obj.borderCol,
                background: obj.fillCol,
                positionStart: obj.begin,
                positionEnd: obj.end
            }
            return ret
        case 7: //TEXT
            var ret = {
                type: "TEXT",
                color: obj.textColor,
                positionStart: obj.end,
                positionEnd: obj.end,
                text: obj.text,
                size: obj.sizeText,
                isItalic: obj.italicM,
                isBold: obj.boldM
            }
            return ret
        default:
            return null
        }
    }

    Flickable {
        id: flickableMain
        anchors.fill: parent
        interactive: false

        contentHeight: canvasMain.height
        contentWidth: canvasMain.width

        Canvas {
            id: canvasMain
            width: 4096
            height: 2160

            renderTarget: Canvas.FramebufferObject

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
                if (colorFil !== null)
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
                if (colorFil !== null)
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
                context.moveTo(realStX + realWidth / 2, realStY);
                context.lineTo(realEdX, realStY + realHeight / 2);
                context.lineTo(realStX + realWidth / 2, realEdY);
                context.lineTo(realStX, realStY + realHeight / 2);
                context.lineTo(realStX + realWidth / 2, realStY);
                if (colorFil !== null)
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

            onAvailableChanged: {
                console.log("Available changed !")
            }

            onPaint: {
                context = getContext("2d");
                context.clearRect(0, 0, width, height);
                context.fillStyle = "#FFFFFF";
                context.fillRect(0, 0, width, height);
                if (whiteModel.currentItem != -1)
                {
                    var data = whiteModel.whiteboardList[whiteModel.currentItem];
                    for (var i = 0; i < data.content.length; ++i) {
                        var obj = data.content[i];
                        switch (obj.type) {
                        case "HANDWRITE":
                            drawHand(obj.points, obj.lineWeight, obj.color);
                            break;
                        case "LINE":
                            drawLine(obj.positionStart.x, obj.positionStart.y, obj.positionEnd.x, obj.positionEnd.y, obj.lineWeight, obj.color, fillColor);
                            break;
                        case "RECTANGLE":
                            drawRect(obj.positionStart.x, obj.positionStart.y, obj.positionEnd.x, obj.positionEnd.y, obj.lineWeight, obj.color, obj.background);
                            break;
                        case "ELLIPSE":
                            drawCircle(obj.positionStart.x, obj.positionStart.y, obj.positionEnd.x, obj.positionEnd.y, obj.lineWeight, obj.color, obj.background);
                            break;
                        case "DIAMOND":
                            drawDiamond(obj.positionStart.x, obj.positionStart.y, obj.positionEnd.x, obj.positionEnd.y, obj.lineWeight, obj.color, obj.background);
                            break;
                        case "TEXT":
                            drawText(obj.positionStart.x, obj.positionStart.y, obj.text, obj.color, obj.size, obj.isBold, obj.isItalic);
                            break;
                        }
                    }
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
                        drawText(obj.end.x, obj.end.y, obj.text, obj.textColor, obj.sizeText, obj.boldM, obj.italicM);
                        break;
                    }
                }
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
                    drawText(endX, endY, text, colorText, sizeText, bold, italic);
                    break;
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
                    else if (canvasMain.currentMode == 0) {
                        var sensitivity = 0.3
                        flickableMain.contentX += (canvasMain.startX - canvasMain.endX) * sensitivity
                        flickableMain.contentY += (canvasMain.startY - canvasMain.endY) * sensitivity
                        if (flickableMain.contentX < 0)
                            flickableMain.contentX = 0
                        if (flickableMain.contentX + flickableMain.width > flickableMain.contentWidth)
                            flickableMain.contentX = flickableMain.contentWidth - flickableMain.width
                        if (flickableMain.contentY < 0)
                            flickableMain.contentY = 0
                        if (flickableMain.contentY + flickableMain.height > flickableMain.contentHeight)
                            flickableMain.contentY = flickableMain.contentHeight - flickableMain.height
                        canvasMain.startX = canvasMain.endX
                        canvasMain.startY = canvasMain.endY
                    }
                    else
                        canvasMain.requestPaint()
                }

                onReleased: {
                    if (canvasMain.currentMode == 0)
                    {
                        canvasMain.currentMode = -1;
                        return
                    }
                    if (canvasMain.currentMode == 6)
                    {
                        canvasMain.currentMode = -1;
                        whiteModel.removeObjectAt({x: mouseX, y: mouseY}, 20);
                    }

                    canvasMain.currentMode = -1
                    var newObj = {type: mode,
                                    begin: {x: canvasMain.startX,
                                            y: canvasMain.startY},
                                    end: {x: mouseX,
                                          y: mouseY},
                                    text: mainItem.text,
                                    sizeText: mainItem.sizeText,
                                    textColor: colorText,
                                    arrayPoint: canvasMain.tabHandWrite.slice(),
                                    borderCol: colorBorder,
                                    fillCol: colorFill,
                                    tickness: tick,
                                    boldM: bold,
                                    italicM: italic}
                    var toSendObj = getAPIObjectFormat(newObj)
                    if (toSendObj === null)
                    {
                        console.log("Error in code, toSendObj is null. ")
                    }
                    else
                    {
                        canvasMain.tabHandWrite = []
                        canvasMain.objectsAdded.push(newObj)
                        pushObject(toSendObj, canvasMain.objectsAdded.length - 1)
                        canvasMain.requestPaint()
                    }
                }
            }
        }
    }

    Scrollbar {
        flickableItem: flickableMain
    }
}

