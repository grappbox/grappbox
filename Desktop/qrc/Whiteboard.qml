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
    id: bugTrackerItem
    property var mouseCursor

    function finishedLoad() {
    }

    WhiteboardCanvas {
        anchors.left: parent.left
        anchors.right: parent.right
        anchors.top: toolBox.bottom
        anchors.bottom: parent.bottom
        id: whiteboardCanvas
    }

    Flow {
        id: toolBox

        anchors.top: parent.top
        anchors.left: parent.left
        anchors.right: parent.right
        anchors.leftMargin: Units.dp(8)
        anchors.rightMargin: Units.dp(8)
        spacing: Units.dp(32)

        Row {
            height: Units.dp(64)
            spacing: Units.dp(8)

            IconButton {
                id: buttonMove
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "action/open_with"

                color: whiteboardCanvas.mode != 0 ? "#333333" : "#FC575E"

                onClicked: {
                    whiteboardCanvas.mode = 0
                }
            }

            IconButton {
                id: buttonHandwrite
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "content/gesture"

                color: whiteboardCanvas.mode != 1 ? "#333333" : "#FC575E"

                onClicked: {
                    whiteboardCanvas.mode = 1
                }
            }

            IconButton {
                id: buttonLine
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "content/remove"

                color: whiteboardCanvas.mode != 2 ? "#333333" : "#FC575E"

                onClicked: {
                    whiteboardCanvas.mode = 2
                }
            }

            IconButton {
                id: buttonRect
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "image/crop_din"

                color: whiteboardCanvas.mode != 3 ? "#333333" : "#FC575E"

                onClicked: {
                    whiteboardCanvas.mode = 3
                }
            }

            IconButton {
                id: buttonCircle
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "image/panorama_fish_eye"

                color: whiteboardCanvas.mode != 4 ? "#333333" : "#FC575E"

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

                color: whiteboardCanvas.mode != 5 ? "#333333" : "#FC575E"

                onClicked: {
                    whiteboardCanvas.mode = 5
                }
            }

            IconButton {
                id: buttonErase
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "action/delete"

                color: whiteboardCanvas.mode != 6 ? "#333333" : "#FC575E"

                onClicked: {
                    whiteboardCanvas.mode = 6
                }
            }
        }

        Row {
            height: Units.dp(64)
            spacing: Units.dp(8)

            IconButton {
                id: buttonColorBorder
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "editor/border_color"
                color: whiteboardCanvas.colorBorder

                onClicked: {
                    borderColor.visible = !borderColor.visible
                }

                WhiteboardColorPopup {
                    id: borderColor
                    anchors.top: parent.bottom
                    anchors.horizontalCenter: parent.horizontalCenter

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
                color: whiteboardCanvas.colorFill == "Translucent" ? "#424242" : whiteboardCanvas.colorFill

                onClicked: {
                    fillColor.visible = !fillColor.visible
                }

                WhiteboardColorPopup {
                    id: fillColor
                    anchors.top: parent.bottom
                    anchors.horizontalCenter: parent.horizontalCenter

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
                        whiteboardCanvas.tick = (selectedIndex < 4) ? selectedIndex * 0.5 : selectedIndex - 1
                    }
                }
            }
        }

        Row {
            height: Units.dp(64)
            spacing: Units.dp(8)

            IconButton {
                id: buttonText
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "editor/text_fields"

                color: whiteboardCanvas.mode != 7 ? "#333333" : "#FC575E"

                onClicked: {
                    whiteboardCanvas.mode = 7
                }
            }

            TextField {
                height: Units.dp(48)
                anchors.verticalCenter: parent.verticalCenter

                onTextChanged: {
                    whiteboardCanvas.text = text
                }
            }

            IconButton {
                id: buttonToBold
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "editor/format_bold"
                color: whiteboardCanvas.bold ? "#FC575E" : "#333333"

                onClicked: {
                    whiteboardCanvas.bold = !whiteboardCanvas.bold
                }
            }

            IconButton {
                id: buttonToItalic
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(48)
                iconName: "editor/format_italic"
                color: whiteboardCanvas.italic ? "#FC575E" : "#333333"

                onClicked: {
                    whiteboardCanvas.italic = !whiteboardCanvas.italic
                }
            }

            Item {

                width: iconSizeText.width + Units.dp(90)
                height: Units.dp(48)
                anchors.verticalCenter: parent.verticalCenter

                Icon {
                    id: iconSizeText
                    name: "editor/format_size"
                    anchors.left: parent.left
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(32)
                    width: Units.dp(32)
                }

                MenuField {
                    model: ["8", "9", "10", "11", "12", "14", "18", "24", "30", "36", "48", "60", "72", "96"]

                    anchors.right: parent.right
                    anchors.left: iconSizeText.right
                    anchors.leftMargin: Units.dp(4)
                    anchors.verticalCenter: parent.verticalCenter

                    onSelectedTextChanged: {
                        whiteboardCanvas.sizeText = parseInt(selectedText)
                    }
                }
            }
        }
    }
}

