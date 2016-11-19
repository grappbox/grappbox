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

    function loadTask(task)
    {
        currentTask = task;
        titleTicket.text = (currentTask.isMilestone ? "(Milestone) " : "") + currentTask.title;
        messageTicket.text = currentTask.description;
        if (!currentTask.isMilestone)
            dates.text = "From " + Qt.formatDateTime(currentTask.startDate, "yyyy-MM-dd hh:mm") + " to " + Qt.formatDateTime(currentTask.dueDate, "yyyy-MM-dd hh:mm");
        else
            dates.text = "The " + Qt.formatDateTime(currentTask.startDate, "yyyy-MM-dd hh:mm");
        taskProgression.value = currentTask.progression;
        repeaterTag.model = currentTask.tagAssigned;
        repeaterUserAssigned.model = currentTask.usersRessources;
        repeaterDependencies.model = currentTask.dependenciesAssigned;
        repeaterTasks.model = currentTask.taskChild;
        createdBy.text = "Created by " + currentTask.creator.firstName + " " + currentTask.creator.lastName + " the " + Qt.formatDateTime(currentTask.createDate, "yyyy-MM-dd hh:mm:ss");
        console.log(repeaterTasks.model)
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
            id: titleTicket
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)

            text: "Title"
            style: "title"
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        Label {
            id: messageTicket
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)

            text: "Description"
            style: "body2"
            wrapMode: Text.Wrap
        }

        Item {
            height: Units.dp(16)
            width: parent.width
        }

        Label {
            id: dates
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)

            text: "From XXXX to XXXX"
            style: "body2"
            wrapMode: Text.Wrap
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

            Label {
                anchors.left: taskProgression.right
                anchors.leftMargin: Units.dp(8)
                anchors.verticalCenter: parent.verticalCenter
                text: Math.round(taskProgression.value) + "%"
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

        View {
            anchors.left: parent.left
            anchors.right: parent.right
            height: sectionHeaderTag.expanded ? Units.dp(48) : 0

            visible: repeaterTag.model.Length === 0

            Behavior on height {
                NumberAnimation {
                    duration: 200
                }
            }

            ListItem.Standard {

                anchors.fill: parent

                action: Icon {
                    anchors.centerIn: parent
                    name: "content/add_circle_outline"
                    size: Units.dp(32)
                }

                text: "Add a tag to the task"
            }
        }


        View {
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)
            height: sectionHeaderTag.expanded ? flowTag.implicitHeight + Units.dp(16) : 0

            visible: repeaterTag.model.Length > 0

            Behavior on height {
                NumberAnimation {
                    duration: 200
                }
            }

            Flow {
                id: flowTag
                anchors.fill: parent
                anchors.topMargin: Units.dp(8)
                anchors.bottomMargin: Units.dp(8)

                spacing: Units.dp(8)

                Repeater {
                    id: repeaterTag
                    model: []
                    delegate: Button {
                        text: modelData
                        visible: text !== ""
                        elevation: 1
                        textColor: "#FFF"
                        backgroundColor: "#44BBFF"

                        enabled: false

                        onClicked: {
                            /*for (var item in bugModel.tags)
                            {
                                if (bugModel.tags[item].id === modelData)
                                {
                                    tagEdit.assignedTag = bugModel.tags[item]
                                    console.log(tagEdit.assignedTag)
                                    break
                                }
                            }
                            tagEdit.open()*/
                        }

                        Component.onCompleted: {
                            text = Qt.binding(function() {
                                for (var item in ganttModel.taskTags)
                                {
                                    if (ganttModel.taskTags[item].id === modelData)
                                    {
                                        return ganttModel.taskTags[item].name
                                    }
                                }
                                return ""
                            })
                            backgroundColor = Qt.binding(function() {
                                for (var item in ganttModel.taskTags)
                                {
                                    if (ganttModel.taskTags[item].id === modelData)
                                    {
                                        return "#" + ganttModel.taskTags[item].color
                                    }
                                }
                                return "#44BBFF"
                            })
                        }
                    }
                }

                /*IconButton {
                    Layout.alignment: Qt.AlignVCenter
                    iconName: "content/add_circle_outline"
                    onClicked: {
                        addTagDialog.show()
                    }
                }*/
            }
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

        View {

            id: viewUserAssigned
            anchors.left: parent.left
            anchors.right: parent.right
            height: headerUserAssigned.expanded ? columnUserAssigned.implicitHeight : 0

            Behavior on height {
                NumberAnimation {
                    duration: 200
                }
            }

            Column {
                id: columnUserAssigned
                anchors.fill: parent

                spacing: Units.dp(8)

                    Repeater {
                        id: repeaterUserAssigned
                        model: []
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

                            text: modelData.firstName + " " + modelData.lastName
                        }
                    }

                    /*ListItem.Standard {
                        action: Icon {
                            anchors.centerIn: parent
                            name: "content/add_circle_outline"
                            size: Units.dp(32)
                        }

                        text: "Add a new user to the task"
                    }*/
            }
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

        View {

            id: viewDependencies
            anchors.left: parent.left
            anchors.right: parent.right
            height: headerDependencies.expanded ? columnDependencies.implicitHeight : 0

            Behavior on height {
                NumberAnimation {
                    duration: 200
                }
            }

            Column {
                id: columnDependencies
                anchors.fill: parent

                property var enumToTextType: ["Finish to start", " Start to start", "Finish to finish", "Start to finish"]

                spacing: Units.dp(8)

                    Repeater {
                        id: repeaterDependencies
                        model: []
                        delegate: ListItem.Standard {
                            secondaryItem: Label {
                                anchors.verticalCenter: parent.verticalCenter
                                anchors.right: parent.right

                                text: enumToTextType[modelData.type]
                            }

                            Component.onCompleted: {
                                text = Qt.binding(function () {
                                    for (var i = 0; i < ganttModel.tasks; ++i)
                                    {
                                        if (ganttModel.tasks[i].id === modelData.linkedTask)
                                        {
                                            return ganttModel.tasks[i].name;
                                        }
                                    }
                                    return "";
                                })
                            }
                        }
                    }

                    /*ListItem.Standard {
                        action: Icon {
                            anchors.centerIn: parent
                            name: "content/add_circle_outline"
                            size: Units.dp(32)
                        }

                        text: "Add a new dependency to the task"
                    }*/
            }
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        CustomListStandart {
            id: headerTaskContain
            visible: repeaterTasks.model.Length > 0

            expandedColor: "#44BBFF"

            text: "Contained task"
        }

        Item {
            visible: repeaterTasks.model.Length > 0
            height: Units.dp(8)
            width: parent.width
        }

        View {
            visible: repeaterTasks.model.Length > 0
            id: viewContain
            anchors.left: parent.left
            anchors.right: parent.right
            height: headerTaskContain.expanded ? columnContain.implicitHeight : 0

            Behavior on height {
                NumberAnimation {
                    duration: 200
                }
            }

            Column {
                id: columnContain
                anchors.fill: parent

                spacing: Units.dp(8)

                    Repeater {
                        id: repeaterTasks
                        model: []
                        delegate: ListItem.Standard {
                            secondaryItem: Label {
                                id: labelDate
                                anchors.verticalCenter: parent.verticalCenter
                                anchors.right: parent.right

                                Component.onCompleted: {
                                    text = Qt.binding(function () {
                                        for (var i = 0; i < ganttModel.tasks; ++i)
                                        {
                                            if (ganttModel.tasks[i].id === modelData.linkedTask)
                                            {
                                                return Qt.formatDateTime(ganttModel.tasks[i].dueDate, "yyyy-MM-dd hh:mm");
                                            }
                                        }
                                        return "";
                                    })
                                }
                            }

                            Component.onCompleted: {
                                text = Qt.binding(function () {
                                    for (var i = 0; i < ganttModel.tasks; ++i)
                                    {
                                        if (ganttModel.tasks[i].id === modelData.linkedTask)
                                        {
                                            return ganttModel.tasks[i].name;
                                        }
                                    }
                                    return "";
                                })
                            }
                        }
                    }

                    /*ListItem.Standard {
                        action: Icon {
                            anchors.centerIn: parent
                            name: "content/add_circle_outline"
                            size: Units.dp(32)
                        }

                        text: "Add a new child task"
                    }*/
            }
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

                text: "Edit"

                onClicked: {
                    edit(currentTask.id)
                }
            }

            Button {
                id: closeButton
                anchors.right: parent.right
                text: "Delete"
                textColor: Theme.primaryColor

                onClicked: {
                    console.log("DELETE")
                }
            }

        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }
    }

}

