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
    anchors.fill: parent
    anchors.margins: Units.dp(16)

    property BugTrackerModel bugModel
    property var tagsSelected: []
    property var usersSelected: []
    signal back()

    function reset() {
        title.text = ""
        message.text = ""
    }

    Dialog {
        id: addTagDialog

        title: "Add a new tag"

        TextField {
            id: tag
            width: parent.width
            placeholderText: "Tag name"
        }

        onRejected: tag.text = ""

        onAccepted: {
            bugModel.createAndAddTagsToTicket(-1, tag.text)
            tag.text = ""
        }

        positiveButtonText: "Add"
        negativeButtonText: "Cancel"
    }

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

    TextField {
        id: title
        anchors.left: parent.left
        anchors.right: parent.right
        placeholderText: "Title"
    }

    Item {
        height: Units.dp(8)
        width: parent.width
    }

    TextArea {
        id: message
        height: Units.dp(86)
        anchors.left: parent.left
        anchors.right: parent.right
        placeHolderText: "Message"
    }

    Item {
        height: Units.dp(8)
        width: parent.width
    }

    Row {

        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(16)

        Column {

            width: parent.width / 2 - 8

            ListItem.Subheader {
                text: "Assign users"
            }

            Repeater {
                model: SDataManager.project.users
                delegate: ListItem.Standard {
                    text: modelData.firstName + " " + modelData.lastName
                    action: CheckBox {
                        id: userSwitch
                        anchors.verticalCenter: parent.verticalCenter

                        onCheckedChanged: {
                            usersSelected[modelData.id] = checked
                        }
                    }

                    onClicked: {
                        userSwitch.checked = !userSwitch.checked
                    }
                }
            }
        }

        Column {

            width: parent.width / 2 - 8

            ListItem.Subheader {
                text: "Add tags"
            }

            Repeater {
                model: bugModel.tags
                delegate: ListItem.Standard {
                    text: modelData.name
                    action: CheckBox {
                        id: tagSwitch
                        anchors.verticalCenter: parent.verticalCenter

                        onCheckedChanged: {
                            tagsSelected[modelData.id] = checked
                        }
                    }

                    secondaryItem: IconButton {
                        iconName: "action/info_outline"
                        anchors.verticalCenter: parent.verticalCenter

                        onClicked: {
                            rightClickTag.open()
                        }
                    }

                    onClicked: {
                        tagSwitch.checked = !tagSwitch.checked
                    }
                }
            }

            ListItem.Standard {
                text: "Add a new tag"

                secondaryItem: Icon {
                    name: "content/add"
                    anchors.verticalCenter: parent.verticalCenter
                }

                onClicked: {
                    addTagDialog.show()
                }
            }
        }
    }

    RowLayout {
        Layout.alignment: Qt.AlignRight
        spacing: Units.dp(8)

        anchors {
            right: parent.right
            margins: Units.dp(16)
        }

        Button {
            text: "Cancel"
            textColor: Theme.primaryColor

            onClicked: {
                back()
            }
        }

        Button {
            text: "Add"
            textColor: Theme.primaryColor

            onClicked: {
                bugModel.addTicket(title.text, message.text, usersSelected, tagsSelected)
                back()
            }
        }
    }

    BottomActionSheet {
        id: rightClickTag

        title: "Action"

        actions: [
            Action {
                iconName: "image/edit"
                name: "Edit"
                onTriggered: {

                }
            },
            Action {
                iconName: "action/delete"
                name: "Delete"
                onTriggered: {

                }
            }

        ]
    }
}

