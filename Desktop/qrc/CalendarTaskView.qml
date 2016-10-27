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
    id: itemTaskView
    anchors.top: parent.top
    anchors.bottom: parent.bottom
    width: widthReal
    anchors.right: parent.right
    anchors.rightMargin: open ? 0 : -(widthReal - Units.dp(52))

    property int widthReal: Units.dp(400)
    property bool open: false

    property CalendarModel calendarModel

    signal openTask(EventModelData taskdata)

    Behavior on anchors.rightMargin {
        NumberAnimation {
            duration: 200
        }
    }

    View {
        anchors.right: parent.right
        anchors.top: parent.top
        anchors.bottom: parent.bottom
        width: widthReal
        elevation: 1

        Flickable {
            visible: itemTaskView.open
            id: mainFlickable
            anchors.top: viewTitle.bottom
            anchors.bottom: parent.bottom
            anchors.right: parent.right
            anchors.left: parent.left

            contentHeight: Math.max(height, eventsColumn.implicitHeight)

            Column {
                id: eventsColumn
                anchors.fill: parent

                Repeater {
                    model: calendarModel.eventDay

                    delegate: CalendarEventCard {
                        eventData: modelData
                        onClicked: {
                            itemTaskView.openTask(modelData)
                        }
                    }
                }
            }

            Label {
                id: noEventsFound
                anchors.fill: parent
                anchors.topMargin: Units.dp(32)
                visible: calendarModel.eventDay.length === 0
                text: "No events found for this date."
                verticalAlignment: Text.AlignTop
                horizontalAlignment: Text.AlignHCenter
            }
        }

        Scrollbar {
            flickableItem: mainFlickable
        }

        View {
            id: viewTitle
            anchors.left: parent.left
            anchors.right: parent.right
            height: Units.dp(52)
            elevation: itemTaskView.open ? 1 : 0

            IconButton {
                id: openMenu
                anchors.left: parent.left
                anchors.top: parent.top
                anchors.margins: Units.dp(8)
                iconName: "navigation/menu"
                width: Units.dp(36)
                height: Units.dp(36)

                Behavior on anchors.rightMargin {
                    NumberAnimation {
                        duration: 200
                    }
                }

                onClicked: itemTaskView.open = !itemTaskView.open
            }

            Label {
                id: titleLab
                text: "Details"
                style: "title"
                anchors.right: parent.right
                anchors.left: parent.left
                anchors.top: parent.top
                anchors.topMargin: Units.dp(8)
                height: Units.dp(36)
                horizontalAlignment: Text.AlignHCenter
                verticalAlignment: Text.AlignVCenter
            }
        }
    }
}

