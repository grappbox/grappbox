import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0

Item {

    property alias avatarSource: userAvatar.source
    property alias user: userName.text
    property alias title: titleMessage.text
    property alias text: message.text
    property bool isLoading: true

    property bool onEditMessage: false

    signal close()

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

                    Image {
                        id: userAvatar
                        width: 48
                        height: 48
                        anchors.left: parent.left
                    }

                    Label {
                        id: userName
                        anchors.left: userAvatar.right
                        anchors.leftMargin: Units.dp(8)
                        anchors.verticalCenter: userAvatar.verticalCenter
                    }

                    Label {
                        id: date
                        anchors.right: parent.right
                        text: "03 Feb 2016 - 08:43AM"
                        anchors.verticalCenter: userAvatar.verticalCenter
                    }
                }

                Item {
                    width: parent.width
                    height: Units.dp(8)
                }

                TextField {
                    id: editTitleMessage

                    visible: onEditMessage
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

                    visible: !onEditMessage
                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)

                    style: "title"
                }

                Item {
                    width: parent.width
                    height: Units.dp(8)
                }

                TextArea {
                    id: editMessage

                    visible: onEditMessage
                    anchors.left: parent.left
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)
                    width: parent.width - Units.dp(32)
                    height: message.height

                    text: message.text

                }

                Label {
                    id: message

                    visible: !onEditMessage
                    anchors.left: parent.left
                    anchors.leftMargin: Units.dp(16)
                    anchors.rightMargin: Units.dp(16)
                    width: parent.width - Units.dp(32)

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
                        visible: !onEditMessage
                        id: editMessageButton
                        iconName: "image/edit"

                        onClicked: onEditMessage = true
                    }

                    IconButton {
                        visible: !onEditMessage
                        id: deleteMessageButton
                        iconName: "action/delete"
                    }

                    IconButton {
                        visible: !onEditMessage
                        id: convertIntoBugButton
                        iconName: "action/bug_report"
                    }

                    Button {
                        visible: onEditMessage
                        id: saveButton
                        text: "Save"
                        elevation: 1

                        onClicked: {
                            console.log("Save !")
                            onEditMessage = false
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

                    onHeightChanged: {
                        console.log("Height equal to ", height);
                    }

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
                    }
                }

                Item {
                    width: parent.width
                    height: Units.dp(8)
                }

                Repeater {
                    model: ["Comment 1", "Comment 2", "Comment 3"]
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
                                text: modelData
                            }

                            Item {
                                width: parent.width
                                height: Units.dp(8)
                            }

                            TextArea {
                                visible: comment.onEditComment
                                anchors.left: parent.left
                                anchors.right: parent.right
                                height: messageCommentary.height
                                text: messageCommentary.text
                            }

                            Label {
                                visible: !comment.onEditComment
                                id: messageCommentary
                                anchors.left: parent.left
                                anchors.right: parent.right
                                text: "Lorem ipsum dolor sit amet, ius novum zril oblique ut, ut consequat complectitur pro. At dicant feugait eam, ius meliore indoctum concludaturque eu, alii euripidis quaerendum pro te."
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
                                visible: !comment.onEditComment
                                id: editComment
                                iconName: "image/edit"

                                onClicked: comment.onEditComment = true
                            }

                            IconButton {
                                visible: !comment.onEditComment
                                id: deleteComment
                                iconName: "action/delete"
                            }

                            Button {
                                visible: comment.onEditComment
                                id: saveButtonComment
                                text: "Save"
                                elevation: 1

                                onClicked: {
                                    console.log("Save !")
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

