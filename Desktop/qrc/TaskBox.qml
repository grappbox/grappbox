import QtQuick 2.0
import QtQuick.Layouts 1.1
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem
import QtQuick.Controls 1.3 as Controls
import GrappBoxController 1.0

Item {
    width: 300
    height: 180

    property var mouseCursor

    signal onClicked();

    property TaskData task;

    View {
        anchors.fill: parent

        MouseArea {
            id: viewMouseArea
            anchors.fill: parent

            hoverEnabled: true

            onClicked: {
                if (mouse.button == Qt.LeftButton)
                {
                    dashboardModel.selectProject(modelData)
                }
            }

            onHoveredChanged: {
                if (containsMouse)
                    mouseCursor.cursorShape = Qt.PointingHandCursor
                else
                    mouseCursor.cursorShape = Qt.ArrowCursor
            }

            Component.onCompleted: {
                if (containsMouse)
                    mouseCursor.cursorShape = Qt.PointingHandCursor
                else
                    mouseCursor.cursorShape = Qt.ArrowCursor
            }
        }

        ColumnLayout {
            anchors.fill: parent
            anchors.margins: Units. dp(16)

            Label {
                anchors {
                    left: parent.left
                    right: parent.right
                    margins: Units. dp(16)
                }

                style: "body2"
                text: task.title
            }

            Item {
                anchors {
                    left: parent.left
                    right: parent.right
                    margins: Units. dp(16)
                }
                height: Units. dp(24)
                Icon {
                    id: dueDateIcon
                    name: "action/alarm_off"
                    anchors.verticalCenter: parent.verticalCenter
                    anchors.left: parent.left
                    width: 24
                    height: 24
                }

                Label {
                    anchors {
                        left: bugIcon.right
                        right: parent.right
                        leftMargin: Units. dp(16)
                        verticalCenter: parent.verticalCenter
                    }
                    style: "body1"
                    text: task.dueDate
                    color: task.dueDate > new Date() ? Theme.primaryColor : Theme.accentColor
                }
            }

            Item {
                anchors {
                    left: parent.left
                    right: parent.right
                    margins: Units. dp(16)
                }
                height: Units. dp(24)
                Icon {
                    id: finishDateIcon
                    name: "action/alarm_off"
                    anchors.verticalCenter: parent.verticalCenter
                    anchors.left: parent.left
                    width: 24
                    height: 24
                }

                Label {
                    anchors {
                        left: bugIcon.right
                        right: parent.right
                        leftMargin: Units. dp(16)
                        verticalCenter: parent.verticalCenter
                    }
                    style: "body1"
                    text: task.dueDate
                    color: task.dueDate > new Date() ? Theme.primaryColor : Theme.accentColor
                }
            }
        }
    }
}

