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

    id: viewDependencies
    anchors.left: parent.left
    anchors.right: parent.right
    height: expanded ? columnDependencies.implicitHeight : 0

    property bool expanded: false
    property bool editMode: false
    property GanttModel ganttModel
    property alias repeaterDependencies: repeater
    property var toAdd: []
    property var toRemove: []

    Behavior on height {
        NumberAnimation {
            duration: 200
        }
    }

    Dialog {
        id: newDependencyDialog
        title: "Add a dependency to the task"
        width: Units.dp(300)
        hasActions: true
        positiveButtonText: "Add"
        negativeButtonText: "Cancel"

        property var idTasks: []

        ListItem.Standard {


            action: Label {
                text: "Type"
            }

            content: MenuField {
                width: parent.width
                id: taskTypeDialog
                model: columnDependencies.enumToTextType
            }
        }

        ListItem.Standard {
            action: Label {
                text: "Task"
                verticalAlignment: Text.AlignVCenter
                anchors.centerIn: parent
            }

            content: MenuField {
                width: parent.width
                id: taskChoiceDialog
                model: []
            }
        }

        onAccepted: {
            toAdd.push(
                        {
                            id: idTasks[taskChoiceDialog.selectedIndex],
                            type: taskTypeDialog.selectedIndex
                        })
            repeaterToAdd.model = toAdd
        }

        onOpened: {
            taskTypeDialog.selectedIndex = 0
            taskChoiceDialog.selectedIndex = 0
            var modelTaskText = []
            console.log("REMOVE : ", toRemove)
            for (var item in ganttModel.tasks)
            {
                var ignore = false
                for (var itemD in repeater.model)
                {
                    if (ganttModel.tasks[item].id === repeater.model[itemD].linkedTask)
                    {
                        if (toRemove.indexOf(repeater.model[itemD].id) != -1)
                            continue
                        ignore = true
                        break
                    }
                }
                for (var itemDA in toAdd)
                {
                    if (ganttModel.tasks[item].id === toAdd[itemDA].id)
                    {
                        ignore = true
                        break
                    }
                }
                if (ignore)
                    continue
                idTasks.push(ganttModel.tasks[item].id)
                modelTaskText.push(ganttModel.tasks[item].title)
            }
            taskChoiceDialog.model = modelTaskText
        }
    }

    Column {
        id: columnDependencies
        anchors.fill: parent

        property var enumToTextType: ["Finish to start", "Start to start", "Finish to finish", "Start to finish"]

        spacing: Units.dp(8)

            Repeater {
                id: repeater
                model: []
                delegate: ListItem.Standard {
                    id: delegateAlreadyDepen
                    visible: toRemove.indexOf(modelData.id)

                    secondaryItem: Label {
                        anchors.verticalCenter: parent.verticalCenter
                        anchors.right: parent.right

                        text: columnDependencies.enumToTextType[modelData.type]
                    }

                    Component.onCompleted: {
                        console.log(modelData.type, " : ", modelData.linkedTask)
                        text = Qt.binding(function () {
                            console.log(ganttModel.tasks)
                            for (var i = 0; i < ganttModel.tasks.length; ++i)
                            {
                                console.log(ganttModel.tasks[i])
                                if (ganttModel.tasks[i].id === modelData.linkedTask)
                                {
                                    console.log(ganttModel.tasks[i].title);
                                    return ganttModel.tasks[i].title;
                                }
                            }
                            return "";
                        })
                    }

                    action: IconButton {
                        iconName: "action/delete"
                        color: Theme.primaryColor
                        anchors.centerIn: parent
                        size: Units.dp(32)
                        visible: editMode
                        onClicked: {
                            toRemove.push(modelData.id)
                            delegateAlreadyDepen.visible = false
                        }
                    }
                }
            }

            Repeater {
                id: repeaterToAdd
                model: toAdd
                delegate: ListItem.Standard {
                    secondaryItem: Label {
                        anchors.verticalCenter: parent.verticalCenter
                        anchors.right: parent.right

                        text: columnDependencies.enumToTextType[modelData.type]
                    }

                    Component.onCompleted: {
                        text = Qt.binding(function () {
                            console.log(ganttModel.tasks)
                            for (var i = 0; i < ganttModel.tasks.length; ++i)
                            {
                                console.log(ganttModel.tasks[i])
                                if (ganttModel.tasks[i].id === modelData.id)
                                {
                                    console.log(ganttModel.tasks[i].title);
                                    return ganttModel.tasks[i].title;
                                }
                            }
                            return "";
                        })
                    }

                    action: IconButton {
                        iconName: "action/delete"
                        color: Theme.primaryColor
                        anchors.centerIn: parent
                        size: Units.dp(32)
                        onClicked: {
                            var array = toAdd
                            array.splice(index, 1)
                            toAdd = array
                            repeaterToAdd.model = toAdd
                        }
                    }
                }
            }

            ListItem.Standard {

                visible: editMode

                action: Icon {
                    anchors.centerIn: parent
                    name: "content/add_circle_outline"
                    size: Units.dp(32)
                }

                text: "Add a new dependency to the task"

                onClicked: {
                    console.log("Open");
                    newDependencyDialog.open()
                }
            }
    }
}
