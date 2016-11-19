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

        height: Math.max(createTask.height, filters.height)
        anchors.left: parent.left
        anchors.right: parent.right

        Button {
            id: createTask
            anchors.left: parent.left
            anchors.verticalCenter: parent.verticalCenter
            text: "Create a task"
            backgroundColor: Theme.primaryColor
            textColor: Theme.dark.textColor

            onClicked: {
                create()
            }
        }

        Label {
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
        }
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
                    text: modelData.tag
                    style: "body1"
                    wrapMode: Text.Wrap

                    Component.onCompleted: {
                        text = Qt.binding(function() {
                            var ret = ""
                            for (var item in modelData.tags)
                            {
                                var realname = ""
                                for (var itemName in bugModel.tags)
                                {
                                    if (modelData.tagAssigned[item] === ganttModel.tags[itemName].id)
                                    {
                                        realname = bugModel.tags[itemName].name
                                        break
                                    }
                                }
                                if (realname != "")
                                {
                                    if (ret === "")
                                        ret = realname
                                    else
                                        ret += ", " + realname
                                }
                            }
                            if (ret === "")
                                ret = "-"
                            return ret
                        })
                    }
                }

                Label {
                    anchors.margins: Units. dp(8)
                    width: parent.width * purcentWidth[3]
                    anchors.verticalCenter: parent.verticalCenter
                    text: modelData.user
                    style: "body1"
                    wrapMode: Text.Wrap
                    Component.onCompleted: {
                        text = Qt.binding(function() {
                            var ret = ""
                            for (var item in modelData.users)
                            {
                                var realname = ""
                                for (var itemName in SDataManager.project.users)
                                {
                                    if (modelData.usersRessources[item] === SDataManager.project.users[itemName].id)
                                    {
                                        realname = SDataManager.project.users[itemName].firstName + " " + SDataManager.project.users[itemName].lastName
                                        break
                                    }
                                }
                                if (realname != "")
                                {
                                    if (ret === "")
                                        ret = realname
                                    else
                                        ret += ", " + realname
                                }
                            }
                            if (ret === "")
                                ret = "-"
                            return ret
                        })
                    }
                }

                Label {
                    anchors.margins: Units. dp(8)
                    width: parent.width * purcentWidth[4]
                    anchors.verticalCenter: parent.verticalCenter
                    text: Qt.formatDate(modelData.dueDate, "yyyy-MM-dd hh:mm:ss")
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

