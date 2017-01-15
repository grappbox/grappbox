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

    property DashboardModel dashboardModel
    property bool module

    anchors.left: parent.left
    anchors.right: parent.right
    spacing: Units.dp(10)

    property int percentProject: 20
    property var lateDrawUser: [{label: "Leo Nadeau", value: [2, 4]}, {label: "Marc Wieser", value: [1, 6]}]
    property var lateDrawRole: [{label: "Dev", value: [3, 0]}, {label: "Graphiste", value: [4, 2]}]



    Item {
        width: parent.width
        height: titleUser.height + flowUser.height + Units. dp(32)

        visible: module && dashboardModel.activatedStat["Members"]

        Label {
            id: titleUser
            anchors.top: parent.top

            style: "title"
            text: "Member of your project"
        }

        Flow {
            id: flowUser

            anchors {
                top: titleUser.bottom
                topMargin: Units. dp(16)
                left: parent.left
                right: parent.right
            }

            spacing: Units. dp(16)

            Repeater {
                model: dashboardModel.userProjectList
                delegate: Item {
                    width: Units.dp(200)
                    height: Units.dp(210)
                    View {
                        anchors.fill: parent
                        elevation: viewUserMouseArea.containsMouse ? 2 : 1
                        radius: Units. dp(2)

                        MouseArea {
                            id: viewUserMouseArea
                            anchors.fill: parent

                            hoverEnabled: true
                        }

                        Image {
                            id: userAvatar

                            anchors.top: parent.top
                            anchors.horizontalCenter: parent.horizontalCenter
                            height: 110

                            source: "qrc:/Logo/Title.png"
                            fillMode: Image.PreserveAspectFit
                        }

                        ColumnLayout {
                            anchors {
                                left: parent.left
                                right: parent.right
                                top: userAvatar.bottom
                                bottom: parent.bottom
                                margins: Units. dp(16)
                            }

                            Label {
                                Layout.alignment: Qt.AlignCenter

                                style: "body2"
                                text: modelData.firstName + " " + modelData.lastName
                            }

                            Label {
                                Layout.alignment: Qt.AlignCenter

                                style: "button"
                                text: modelData.occupation === 1 ? "Busy" : "Free"
                                color: modelData.occupation === 1 ? Theme.primaryColor : "#70ad47"
                            }

                            Button {
                                elevation: 0
                                anchors {
                                    leftMargin: Units. dp(-16)
                                    rightMargin: Units. dp(-16)
                                    left: parent.left
                                    right: parent.right
                                }

                                backgroundColor: "#70ad47"

                                textColor: "#FFFFFF"
                                text: "Assign a task"
                                enabled: true
                                Layout.alignment: Qt.AlignVCenter
                            }
                        }
                    }
                }
            }
        }
    }

    Item {

        visible: module && dashboardModel.activatedStat["Nextevent"]

        width: parent.width
        height: titleEvent.height + (dashboardModel.newEventList.length > 0 ? flowUser.height : 0) + Units. dp(32)

        Label {
            id: titleEvent
            anchors.top: parent.top

            style: "title"
            text: "Next event of your project"
        }

        Flow {
            id: flowEvent

            anchors {
                top: titleEvent.bottom
                topMargin: Units. dp(16)
                left: parent.left
                right: parent.right
            }

            spacing: Units. dp(16)

            Repeater {
                model: dashboardModel.newEventList
                delegate: Item {
                    width: 400
                    height: 220
                    View {
                        anchors.fill: parent
                        elevation: viewEventMouseArea.containsMouse ? 2 : 1
                        radius: Units. dp(2)

                        MouseArea {
                            id: viewEventMouseArea
                            anchors.fill: parent

                            hoverEnabled: true

                            onClicked: {
                            }
                        }

                        Image {
                            id: logoEvent

                            anchors.left: parent.left
                            anchors.top: parent.top
                            anchors.bottom: openEvent.top
                            anchors.leftMargin: Units. dp(8)
                            width: 80

                            source: "qrc:/Logo/Title.png"
                            fillMode: Image.PreserveAspectFit
                        }

                        ColumnLayout {
                            anchors {
                                left: logoEvent.right
                                right: parent.right
                                top: parent.top
                                bottom: openEvent.top
                                leftMargin: Units. dp(16)
                                topMargin: Units. dp(16)
                                bottomMargin: Units. dp(16)
                            }

                            Label {
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                    margins: Units. dp(16)
                                }

                                style: "body2"
                                text: modelData.title
                            }

                            Item {
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                    margins: Units. dp(16)
                                }
                                height: Units. dp(24)
                                Icon {
                                    id: eventTypeIcon
                                    name: "action/event"
                                    anchors.verticalCenter: parent.verticalCenter
                                    anchors.left: parent.left
                                    width: 24
                                    height: 24
                                }

                                Label {
                                    anchors {
                                        left: eventTypeIcon.right
                                        right: parent.right
                                        leftMargin: Units. dp(16)
                                        verticalCenter: parent.verticalCenter
                                    }
                                    style: "body1"
                                    text: modelData.type
                                }
                            }

                            Item {
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                    margins: Units. dp(16)
                                }
                                height: Units. dp(48)
                                Icon {
                                    id: timeIcon
                                    name: "device/access_time"
                                    anchors.verticalCenter: dateStart.verticalCenter
                                    anchors.left: parent.left
                                    width: 24
                                    height: 24
                                }

                                Label {
                                    id: dateStart
                                    anchors {
                                        left: timeIcon.right
                                        right: parent.right
                                        leftMargin: Units. dp(16)
                                        top: parent.top
                                    }
                                    style: "body1"
                                    text: Qt.formatDateTime(modelData.startDate, "dddd, MMMM dd - hh:mm AP")
                                }

                                Label {
                                    anchors {
                                        left: timeIcon.right
                                        right: parent.right
                                        leftMargin: Units. dp(16)
                                        top: dateStart.bottom
                                        topMargin: Units. dp(4)
                                    }
                                    style: "body1"
                                    text: Qt.formatDateTime(modelData.endDate, "dddd, MMMM dd - hh:mm AP")
                                }
                            }

                            Item {
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                    margins: Units. dp(16)
                                }
                                height: Units. dp(24)
                                Icon {
                                    id: eventDescription
                                    name: "action/event"
                                    anchors.verticalCenter: parent.verticalCenter
                                    anchors.left: parent.left
                                    width: 24
                                    height: 24
                                }

                                Label {
                                    anchors {
                                        left: eventDescription.right
                                        right: parent.right
                                        leftMargin: Units. dp(16)
                                        verticalCenter: parent.verticalCenter
                                    }
                                    style: "body1"
                                    text: modelData.description
                                }
                            }
                        }

                        Button {
                            id: openEvent
                            text: "Open"

                            anchors.bottom: parent.bottom
                            anchors.right: parent.right
                            anchors.rightMargin: Units. dp(16)

                            textColor: Theme.primaryColor

                            onClicked: {
                                console.log("Open event")
                            }
                        }
                    }
                }
            }
        }

    }

    // PROGRESSION
    //
    ListItem.Subtitled {
        elevation: 1
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(48)
        interactive: false

        visible: !module || dashboardModel.activatedStat["Totalprogression"]

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
        visible: !module || dashboardModel.activatedStat["Progressioncurve"]
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
        dataValues: modelStat.projectInfo.progressionDone
        dataCategories: modelStat.projectInfo.categoriesProgression
        dataName: "Progression in task"
    }

    StatisticsBar {
        visible: !module || dashboardModel.activatedStat["Progressionbar"]
        anchors.left: parent.left
        anchors.right: parent.right
        height: Units.dp(400)
        widthBar: width - Units.dp(200)
        colorsUsed: ["#FC575E"]
        minValue: 0
        maxValue: 20
        titleText: "Project progression"
        subtitleText: "(By number of tasks done each time)"
        dataValues: modelStat.projectInfo.progressionTime
        dataCategories: ["Stat progression"]
    }

    RowLayout {
        height: (lateDoneUser.visible || lateDoneRole.visible) ? Units.dp(400) : 0
        anchors.left: parent.left
        anchors.right: parent.right
        spacing: Units.dp(10)

        // LATE DONE USER
        StatisticsBar {
            id: lateDoneUser
            visible: !module || dashboardModel.activatedStat["Numberoflateanddonetasksbyrole"]
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
            id: lateDoneRole
            visible: !module || dashboardModel.activatedStat["Numberoflateanddonetasksbyusers"]
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
            visible: !module || dashboardModel.activatedStat["Daysontheproject"]
            Layout.fillWidth: true
            text: "%1 days on the project.".arg(modelStat.projectInfo.dayPassed)
            icon: "communication/forum"
        }

        StatisticsField {
            visible: !module || dashboardModel.activatedStat["Daysremaining"]
            Layout.fillWidth: true
            text: modelStat.projectInfo.dayRemaining < 0 ?
                      "Project late for %1 days.".arg(Math.abs(modelStat.projectInfo.dayRemaining)) :
                      "%1 days remaining on the project.".arg(modelStat.projectInfo.dayRemaining)
            icon: "communication/forum"
        }

        StatisticsField {
            visible: !module || dashboardModel.activatedStat["Numberofclient"]
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
            visible: !module || dashboardModel.activatedStat["Numberofclientmessage"]
            Layout.fillWidth: true
            text: "%1 messages on client timeline.".arg(modelStat.projectInfo.clientTimeline)
            icon: "communication/forum"
        }

        StatisticsField {
            visible: !module || dashboardModel.activatedStat["Numberofteammessage"]
            Layout.fillWidth: true
            text: "%1 messages on team timeline.".arg(modelStat.projectInfo.teamTimeline)
            icon: "communication/forum"
        }
    }

    ListItem.Subtitled {
        visible: !module || dashboardModel.activatedStat["Cloudstorage"]
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
