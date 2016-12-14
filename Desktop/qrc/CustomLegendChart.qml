import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles
import QtCharts 2.0

Rectangle {
    id: legend
    property int seriesCount: 0
    property variant seriesNames: []
    property variant seriesColors: []

    signal entered(string seriesName)
    signal exited(string seriesName)
    signal selected(string seriesName)

    function addSeries(seriesName, color) {
        var names = seriesNames;
        names[seriesCount] = seriesName;
        seriesNames = names;

        var colors = seriesColors;
        colors[seriesCount] = color;
        seriesColors = colors;

        seriesCount++;
    }

    Component {
        id: legendDelegate
        Item {
            id: rect
            property string name: seriesNames[index]
            property color markerColor: seriesColors[index]

            implicitWidth: label.implicitWidth + marker.implicitWidth + 30
            implicitHeight: label.implicitHeight + marker.implicitHeight + 10

            Row {
                id: row
                spacing: 5
                anchors.verticalCenter: parent.verticalCenter
                anchors.left: parent.left
                anchors.leftMargin: 5
                Rectangle {
                    id: marker
                    anchors.verticalCenter: parent.verticalCenter
                    color: markerColor
                    radius: width / 2
                    width: 10
                    height: 10
                }
                Label {
                    id: label
                    anchors.verticalCenter: parent.verticalCenter
                    anchors.verticalCenterOffset: -1
                    text: name
                    style: "body2"
                }
            }

            MouseArea {
                id: mouseArea
                anchors.fill: parent
                hoverEnabled: true
                onEntered: {
                    legend.entered(label.text);
                }
                onExited: {
                    legend.exited(label.text);
                }
                onClicked: {
                    legend.selected(label.text);
                }
            }
        }
    }

    Column {
        id: legendRow
        anchors.centerIn: parent
        spacing: 10

        Repeater {
            id: legendRepeater
            model: seriesCount
            delegate: legendDelegate
        }
    }

}
