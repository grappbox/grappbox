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

    property DashboardModel modelDash
    property bool module

    property StatisticsModel modelStat

    Repeater {
        model: modelStat.userInfo.workingCharge
        delegate: ListItem.Subtitled {
            visible: !module || dashboardModel.activatedStat["Userschargerepartition"]
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
                    value: Math.max(modelData.charge, 0, 100)
                    minimumValue: 0
                    maximumValue: 100
                    color: "#FC575E"
                }

                Label {
                    id: percentCloud
                    anchors.right: parent.right
                    anchors.verticalCenter: parent.verticalCenter
                    text: Math.round(modelData.charge) + "%"
                }
            }
            text: modelData.user.firstname + " " + modelData.user.lastname
        }
    }

    StatisticsBarStacked {
        visible: !module || dashboardModel.activatedStat["Userstasksstate"]
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(400)
        widthBar: width - Units.dp(200)
        minValue: 0
        maxValue: 20
        titleText: "Task state"
        subtitleText: "(By Users)"
        dataCategories: ["Done", "Doing", "To do", "Late"]
        dataValues: modelStat.userInfo.taskState
    }
}
