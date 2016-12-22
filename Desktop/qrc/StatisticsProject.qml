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

    property int percentProject: 20
    property var projectProgression: [{x: 1, value: 3}, {x: 2, value: 15}, {x: 3, value: 5}]
    property var lateDrawUser: [{label: "Leo Nadeau", value: [2, 4]}, {label: "Marc Wieser", value: [1, 6]}]
    property var lateDrawRole: [{label: "Dev", value: [3, 0]}, {label: "Graphiste", value: [4, 2]}]
    // PROGRESSION
    //
    ListItem.Subtitled {
        elevation: 1
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(48)
        interactive: false

        content: Item {
            anchors.left: parent.left
            anchors.top: parent.top
            anchors.bottom: parent.bottom
            anchors.right: parent.right

            ProgressBar {
                id: progressProg
                anchors.left: parent.left
                anchors.verticalCenter: parent.verticalCenter
                anchors.right: percentProg.left
                anchors.rightMargin:  Units.dp(8)
                value: percentProject
                minimumValue: 0
                maximumValue: 100
                color: "#FC575E"
            }

            Label {
                id: percentProg
                anchors.right: parent.right
                anchors.verticalCenter: parent.verticalCenter
                text: Math.round(percentProject) + "%"
            }
        }

        text: "Progression"
    }

    StatisticsCurve {
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(400)
        useLine: false
        widthBar: width - Units.dp(200)
        colorsUsed: ["#FC575E"]
        minValue: 0
        maxValue: 20
        titleText: "Project progression"
        subtitleText: "(By number of tasks done)"
        dataValues: projectProgression
        dataCategories: ["Jan 2016", "Feb 2016", "Mar 2016", "Apr 2016", "May 2016"]
        dataName: "Progression in task"
    }

    StatisticsBar {
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(400)
        widthBar: width - Units.dp(200)
        colorsUsed: ["#FC575E"]
        minValue: 0
        maxValue: 20
        titleText: "Project progression"
        subtitleText: "(By number of tasks done in a week)"
        dataValues: projectProgression
        dataCategories: ["Jan 2016", "Feb 2016", "Mar 2016", "Apr 2016", "May 2016"]
    }

    RowLayout {
        height: Units.dp(400)
        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(10)

        // LATE DONE USER
        StatisticsBar {
            Layout.fillWidth: true
            Layout.fillHeight: true
            widthBar: width - Units.dp(200)
            dataCategories: ["Late", "Done"]
            dataValues: modelStat.projectInfo.userLate
            titleText: "Number of late and done tasks"
            subtitleText: "(By users)"
        }

        // LATE DONE ROLE
        StatisticsBar {
            Layout.fillWidth: true
            Layout.fillHeight: true
            widthBar: width - Units.dp(200)
            dataCategories: ["Late", "Done"]
            dataValues: modelStat.projectInfo.roleLate
            titleText: "Number of late and done tasks"
            subtitleText: "(By roles)"
        }
    }

    RowLayout {
        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(10)

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 days on the project.".arg(modelStat.projectInfo.dayPassed)
            icon: "communication/forum"
        }

        StatisticsField {
            Layout.fillWidth: true
            text: modelStat.projectInfo.dayRemaining < 0 ?
                      "Project late for %1 days.".arg(Math.abs(modelStat.projectInfo.dayRemaining)) :
                      "%1 days remaining on the project.".arg(modelStat.projectInfo.dayRemaining)
            icon: "communication/forum"
        }

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 clients on the project.".arg(modelStat.projectInfo.clientActual)
            subText: "On a total of %1".arg(modelStat.projectInfo.clientMax)
            icon: "communication/forum"
        }
    }

    RowLayout {
        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(10)

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 messages on client timeline.".arg(modelStat.projectInfo.clientTimeline)
            icon: "communication/forum"
        }

        StatisticsField {
            Layout.fillWidth: true
            text: "%1 messages on team timeline.".arg(modelStat.projectInfo.teamTimeline)
            icon: "communication/forum"
        }
    }

    ListItem.Subtitled {
        elevation: 1
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(48)
        interactive: false

        content: Item {
            anchors.left: parent.left
            anchors.top: parent.top
            anchors.bottom: parent.bottom
            anchors.right: parent.right

            ProgressBar {
                id: progressCloud
                anchors.left: parent.left
                anchors.verticalCenter: parent.verticalCenter
                anchors.right: percentCloud.left
                anchors.rightMargin:  Units.dp(8)
                value: modelStat.projectInfo.cloud
                minimumValue: 0
                maximumValue: 100
                color: "#FC575E"
            }

            Label {
                id: percentCloud
                anchors.right: parent.right
                anchors.verticalCenter: parent.verticalCenter
                text: Math.round(progressCloud.value) + "%"
            }
        }

        text: "Cloud storage"
    }
}
