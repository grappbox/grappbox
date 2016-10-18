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
    id: userIconMain
    width: Units.dp(36)
    height: Units.dp(36)

    property int idUser
    property alias avatarDate: circleImage.avatarDate

    CircleImageAsync {
        id: circleImage
        anchors.fill: parent
    }

    MouseArea {
        id: area
        hoverEnabled: true
        anchors.fill: parent

        onHoveredChanged: {
            if (containsMouse)
            {

            }
        }
    }

    Popover {
        id: popover
        data: Label {
            id: nameLabel
        }
    }
}
