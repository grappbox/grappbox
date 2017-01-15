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

    id: viewTasks
    anchors.left: parent.left
    anchors.right: parent.right
    height: expanded ? columnTasks.implicitHeight : 0

    property bool expanded: false
    property bool editMode: true
    property GanttModel ganttModel
    property alias repeaterTasks: repeater
    property var toAdd: []
    property var toRemove: []

    Behavior on height {
        NumberAnimation {
            duration: 200
        }
    }

    Dialog {
        id: newTaskDialog
        title: "Add a task contained by the task"
        width: Units.dp(300)
        hasActions: true
        positiveButtonText: "Add"
        negativeButtonText: "Cancel"

        property var idTasks: []

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
                            id: idTasks[taskChoiceDialog.selectedIndex]
                        })
            repeaterToAdd.model = toAdd
        }

        onOpened: {
            taskChoiceDialog.selectedIndex = 0
            var modelTaskText = []
            idTasks = []
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
        id: columnTasks
        anchors.fill: parent

        spacing: Units.dp(8)

            Repeater {
                id: repeater
                model: []
                delegate: ListItem.Standard {
                    id: delegateAlreadyDepen
                    visible: toRemove.indexOf(modelData.id)

                    Component.onCompleted: {
                        text = Qt.binding(function () {
                            for (var i = 0; i < ganttModel.tasks.length; ++i)
                            {
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

                    Component.onCompleted: {
                        text = Qt.binding(function () {
                            for (var i = 0; i < ganttModel.tasks.length; ++i)
                            {
                                if (ganttModel.tasks[i].id === modelData.id)
                                {
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

                text: "Add a task contained by the task"

                onClicked: {
                    console.log("Open");
                    newTaskDialog.open()
                }
            }
    }
}
