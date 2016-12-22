import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0

Item {

    property TaskData task: TaskData {}
    property GanttModel ganttModel

    property var dependecies: []
    property var users: []
    property var containedTasks: []
    property var tags: []

    signal back()

    onVisibleChanged: {
    }

    Component.onCompleted: {
    }

    function getProblems() {
        var hasContainedAndDependenciesDoublon = false
        var hasDependenciesDoublon = false
        var hasContainDoublons = false
        var ret = "";
        for (var itemD in dependecies)
        {
            var count = 0
            for (var itemComp in dependecies)
            {
                console.log(dependecies[itemD].linkedTask, " : ", dependecies[itemComp].linkedTask)
                if (dependecies[itemD].linkedTask === dependecies[itemComp].linkedTask)
                {
                    count++;
                    console.log(count)
                    if (count > 1)
                    {
                        console.log("Doublon")
                        hasDependenciesDoublon = true
                    }
                }
            }
            for (var itemCompV in containedTasks)
            {
                if (dependecies[itemD].linkedTask === containedTasks[itemCompV])
                    hasContainedAndDependenciesDoublon = true
            }
        }
        for (var itemC in containedTasks)
        {
            var countC = 0
            for (var itemCompC in containedTasks)
            {
                if (containedTasks[itemC] === containedTasks[itemCompC])
                {
                    countC++;
                    if (countC > 1)
                        hasContainDoublons = true
                }
            }
        }
        if (taskName.text == "")
            ret += "Need a name for the task.\n"
        if (hasDependenciesDoublon)
            ret += "Two or more predecesor are created with the same task.\n"
        if (hasContainDoublons)
            ret += "Two or more task contained are the same.\n"
        if (hasContainedAndDependenciesDoublon)
            ret += "Two or more tasks are in predecessor and contained.\n"
        return ret
    }

    Rectangle {
        anchors.fill: parent
        color: "#f2f2f2"
    }

    Dialog {
        id: datePickerDialog
        hasActions: true
        contentMargins: 0
        floatingActions: true
        property bool isStartDate: true

        DatePicker {
            id: datePicker
            frameVisible: false
            dayAreaBottomMargin : Units. dp(48)
            isLandscape: true
        }

        onAccepted: {
            if (isStartDate)
                task.startDate = datePicker.selectedDate
            else
                task.dueDate = datePicker.selectedDate
        }
    }

    Dialog {
        id: newTagDialog
        hasActions: true
        title: "Add a new tag"

        TextField {
            id: newTagText
            width: parent.width
            placeholderText: "Name of the new tag"

            onTextChanged: {
                console.log(text)
            }
        }

        Item {
            width: parent.width
            height: choicer.visible ? choicer.height : colorChoicer.height

            Rectangle {
                id: colorChoicer
                anchors.left: parent.left
                anchors.verticalCenter: parent.verticalCenter
                width: Units.dp(32)
                height: Units.dp(32)
                radius: width / 2

                color: "#9E58DC"

                MouseArea {
                    anchors.fill: parent

                    onClicked: {
                        choicer.visible = true
                    }
                }
            }

            TagColorChoicer {
                id: choicer
                anchors.left: colorChoicer.left
                anchors.verticalCenter: colorChoicer.verticalCenter

                onChooseColor: {
                    colorChoicer.color = color
                }
            }
        }

        onAccepted: {
            console.log(newTagText.text)
            ganttModel.addTag(newTagText.text, colorChoicer.color)
        }

        onShowingChanged: {
            if (showing)
                newTagText.text = ""
        }
    }

    Dialog {
        id: confirmDelete
        hasActions: true
        title: ""
        positiveButtonText: "Yes"
        negativeButtonText: "No"

        property int toDelete: 0
        property int id

        onAccepted: {
            if (toDelete === 0)
            {

            }
            if (toDelete === 1)
            {

            }
            if (toDelete === 2)
            {
                ganttModel.removeTag(id)
            }
        }
    }

    Flickable
    {
        id: flickableScroll
        anchors.fill: parent
        contentHeight: Math.max(viewForm.height + Units. dp(64), parent.height)

        View {
            id: viewForm
            anchors.horizontalCenter: parent.horizontalCenter
            anchors.top: parent.top
            anchors.topMargin: Units. dp(32)

            width: Units. dp(800)
            height: columnLogin.implicitHeight + Units. dp(32)


            elevation: 1
            radius: Units. dp(2)

            Column {
                id: columnLogin

                anchors {
                    top: parent.top
                    left: parent.left
                    right: parent.right
                    topMargin: Units. dp(16)
                    bottomMargin: Units. dp(16)
                }

                Label {
                    id: titlePopup

                    anchors {
                        left: parent.left
                        right: parent.right
                        margins: Units. dp(16)
                    }

                    style: "title"
                    text: "Task properties"
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                ListItem.Standard {
                    content: TextField {
                        id: taskName
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "Name of the task"
                        floatingLabel: true
                        onTextChanged: {
                            task.title = text
                        }
                    }
                }

                ListItem.Standard {
                    content: TextField {
                        id: taskDescription
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "Description"
                        floatingLabel: true
                        onTextChanged: {
                            task.description = text
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                ListItem.Standard {
                    content: Item {
                        anchors.fill: parent
                        RowLayout {
                            Layout.alignment: Qt.AlignCenter
                            spacing: Units. dp(8)

                            Button {
                                text: task.startDate.toDateString()
                                elevation: 1

                                onClicked: {
                                    datePickerDialog.isStartDate = true
                                    datePickerDialog.show()
                                }
                            }

                            Button {
                                text: task.dueDate.toDateString()
                                elevation: 1

                                onClicked: {
                                    datePickerDialog.isStartDate = false
                                    datePickerDialog.show()
                                }
                            }
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                ListItem.Standard {
                    content: CheckBox {
                        id: milestone
                        checked: false
                        text: "Is a milestone ?"
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                ListItem.Standard {
                    content: RowLayout {
                        Layout.alignment: Qt.AlignLeft
                        spacing: Units. dp(32)

                        Label {
                            text: progressionSlider.value + "%"
                        }

                        Slider {
                            id: progressionSlider
                            stepSize: 1
                            minimumValue: 0
                            maximumValue: 100
                            onValueChanged: {
                                task.progression = value
                            }
                        }
                    }
                }

                Label {
                    id: errorLabel
                    visible: false
                    text: ""
                    color: Theme.primaryColor
                    Layout.fillWidth: true
                    anchors.margins: Units.dp(16)
                    style: "title"
                }

                RowLayout {
                    Layout.alignment: Qt.AlignRight
                    spacing: Units. dp(8)

                    anchors {
                        right: parent.right
                        margins: Units. dp(16)
                    }

                    Button {
                        text: "Cancel"
                        textColor: Theme.accentColor
                        onClicked: back()
                    }

                    Button {
                        text: "Save"
                        textColor: Theme.primaryColor
                        onClicked: {
                            var ret = getProblems();
                            if (ret !== "")
                            {
                                errorLabel.visible = true
                                errorLabel.text = ret
                            }
                            else
                            {
                                var startDate = task.startDate
                                var endDate = task.dueDate
                                ganttModel.addTask(taskName.text,
                                                   taskDescription.text,
                                                   milestone.checked,
                                                   progressionSlider.value,
                                                   startDate,
                                                   endDate,
                                                   users,
                                                   dependecies,
                                                   containedTasks,
                                                   tags)
                                back();
                            }
                        }
                    }
                }
            }
        }
    }

    Scrollbar {
        flickableItem: flickableScroll
    }
}

