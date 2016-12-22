import QtQuick 2.4
import QtQuick.Layouts 1.1
import GrappBoxController 1.0
import Material 0.2
import Material.ListItems 0.1

View {
    id: dropDown


    width: Units.dp(400)
    elevation: 1

    property bool open: false

    signal itemSelected(string type)

    anchors.top: parent.top
    anchors.bottom: parent.bottom
    anchors.right: parent.right
    anchors.rightMargin: notification.open ? 0 : -width

    NotificationModel {
        id: modelNotif
    }

    Behavior on anchors.rightMargin {
        NumberAnimation {
            duration: 200
        }
    }

    ListView {
        id: listView

        anchors.top: title.bottom
        anchors.bottom: parent.bottom
        anchors.left: parent.left
        anchors.right: parent.right

        interactive: true

        model: modelNotif.notification
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

    Scrollbar {
        flickableItem: listView
    }
}
