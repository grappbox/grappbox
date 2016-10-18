import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0

Item {

    property TaskData task: TaskData {}
    property var modelTaskName
    property var modelTaskTag

    signal updateTaskTag()
    signal modifyTask(var taskData)
    signal addTask(var taskData)
    signal addTag(var tagName)
    signal removeTag(var tagId)

    onVisibleChanged: {
        updateTaskTag()
    }

    Component.onCompleted: {
        SDataManager.updateCurrentProject()
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
        }

        onAccepted: {
            console.log(newTagText.text)
            addTag(newTagText.text)
        }

        onShowingChanged: {
            optionText.text = ""
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
                removeTag(id)
            }
        }
    }

    ColorDialog {
        id: colorDialog
        title: "Please choose a color for the task"
        onAccepted: {
            task.color = colorDialog.color
        }
        Component.onCompleted: {
            colorDialog.color = task.color
        }
    }

    Flickable
    {
        id: flickableScroll
        anchors.fill: parent
        contentHeight: Math.max(viewForm.height + Units. dp(64), height)

        View {
            id: viewForm
            anchors.centerIn: parent
            anchors.top: parent.top
            anchors.topMargin: Units. dp(32)

            width: Units. dp(500)
            height: columnLogin.implicitHeight + Units. dp(32)


            elevation: 1
            radius: Units. dp(2)

            ColumnLayout {
                id: columnLogin

                anchors {
                    fill: parent
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

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                ListItem.Standard {
                    content: Item {
                        RowLayout {
                            Layout.alignment: Qt.AlignLeft
                            spacing: Units. dp(8)

                            Rectangle {
                                Layout.fillHeight: true
                                Layout.preferredWidth: 100
                                color: task.color
                            }

                            Button {
                                text: "Change color"
                                onClicked: {
                                    colorDialog.open()
                                }
                            }
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                RowLayout {
                    Label {
                        id: titleDependencies

                        Layout.alignment: Qt.AlignLeft
                        Layout.margins: Units. dp(16)

                        style: "title"
                        text: "Predecessors"
                    }

                    IconButton {
                        id: addButton
                        enabled: modelTaskName.length > 1
                        Layout.alignment: Qt.AlignRight
                        Layout.margins: Units. dp(16)
                        iconName: "content/add"

                        function addDependecies(array)
                        {
                            var dependencies = Qt.createQmlObject('import GrappBoxController 1.0; DependenciesData { }', task, "predecessor1")
                            array.push(dependencies)
                            return array
                        }

                        onClicked: {
                            var dependences = task.dependenciesAssigned
                            task.dependenciesAssigned = addDependecies(dependences)
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }


                ColumnLayout {
                    Layout.fillWidth: true
                    anchors {
                        left: parent.left
                        right: parent.right
                        margins: Units. dp(16)
                    }

                    Label {
                        visible: modelTaskName.length <= 1
                        text: "You can't add a dependencie because there is no other task in the project."
                        style: "body2"
                        color: Theme.primaryColor
                    }

                    Repeater {
                        model: task.dependenciesAssigned
                        delegate: ListItem.Standard {
                            content: Item {
                                Layout.fillWidth: true
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                }

                                RowLayout {
                                    spacing: Units. dp(8)
                                    anchors {
                                        left: parent.left
                                        right: parent.right
                                    }

                                    MenuField {
                                        id: menuFieldTaskName
                                        Layout.preferredWidth: parent.width / 3 - parent.spacing
                                        model: modelTaskName

                                        onSelectedIndexChanged: {
                                            modelData.linkedTask = selectedIndex
                                        }
                                    }

                                    MenuField {
                                        Layout.preferredWidth: parent.width / 3 - parent.spacing
                                        model: ["Finish to start", "Start to start", "Finish to finish", "Start to finish"]
                                        selectedIndex: modelData.type

                                        onSelectedIndexChanged: {
                                            modelData.type = selectedIndex
                                        }
                                    }

                                    Label {
                                        visible: false
                                        text: "There is conflict with this dependency."
                                    }

                                    IconButton {
                                        Layout.preferredWidth: Units. dp(40)
                                        iconName: "action/delete"

                                        color: Theme.primaryColor

                                        onClicked: {
                                            console.log("Delete dependencies")
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                RowLayout {
                    Label {
                        id: titleRessources

                        Layout.alignment: Qt.AlignLeft
                        Layout.margins: Units. dp(16)

                        style: "title"
                        text: "Ressources"
                    }

                    IconButton {
                        id: addButtonRessources
                        enabled: SDataManager.project.users.length > task.usersAssigned.length
                        Layout.alignment: Qt.AlignRight
                        Layout.margins: Units. dp(16)
                        iconName: "content/add"

                        onClicked: {
                            userAssigned.push({id: 1, ressource: 50});
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                ColumnLayout {
                    Layout.fillWidth: true
                    anchors {
                        left: parent.left
                        right: parent.right
                        margins: Units. dp(16)
                    }

                    Label {
                        visible: SDataManager.project.users.length === task.usersAssigned.length || task.hasDoubleUser()
                        text: task.hasDoubleUser() ? "You have a user assigned more than one time." : "You can't add new ressources because all users available is assigned."
                        style: "body2"
                        color: Theme.primaryColor
                    }

                    Repeater {
                        model: task.usersRessources
                        delegate: ListItem.Standard {
                            content: Item {
                                Layout.fillWidth: true
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                }

                                RowLayout {
                                    spacing: Units. dp(8)
                                    anchors {
                                        left: parent.left
                                        right: parent.right
                                    }

                                    MenuField {
                                        id: menuField
                                        Layout.preferredWidth: parent.width / 3 - parent.spacing
                                        model: SDataManager.project.usersName()

                                        Component.onCompleted: {
                                            selectedIndex = SDataManager.project.getIndexByUserData(modelData.id)
                                        }

                                        onSelectedIndexChanged: {
                                            modelData.id = SDataManager.project.getUserDataByIndex(selectedIndex)
                                        }
                                    }

                                    TextField {
                                        id: progress
                                        inputMethodHints: Qt.ImhPreferNumbers
                                        placeholderText: "Purcentage ressource"
                                        text: modelData.ressource
                                        floatingLabel: true
                                        characterLimit: 3

                                        onTextChanged: {
                                            modelData.ressource = Math.min(0, Math.max(100, parseInt(text)))
                                        }
                                    }

                                    IconButton {
                                        Layout.preferredWidth: Units. dp(40)
                                        iconName: "action/delete"

                                        color: Theme.primaryColor

                                        onClicked: {

                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                RowLayout {
                    Label {
                        id: titleTag

                        Layout.alignment: Qt.AlignLeft
                        Layout.margins: Units. dp(16)

                        style: "title"
                        text: "Tags"
                    }

                    IconButton {
                        id: addButtonTag
                        Layout.alignment: Qt.AlignRight
                        Layout.margins: Units. dp(16)
                        iconName: "content/add"

                        onClicked: {
                            newTagDialog.show()
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }
                ColumnLayout {
                    Layout.fillWidth: true
                    anchors {
                        left: parent.left
                        right: parent.right
                        margins: Units. dp(16)
                    }

                    Repeater {
                        model: modelTaskTag
                        delegate: ListItem.Standard {
                            text: modelData.name
                            action : Switch {
                                id: enablingTask
                                anchors.verticalCenter: parent.verticalCenter

                                checked: task.isOnTask(modelData)

                                onCheckedChanged: {
                                    if (checked)
                                    {
                                        task.addTag(modelData)
                                    }
                                    else
                                    {
                                        task.removeTag(modelData)
                                    }
                                }
                            }

                            secondaryItem: IconButton {
                                width: Units. dp(40)
                                iconName: "action/delete"

                                anchors.verticalCenter: parent.verticalCenter

                                color: Theme.primaryColor

                                onClicked: {
                                    confirmDelete.id = modelData.id
                                    confirmDelete.text = "Are you sure you want to delete this tag for your project ?"
                                    confirmDelete.toDelete = 2
                                    confirmDelete.show()
                                }
                            }

                            onClicked: {
                                enablingTask.checked = !enablingTask.checked
                            }
                        }
                    }
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
                        onClicked: loginPage.close()
                    }

                    Button {
                        text: "Save"
                        textColor: Theme.primaryColor
                        onClicked: {
                            controller.login(loginName.text, loginPassword.text)
                            loginPage.isLoading = true
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

