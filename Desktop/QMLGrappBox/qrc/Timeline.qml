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

    property var mouseCursor
    property bool openedCommentary: false

    function finishedLoad() {
        // Maybe you found this stupid but a bug in the TabBar
        // show the 2nd bar selected but in the code it's the first.
        // This permit to select an other tab and reselect the first
        // in order to have the correct position of the cursor.
        tab.selectedIndex = 1
        tab.selectedIndex = 0
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
            height: Units.dp(128)
            placeHolderText: "Description"
        }

        onAccepted: {
            console.log("add a message")
        }

        onShowingChanged: {
            newMessageText.text = ""
            newMessageTitle.text = ""
        }
    }

    Flickable {
        visible: tab.selectedIndex == 0
        id: flickmainClient

        anchors.left: parent.left
        anchors.right: parent.right
        anchors.bottom: parent.bottom
        anchors.top: tab.bottom
        clip: true

        contentHeight: Math.max(parent.height, columnClient.implicitHeight + Units.dp(16))

        onWidthChanged: {
            if (width >= 1170)
            {
                lineClient.anchors.left = undefined
                lineClient.anchors.horizontalCenter = lineClient.parent.horizontalCenter
                lineClient.width = Units.dp(4)
            }
            else
            {
                lineClient.anchors.horizontalCenter = undefined
                lineClient.anchors.left = lineClient.parent.left
            }
        }

        Rectangle {
            id: lineClient
            color: "grey"

            anchors.horizontalCenter: parent.width >= 1170 ? parent.horizontalCenter : undefined
            anchors.left: parent.width >= 1170 ? undefined : parent.left
            anchors.leftMargin: parent.width >= 1170 ? 0 : Units.dp(36)
            anchors.top: columnClient.top
            anchors.bottom: (columnClient.height <= parent.height) ? parent.bottom : columnClient.bottom
            anchors.topMargin: Units.dp(8)
            anchors.bottomMargin: Units.dp(8)
            width: Units.dp(4)
        }

        ColumnLayout {
            id: columnClient
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.top: parent.top

            spacing: Units.dp(16)

            Item {
                Layout.fillWidth: true
                Layout.preferredHeight: Units.dp(8)
            }

            Repeater {
                model: ["First C", "Seconde C", "Third C", "Fourth C"]
                delegate: TimelineBlock {
                    title: modelData
                    description: "Lorem ipsum dolor sit amet, ius novum zril oblique ut, ut consequat complectitur pro. At dicant feugait eam, ius meliore indoctum concludaturque eu, alii euripidis quaerendum pro te. Ea est numquam pericula, et ipsum vocibus mediocritatem his, sea doming doctus ne. His ex conceptam appellantur, pri te enim timeam antiopam. Has ne verear perpetua pertinacia, vidisse deserunt incorrupte eum ei."
                    information: "By Leo Nadeau - 07 Feb 2016"
                    iconSource: Qt.resolvedUrl("qrc:/icons/icons/linkedin-box.svg")
                    onRight: index % 2 == 0
                    large: parent.width >= 1170

                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.margins: Units.dp(16)

                    onReadMore: {
                        openedCommentary = true
                    }
                }
            }
        }
    }

    Flickable {
        visible: tab.selectedIndex == 1

        id: flickmainTeam

        anchors.left: parent.left
        anchors.right: parent.right
        anchors.bottom: parent.bottom
        anchors.top: tab.bottom
        clip: true

        contentHeight: Math.max(parent.height, columnTeam.implicitHeight + Units.dp(16))

        onWidthChanged: {
            if (width >= 1170)
            {
                lineTeam.anchors.left = undefined
                lineTeam.anchors.horizontalCenter = lineTeam.parent.horizontalCenter
                lineTeam.width = Units.dp(4)
            }
            else
            {
                lineTeam.anchors.horizontalCenter = undefined
                lineTeam.anchors.left = lineTeam.parent.left
                lineTeam.width = Units.dp(4)
            }
        }

        Rectangle {
            id: lineTeam
            color: "grey"

            anchors.horizontalCenter: parent.width >= 1170 ? parent.horizontalCenter : undefined
            anchors.left: parent.width >= 1170 ? undefined : parent.left
            anchors.leftMargin: parent.width >= 1170 ? 0 : Units.dp(36)
            anchors.top: columnTeam.top
            anchors.bottom: (columnTeam.height <= parent.height) ? parent.bottom : columnTeam.bottom
            anchors.topMargin: Units.dp(8)
            anchors.bottomMargin: Units.dp(8)
            width: Units.dp(4)
        }

        ColumnLayout {
            id: columnTeam
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.top: parent.top

            spacing: Units.dp(16)

            Item {
                Layout.fillWidth: true
                Layout.preferredHeight: Units.dp(8)
            }

            Repeater {
                model: ["First", "Seconde", "Third", "Fourth", "Fifth"]
                delegate: TimelineBlock {
                    title: modelData
                    description: "Lorem ipsum dolor sit amet, ius novum zril oblique ut, ut consequat complectitur pro. At dicant feugait eam, ius meliore indoctum concludaturque eu, alii euripidis quaerendum pro te. Ea est numquam pericula, et ipsum vocibus mediocritatem his, sea doming doctus ne. His ex conceptam appellantur, pri te enim timeam antiopam. Has ne verear perpetua pertinacia, vidisse deserunt incorrupte eum ei."
                    information: "By Leo Nadeau - 07 Feb 2016"
                    iconSource: Qt.resolvedUrl("qrc:/icons/icons/linkedin-box.svg")
                    onRight: index % 2 == 0
                    large: parent.width >= 1170

                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.margins: Units.dp(16)

                    onReadMore: {
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
        visible: openedCommentary
        anchors.fill: parent
        avatarSource: Qt.resolvedUrl("qrc:/icons/icons/linkedin-box.svg")
        user: "Leo Nadeau"
        title: "New timeline message"
        text: "Lorem ipsum dolor sit amet, ius novum zril oblique ut, ut consequat complectitur pro. At dicant feugait eam, ius meliore indoctum concludaturque eu, alii euripidis quaerendum pro te. Ea est numquam pericula, et ipsum vocibus mediocritatem his, sea doming doctus ne."
        isLoading: false

        onClose: {
            openedCommentary = false
        }
    }

    ActionButton {
        anchors {
            right: parent.right
            bottom: parent.bottom
            margins: Units.dp(32)
        }

        iconName: "content/add"

        onClicked: {
            newMessageDialog.show()
        }
    }
}

