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
    id: bugTrackerItem
    property var mouseCursor

    function finishedLoad() {
        modelWhiteboard.updateList()
    }

    WhiteboardModel {
        id: modelWhiteboard
    }

    WhiteboardChoosingPage {
        anchors.fill: parent
        id: choosePage
        cursor: mouseCursor
        whiteModel: modelWhiteboard
        visible: modelWhiteboard.currentItem == -1
    }

    WhiteboardDrawingPage {
        anchors.fill: parent
        id: drawPage
        visible: modelWhiteboard.currentItem != -1
        whiteModel: modelWhiteboard
    }

    IconTextButton {
        id: backButton
        anchors.top:parent.top
        anchors.left: parent.left
        anchors.leftMargin: Units.dp(16)
        width: Units.dp(130)
        visible: modelWhiteboard.currentItem != -1
        text: "BACK"
        iconName: "hardware/keyboard_backspace"
        elevation: 1

        onClicked: {
            modelWhiteboard.closeWhiteboard()
        }
    }
}

