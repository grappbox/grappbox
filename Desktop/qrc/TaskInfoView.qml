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
    id: infoView

    property GanttModel ganttModel
    anchors.fill: parent
    anchors.margins: Units.dp(16)
    property TaskData currentTask
    property bool editMode: true

    function loadTask(task)
    {
        currentTask = task;
        titleTicket.text = (currentTask.isMilestone ? "(Milestone) " : "") + currentTask.title;
        messageTicket.text = currentTask.description;
        if (!currentTask.isMilestone)
            dates.text = "From " + Qt.formatDateTime(currentTask.startDate, "yyyy-MM-dd hh:mm") + " to " + Qt.formatDateTime(currentTask.dueDate, "yyyy-MM-dd hh:mm");
        else
            dates.text = "The " + Qt.formatDateTime(currentTask.startDate, "yyyy-MM-dd hh:mm");
        dateEdit.dateBegin = currentTask.startDate
        dateEdit.dateEnd = currentTask.dueDate
        taskProgression.value = currentTask.progression;
        taskTag.repeaterTags.model = currentTask.tagAssigned;
        console.log(ganttModel)
        taskUser.repeaterDependencies.model = currentTask.usersAssigned;
        console.log(currentTask.usersAssigned)
        dependencies.repeaterDependencies.model = currentTask.dependenciesAssigned;
        taskContainer.repeaterTasks.model = currentTask.taskChild;
        console.log(currentTask.createDate)
        createdBy.text = "Created by " + currentTask.creator.firstName + " " + currentTask.creator.lastName + " the " + Qt.formatDateTime(currentTask.createDate, "yyyy-MM-dd hh:mm:ss");
        milestone.checked = currentTask.isMilestone;
    }

    signal edit(var task)
    signal back()

    IconTextButton {
        iconName: "hardware/keyboard_backspace"
        text: "BACK"
        width: Units.dp(120)
        height: Units.dp(32)
        elevation: 0

        onClicked: {
            back()
        }
    }

    Item {
        height: Units.dp(8)
        width: parent.width
    }

    Column {
        id: columnMainMessage
        width: parent.width - Units.dp(56)

        Label {
            visible: !editMode
            id: titleTicket
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)

            text: "Title"
            style: "title"
        }

        TextField {
            id: editTitleTicket
            width: parent.width
            text: titleTicket.text
            visible: editMode
            placeholderText: "Title"
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        Label {
            visible: !editMode
            id: messageTicket
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)

            text: "Description"
            style: "body2"
            wrapMode: Text.Wrap
        }

        TextArea {
            id: editMessageTicket
            text: messageTicket.text
            visible: editMode

            width: parent.width
            height: Units.dp(64)
            placeHolderText: "Message"
        }

        Item {
            height: Units.dp(16)
            width: parent.width
        }

        Label {
            visible: !editMode
            id: dates
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)

            text: "From XXXX to XXXX"
            style: "body2"
            wrapMode: Text.Wrap
        }

        TaskDateEdit {
            visible: editMode
            id: dateEdit
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        Item {
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)
            height: Units.dp(32)

            Label {
                id: labelProgression
                anchors.left: parent.left
                anchors.verticalCenter: parent.verticalCenter
                text: "Progression"
            }

            ProgressBar {
                visible: !editMode
                id: taskProgression
                anchors.left: labelProgression.right
                anchors.leftMargin: Units.dp(8)
                anchors.verticalCenter: parent.verticalCenter
                width: Units.dp(200)
                color: "#44BBFF"
                minimumValue: 0
                maximumValue: 100
                value: 60
            }

            Slider {
                id: sliderProgression
                visible: editMode
                anchors.left: taskProgression.left
                anchors.right: taskProgression.right
                color: "#44BBFF"
                minimumValue: 0
                maximumValue: 100
                value: taskProgression.value
                onValueChanged: {
                    taskProgression.value = value
                }
            }

            Label {
                anchors.left: taskProgression.right
                anchors.leftMargin: Units.dp(8)
                anchors.verticalCenter: parent.verticalCenter
                text: Math.round(taskProgression.value) + "%"
            }
        }

        Item {
            visible: editMode
            height: Units.dp(8)
            width: parent.width
        }

        CheckBox {
            visible: editMode
            id: milestone
            checked: false
            text: "Is a milestone ?"

            onCheckedChanged: {
                headerTaskContain.visible = taskContainer.repeaterTasks.model.Length > 0 || (editMode && !milestone.checked);
                taskContainer.visible = taskContainer.repeaterTasks.model.Length > 0 || (editMode && !milestone.checked);
            }
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        CustomListStandart {
            id: sectionHeaderTag
            expanded: true
            expandedColor: "#44BBFF"
            text: "Tags"
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        TaskTagEdit {
            id: taskTag
            ganttModel: infoView.ganttModel
            expanded: sectionHeaderTag.expanded
            editMode: infoView.editMode
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        CustomListStandart {
            id: headerUserAssigned
            text: "Assigned users"
            expandedColor: "#44BBFF"
            expanded: true
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        TagUsersEdit {
            id: taskUser
            ganttModel: infoView.ganttModel
            expanded: headerUserAssigned.expanded
            editMode: infoView.editMode
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        CustomListStandart {
            id: headerDependencies
            expandedColor: "#44BBFF"
            text: "Dependencies"
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        TaskDependenciesEdit {
            id: dependencies
            ganttModel: infoView.ganttModel
            expanded: headerDependencies.expanded
            editMode: infoView.editMode
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        CustomListStandart {
            id: headerTaskContain
            visible: taskContainer.repeaterTasks.model.Length > 0 || (editMode && !milestone.checked)

            expandedColor: "#44BBFF"

            text: "Contained task"
        }

        Item {
            visible: repeaterTasks.model.Length > 0
            height: Units.dp(8)
            width: parent.width
        }

        TaskContainerEdit {
            visible: taskContainer.repeaterTasks.model.Length > 0 || (editMode && !milestone.checked)
            id: taskContainer
            ganttModel: infoView.ganttModel
            expanded: headerTaskContain.expanded
            editMode: infoView.editMode
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        Item {

            anchors.left: parent.left
            anchors.right: parent.right
            height: closeButton.height

            CircleImageAsync {
                id: image
                width: parent.height
                height: parent.height
                anchors.left: parent.left
                anchors.verticalCenter: parent.verticalCenter


            }

            Label {
                id: createdBy
                anchors.left: image.right
                anchors.leftMargin: Units.dp(8)
                anchors.verticalCenter: parent.verticalCenter

                text: "Created by XXX XXX the YYYY-MM-DD hh:mm:ss"
                style: "caption"
            }

            Button {
                anchors.right: closeButton.left
                anchors.rightMargin: Units.dp(8)

                text: editMode ? "SAVE" : "EDIT"

                onClicked: {
                    if (editMode)
                    {
                        var user = {Add: [], Remove: []}
                        var dep = {Add: [], Remove: []}
                        var tag = {Add: [], Remove: []}
                        var task = {Add: [], Remove: []}

                        var enumToTextDep = ["fs", "ss", "ff", "sf"]

                        // tag
                        for (var item in taskTag.toAdd)
                            tag.Add.push(taskTag.toAdd[item].id)
                        tag.Remove = taskTag.toRemove

                        console.log(tag.Remove);

                        // user
                        for (var itemU in taskUser.toAdd)
                        {
                            user.Add.push({id: taskUser.toAdd[itemU].id, percent: taskUser.toAdd[itemU].percent})
                        }
                        user.Remove = taskUser.toRemove

                        console.log(user.Remove);

                        // dep
                        for (var itemD in dependencies.toAdd)
                        {
                            dep.Add.push(
                                        {
                                            name: enumToTextDep[dependencies.toAdd[itemD].type],
                                            id: dependencies.toAdd[itemD].id
                                        });
                        }
                        dep.Remove = dependencies.toRemove

                        console.log(dep.Remove);

                        // task
                        for (var itemT in taskContainer.toAdd)
                            task.Add.push(taskContainer.toAdd[itemT].id)
                        task.Remove = taskContainer.toRemove

                        console.log(task.Remove);

                        var isntContainer = taskContainer.toAdd.length == 0 && (taskContainer.toRemove.length === taskContainer.repeaterTasks.model.length);

                        ganttModel.editTask(currentTask.id,
                                            editTitleTicket.text,
                                            editMessageTicket.text,
                                            milestone.checked,
                                            !isntContainer,
                                            sliderProgression.value,
                                            dateEdit.getDateBegin(),
                                            dateEdit.getDateEnd(),
                                            user,
                                            dep,
                                            task,
                                            tag);
                    }
                    else
                        editMode = true
                }
            }

            Button {
                id: closeButton
                anchors.right: parent.right
                text: editMode ? "CANCEL" : "DELETE"
                textColor: Theme.primaryColor

                onClicked: {
                    if (editMode)
                        editMode = false
                    else
                    {
                        deleteUser.id = currentTask.id
                        deleteUser.open()
                    }
                }
            }

        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }
    }



    Dialog {
        id: deleteUser

        title: "Do you want to remove this task ?"
        text: "You will not be able to retrieve it."
        hasActions: true
        positiveButtonText: "Yes"
        negativeButtonText: "No"

        property int id

        width: Units.dp(300)

        onAccepted: {
            ganttModel.deleteTask(id)
            back();
        }
    }
}

