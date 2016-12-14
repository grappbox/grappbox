import QtQuick 2.4
import QtQuick.Layouts 1.1

import Material 0.2
import Material.ListItems 0.1

View {
    id: dropDown


    width: Units.dp(300)
    elevation: 1

    property bool open: false

    signal itemSelected(string type)

    anchors.top: parent.top
    anchors.bottom: parent.bottom
    anchors.right: parent.right
    anchors.rightMargin: notification.open ? 0 : -width

    Behavior on anchors.rightMargin {
        NumberAnimation {
            duration: 200
        }
    }

    View {
        id: title
        anchors.top: parent.top
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(48)

        elevation: 2

        Label {
            text: "Notifications"
            style: "title"
            anchors.centerIn: parent
        }
    }

    ListView {
        id: listView

        anchors.top: title.bottom
        anchors.bottom: parent.bottom
        anchors.left: parent.left
        anchors.right: parent.right

        height: count > 0 ? menu.height : 0

        interactive: true

        delegate: Subtitled {
            id: delegateItem

            text: modelData.type
            subText: modelData.message

            onClicked: {
                itemSelected(modelData.type)
                listView.currentIndex = index
            }
        }
    }

    Scrollbar {
        flickableItem: listView
    }
}
