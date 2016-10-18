import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import Material 0.2
import Material.Extras 0.1
import GrappBoxController 1.0

Item {

    property alias text: textEd.text
    property string placeHolderText
    property color placeHolderColor: Theme.light.hintColor
    property color textColor: Theme.light.textColor

    Flickable {
        id: flick

        anchors.fill: parent
        anchors.bottomMargin: Units. dp(4)

        contentHeight: textEd.paintedHeight
        clip: true

        function ensureVisible(r)
        {
            if (contentX >= r.x)
                contentX = r.x;
            else if (contentX+width <= r.x+r.width)
                contentX = r.x+r.width-width;
            if (contentY >= r.y)
                contentY = r.y;
            else if (contentY+height <= r.y+r.height)
                contentY = r.y+r.height-height;
        }

        TextEdit {
            id: textEd

            font.pixelSize: Units. dp(16)
            width: flick.width
            height: flick.height
            color: textColor

            wrapMode: TextEdit.Wrap
            onCursorRectangleChanged: flick.ensureVisible(cursorRectangle)

            onFocusChanged: {
                textEditFocusChanged(focus)
            }
        }

        Label {
            id: placeholder
            anchors.left: textEd.left
            anchors.top: textEd.top

            color: textEd.text !== "" ? "transparent" : placeHolderColor
            text: placeHolderText

            font.pixelSize: Units. dp(16)

            Behavior on color {
                ColorAnimation { duration: 200 }
            }
        }
    }

    Rectangle {
        id: underline
        color: textEd.activeFocus ? background.color : Theme.light.hintColor

        height: textEd.activeFocus ? Units. dp(2) : Units. dp(1)
        visible: true

        anchors {
            left: parent.left
            right: parent.right
            bottom: parent.bottom
        }

        Behavior on height {
            NumberAnimation { duration: 200 }
        }

        Behavior on color {
            ColorAnimation { duration: 200 }
        }
    }

    Scrollbar {
        flickableItem: flick
    }

    signal textEditFocusChanged(var focus)
}

