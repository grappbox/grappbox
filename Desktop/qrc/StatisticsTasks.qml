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
    property StatisticsModel modelStat

    anchors.left: parent.left
    anchors.right: parent.right
    spacing: Units.dp(10)

    RowLayout {
        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(10)

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 tasks late".arg(modelStat.taskInfo.taskLate)
            subText: "On a total of %1 tasks".arg(modelStat.taskInfo.taskTotal)
            icon: "action/query_builder"
        }

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 tasks done".arg(modelStat.taskInfo.taskDone)
            subText: "On a total of %1 tasks".arg(modelStat.taskInfo.taskTotal)
            icon: "action/query_builder"
        }
    }

    RowLayout {
        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(10)

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 tasks waiting".arg(modelStat.taskInfo.taskToDo)
            subText: "On a total of %1 tasks".arg(modelStat.taskInfo.taskTotal)
            icon: "action/query_builder"
        }

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 tasks in development".arg(modelStat.taskInfo.taskDoing)
            subText: "On a total of %1 tasks".arg(modelStat.taskInfo.taskTotal)
            icon: "action/query_builder"
        }
    }

    StatisticsPie {
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(400)
        widthPie: height - Units.dp(60)
        titleText: "Repartition of tasks"
        subtitleText: "(By roles) CHANGE THIS"
        dataValues: modelStat.taskInfo.taskRepartition
    }
}
