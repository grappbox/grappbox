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

    property bool onEdit: false

    anchors.fill: parent
    anchors.margins: Units.dp(16)

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
            visible: !infoView.onEdit
        }

        TextField {
            id: editTitleTicket
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)

            text: titleTicket.text
            visible: infoView.onEdit
            placeholderText: "Title"
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
            visible: !infoView.onEdit
            wrapMode: Text.Wrap
        }

        TextArea {
            id: editMessageTicket
            text: messageTicket.text

            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)

            height: Units.dp(64)

            visible: infoView.onEdit
            placeHolderText: "Message"
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

            visible: repeaterTag.model.length === 0

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

            visible: repeaterTag.model.length > 0

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
                    model: ["Tag #1", "Tag #2", "Tag #3", "Tag #4"]
                    delegate: Button {
                        text: modelData
                        visible: text !== ""
                        elevation: 1
                        textColor: "#FFF"

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
                            /*text = Qt.binding(function() {
                                for (var item in bugModel.tags)
                                {
                                    if (bugModel.tags[item].id === modelData)
                                    {
                                        return bugModel.tags[item].name
                                    }
                                }
                                return ""
                            })
                            backgroundColor = Qt.binding(function() {
                                for (var item in bugModel.tags)
                                {
                                    if (bugModel.tags[item].id === modelData)
                                    {
                                        return bugModel.tags[item].color
                                    }
                                }
                                return ""
                            })*/
                        }
                    }
                }

                IconButton {
                    Layout.alignment: Qt.AlignVCenter
                    iconName: "content/add_circle_outline"
                    onClicked: {
                        addTagDialog.show()
                    }
                }
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
                        model: [{name: "Marc Wieser", percent: 30}, {name: "Leo Nadeau", percent: 120}, {name: "Frederic Tan", percent: 20}]
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

                            text: modelData.name
                        }
                    }

                    ListItem.Standard {
                        action: Icon {
                            anchors.centerIn: parent
                            name: "content/add_circle_outline"
                            size: Units.dp(32)
                        }

                        text: "Add a new user to the task"
                    }
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

                spacing: Units.dp(8)

                    Repeater {
                        model: [{name: "Task #1", type: "Finish to start"}, {name: "Task #2", type: "Start to Finish"}]
                        delegate: ListItem.Standard {
                            secondaryItem: Label {
                                anchors.verticalCenter: parent.verticalCenter
                                anchors.right: parent.right

                                text: modelData.type
                            }

                            text: modelData.name
                        }
                    }

                    ListItem.Standard {
                        action: Icon {
                            anchors.centerIn: parent
                            name: "content/add_circle_outline"
                            size: Units.dp(32)
                        }

                        text: "Add a new dependency to the task"
                    }
            }
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        CustomListStandart {
            id: headerTaskContain

            expandedColor: "#44BBFF"

            text: "Contained task"
        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }

        View {

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
                        model: [{name: "Task #3", type: "YYYY-MM-DD hh:mm:ss"}, {name: "Task #4", type: "YYYY-MM-DD hh:mm:ss"}]
                        delegate: ListItem.Standard {
                            secondaryItem: Label {
                                anchors.verticalCenter: parent.verticalCenter
                                anchors.right: parent.right

                                text: modelData.type
                            }

                            text: modelData.name
                        }
                    }

                    ListItem.Standard {
                        action: Icon {
                            anchors.centerIn: parent
                            name: "content/add_circle_outline"
                            size: Units.dp(32)
                        }

                        text: "Add a new child task"
                    }
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
                anchors.left: image.right
                anchors.leftMargin: Units.dp(8)
                anchors.verticalCenter: parent.verticalCenter

                text: "Created by XXX XXX the YYYY-MM-DD hh:mm:ss"
                style: "caption"
            }

            Button {
                anchors.right: closeButton.left
                anchors.rightMargin: Units.dp(8)

                text: infoView.onEdit ? "Save" : "Edit"

                onClicked: {
                    if (infoView.onEdit)
                    {
                        infoView.onEdit = false
                    }
                    else
                    {
                        infoView.onEdit = true
                    }
                }
            }

            Button {
                id: closeButton
                anchors.right: parent.right
                text: infoView.onEdit ? "Cancel" : "Delete"
                textColor: Theme.primaryColor

                onClicked: {
                    if (infoView.onEdit)
                    {
                        infoView.onEdit = false
                    }
                    else if (ticket.isClosed)
                    {
                        bugModel.reopenTicket(ticket.id)
                        back()
                    }
                    else
                    {
                        bugModel.closeTicket(ticket.id)
                        back()
                    }
                }
            }

        }

        Item {
            height: Units.dp(8)
            width: parent.width
        }
    }

}

