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
    property bool editMode: true
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
        id: newUserDialog
        title: "Add a user to the task"
        width: Units.dp(300)
        hasActions: true
        positiveButtonText: "Add"
        negativeButtonText: "Cancel"

        property var idUsers: []

        ListItem.Standard {


            action: Label {
                text: "User"
            }

            content: MenuField {
                width: parent.width
                id: taskUserDialog
                model: []
            }
        }

        ListItem.Standard {
            action: Label {
                text: "Task"
                verticalAlignment: Text.AlignVCenter
                anchors.centerIn: parent
            }

            content: Slider {
                anchors.verticalCenter: parent
                id: taskProgressionDialog
                stepSize: 1
                minimumValue: 0
                maximumValue: 100
            }
        }

        onAccepted: {
            var user
            for (var item in SDataManager.project.users)
            {
                console.log(idUsers[taskUserDialog.selectedIndex], " : ", SDataManager.project.users[item].id)
                if (idUsers[taskUserDialog.selectedIndex] === SDataManager.project.users[item].id)
                {
                    user = SDataManager.project.users[item];
                }
            }
            console.log(user.firstName, user.lastName)
            toAdd.push(
                        {
                            id: idUsers[taskUserDialog.selectedIndex],
                            firstname: user.firstName,
                            lastname: user.lastName,
                            percent: taskProgressionDialog.value
                        })
            repeaterToAdd.model = toAdd
            editListItem.visible = toAdd.length - toRemove.length + repeater.model.length !== SDataManager.project.users.length
        }

        onOpened: {
            taskUserDialog.selectedIndex = 0
            taskProgressionDialog.value = 50
            var modelTaskText = []
            idUsers = []
            for (var item in SDataManager.project.users)
            {
                var ignore = false
                for (var itemD in repeater.model)
                {
                    if (SDataManager.project.users[item].id === repeater.model[itemD].id)
                    {
                        if (toRemove.indexOf(repeater.model[itemD].id) != -1)
                            continue
                        ignore = true
                        break
                    }
                }
                for (var itemDA in toAdd)
                {
                    if (SDataManager.project.users[item].id === toAdd[itemDA].id)
                    {
                        ignore = true
                        break
                    }
                }
                if (ignore)
                    continue
                idUsers.push(SDataManager.project.users[item].id)
                console.log(SDataManager.project.users[item])
                modelTaskText.push(SDataManager.project.users[item].firstName + " " + SDataManager.project.users[item].lastName)
            }
            taskUserDialog.model = modelTaskText
        }
    }

    Column {
        id: columnDependencies
        anchors.fill: parent

        spacing: Units.dp(8)

        Repeater {
            id: repeater
            model: []
            delegate: ListItem.Subtitled {
                id: userDelegate

                visible: toRemove.indexOf(modelData.id) == -1

                action: CircleImageAsync {
                    anchors.centerIn: parent
                    width: Units.dp(32)
                    height: Units.dp(32)
                }

                content: Item {
                    anchors.left: parent.left
                    anchors.top: parent.top
                    anchors.bottom: parent.bottom

                    width: progressUser.width + percentUser.width + Units.dp(8)

                    ProgressBar {
                        id: progressUser
                        anchors.left: parent.left
                        anchors.verticalCenter: parent.verticalCenter
                        width: Units.dp(120)
                        value: modelData.percent
                        minimumValue: 0
                        maximumValue: 100
                        color: modelData.percent >= 100 ? Theme.primaryColor : "#44BBFF"
                    }

                    Label {
                        id: percentUser
                        anchors.left: progressUser.right
                        anchors.leftMargin:  Units.dp(8)
                        anchors.verticalCenter: parent.verticalCenter
                        text: Math.round(modelData.percent) + "%"
                    }
                }

                secondaryItem: IconButton {
                    iconName: "action/delete"
                    color: Theme.primaryColor
                    anchors.centerIn: parent
                    size: Units.dp(32)
                    onClicked: {
                        toRemove.push(modelData.id)
                        userDelegate.visible = false
                        editListItem.visible = true
                    }
                }

                text: modelData.firstName + " " + modelData.lastName
            }
        }

        Repeater {
            id: repeaterToAdd
            model: toAdd
            delegate: ListItem.Subtitled {
                action: CircleImageAsync {
                    anchors.centerIn: parent
                    width: Units.dp(32)
                    height: Units.dp(32)
                }

                content: Item {
                    anchors.left: parent.left
                    anchors.top: parent.top
                    anchors.bottom: parent.bottom

                    width: progressUserToAdd.width + percentUserToAdd.width + Units.dp(8)

                    ProgressBar {
                        id: progressUserToAdd
                        anchors.left: parent.left
                        anchors.verticalCenter: parent.verticalCenter
                        width: Units.dp(120)
                        value: modelData.percent
                        minimumValue: 0
                        maximumValue: 100
                        color: modelData.percent >= 100 ? Theme.primaryColor : "#44BBFF"
                    }

                    Label {
                        id: percentUserToAdd
                        anchors.left: progressUserToAdd.right
                        anchors.leftMargin:  Units.dp(8)
                        anchors.verticalCenter: parent.verticalCenter
                        text: Math.round(modelData.percent) + "%"
                    }
                }

                secondaryItem: IconButton {
                    iconName: "action/delete"
                    color: Theme.primaryColor
                    anchors.centerIn: parent
                    size: Units.dp(32)
                    onClicked: {
                        editListItem.visible = true
                        var array = toAdd
                        array.splice(index, 1)
                        toAdd = array
                        repeaterToAdd.model = toAdd
                    }
                }

                text: modelData.firstname + " " + modelData.lastname
            }
        }

        ListItem.Standard {
            id: editListItem
            visible: editMode && toAdd.length - toRemove.length + repeater.model.length !== SDataManager.project.users.length

            action: Icon {
                anchors.centerIn: parent
                name: "content/add_circle_outline"
                size: Units.dp(32)
            }

            text: "Add a new user to the task"

            onClicked: {
                newUserDialog.open()
            }
        }
    }
}
