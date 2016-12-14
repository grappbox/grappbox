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

Item {
    property var mouseCursor

    function finishedLoad() {
    }

    property var colorsUsed: ["#F44336", "#E91E63", "#9C27B0", "#673AB7", "#3F51B5", "#2196F3", "#03A9F4", "#00BCD4", "#009688", "#4CAF50", "#8BC34A", "#CDDC39", "#FFEB3B", "#FFC107", "#FF9800", "#FF5722", "#795548", "#607D8B"]

    Flickable {
        id: mainFlickable
        anchors.fill: parent
        contentHeight: Math.max(mainColumn.implicitHeight + Units.dp(96), parent.height)

        Column {
            id: mainColumn
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.top: parent.top
            anchors.margins: Units.dp(48)
            spacing: Units.dp(10)

            StatisticsCategoryName {
                colorIcon: "#FC575E"
                iconName: "action/dashboard"
                categoryName: "Project"
            }

            StatisticsProject {}

            Item {
                height: Units.dp(32)
                width: parent.width
            }

            StatisticsCategoryName {
                colorIcon: "#44BBFF"
                iconName: "action/view_list"
                categoryName: "Tasks"
            }

            StatisticsTasks {}


        }
    }

    Scrollbar {
        flickableItem: mainFlickable
    }
}

