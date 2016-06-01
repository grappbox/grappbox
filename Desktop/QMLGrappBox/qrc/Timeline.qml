import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0

Item {

    property var mouseCursor

    function finishedLoad() {

    }

    Rectangle {
        color: "grey"

        anchors.horizontalCenter: parent.horizontalCenter
        anchors.top: parent.top
        anchors.bottom: column.bottom
        anchors.topMargin: Units.dp(8)
        anchors.bottomMargin: Units.dp(8)
        width: Units.dp(4)
    }

    ColumnLayout {
        id: column
        anchors.fill: parent

        spacing: Units.dp(16)

        Repeater
        {
            model: ["First", "Seconde", "Third"]
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
            }
        }
    }
}

