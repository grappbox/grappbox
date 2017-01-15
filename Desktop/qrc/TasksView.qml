import QtQuick 2.0

import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

Column {

    anchors.fill: parent
    anchors.margins: Units. dp(16)

    property GanttModel ganttModel
    property var purcentWidth: [0.10, 0.25, 0.20, 0.20, 0.25]

    signal view(var task)
    signal create()

    Item {
        width: parent.width
        height: Units. dp(8)
    }

    Item {

        height: createTask.height + Units.dp(16)
        anchors.left: parent.left
        anchors.right: parent.right

        Button {
            id: createTask
            anchors.left: parent.left
            anchors.verticalCenter: parent.verticalCenter
            text: "Create a task"
            backgroundColor: "#44BBFF"
            textColor: Theme.dark.textColor

            onClicked: {
                create()
            }
        }

        /*Label {
            text: "Filters : "
            anchors.verticalCenter: parent.verticalCenter
            anchors.right: filters.left
            width: Units.dp(90)
            style: "body2"
        }

        FiltersMenuField {
            id: filters
            anchors.right: parent.right
            anchors.verticalCenter: parent.verticalCenter
            width: Units.dp(256)
            model: ["To do", "Doing", "Done", "Containers", "Milestone"]
        }*/
    }

    Item {
        width: parent.width
        height: Units. dp(8)
    }

    Row {
        id: rowTabHeader
        anchors.left: parent.left
        anchors.right: parent.right
        anchors.leftMargin: Units. dp(8)
        anchors.rightMargin: Units. dp(8)
        height: Math.max(Units. dp(32), implicitHeight)
        spacing: Units. dp(8)

        Repeater {
            model: [
                ["content/flag", "Type"],
                ["action/label_outline", "Title"],
                ["action/loyalty", "Tags"],
                ["social/person", "Assigned users"],
                ["action/event", "Creation date"]
            ]
            delegate: Item {
                width: parent.width * purcentWidth[index]
                height: parent.height

                Icon {
                    id: iconTitle
                    anchors.left: parent.left
                    anchors.verticalCenter: parent.verticalCenter
                    name: modelData[0]
                }

                Label {
                    id: labelTitle
                    visible: rowTabHeader.width >= 800
                    anchors.left: iconTitle.right
                    anchors.right: parent.right
                    anchors.leftMargin: Units. dp(8)
                    anchors.verticalCenter: parent.verticalCenter
                    text: modelData[1]
                    style: "body2"
                    wrapMode: Text.Wrap
                }
            }
        }
    }

    Rectangle {
        height: Units. dp(2)
        color: "#dddddd"
        anchors.left: parent.left
        anchors.right: parent.right
    }

    Repeater {
        model: ganttModel.tasks
        delegate: ListItem.BaseListItem {
            height: Math.max(Units. dp(24), rowItem.implicitHeight + Units. dp(16))

            Row {
                id: rowItem
                anchors.left: parent.left
                anchors.right: parent.right
                anchors.leftMargin: Units. dp(8)
                anchors.rightMargin: Units. dp(8)
                anchors.bottom: parent.bottom
                height: parent.height

                spacing: Units. dp(8)

                Label {
                    anchors.margins: Units. dp(8)
                    width: parent.width * purcentWidth[0]
                    anchors.verticalCenter: parent.verticalCenter
                    text: modelData.isMilestone ? "Milestone" : modelData.taskChild.Length > 0 ? "Container" : "Task"
                    style: "body1"
                    wrapMode: Text.Wrap
                }

                Label {
                    anchors.margins: Units. dp(8)
                    width: parent.width * purcentWidth[1]
                    anchors.verticalCenter: parent.verticalCenter
                    text: modelData.title
                    style: "body1"
                    wrapMode: Text.Wrap
                }

                Label {
                    anchors.margins: Units. dp(8)
                    width: parent.width * purcentWidth[2]
                    anchors.verticalCenter: parent.verticalCenter
                    text: ""//modelData.tag
                    style: "body1"
                    wrapMode: Text.Wrap

                    function updateText() {
                        console.log("Update : ", modelData.tagAssigned.length)
                        for (var item in modelData.tagAssigned)
                        {
                            if (text === "")
                                text = modelData.tagAssigned[item].name
                            else
                                text += ", " + modelData.tagAssigned[item].name
                        }
                        if (text === "")
                            text = "-"
                    }

                    Component.onCompleted: {
                        modelData.tagAssignedChanged.connect(updateText);
                        updateText();
                    }
                }

                Label {
                    anchors.margins: Units. dp(8)
                    width: parent.width * purcentWidth[3]
                    anchors.verticalCenter: parent.verticalCenter
                    text: ""//modelData.user
                    style: "body1"
                    wrapMode: Text.Wrap

                    function updateText() {
                        console.log("Update text !! : ", modelData.title)
                        console.log(modelData.usersAssigned)
                        var ret = ""
                        for (var item in modelData.usersAssigned)
                        {
                            var realname = modelData.usersAssigned[item].firstName + " " + modelData.usersAssigned[item].lastName + "(" + modelData.usersAssigned[item].percent + "%)"
                            if (ret === "")
                                ret = realname
                            else
                                ret += ", " + realname
                        }
                        if (ret === "")
                            ret = "-"
                        text = ret
                    }

                    Component.onCompleted: {
                        modelData.usersAssignedChanged.connect(updateText)
                        updateText()
                    }
                }

                Label {
                    anchors.margins: Units. dp(8)
                    width: parent.width * purcentWidth[4]
                    anchors.verticalCenter: parent.verticalCenter
                    text: Qt.formatDateTime(modelData.dueDate, "yyyy-MM-dd hh:mm:ss")
                    style: "body1"
                    wrapMode: Text.Wrap

                    Component.onCompleted: {
                        console.log(modelData.dueDate)
                    }
                }
            }

            Rectangle {
                height: Units. dp(1)
                color: "#dddddd"
                anchors.left: parent.left
                anchors.right: parent.right
                anchors.bottom: parent.bottom
            }

            onClicked: {
                view(modelData)
            }
        }
    }
}

