import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles
import QtCharts 2.0

Row {
    anchors.left: parent.left
    anchors.right: parent.right
    height: Units.dp(48)
    spacing: Units.dp(10)
    property alias colorIcon: iconBackground.color
    property alias iconName: icon.name
    property alias categoryName: text.text

    Rectangle {
        id: iconBackground
        width: Units.dp(48)
        height: width
        radius: width / 2
        color: "#44BBFF"

        Icon {
            id: icon
            anchors.fill: parent
            anchors.margins: Units.dp(8)
            name: "action/view_list"
            color: Theme.dark.iconColor
        }
        anchors.verticalCenter: parent.verticalCenter
    }

    Label {
        id: text
        text: "Tasks"
        style: "title"
        anchors.verticalCenter: parent.verticalCenter
    }
}
