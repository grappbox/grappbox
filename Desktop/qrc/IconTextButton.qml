import QtQuick 2.4
import QtGraphicalEffects 1.0
import QtQuick.Layouts 1.1
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem

View {
    id: buttonItem

    property int margins: Units. dp(16)

    property alias text: label.text
    property alias iconName: icon.name
    property alias iconColor: icon.color
    property alias textColor: label.color

    property bool selected
    property bool interactive: true

    elevation: 1

    signal clicked()
    signal rightClicked()
    signal doubleClicked()
    signal pressAndHold()

    width: row.implicitWidth

    Ink {
        id: ink
        acceptedButtons: Qt.LeftButton | Qt.RightButton
        onClicked: {
            if (mouse.button == Qt.LeftButton)
                buttonItem.clicked()
            else
                buttonItem.rightClicked()
        }
        onDoubleClicked: buttonItem.doubleClicked()
        onPressAndHold: buttonItem.pressAndHold()

        anchors.fill: parent
        z: buttonItem.elevation
    }

    RowLayout {
        id: row
        anchors.fill: parent
        anchors.leftMargin: buttonItem.margins
        anchors.rightMargin: buttonItem.margins

        spacing: Units. dp(16)

        Item {
            id: actionItem

            Layout.preferredWidth: Units. dp(22)
            Layout.preferredHeight: width

            Layout.alignment: Qt.AlignCenter

            visible: icon.valid

            Icon {
                id: icon

                anchors {
                    verticalCenter: parent.verticalCenter
                    left: parent.left
                }

                visible: valid
                size: Units. dp(24)
            }
        }

        Label {
            id: label

            clip: true
            Layout.fillWidth: true
        }
    }

    tintColor: selected ? Qt.rgba(0, 0, 0, 0.05) : ink.containsMouse ? Qt.rgba(0, 0, 0, 0.03) : Qt.rgba(0, 0, 0, 0)
}

