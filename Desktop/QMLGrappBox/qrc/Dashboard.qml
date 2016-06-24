import QtQuick 2.0
import QtQuick.Layouts 1.1
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem
import QtQuick.Controls 1.3 as Controls
import GrappBoxController 1.0

Item {

    anchors.fill: parent

    property var mouseCursor

    function finishedLoad()
    {
        dashboardModel.loadProjectList()
        if (SDataManager.hasProject)
            dashboardModel.selectProject(SDataManager.project)
    }

    DashboardModel {
        id: dashboardModel
    }

    Flickable {

        id: dashboardFlick
        visible: !SDataManager.hasProject

        anchors.fill: parent

        contentHeight: Math.max(flowProject.height + 190, parent.height)

        Label {
            id: welcomeLabel
            text: "Welcome to GrappBox"
            style: "display3"
            anchors.horizontalCenter: parent.horizontalCenter
            anchors.top: parent.top
            anchors.topMargin: Units.dp(32)
        }

        Label {
            id: bodyWelcomeLabel
            text: "You can create a project or select a project from the list"
            style: "body2"
            anchors.horizontalCenter: parent.horizontalCenter
            anchors.top: welcomeLabel.bottom
            anchors.topMargin: Units.dp(32)
        }

        Button {
            id: createProjectButton
            text: "Create a project"
            elevation: 1
            backgroundColor: Theme.primaryColor
            anchors.horizontalCenter: parent.horizontalCenter
            anchors.top: bodyWelcomeLabel.bottom
            anchors.topMargin: Units.dp(32)

            onClicked: {
                console.log("Create Project")
            }
        }

        Flow {
            id: flowProject
            anchors.top : createProjectButton.bottom
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.topMargin: Units.dp(32)
            property int rowCount: elements.count === 0 ? 0 : (parent.width - 16) / (elements.itemAt(0).width + spacing)
            property int rowWidth: elements.count === 0 ? parent.width - 16 : rowCount * elements.itemAt(0).width + (rowCount - 1) * spacing
            property int mar: (parent.width - rowWidth) / 2
            spacing: Units.dp(16)
            anchors.leftMargin: mar
            anchors.rightMargin: mar

            Repeater {
                id: elements
                model: dashboardModel.projectList
                delegate: Item {
                    width: 400
                    height: 180
                    View {
                        anchors.fill: parent
                        elevation: viewMouseArea.containsMouse ? 2 : 1
                        radius: Units.dp(2)

                        onElevationChanged: {

                        }

                        MouseArea {
                            id: viewMouseArea
                            anchors.fill: parent

                            hoverEnabled: true

                            onClicked: {
                                if (mouse.button == Qt.LeftButton)
                                {
                                    dashboardModel.selectProject(modelData)
                                }
                            }

                            onHoveredChanged: {
                                if (containsMouse)
                                    mouseCursor.cursorShape = Qt.PointingHandCursor
                                else
                                    mouseCursor.cursorShape = Qt.ArrowCursor
                            }

                            Component.onCompleted: {
                                if (containsMouse)
                                    mouseCursor.cursorShape = Qt.PointingHandCursor
                                else
                                    mouseCursor.cursorShape = Qt.ArrowCursor
                            }
                        }

                        Image {
                            id: logo

                            anchors.left: parent.left
                            anchors.top: parent.top
                            anchors.bottom: openProject.top
                            anchors.leftMargin: Units.dp(8)
                            width: 80

                            source: "qrc:/Logo/Title.png"
                            fillMode: Image.PreserveAspectFit
                        }

                        ColumnLayout {
                            anchors {
                                left: logo.right
                                right: parent.right
                                top: parent.top
                                bottom: openProject.top
                                leftMargin: Units.dp(16)
                                topMargin: Units.dp(16)
                                bottomMargin: Units.dp(16)
                            }

                            Label {
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                    margins: Units.dp(16)
                                }

                                style: "body2"
                                text: modelData.name
                            }

                            Item {
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                    margins: Units.dp(16)
                                }
                                height: Units.dp(24)
                                Icon {
                                    id: bugIcon
                                    name: "action/bug_report"
                                    anchors.verticalCenter: parent.verticalCenter
                                    anchors.left: parent.left
                                    width: 24
                                    height: 24
                                }

                                Label {
                                    anchors {
                                        left: bugIcon.right
                                        right: parent.right
                                        leftMargin: Units.dp(16)
                                        verticalCenter: parent.verticalCenter
                                    }
                                    style: "body1"
                                    text: modelData.numBugTotal + " issue" + (modelData.numBugTotal > 1 ? "s" : "")
                                }
                            }

                            Item {
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                    margins: Units.dp(16)
                                }
                                height: Units.dp(24)
                                Icon {
                                    id: timelineIcon
                                    name: "communication/message"
                                    anchors.verticalCenter: parent.verticalCenter
                                    anchors.left: parent.left
                                    width: 24
                                    height: 24
                                }

                                Label {
                                    anchors {
                                        left: timelineIcon.right
                                        right: parent.right
                                        leftMargin: Units.dp(16)
                                        verticalCenter: parent.verticalCenter
                                    }
                                    style: "body1"
                                    text: modelData.numMessageTimeline + " message" + (modelData.numBugTotal > 1 ? "s" : "")
                                }
                            }

                            Item {
                                anchors {
                                    left: parent.left
                                    right: parent.right
                                    margins: Units.dp(16)
                                }
                                height: Units.dp(24)
                                Icon {
                                    id: taskIcon
                                    name: "action/assignment"
                                    anchors.verticalCenter: parent.verticalCenter
                                    anchors.left: parent.left
                                    width: 24
                                    height: 24
                                }

                                Label {
                                    anchors {
                                        left: taskIcon.right
                                        right: parent.right
                                        leftMargin: Units.dp(16)
                                        verticalCenter: parent.verticalCenter
                                    }
                                    style: "body1"
                                    text: modelData.numTaskFinished + "/" + modelData.numTaskTotal + " Task" + (modelData.numBugTotal > 1 ? "s finished" : " finished")
                                }
                            }
                        }

                        Button {
                            id: openProject
                            text: "Open"

                            anchors.bottom: parent.bottom
                            anchors.right: parent.right
                            anchors.rightMargin: Units.dp(16)

                            textColor: Theme.primaryColor

                            onClicked: {
                                "Select project"
                            }
                        }
                    }
                }
            }
        }

    }

    Flickable {

        id: dashboardProjectFlick
        visible: SDataManager.hasProject

        anchors.fill: parent

        contentHeight: Math.max(flowElementDashboard.height + Units.dp(32), parent.height)

        Flow {
            id: flowElementDashboard

            anchors {
                top: parent.top
                left: parent.left
                right: parent.right
                margins: Units.dp(16)
            }

            Item {
                width: parent.width
                height: titleUser.height + flowUser.height + Units.dp(32)

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
                        topMargin: Units.dp(16)
                        left: parent.left
                        right: parent.right
                    }

                    spacing: Units.dp(16)

                    Repeater {
                        model: dashboardModel.userProjectList
                        delegate: Item {
                            width: 200
                            height: 210
                            View {
                                anchors.fill: parent
                                elevation: viewUserMouseArea.containsMouse ? 2 : 1
                                radius: Units.dp(2)

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
                                        margins: Units.dp(16)
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
                                            leftMargin: Units.dp(-16)
                                            rightMargin: Units.dp(-16)
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

                width: parent.width
                height: titleEvent.height + flowUser.height + Units.dp(32)

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
                        topMargin: Units.dp(16)
                        left: parent.left
                        right: parent.right
                    }

                    spacing: Units.dp(16)

                    Repeater {
                        model: dashboardModel.newEventList
                        delegate: Item {
                            width: 400
                            height: 220
                            View {
                                anchors.fill: parent
                                elevation: viewEventMouseArea.containsMouse ? 2 : 1
                                radius: Units.dp(2)

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
                                    anchors.leftMargin: Units.dp(8)
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
                                        leftMargin: Units.dp(16)
                                        topMargin: Units.dp(16)
                                        bottomMargin: Units.dp(16)
                                    }

                                    Label {
                                        anchors {
                                            left: parent.left
                                            right: parent.right
                                            margins: Units.dp(16)
                                        }

                                        style: "body2"
                                        text: modelData.title
                                    }

                                    Item {
                                        anchors {
                                            left: parent.left
                                            right: parent.right
                                            margins: Units.dp(16)
                                        }
                                        height: Units.dp(24)
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
                                                leftMargin: Units.dp(16)
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
                                            margins: Units.dp(16)
                                        }
                                        height: Units.dp(48)
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
                                                leftMargin: Units.dp(16)
                                                top: parent.top
                                            }
                                            style: "body1"
                                            text: Qt.formatDateTime(modelData.startDate, "dddd, MMMM dd - hh:mm AP")
                                        }

                                        Label {
                                            anchors {
                                                left: timeIcon.right
                                                right: parent.right
                                                leftMargin: Units.dp(16)
                                                top: dateStart.bottom
                                                topMargin: Units.dp(4)
                                            }
                                            style: "body1"
                                            text: Qt.formatDateTime(modelData.endDate, "dddd, MMMM dd - hh:mm AP")
                                        }
                                    }

                                    Item {
                                        anchors {
                                            left: parent.left
                                            right: parent.right
                                            margins: Units.dp(16)
                                        }
                                        height: Units.dp(24)
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
                                                leftMargin: Units.dp(16)
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
                                    anchors.rightMargin: Units.dp(16)

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



        }
    }


    /*ActionButton {
        anchors {
            right: parent.right
            bottom: parent.bottom
            margins: Units.dp(32)
        }

        iconName: "content/add"

        onClicked: {
            taskFormDialog.show()
        }
    }*/

    Scrollbar {
        flickableItem: dashboardFlick
        enabled: dashboardFlick.visible
    }

    Scrollbar {
        flickableItem: dashboardProjectFlick
        enabled: dashboardProjectFlick.visible
    }
}

