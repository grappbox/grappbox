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

    property BugTrackerTicketData ticket

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

        Image {
            source: Qt.resolvedUrl("qrc:/icons/icons/linkedin-box.svg")
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

            property bool onEditMessage: false

            Label {
                id: titleTicket
                text: (ticketColumn.ticket != undefined) ? ticketColumn.ticket.title : ""
                style: "title"
                visible: !columnMainMessage.onEditMessage
            }

            TextField {
                width: parent.width
                text: titleTicket.text
                visible: columnMainMessage.onEditMessage
                placeholderText: "Title"
            }

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            Label {
                id: messageTicket
                text: (ticketColumn.ticket != undefined) ? ticketColumn.ticket.message : ""
                style: "body2"
                visible: !columnMainMessage.onEditMessage
                wrapMode: Text.Wrap
            }

            TextArea {
                text: messageTicket.text

                width: parent.width
                height: Units.dp(64)

                visible: columnMainMessage.onEditMessage
                placeHolderText: "Message"
            }

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            Label {
                text: "Tags"
                style: "body1"
            }

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            Flow {
                anchors.left: parent.left
                anchors.right: parent.right
                height: implicitHeight

                spacing: Units.dp(8)

                Repeater {
                    model: (ticketColumn.ticket != undefined) ? ticketColumn.ticket.tags : []
                    delegate: Button {
                        text: ""
                        elevation: 1
                        Component.onCompleted: {
                            text = Qt.binding(function() {
                                for (var item in bugModel.tags)
                                {
                                    if (bugModel.tags[item].id === modelData)
                                    {
                                        return bugModel.tags[item].name
                                    }
                                }
                            })
                        }
                    }
                }

                IconButton {
                    Layout.alignment: Qt.AlignVCenter
                    iconName: "content/add_circle_outline"
                }
            }

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            Label {
                text: "Assigned users"
                style: "body1"
            }

            Item {
                height: Units.dp(8)
                width: parent.width
            }

            Flow {
                anchors.left: parent.left
                anchors.right: parent.right
                height: implicitHeight

                spacing: Units.dp(8)

                Repeater {
                    model: (ticketColumn.ticket != undefined) ? ticketColumn.ticket.users : []
                    delegate: Button {
                        text: ""
                        elevation: 1
                        Component.onCompleted: {
                            text = Qt.binding(function() {
                                for (var item in SDataManager.project.users)
                                {
                                    if (SDataManager.project.users[item].id === modelData)
                                    {
                                        return SDataManager.project.users[item].firstName + " " + SDataManager.project.users[item].lastName
                                    }
                                }
                            })
                        }
                    }
                }

                IconButton {
                    Layout.alignment: Qt.AlignVCenter
                    iconName: "content/add_circle_outline"
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

                    text: ticketColumn.ticket ? "Created by " + ticketColumn.ticket.creator.firstName + " edited the " + Qt.formatDateTime(ticketColumn.ticket.editDate, "yyyy-MM-dd hh:mm") : ""
                    style: "caption"
                }

                Button {
                    anchors.right: closeButton.left
                    anchors.rightMargin: Units.dp(8)

                    text: columnMainMessage.onEditMessage ? "Save" : "Edit"

                    onClicked: {
                        if (columnMainMessage.onEditMessage)
                        {

                        }
                        else
                        {
                            columnMainMessage.onEditMessage = true
                        }
                    }
                }

                Button {
                    id: closeButton
                    anchors.right: parent.right
                    text: columnMainMessage.onEditMessage ? "Cancel" : "Close"
                    textColor: Theme.primaryColor

                    onClicked: {
                        if (columnMainMessage.onEditMessage)
                        {
                            columnMainMessage.onEditMessage = false
                        }
                        else
                        {

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

    Rectangle {
        height: Units.dp(2)
        width: parent.width
        color: "#dddddd"
    }

    Item {
        height: Units.dp(8)
        width: parent.width
    }

    Item {

        anchors.left: parent.left
        anchors.right: parent.right
        anchors.leftMargin: Units.dp(16)
        anchors.rightMargin: Units.dp(16)
        height: newCommentary.height + Units.dp(8) + (postCommentary.visible ? postCommentary.height : 0)

        TextArea {
            id: newCommentary
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.top: parent.top

            placeHolderText: "Write a comment..."

            height: isOpened ? Units.dp(64) : Units.dp(24)

            property bool isOpened: false

            onTextEditFocusChanged: {
                if (focus)
                    isOpened = true
            }
        }

        Button {
            id: postCommentary
            text: "Comment"
            elevation: 1
            visible: newCommentary.isOpened
            backgroundColor: Theme.primaryColor
            textColor: Theme.dark.textColor
            anchors.right: parent.right
            anchors.top: newCommentary.bottom
            anchors.topMargin: Units.dp(8)
            height: Units.dp(24)

            onClicked: {
                addComment(newCommentary.text)
                newCommentary.text = ""
            }
        }
    }

    Item {
        height: Units.dp(8)
        width: parent.width
    }

    Rectangle {
        height: Units.dp(2)
        width: parent.width
        color: "#dddddd"
    }

    Item {
        height: Units.dp(8)
        width: parent.width
    }

    Label {
        text: "No comments add one !"
        visible: !repeaterComment.visible
        width: parent.width
        style: "body2"
        textFormat: Text.AlignHCenter
    }

    Repeater {
        id: repeaterComment
        visible: model.length > 0
        model: ticketColumn.ticket ? ticketColumn.ticket.comments : []
        delegate: Item {
            id: comment
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(16)
            anchors.rightMargin: Units.dp(16)

            height: Math.max(avatarComment.height, columnCommentary.implicitHeight) + editionButtonCommentary.implicitHeight + Units.dp(32)

            property bool onEditComment: false

            Image {
                id: avatarComment

                anchors.left: parent.left
                anchors.top: parent.top

                source: Qt.resolvedUrl("qrc:/icons/icons/linkedin-box.svg")
                height: Units.dp(48)
                width: Units.dp(48)
            }

            Column {
                id: columnCommentary
                anchors.left: avatarComment.right
                anchors.leftMargin: Units.dp(16)
                anchors.right: parent.right

                Label {
                    id: nameUserCommentary
                    anchors.left: parent.left
                    anchors.right: parent.right
                    text: modelData.user.firstName
                }

                Item {
                    width: parent.width
                    height: Units.dp(8)
                }

                TextArea {
                    id: editMessageComment
                    visible: comment.onEditComment
                    anchors.left: parent.left
                    anchors.right: parent.right
                    height: Math.max(messageCommentary.height, Units.dp(86))
                    text: messageCommentary.text
                }

                Label {
                    visible: !comment.onEditComment
                    id: messageCommentary
                    anchors.left: parent.left
                    anchors.right: parent.right
                    text: modelData.message
                    wrapMode: Text.Wrap
                }
            }

            RowLayout {
                id: editionButtonCommentary
                anchors.right: parent.right
                anchors.top: columnCommentary.bottom
                anchors.topMargin: Units.dp(8)
                Layout.alignment: Qt.AlignRight

                IconButton {
                    visible: !comment.onEditComment && (modelData.user && modelData.user.id === SDataManager.user.id)
                    id: editComment
                    iconName: "image/edit"

                    onClicked: comment.onEditComment = true
                }

                IconButton {
                    visible: !comment.onEditComment && (modelData.user && modelData.user.id === SDataManager.user.id)
                    id: deleteComment
                    iconName: "action/delete"
                    onClicked: {
                        deleteMessage(modelData.id, messageData.id)
                    }
                }

                Button {
                    visible: comment.onEditComment
                    id: saveButtonComment
                    text: "Save"
                    elevation: 1

                    onClicked: {
                        editMessages(messageData.id, modelData.id, modelData.title, editMessageComment.text)
                        comment.onEditComment = false
                    }
                }
            }

            Rectangle {
                anchors.left: parent.left
                anchors.right: parent.right
                anchors.leftMargin: Units.dp(16)
                anchors.rightMargin: Units.dp(16)
                anchors.top: editionButtonCommentary.bottom
                anchors.topMargin: Units.dp(8)
                height: Units.dp(1)
                color: "grey"
                opacity: 0.25
            }
        }
    }
}


