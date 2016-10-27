import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

Rectangle {
    id: caseDay

    property var mouseCursor
    property int numberOfEvent: 2
    property bool isToday
    property bool isWeekEnd
    property bool isSelected
    property int numberOfDay: 1
    property var dateInfo

    property alias visibleCircle: circle.circleVisible

    border.color: "#E0E0E0"
    border.width: 1

    color: (clickObject.containsMouse || isSelected) ?
               (isToday ? "#a8dbf1" : "#f0f0f0") :
               (isToday ? "#03a9f4" : "#ffffff")

    signal selected()

    MouseArea {
        id: clickObject
        anchors.fill: parent
        hoverEnabled: true

        onClicked: {
            selected()
        }
    }

    Label {
        anchors.right: parent.right
        anchors.top: parent.top
        anchors.margins: Units.dp(8)
        height: 25
        text: numberOfDay
        color: caseDay.isWeekEnd ? "#FC575E" : "#666666"
    }

    Rectangle {
        id: circle
        visible: caseDay.numberOfEvent > 0 && circleVisible
        anchors.left: parent.left
        anchors.top: parent.top
        anchors.margins: Units.dp(8)
        height: 25
        width: 25

        property bool circleVisible: false

        smooth: true
        radius: Math.max(width / 2, height / 2)
        color: "#FC575E"

        Label {
            text: numberOfEvent
            color: "#FFFFFF"
            font.bold: true
            anchors.fill: parent
            verticalAlignment: Text.AlignVCenter
            horizontalAlignment: Text.AlignHCenter
            anchors.margins: Units.dp(4)
        }
    }
}

