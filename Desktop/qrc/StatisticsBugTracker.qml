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
    anchors.left: parent.left
    anchors.right: parent.right
    spacing: Units.dp(10)

    RowLayout {
        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(10)

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 number of issues".arg(0)
            subText: "On a total of %1 tasks".arg(3)
            icon: "action/query_builder"
        }

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 number of issues".arg(0)
            subText: "On a total of %1 tasks".arg(3)
            icon: "action/query_builder"
        }

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 number of issues".arg(0)
            subText: "On a total of %1 tasks".arg(3)
            icon: "action/query_builder"
        }
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
            widthPie: width - Units.dp(200)
            titleText: "Repartition of tasks"
            subtitleText: "(By roles)"
            dataValues: [{label: "Dev", value: 20}, {label: "Graphiste", value: 10}]
        }

        StatisticsBar {
            Layout.fillWidth: true
            Layout.fillHeight: true
            widthBar: width - Units.dp(200)
            minValue: 0
            maxValue: 10
            titleText: "Number of task"
            subtitleText: "(By users)"
            dataCategories: ["Doing", "Done"]
            dataValues: [
                {
                    label: "Léo Nadeau",
                    value: [5, 1]
                },
                {
                    label: "Marc Wieser",
                    value: [1, 7]
                },
                {
                    label: "Roland Hemmer",
                    value: [3, 0]
                },
                {
                    label: "Valentin Mougenot",
                    value: [8, 4]
                }
            ]
        }
    }
}
