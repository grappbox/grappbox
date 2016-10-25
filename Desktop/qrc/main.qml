import QtQuick 2.5
import QtQuick.Window 2.2
import QtQuick.Layouts 1.1
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem
import QtQuick.Controls 1.3 as Controls
import GrappBoxController 1.0

Item {

    Dialog {
        id: errorDialog
        text: ""
        title: ""

        hasActions: true
        positiveButtonText: "OK"
        negativeButtonText: "Cancel"

        onAccepted: {
            errorDialog.close()
        }

        onRejected: {
            errorDialog.close()
        }
    }

    function errorEmited(title, message) {
        errorDialog.text = message
        errorDialog.title = title
        errorDialog.open()
    }

    function infoEmited(message) {
        snackBar.open(message)
    }

    Component.onCompleted: {
        SInfoManager.error.connect(errorEmited)
        SInfoManager.info.connect(infoEmited)
    }

    ApplicationWindow {
        id: demo

        minimumWidth: Units. dp(1280)
        minimumHeight: Units. dp(720)

        title: SDataManager.hasProject ? "GrappBox - " + SDataManager.project.name : "GrappBox"
        visible: true// !loginPage.visible && controller.isLoged

        theme {
            primaryColor: "#fc575e"
            primaryDarkColor: "#3b3b3b"
            accentColor: "#3c3b3b"
            tabHighlightColor: "white"
        }

        property var sectionTitles: [   "Dashboard",
                                        "Calendar",
                                        "Cloud",
                                        "Timeline",
                                        "Bug Tracker",
                                        "Tasks",
                                        "Gantt",
                                        "Whiteboard",
                                        "Project Settings",
                                        "Login" ]

        property var toHideSection: [   "Login" ]

        property var sectionIcon: [     "action/dashboard",
                                        "action/event",
                                        "file/cloud_upload",
                                        "communication/forum",
                                        "action/bug_report",
                                        "action/view_list",
                                        "content/create",
                                        "action/settings",
                                        "action/settings" ]

        property var sectionColor: [    "#FC575E",
                                        "#44BBFF",
                                        "#F1C40F",
                                        "#FF9F55",
                                        "#9E58DC",
                                        "#44BBFF",
                                        "#44BBFF",
                                        "#27AE60",
                                        "#FC575E",
                                        "#FC575E"
                                        ]

        property string previousSelectedComponent: ""
        property string selectedComponent: "Login"

        property bool navOpen

        property var arguments
        property string loadPageName

        initialPage:
            TabbedPage {
                id: page

                title: demo.selectedComponent

                actionBar.hidden: demo.selectedComponent == "Login"

                actionBar.maxActionCount: 3

                actionBar.backgroundColor: "#f4c"

                //actionBar.anchors.left: content.right

                actions: [

                    Action {
                        iconName: "action/settings"
                        name: "Settings"
                        hoverAnimation: true
                        visible: demo.selectedComponent != "Login"
                    },

                    Action {
                        iconName: "social/notifications"
                        name: "Notification"
                        hoverAnimation: true
                        visible: demo.selectedComponent != "Login"
                    },


                    Action {
                        iconName: "action/language"
                        name: "Language"
                        visible: demo.selectedComponent != "Login"
                    },

                    Action {
                        iconName: "action/account_circle"
                        name: "Accounts"
                        visible: demo.selectedComponent != "Login"

                        onTriggered: {
                            demo.previousSelectedComponent = demo.selectedComponent
                            demo.selectedComponent = "UserSettings"
                        }
                    },

                    Action {
                        iconName: "action/work"
                        name: "Change project"
                        visible: demo.selectedComponent != "Login"

                        onTriggered: {
                            SDataManager.changeProject()
                            demo.selectedComponent = "Dashboard"
                        }
                    },

                    Action {
                        iconName: "action/power_settings_new"
                        name: "Logout"
                        visible: demo.selectedComponent != "Login"

                        onTriggered: {
                            demo.selectedComponent = "Login"
                        }
                    }

                ]

                backAction: Action {
                    iconName: "navigation/menu"
                    visible: demo.selectedComponent != "Login"

                    onTriggered: {
                        demo.navOpen = !demo.navOpen
                    }
                }

                Component.onCompleted: {
                    console.log(actionBar.anchors)
                }

                Loader {
                    id: largeLoader
                    anchors.top: parent.top
                    anchors.bottom: parent.bottom
                    anchors.right: parent.right
                    anchors.left: content.right
                    sourceComponent: tabDelegate

                    visible: active
                    active: true
                }


                View {
                    id: content
                    anchors.left: parent.left
                    anchors.top: parent.top
                    anchors.bottom: parent.bottom
                    anchors.leftMargin: demo.navOpen ? 0 : -width
                    elevation: 1

                    backgroundColor: "#666"

                    Behavior on anchors.leftMargin {
                        NumberAnimation { duration: 200 }
                    }

                    width: Units. dp(300)

                    Flickable {
                        id: flickableNavDrawer
                        anchors.left: parent.left
                        anchors.right: parent.right
                        anchors.bottom: parent.bottom
                        anchors.top: head.bottom
                        anchors.topMargin: Units.dp(32)

                        contentHeight: Math.max(parent.height, columnFunction.implicitHeight)

                        Column {
                            id: columnFunction
                            anchors.left: parent.left
                            anchors.right: parent.right
                            spacing: Units.dp(20)

                            Repeater {
                                model: demo.sectionTitles
                                delegate: ListItem.Standard {

                                    action: Icon {
                                        anchors.verticalCenter: parent.verticalCenter
                                        name: demo.sectionIcon[index]
                                        color: selected ? "#333" : "#ccc"
                                    }
                                    textColor: selected ? "#333" : "#ccc"
                                    backgroundColor: selected ? "#ccc" : "#666"
                                    text: modelData
                                    height: Units.dp(54)
                                    selected: modelData == demo.selectedComponent
                                    visible: (demo.toHideSection.indexOf(modelData) === -1) && (SDataManager.hasProject || index <= 1)
                                    onClicked: {
                                        demo.previousSelectedComponent = demo.selectedComponent
                                        demo.selectedComponent = modelData
                                        page.actionBar.backgroundColor = demo.sectionColor[index]
                                    }
                                }
                            }
                        }
                    }

                    ListItem.Standard {
                        id: head
                        backgroundColor: "#333"
                        anchors.top: parent.top
                        anchors.left: parent.left
                        anchors.right: parent.right
                        textColor: "#ccc"

                        height: Units.dp(60)
                        action: CircleImageAsync {
                            anchors.verticalCenter: parent.verticalCenter
                            avatarDate: SDataManager.user.avatarDate
                            avatarId: SDataManager.user.id
                            width: height
                            height: Units.dp(40)
                        }

                        text: SDataManager.user.firstName + " " + SDataManager.user.lastName
                    }


                    Scrollbar {
                        flickableItem: flickableNavDrawer
                    }
                }
            }

        MouseArea {
            id: cursorMouseArea
            anchors.fill: parent
            cursorShape: Qt.ArrowCursor
            acceptedButtons: Qt.NoButton
        }

        Component {
            id: tabDelegate

            Item {


                Flickable {
                    id: flickable
                    anchors {
                        left: parent.left
                        right: parent.right
                        top: parent.top
                        bottom: parent.bottom
                    }
                    clip: true
                    contentHeight: Math.max(example.implicitHeight + 40, height)
                    Loader {
                        id: example
                        anchors.fill: parent
                        asynchronous: true
                        visible: status == Loader.Ready
                        property var compo: null

                        source: {
                            if (demo.selectedComponent == "")
                                return null

                            compo = Qt.resolvedUrl(demo.selectedComponent.replace(" ", "") + ".qml")
                            return compo
                        }
                        onLoaded: {
                            if (example.item)
                            {
                                example.item.mouseCursor = cursorMouseArea
                                if (example.item.args !== undefined)
                                    example.item.args = demo.arguments
                                example.item.finishedLoad()
                                if (demo.selectedComponent == demo.loadPageName)
                                {
                                    demo.loadPageName = ""
                                    demo.arguments = undefined
                                }
                            }
                        }

                        function loadPage(pageName, args) {
                            demo.arguments = args
                            demo.loadPageName = pageName
                            demo.selectedComponent = pageName
                            var index = 0
                            for (index = 0; index < demo.sectionTitles.length; index++)
                            {
                                if (demo.sectionTitles[index] === pageName)
                                    break;
                            }

                            page.actionBar.backgroundColor = demo.sectionColor[index]
                        }

                        function returnPage() {
                            demo.selectedComponent = demo.previousSelectedComponent
                        }

                        function error(title, message) {
                            errorDialog.title = title
                            errorDialog.text = message
                            errorDialog.show()
                        }

                        function info(val) {
                            snackBar.open(val)
                        }
                    }

                    ProgressCircle {
                        anchors.centerIn: parent
                        visible: example.status == Loader.Loading
                    }
                }
                Scrollbar {
                    flickableItem: flickable
                }
            }
        }

        Snackbar {
            id: snackBar
        }
    }

}
