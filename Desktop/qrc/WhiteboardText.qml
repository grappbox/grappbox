import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

CustomDropdown {

    property var whiteboardCanvas
    closeOnOtherDropDownOpen: false

    height: column.implicitHeight + Units.dp(32)
    width: Units.dp(300)
    anchor: Item.BottomLeft

    Column {
        id: column
        anchors.top: parent.top
        anchors.left: parent.left
        anchors.right: parent.right
        anchors.margins: Units.dp(16)
        spacing: Units.dp(8)

        Item {
            id: textItem
            anchors.left: parent.left
            anchors.right: parent.right
            height: Units.dp(32)

            Icon {
                id: iconTextField
                anchors.left: parent.left
                width: parent.height
                height: parent.height
                anchors.verticalCenter: parent.verticalCenter
                name: "editor/text_fields"
            }

            TextField {
                anchors.left: iconTextField.right
                anchors.leftMargin: Units.dp(8)
                anchors.right: parent.right
                height: parent.height
                anchors.verticalCenter: parent.verticalCenter

                onTextChanged: {
                    whiteboardCanvas.text = text
                }
            }
        }

        RowLayout {
            anchors.left: parent.left
            anchors.right: parent.right
            height: Units.dp(32)
            IconButton {
                id: buttonToBold
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(32)
                iconName: "editor/format_bold"
                color: whiteboardCanvas.bold ? "#27AE60" : "#333333"

                onClicked: {
                    whiteboardCanvas.bold = !whiteboardCanvas.bold
                }
            }

            IconButton {
                id: buttonToItalic
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(32)
                iconName: "editor/format_italic"
                color: whiteboardCanvas.italic ? "#27AE60" : "#333333"

                onClicked: {
                    whiteboardCanvas.italic = !whiteboardCanvas.italic
                }
            }

            Item {

                width: iconSizeText.width + Units.dp(90)
                height: Units.dp(32)
                anchors.verticalCenter: parent.verticalCenter

                Icon {
                    id: iconSizeText
                    name: "editor/format_size"
                    anchors.left: parent.left
                    anchors.verticalCenter: parent.verticalCenter
                    height: Units.dp(32)
                    width: Units.dp(32)
                }

                CustomMenuField {
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

            IconButton {
                id: buttonColorFill
                anchors.verticalCenter: parent.verticalCenter
                height: Units.dp(32)
                iconName: "editor/format_color_text"
                color: whiteboardCanvas.colorText == "#00000000" ? "#424242" : whiteboardCanvas.colorText

                onClicked: {
                    fillColor.open(buttonColorFill, 0, 0)
                }

                WhiteboardColorPopup {
                    id: fillColor
                    anchor: Item.BottomLeft
                    color: whiteboardCanvas.colorText

                    onChooseColor: {
                        whiteboardCanvas.colorText = color
                    }
                }
            }
        }
    }
}

