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

Column {

    property StatisticsModel modelState

    anchors.left: parent.left
    anchors.right: parent.right
    spacing: Units.dp(10)

    RowLayout {
        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(10)

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 bugs created by clients.".arg(modelState.bugTrackerInfo.clientBug)
            subText: "On a total of %1 bugs".arg(modelState.bugTrackerInfo.totalBug)
            icon: "action/query_builder"
        }

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 assigned bugs and %2 not assigned bugs".arg(modelState.bugTrackerInfo.assignedBug).arg(modelState.bugTrackerInfo.unassignedBug)
            subText: "On a total of %1 bugs".arg(modelState.bugTrackerInfo.totalBug)
            icon: "action/query_builder"
        }

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 opened bugs and %2 closed bugs".arg(modelState.bugTrackerInfo.openBug).arg(modelState.bugTrackerInfo.closeBug)
            icon: "action/query_builder"
        }
    }

    StatisticsBar {
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(400)
        widthBar: height - Units.dp(60)
        minValue: 0
        maxValue: 10
        titleText: "Number of bugs opened and closed"
        subtitleText: "(By time)"
        dataCategories: ["Doing", "Done"]
        dataValues: modelState.bugTrackerInfo.bugEvolution
    }

    RowLayout {
        height: Units.dp(400)
        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(10)

        StatisticsPie {
            Layout.fillWidth: true
            Layout.fillHeight: true
            height: parent.height
            widthPie: height - Units.dp(60)
            titleText: "Repartition of bugs"
            subtitleText: "(By tags)"
            dataValues: modelState.bugTrackerInfo.bugRepartitionTag
        }

        StatisticsPie {
            Layout.fillWidth: true
            Layout.fillHeight: true
            height: parent.height
            widthPie: height - Units.dp(60)
            titleText: "Repartition of bugs"
            subtitleText: "(By users)"
            dataValues: modelState.bugTrackerInfo.bugRepartitionUser
        }
    }
}
