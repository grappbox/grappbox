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

    Row {
        anchors.left: parent.left
        anchors.right: parent.right
        height: columnMainMessage.implicitHeight

        CircleImageAsync {
            width: Units.dp(48)
            height: Units.dp(48)
            Layout.alignment: Qt.AlignTop


        }

        Item {
            width: Units.dp(8)
            height: parent.height
        }

        Column {
            id: columnMainMessage
            width: parent.width - Units.dp(56)

            Row {
                height: Math.max(columnTitles.implicitHeight, columnProgression)
                anchors.left: parent.left
                anchors.right: parent.right
                spacing: Units.dp(8)

                Column {
                    id: columnTitles
                    anchors.top: parent.top
                    width: parent.width - columnProgression.width - parent.spacing

                    Label {
                        id: titleTicket
                        text: "Title"
                        style: "title"
                        visible: !infoView.onEdit
                    }

                    TextField {
                        id: editTitleTicket
                        width: parent.width
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
                        text: "Description"
                        style: "body2"
                        visible: !infoView.onEdit
                        wrapMode: Text.Wrap
                    }

                    TextArea {
                        id: editMessageTicket
                        text: messageTicket.text

                        width: parent.width
                        height: Units.dp(64)

                        visible: infoView.onEdit
                        placeHolderText: "Message"
                    }
                }

                Column {
                    id: columnProgression
                    anchors.top: parent.top

                    width: Math.max(taskProgression.width, labelProgression.width)

                    Label {
                        id: labelProgression
                        text: "Progression"
                    }

                    CustomProgressCircle {
                        id: taskProgression
                        Layout.alignment: Qt.AlignCenter
                        width: Units.dp(64)
                        height: Units.dp(64)
                        indeterminate: false
                        dashThickness: Units.dp(8)
                        minimumValue: 0
                        maximumValue: 100
                        value: 60

                        Label {
                            anchors.centerIn: parent
                            text: Math.round(taskProgression.value) + "%"
                        }
                    }
                }
            }

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            ListItem.Standard {

                interactive: false

                action: Icon {
                    anchors.centerIn: parent
                    name: "device/access_alarm"
                    size: Units.dp(32)
                }

                text: "Tags"
            }

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            ListItem.Standard {
                anchors.left: parent.left
                anchors.right: parent.right
                anchors.leftMargin: Units.dp(16)

                visible: repeaterTag.model.length === 0

                action: Icon {
                    anchors.centerIn: parent
                    name: "content/add_circle_outline"
                    size: Units.dp(32)
                }

                text: "Add a tag to the task"
            }

            Flow {
                visible: repeaterTag.model.length > 0
                anchors.left: parent.left
                anchors.right: parent.right
                anchors.leftMargin: Units.dp(16)
                height: implicitHeight

                spacing: Units.dp(8)

                Repeater {
                    id: repeaterTag
                    model: []//["Tag #1", "Tag #2", "Tag #3"]
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

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            ListItem.Standard {

                interactive: false

                action: Icon {
                    anchors.centerIn: parent
                    name: "device/access_alarm"
                    size: Units.dp(32)
                }

                text: "Assigned users"
            }

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            Column {
                anchors.left: parent.left
                anchors.right: parent.right
                anchors.leftMargin: Units.dp(16)
                height: implicitHeight

                spacing: Units.dp(8)

                Repeater {
                    model: [{name: "Marc Wieser", percent: 30}, {name: "Leo Nadeau", percent: 50}, {name: "Frederic Tan", percent: 20}]
                    delegate: ListItem.Standard {

                        action: CircleImageAsync {
                            anchors.centerIn: parent
                            width: Units.dp(32)
                            height: Units.dp(32)
                        }

                        secondaryItem: CustomProgressCircle {
                            id: userProgress
                            anchors.centerIn: parent
                            Layout.alignment: Qt.AlignCenter
                            width: Units.dp(32)
                            height: Units.dp(32)
                            dashThickness: Units.dp(1)
                            indeterminate: false
                            minimumValue: 0
                            maximumValue: 100
                            value: modelData.percent

                            Label {
                                anchors.centerIn: parent
                                text: Math.round(userProgress.value) + "%"
                                font.pixelSize: Units.dp(8)
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

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            Item {

                anchors.left: parent.left
                anchors.right: parent.right
                height: closeButton.height

                Label {
                    anchors.left: parent.left
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
}

