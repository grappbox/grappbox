import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

Item {

    id: timelineItem

    property var mouseCursor
    property bool openedCommentary: false

    function finishedLoad() {
        // Maybe you found this stupid but a bug in the TabBar
        // show the 2nd bar selected but in the code it's the first.
        // This permit to select an other tab and reselect the first
        // in order to have the correct position of the cursor.
        tab.selectedIndex = 1
        tab.selectedIndex = 0
        modelTimeline.loadTimelines()
    }

    TimelineModel {
        id: modelTimeline

        onCloseCommentIfId: {
            if (openedCommentary && commentary.messageData && commentary.messageData.id === id)
                openedCommentary = false
        }

        onEditSuccess: {
            timelineItem.parent.info("Message correctly modified.")
        }

        onDeleteSuccess: {
            timelineItem.parent.info("Message correctly deleted.")
        }
    }

    Dialog {
        id: newMessageDialog
        hasActions: true
        title: "Add a new message"

        width: 500

        TextField {
            id: newMessageTitle
            width: parent.width
            placeholderText: "Title"
        }

        TextArea {
            id: newMessageText
            width: parent.width
            height: Units. dp(128)
            placeHolderText: "Description"
        }

        onAccepted: {
            modelTimeline.addMessageTimeline(tab.selectedIndex == 0, newMessageTitle.text, newMessageText.text)
            newMessageText.text = ""
            newMessageTitle.text = ""
        }
    }

    ProgressCircle {
        anchors.centerIn: parent
        visible: modelTimeline.isLoadingTimeline
    }

    Flickable {
        visible: tab.selectedIndex == 0 && !modelTimeline.isLoadingTimeline
        id: flickmainClient

        anchors.left: parent.left
        anchors.right: parent.right
        anchors.bottom: parent.bottom
        anchors.top: tab.bottom
        clip: true

        contentHeight: Math.max(parent.height, columnClient.implicitHeight + Units. dp(16))

        onWidthChanged: {
            if (width >= 1170)
            {
                lineClient.anchors.left = undefined
                lineClient.anchors.horizontalCenter = lineClient.parent.horizontalCenter
                lineClient.width = Units. dp(4)
            }
            else
            {
                lineClient.anchors.horizontalCenter = undefined
                lineClient.anchors.left = lineClient.parent.left
            }
        }

        Label {
            id: noMessageClient
            text: "Be the first to create a message for your client !"
            style: "title"
            anchors.top: parent.top
            anchors.horizontalCenter: parent.horizontalCenter
            anchors.topMargin: Units.dp(32)
            visible: modelTimeline.timelineClient.length === 0
        }

        Rectangle {
            id: lineClient
            color: "grey"
            visible: modelTimeline.timelineClient.length > 0

            anchors.horizontalCenter: parent.width >= 1170 ? parent.horizontalCenter : undefined
            anchors.left: parent.width >= 1170 ? undefined : parent.left
            anchors.leftMargin: parent.width >= 1170 ? 0 : Units. dp(36)
            anchors.top: columnClient.top
            anchors.bottom: (columnClient.height <= parent.height) ? parent.bottom : columnClient.bottom
            anchors.topMargin: Units. dp(8)
            anchors.bottomMargin: Units. dp(8)
            width: Units. dp(4)
        }

        Column {
            id: columnClient
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.top: parent.top

            spacing: Units. dp(16)

            Item {
                height: Units. dp(16)
                width: parent.width
            }

            Repeater {
                model: modelTimeline.timelineClient
                delegate: TimelineBlock {
                    title: modelData.title
                    description: modelData.message
                    information: "By " + modelData.associatedUser.firstName + " - " + Qt.formatDateTime(modelData.lastEdit, "dddd, MMMM dd - hh:mm AP")
                    avatarId: modelData.associatedUser.id
                    avatarDate: modelData.associatedUser.avatarDate
                    onRight: index % 2 == 0
                    large: parent.width >= 1170

                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.margins: Units. dp(16)

                    onReadMore: {
                        commentary.messageData = modelData
                        modelTimeline.loadComments(true, modelData.id)
                        openedCommentary = true
                    }
                }
            }
        }
    }

    Flickable {
        visible: tab.selectedIndex == 1 && !modelTimeline.isLoadingTimeline

        id: flickmainTeam

        anchors.left: parent.left
        anchors.right: parent.right
        anchors.bottom: parent.bottom
        anchors.top: tab.bottom
        clip: true

        contentHeight: Math.max(parent.height, columnTeam.implicitHeight + Units. dp(16))

        onWidthChanged: {
            if (width >= 1170)
            {
                lineTeam.anchors.left = undefined
                lineTeam.anchors.horizontalCenter = lineTeam.parent.horizontalCenter
                lineTeam.width = Units. dp(4)
            }
            else
            {
                lineTeam.anchors.horizontalCenter = undefined
                lineTeam.anchors.left = lineTeam.parent.left
                lineTeam.width = Units. dp(4)
            }
        }

        Label {
            id: noMessageTeam
            text: "Be the first to create a message for your team !"
            style: "title"
            anchors.top: parent.top
            anchors.horizontalCenter: parent.horizontalCenter
            anchors.topMargin: Units.dp(32)
            visible: modelTimeline.timelineTeam.length === 0
        }

        Rectangle {
            id: lineTeam
            color: "grey"
            visible: modelTimeline.timelineTeam.length > 0

            anchors.horizontalCenter: parent.width >= 1170 ? parent.horizontalCenter : undefined
            anchors.left: parent.width >= 1170 ? undefined : parent.left
            anchors.leftMargin: parent.width >= 1170 ? 0 : Units. dp(36)
            anchors.top: columnTeam.top
            anchors.bottom: (columnTeam.height <= parent.height) ? parent.bottom : columnTeam.bottom
            anchors.topMargin: Units. dp(8)
            anchors.bottomMargin: Units. dp(8)
            width: Units. dp(4)
        }

        Column {
            id: columnTeam
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.top: parent.top

            spacing: Units. dp(16)

            Item {
                height: Units. dp(16)
                width: parent.width
            }

            Repeater {
                model: modelTimeline.timelineTeam
                delegate: TimelineBlock {
                    title: modelData.title
                    description: modelData.message
                    information: "By " + modelData.associatedUser.firstName + " - " + Qt.formatDateTime(modelData.lastEdit, "dddd, MMMM dd - hh:mm AP")
                    avatarId: modelData.associatedUser.id
                    avatarDate: modelData.associatedUser.avatarDate
                    onRight: index % 2 == 0
                    large: parent.width >= 1170

                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.margins: Units. dp(16)

                    onReadMore: {
                        commentary.messageData = modelData
                        modelTimeline.loadComments(false, modelData.id)
                        openedCommentary = true
                    }
                }
            }
        }
    }

    Scrollbar {
        enabled: !openedCommentary
        flickableItem: flickmainClient
    }

    Scrollbar {
        enabled: !openedCommentary
        flickableItem: flickmainTeam
    }

    Rectangle {
        anchors.fill: tab

        color: "white"
    }

    TabBar {
        id: tab
        anchors.left: parent.left
        anchors.right: parent.right

        tabs: [tabClient, tabTeam]
        isTabView: false
        fullWidth: true

        highlightColor: Theme.primaryColor

        Tab {
            id: tabClient
            title: "Client"
        }

        Tab {
            id: tabTeam
            title: "Team"
        }

        centered: true
        isLargeDevice: true
    }

    TimelineCommentary {
        id: commentary
        visible: openedCommentary
        anchors.fill: parent

        isLoading: modelTimeline.isLoadingComment

        onClose: {
            openedCommentary = false
        }

        onEditMessages: {
            modelTimeline.editMessageTimeline(parentId, id, title, message)
        }

        onDeleteMessage: {
            alertDelete.id = id
            alertDelete.parentId = parentId
            alertDelete.show()
        }

        onAddComment: {
            modelTimeline.addMessageTimeline(messageData.id, message)
        }

        onMakeBug: {
            modelTimeline.addTicket(title, message)
        }
    }

    ActionButton {
        anchors {
            right: parent.right
            bottom: parent.bottom
            margins: Units. dp(32)
        }

        iconName: "content/add"

        onClicked: {
            newMessageDialog.show()
        }
    }

    Dialog {
        id: alertDelete
        width: Units. dp(300)
        text: "Are you sure you want to delete this message ?"
        hasActions: true

        property int id: -1
        property int parentId: -1

        positiveButtonText: "Yes"
        negativeButtonText: "No"

        onAccepted: {
            modelTimeline.deleteMessageTimeline(id, parentId)
        }

    }
}

