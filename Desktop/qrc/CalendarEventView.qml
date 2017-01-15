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
    id: itemView
    elevation: 1

    width: Units.dp(600)
    height: columnMain.implicitHeight + Units.dp(32)
    visible: false

    property bool onEdit: false
    property bool onAdd: false
    property CalendarModel calendarModel
    property EventModelData eventData
    property int idProject: 0

    Dialog {
        id: deleteEvent

        title: "Do you realy want to remove this evenments ?"
        text: "You are going to remove this evenements. Are you sure ? This action cannot be undone."
        hasActions: true
        positiveButtonText: "Yes"
        negativeButtonText: "No"

        width: Units.dp(300)

        onAccepted: {
            calendarModel.removeEvent(eventData)
            itemView.visible = false
        }
    }

    onVisibleChanged: {
        if (!onEdit && !onAdd)
        {
            var projects = SDataManager.projectList
            for (var i = 0; i < projects.length; ++i)
            {
                if (projects[i].id === eventData.projectId)
                {
                    idProject = i;
                    viewProjectName.text = projects[i].name
                    return
                }
            }
            viewProjectName.text = "Personal"
        }
    }

    onEventDataChanged: {
    }

    Dialog {
        id: datePicker
        hasActions: true
        contentMargins: 0
        floatingActions: true

        property var objectDate

        DatePicker {
            id: inDatePicker
            frameVisible: false
            dayAreaBottomMargin: Units.dp(48)
            isLandscape: true
        }
        onAccepted: {
            objectDate.dateIn = inDatePicker.selectedDate;
            if (buttonDateBegin.dateIn > buttonDateEnd.dateIn)
                buttonDateEnd.dateIn = new Date(buttonDateBegin.dateIn.getFullYear(), buttonDateBegin.dateIn.getMonth(), buttonDateBegin.dateIn.getDate());
            if (buttonDateBegin.dateIn.getFullYear() === buttonDateEnd.dateIn.getFullYear() &&
                    buttonDateBegin.dateIn.getMonth() === buttonDateEnd.dateIn.getMonth() &&
                    buttonDateBegin.dateIn.getDate() === buttonDateEnd.dateIn.getDate())
            {
                if (buttonTimeBegin.timeIn > buttonTimeEnd.timeIn)
                {
                    console.log("TIME CHANGED !");
                    buttonTimeBegin.timeIn = new Date(buttonTimeBegin.timeIn.getFullYear(), buttonTimeBegin.timeIn.getMonth(), buttonTimeBegin.timeIn.getDate(), buttonTimeBegin.timeIn.getHours(), buttonTimeBegin.timeIn.getMinutes(), buttonTimeBegin.timeIn.getSeconds())

                }
            }
        }
    }

    Dialog {
        id: timePicker
        hasActions: true
        contentMargins: 0
        floatingActions: true

        property var objectDate

        CalendarTimePicker {
            id: inTimePicker
            prefer24Hour: true
            bottomMargin: Units.dp(48)
        }

        onAccepted: {
            objectDate.timeIn = inTimePicker.getCurrentTime()
            console.log(inTimePicker.getCurrentTime())
            if (buttonDateBegin.dateIn > buttonDateEnd.dateIn)
                buttonDateEnd.dateIn = new Date(buttonDateBegin.dateIn.getFullYear(), buttonDateBegin.dateIn.getMonth(), buttonDateBegin.dateIn.getDate());
            if (buttonDateBegin.dateIn.getFullYear() === buttonDateEnd.dateIn.getFullYear() &&
                    buttonDateBegin.dateIn.getMonth() === buttonDateEnd.dateIn.getMonth() &&
                    buttonDateBegin.dateIn.getDate() === buttonDateEnd.dateIn.getDate())
            {
                if (buttonTimeBegin.timeIn > buttonTimeEnd.timeIn)
                {
                    console.log("TIME CHANGED !");
                    buttonTimeBegin.timeIn = new Date(buttonTimeBegin.timeIn.getFullYear(), buttonTimeBegin.timeIn.getMonth(), buttonTimeBegin.timeIn.getDate(), buttonTimeBegin.timeIn.getHours(), buttonTimeBegin.timeIn.getMinutes(), buttonTimeBegin.timeIn.getSeconds())

                }
            }
        }
    }

    IconButton {
        iconName: "content/clear"
        width: Units.dp(32)
        height: Units.dp(32)

        anchors.right: parent.right
        anchors.top: parent.top
        anchors.topMargin: Units.dp(16)
        anchors.rightMargin: Units.dp(16)

        visible: !onAdd && !onEdit

        onClicked: {
            itemView.visible = false
        }
    }

    Column {
        id: columnMain
        anchors.top: parent.top
        anchors.left: parent.left
        anchors.right: parent.right
        anchors.margins: Units.dp(16)

        Label {
            id: viewTitle
            text: (!visible || onAdd) ? "" : eventData.title
            style: "title"
            visible: !onAdd && !onEdit
        }

        TextField {
            id: editTitle
            text: viewTitle.text
            visible: onAdd || onEdit
            placeholderText: "Title"
            anchors.left: parent.left
            anchors.right: parent.right
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        Label {
            id: viewProjectName
            text: ""
            style: "body2"
            visible: !onAdd && !onEdit
        }

        MenuField {
            id: editProjectChoice
            model: []
            visible: onAdd || onEdit
            width: Units.dp(200)

            Component.onCompleted: {
                var projects = []
                for (var i = 0; i < SDataManager.projectList.length; ++i)
                {
                    projects.push(SDataManager.projectList[i].name)
                }
                model = projects
            }
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        Label {
            id: viewDescription
            text: (!visible || onAdd) ? "" : eventData.description
            style: "body1"
            wrapMode: Text.Wrap
            visible: !onAdd && !onEdit
        }

        TextArea {
            id: editDescription
            text: viewDescription.text
            visible: onAdd || onEdit
            placeHolderText: "Description"
            height: Units.dp(86)
            anchors.left: parent.left
            anchors.right: parent.right
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        Label {
            text: (!visible || onAdd) ? "From 09/22/2016 12h20 to 09/22/2016 12h40" : "From " + Qt.formatDateTime(eventData.beginDate, "yyyy-MM-dd hh:mm") + " to " + Qt.formatDateTime(eventData.endDate, "yyyy-MM-dd hh:mm")

            visible: !onAdd && !onEdit
        }

        Row {
            height: Units.dp(32)
            visible: onAdd || onEdit

            Label {
                text: "From "
            }

            Item {
                width: Units.dp(8)
                height: parent.height
            }

            Button {
                id: buttonDateBegin
                text: Qt.formatDate(dateIn, "yyyy-MM-dd")

                elevation: 1

                property var dateIn: onEdit ? eventData.beginDate : new Date()
                onDateInChanged: {
                    text = Qt.formatDate(dateIn, "yyyy-MM-dd")
                }

                onClicked: {
                    datePicker.objectDate = this
                    datePicker.show()
                }
            }

            Item {
                width: Units.dp(8)
                height: parent.height
            }

            Button {
                id: buttonTimeBegin
                text: Qt.formatDateTime(timeIn, "hh:mm ap")

                elevation: 1

                property var timeIn: onEdit ? eventData.beginDate : new Date()
                onTimeInChanged: {
                    text = Qt.formatDateTime(timeIn, "hh:mm ap")
                }

                onClicked: {
                    timePicker.objectDate = this
                    timePicker.show()
                }
            }

            Item {
                width: Units.dp(8)
                height: parent.height
            }

            Label {
                text: " to "
            }

            Item {
                width: Units.dp(8)
                height: parent.height
            }

            Button {
                id: buttonDateEnd
                text: Qt.formatDate(dateIn, "yyyy-MM-dd")

                elevation: 1

                property var dateIn: onEdit ? eventData.endDate : new Date()
                onDateInChanged: {
                    text = Qt.formatDate(dateIn, "yyyy-MM-dd")
                }

                onClicked: {
                    datePicker.objectDate = this
                    datePicker.show()
                }
            }

            Item {
                width: Units.dp(8)
                height: parent.height
            }

            Button {
                id: buttonTimeEnd
                text: Qt.formatDateTime(timeIn, "hh:mm ap")

                elevation: 1

                property var timeIn: onEdit ? eventData.endDate : new Date()
                onTimeInChanged: {
                    text = Qt.formatDateTime(timeIn, "hh:mm ap")
                }

                onClicked: {
                    timePicker.objectDate = this
                    timePicker.show()
                }
            }
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        Rectangle {
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)
            anchors.rightMargin: Units.dp(16)
            height: Units.dp(1)
            color: "#dddddd"
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        ListItem.Subheader {
            text: "Users assigned"
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        Flow {
            id: rowHeight
            anchors.left: parent.left
            anchors.right: parent.right
            height: implicitHeight
            spacing: Units.dp(8)
            visible: !onAdd && !onEdit

            function usersChanged() {
                repeaterUsers.model = [];
                repeaterUsers.model = eventData.users;
            }

            Component.onCompleted: {
                eventData.usersChanged.connect(usersChanged);
            }

            Repeater {
                id: repeaterUsers
                model: (!visible || onAdd) ? [] : eventData.users
                delegate: Button {
                    text: modelData.firstName// + " " + modelData.lastName
                    visible: text !== " "
                    elevation: 1

                    Component.onCompleted: {
                    }
                }

                onModelChanged: {
                }
            }
        }

        Column {
            id: columnUsers
            anchors.left: parent.left
            anchors.right: parent.right
            height: implicitHeight
            spacing: Units.dp(8)
            visible: onAdd || onEdit

            property var usersList: []

            Repeater {
                model: []
                delegate: ListItem.Standard {
                    text: modelData.firstName + " " + modelData.lastName
                    id: mainListUsers
                    property bool firstIsChecked: false

                    secondaryItem: Switch {
                        id: chooseForEvent
                        anchors.verticalCenter: parent.verticalCenter
                        enabled: onEdit ? eventData.creator.id != modelData.id : modelData.id != SDataManager.user.id
                        checked: !enabled

                        onCheckedChanged: {
                            var asAdd = false
                            console.log("Checked changed")
                            for (var i = 0; i < columnUsers.usersList.length; ++i)
                            {
                                if (columnUsers.usersList[i][0] === modelData.id)
                                {
                                    asAdd = true
                                    columnUsers.usersList[i] = [modelData.id, checked]
                                    break
                                }
                            }
                            if (!asAdd)
                                columnUsers.usersList.push([modelData.id, checked])
                        }
                    }

                    enabled: chooseForEvent.enabled

                    onClicked: chooseForEvent.checked = !chooseForEvent.checked

                    function onEditChangedCallBack() {
                        if (eventData.creator.id === modelData.id)
                        {
                            chooseForEvent.checked = true
                        }
                        else
                        {
                            for (var i = 0; i < eventData.users.length; ++i)
                            {
                                if (eventData.users[i].id === modelData.id)
                                {
                                    console.log("Checked !");
                                    firstIsChecked = true
                                    chooseForEvent.checked = true
                                }
                            }
                        }
                    }

                    Component.onCompleted: {
                        onEditChanged.connect(onEditChangedCallBack);
                        if (onEdit) {
                            if (eventData.creator.id === modelData.id)
                            {
                                chooseForEvent.checked = true
                            }
                            else
                            {
                                for (var i = 0; i < eventData.users.length; ++i)
                                {
                                    if (eventData.users[i].id === modelData.id)
                                    {
                                        console.log("Checked !");
                                        firstIsChecked = true
                                        chooseForEvent.checked = true
                                    }
                                }
                            }
                        }
                        else {
                            console.log("OnEdit : ", modelData.id, " : ", SDataManager.user.id)
                            if (modelData.id === SDataManager.user.id)
                            {
                                chooseForEvent.checked = true
                                console.log("Checked")
                            }
                        }
                    }
                }

                function updateListUser() {
                    model = SDataManager.projectList[editProjectChoice.selectedIndex].users
                }

                Component.onCompleted: {
                    editProjectChoice.selectedIndexChanged.connect(updateListUser)
                    model = SDataManager.projectList[editProjectChoice.selectedIndex].users
                }
            }
        }

        Item {
            width: parent.width
            height: Units.dp(8)
        }

        RowLayout {
            id: editionButtonCommentary
            anchors.right: parent.right
            anchors.topMargin: Units.dp(8)
            Layout.alignment: Qt.AlignRight

            IconButton {
                visible: !onEdit && !onAdd
                id: editComment
                iconName: "image/edit"

                onClicked: {
                    editTitle.text = viewTitle.text
                    editDescription.text = viewDescription.text
                    editProjectChoice.selectedIndex = idProject
                    onEdit = true
                }
            }

            IconButton {
                visible: !onEdit && !onAdd
                id: deleteComment
                iconName: "action/delete"
                onClicked: {
                    deleteEvent.show()
                }
            }

            Button {
                visible: onEdit || onAdd
                id: saveButtonComment
                text: onEdit ? "Save" : "Create"
                elevation: 1

                onClicked: {
                    var dateBegin = new Date(buttonDateBegin.dateIn.getFullYear(), buttonDateBegin.dateIn.getMonth(), buttonDateBegin.dateIn.getDate(), buttonTimeBegin.timeIn.getHours(), buttonTimeBegin.timeIn.getMinutes(), 0, 0)
                    var dateEnd = new Date(buttonDateEnd.dateIn.getFullYear(), buttonDateEnd.dateIn.getMonth(), buttonDateEnd.dateIn.getDate(), buttonTimeEnd.timeIn.getHours(), buttonTimeEnd.timeIn.getMinutes(), 0, 0)
                    console.log("USERS : ", columnUsers.usersList)
                    if (onEdit)
                    {
                        calendarModel.editEvent(eventData.id, editTitle.text, editDescription.text, SDataManager.projectList[editProjectChoice.selectedIndex].id, dateBegin, dateEnd, columnUsers.usersList)
                        onEdit = false
                    }
                    else
                    {
                        calendarModel.addEvent(editTitle.text, editDescription.text, SDataManager.projectList[editProjectChoice.selectedIndex].id, dateBegin, dateEnd, columnUsers.usersList)
                        onAdd = false
                        itemView.visible = false
                    }
                }
            }

            Button {
                visible: onEdit || onAdd
                text: "Cancel"
                elevation: 1

                onClicked: {
                    onEdit = false
                    if (onAdd)
                        itemView.visible = false
                    onAdd = false
                }
            }
        }
    }
}

