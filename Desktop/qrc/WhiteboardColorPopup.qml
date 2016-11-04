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
    id: mainView

    property double buttonSize: Units.dp(32)
    property int numberPerRow: 5
    property string color
    property string selectedColor: "#FF0000"
    readonly property int numberOfRow: Math.ceil(repeater.model.length / 5)

    //anchor: Item.Bottom
    height: (buttonSize * numberOfRow) + buttonFlow.spacing * (numberOfRow - 1) + Units.dp(32)
    width: (buttonSize * numberPerRow) + (buttonFlow.spacing * (numberPerRow - 1)) + Units.dp(32)

    signal chooseColor(var color)

    Flow {
        id: buttonFlow
        spacing: Units.dp(8)
        anchors.top: parent.top
        anchors.left: parent.left
        anchors.right: parent.right

        anchors.margins: Units.dp(16)

        Repeater {
            id: repeater
            model: ["#000000", null, "#FFFFFF", "#EEEEEE", "#BDBDBD", "#757575", "#424242", "#F44336", "#E91E63", "#9C27B0", "#673AB7", "#3F51B5", "#2196F3", "#03A9F4", "#00BCD4", "#009688", "#4CAF50", "#8BC34A", "#CDDC39", "#FFEB3B", "#FFC107", "#FF9800", "#FF5722", "#795548", "#607D8B"]
            delegate: Item {
                height: mainView.buttonSize
                width: mainView.buttonSize
                Rectangle {
                    anchors.fill: parent
                    radius: Math.max(width/2, height/2)
                    opacity: modelData == mainView.color ? 1 : 0
                    color: selectedColor
                }
                Rectangle {
                    anchors.fill: parent
                    anchors.margins: Units.dp(2)
                    radius: Math.max(width/2, height/2)

                    color: (modelData == null) ? "#FFFFFF" : modelData

                    MouseArea {
                        anchors.fill: parent

                        onClicked: {
                            mainView.color = modelData
                            chooseColor(modelData)
                            mainView.close()
                        }
                    }
                }
            }
        }
    }
}

