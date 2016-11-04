import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

View {
    id: mainView
    elevation: 1

    property double buttonSize: Units.dp(32)
    property int numberPerRow: 5

    height: buttonFlow.height + Units.dp(32)
    width: (buttonSize * numberPerRow) + (buttonFlow.spacing * (numberPerRow - 1)) + Units.dp(32)

    visible: false

    signal chooseColor(var color)

    Flow {
        id: buttonFlow
        spacing: Units.dp(8)
        anchors.top: parent.top
        anchors.left: parent.left
        anchors.right: parent.right

        anchors.margins: Units.dp(16)

        Repeater {
            model: ["#F44336", "#E91E63", "#9C27B0", "#673AB7", "#3F51B5", "#2196F3", "#03A9F4", "#00BCD4", "#009688", "#4CAF50", "#8BC34A", "#CDDC39", "#FFEB3B", "#FFC107", "#FF9800", "#FF5722", "#795548", "#607D8B"]
            delegate: Rectangle {
                height: mainView.buttonSize
                width: mainView.buttonSize
                radius: Math.max(width/2, height/2)

                color: (modelData == "#00000000") ? "#FFFFFF" : modelData

                MouseArea {
                    anchors.fill: parent

                    onClicked: {
                        chooseColor(modelData)
                        mainView.visible = false
                    }
                }
            }
        }
    }
}

