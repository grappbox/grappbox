import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0

Item {

    property TimelineMessageData messageData: TimelineMessageData {}
    property bool isLoading: true

    property bool isEditingMessage: false

    signal close()
    signal editMessages(int parentId, int id, string title, string message)
    signal deleteMessage(int id, int parentId)
    signal addComment(string message)
    signal makeBug(string title, string message)

    Rectangle {
        id: background
        color: "black"
        opacity: 0.4
        anchors.fill: parent
    }

    Flickable {
        id: flick

        anchors.fill: parent

        contentHeight: Math.max(view.height + Units.dp(32), height)

        View {
            id: view
            width: 500

            elevation: 1
            radius: Units.dp(2)
            anchors.top: parent.top
            anchors.horizontalCenter: parent.horizontalCenter
            anchors.topMargin: Units.dp(16)
            height: isLoading ? Units.dp(86) : column.implicitHeight + Units.dp(32)

            Behavior on height {
                NumberAnimation { duration: 200 }
            }

            InvertedMouseArea {
                anchors.fill: parent

                onPressed: {
                    close()
                }
            }

            ProgressCircle {
                anchors.centerIn: parent

                visible: isLoading
            }

            Column {

                id: column

                visible: !isLoading

                anchors {
                    top: parent.top
                    left: parent.left
                    right: parent.right
                    topMargin: Units.dp(16)
                }

                Item {

                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)

                    height: 48

                    CircleImageAsync {
                        id: userAvatar
                        width: 48
                        height: 48

                        avatarId: messageData.associatedUser ? messageData.associatedUser.id : undefined
                        avatarDate: messageData.associatedUser ? messageData.associatedUser.avatarDate : undefined

                        anchors.left: parent.left
                    }

                    Label {
                        id: userName
                        anchors.left: userAvatar.right
                        anchors.leftMargin: Units.dp(8)
                        anchors.verticalCenter: userAvatar.verticalCenter
                        text: messageData.associatedUser ? messageData.associatedUser.firstName : ""
                    }

                    Label {
                        id: date
                        anchors.right: parent.right
                        text: Qt.formatDateTime(messageData.lastEdit, "dddd, MMMM dd - hh:mm AP")
                        anchors.verticalCenter: userAvatar.verticalCenter
                    }
                }

                Item {
                    width: parent.width
                    height: Units.dp(8)
                }

                TextField {
                    id: editTitleMessage

                    visible: isEditingMessage
                    placeholderText: "Title"
                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)
                    height: titleMessage.height

                    text: titleMessage.text

                }

                Label {
                    id: titleMessage

                    visible: !isEditingMessage
                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)

                    text: messageData.title

                    style: "title"
                }

                Item {
                    width: parent.width
                    height: Units.dp(8)
                }

                TextArea {
                    id: editMessage

                    visible: isEditingMessage
                    anchors.left: parent.left
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)
                    width: parent.width - Units.dp(32)
                    height: Math.max(message.height, Units.dp(86))

                    text: message.text

                }

                Label {
                    id: message

                    visible: !isEditingMessage
                    anchors.left: parent.left
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)
                    width: parent.width - Units.dp(32)

                    text: messageData.message

                    style: "body2"

                    wrapMode: Text.Wrap
                }

                Item {
                    width: parent.width
                    height: Units.dp(8)
                }

                RowLayout {
                    id: editionButtons

                    anchors.right: parent.right
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)
                    Layout.alignment: Qt.AlignRight

                    IconButton {
                        visible: !isEditingMessage && (messageData.associatedUser && messageData.associatedUser.id === SDataManager.user.id)
                        id: editMessageButton
                        iconName: "image/edit"

                        onClicked: isEditingMessage = true
                    }

                    IconButton {
                        visible: !isEditingMessage && (messageData.associatedUser && messageData.associatedUser.id === SDataManager.user.id)
                        id: deleteMessageButton
                        iconName: "action/delete"

                        onClicked: {
                            deleteMessage(messageData.id, -1)
                            close()
                        }
                    }

                    IconButton {
                        visible: !isEditingMessage && (messageData.associatedUser && messageData.associatedUser.id === SDataManager.user.id)
                        id: convertIntoBugButton
                        iconName: "action/bug_report"

                        onClicked: {
                            makeBug(messageData.title, messageData.message)
                        }
                    }

                    Button {
                        visible: isEditingMessage
                        id: saveButton
                        text: "Save"
                        elevation: 1

                        onClicked: {
                            editMessages(-1, messageData.id, editTitleMessage.text, editMessage.text)
                            isEditingMessage = false
                        }
                    }
                }

                Item {
                    width: parent.width
                    height: Units.dp(8)
                }

                Rectangle {
                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)

                    height: Units.dp(1)

                    color: "grey"
                    opacity: 0.25
                }

                Item {
                    width: parent.width
                    height: Units.dp(8)
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
                    width: parent.width
                    height: Units.dp(8)
                }

                Repeater {
                    model: messageData.comments
                    delegate: Item {
                        id: comment
                        anchors.left: parent.left
                        anchors.right: parent.right
                        anchors.leftMargin: Units.dp(16)
                        anchors.rightMargin: Units.dp(16)

                        height: Math.max(avatarComment.height, columnCommentary.implicitHeight) + editionButtonCommentary.implicitHeight + Units.dp(32)

                        property bool onEditComment: false

                        CircleImageAsync {
                            id: avatarComment

                            anchors.left: parent.left
                            anchors.top: parent.top

                            avatarId: modelData.associatedUser.id
                            avatarDate: modelData.associatedUser.avatarDate

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
                                text: modelData.associatedUser.firstName
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
                                visible: !comment.onEditComment && (modelData.associatedUser && modelData.associatedUser.id === SDataManager.user.id)
                                id: editComment
                                iconName: "image/edit"

                                onClicked: comment.onEditComment = true
                            }

                            IconButton {
                                visible: !comment.onEditComment && (modelData.associatedUser && modelData.associatedUser.id === SDataManager.user.id)
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
        }
    }

    Scrollbar {
        flickableItem: flick
    }
}

