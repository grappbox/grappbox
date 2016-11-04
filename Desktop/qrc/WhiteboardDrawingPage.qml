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
    id: whiteboardDrawing

    property WhiteboardModel whiteModel

    property var tmpObjects: []

    WhiteboardCanvas {
        anchors.fill: parent
        id: whiteboardCanvas

        onPushObject: {
            tmpObjects[whiteModel.pushObject(obj)] = id
        }

        whiteModel: whiteboardDrawing.whiteModel
    }

    function objectUpdated(id)
    {
        var i = tmpObjects[id]
        whiteboardCanvas.removeObjectTmp(i)
        tmpObjects.splice(i, 1)
    }

    Component.onCompleted: {
        whiteModel.updatedObject.connect(objectUpdated)
        whiteModel.forceUpdate.connect(forceUpdateWhiteboard)
    }

    function forceUpdateWhiteboard()
    {
        whiteboardCanvas.refreshWhiteboard()
    }

    onVisibleChanged: {
        if (visible)
            whiteboardCanvas.refreshWhiteboard()
    }

    View {
        id: toolBox

        anchors.bottom: parent.bottom
        width: mainRowToolBox.implicitWidth + Units.dp(32)
        height: Units.dp(64)
        anchors.horizontalCenter: parent.horizontalCenter
        elevation: 2

        Row {
            id: mainRowToolBox
            anchors.left: parent.left
            anchors.top: parent.top
            anchors.bottom: parent.bottom
            anchors.margins: Units.dp(16)
            spacing: Units.dp(32)

            Row {
                anchors.top: parent.top
                anchors.bottom: parent.bottom
                spacing: Units.dp(8)

                IconButton {
                    id: buttonMove
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "action/open_with"

                    color: whiteboardCanvas.mode != 0 ? "#333333" : "#27AE60"

                    onClicked: {
                        whiteboardCanvas.mode = 0
                    }
                }

                IconButton {
                    id: buttonHandwrite
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "content/gesture"

                    color: whiteboardCanvas.mode != 1 ? "#333333" : "#27AE60"

                    onClicked: {
                        whiteboardCanvas.mode = 1
                    }
                }

                IconButton {
                    id: buttonLine
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "content/remove"

                    color: whiteboardCanvas.mode != 2 ? "#333333" : "#27AE60"

                    onClicked: {
                        whiteboardCanvas.mode = 2
                    }
                }

                IconButton {
                    id: buttonRect
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "image/crop_din"

                    color: whiteboardCanvas.mode != 3 ? "#333333" : "#27AE60"

                    onClicked: {
                        whiteboardCanvas.mode = 3
                    }
                }

                IconButton {
                    id: buttonCircle
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "image/panorama_fish_eye"

                    color: whiteboardCanvas.mode != 4 ? "#333333" : "#27AE60"

                    onClicked: {
                        whiteboardCanvas.mode = 4
                    }
                }

                IconButtonTransform {
                    id: buttonDiamond
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "image/crop_din"
                    iconRotation: 90

                    color: whiteboardCanvas.mode != 5 ? "#333333" : "#27AE60"

                    onClicked: {
                        whiteboardCanvas.mode = 5
                    }
                }

                IconButton {
                    id: buttonText
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "editor/text_fields"

                    color: whiteboardCanvas.mode != 7 ? "#333333" : "#27AE60"

                    onClicked: {
                        whiteboardCanvas.mode = 7
                        popupWhiteboardText.open(buttonText, 0, 0)
                    }

                    WhiteboardText {
                        id: popupWhiteboardText

                        onVisibleChanged: {

                        }

                        whiteboardCanvas: whiteboardCanvas
                    }
                }

                IconButton {
                    id: buttonErase
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "action/delete"

                    color: whiteboardCanvas.mode != 6 ? "#333333" : "#27AE60"

                    onClicked: {
                        whiteboardCanvas.mode = 6
                    }
                }
            }

            Row {
                anchors.top: parent.top
                anchors.bottom: parent.bottom
                spacing: Units.dp(8)

                IconButton {
                    id: buttonColorBorder
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "editor/border_color"
                    color: whiteboardCanvas.colorBorder

                    onClicked: {
                        var testPos = buttonColorBorder.mapToItem(null, 0, 0)
                        console.log(testPos)
                        var offset = buttonColorBorder.mapToItem(borderColor, 0, 0)
                        borderColor.open(buttonColorBorder, 0, -Units.dp(32))
                    }

                    WhiteboardColorPopup {
                        id: borderColor
                        anchor: Item.BottomLeft
                        color: whiteboardCanvas.colorBorder

                        onChooseColor: {
                            whiteboardCanvas.colorBorder = color
                        }
                    }
                }

                IconButton {
                    id: buttonColorFill
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(48)
                    iconName: "editor/format_color_fill"
                    color: whiteboardCanvas.colorFill == "#00000000" ? "#424242" : whiteboardCanvas.colorFill

                    onClicked: {
                        fillColor.open(buttonColorBorder, 0, -Units.dp(32))
                    }

                    WhiteboardColorPopup {
                        id: fillColor
                        anchor: Item.BottomLeft
                        color: whiteboardCanvas.colorFill

                        onChooseColor: {
                            whiteboardCanvas.colorFill = color
                        }
                    }
                }

                Item {

                    width: iconSizeBorder.width + Units.dp(90)
                    height: Units.dp(48)
                    anchors.verticalCenter: parent.verticalCenter

                    Icon {
                        id: iconSizeBorder
                        name: "editor/border_style"
                        anchors.left: parent.left
                        anchors.verticalCenter: parent.verticalCenter
                        height: Units.dp(32)
                        width: Units.dp(32)
                    }

                    MenuField {
                        model: ["0.5 pt", "1 pt", "1.5 pt", "2 pt", "3 pt", "4 pt", "5 pt"]

                        anchors.right: parent.right
                        anchors.left: iconSizeBorder.right
                        anchors.leftMargin: Units.dp(4)
                        anchors.verticalCenter: parent.verticalCenter

                        onSelectedIndexChanged: {
                            whiteboardCanvas.tick = (selectedIndex < 4) ? (selectedIndex + 1) * 0.5 : selectedIndex - 1
                        }
                    }
                }
            }

        }
    }
}

