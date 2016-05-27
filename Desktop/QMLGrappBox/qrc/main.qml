import QtQuick 2.5
import QtQuick.Window 2.2
import QtQuick.Layouts 1.1
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem
import QtQuick.Controls 1.3 as Controls
import GrappBoxController 1.0

Item {
Window {
    id: loginPage

    visible: true

    height: viewLogin.height
    width: viewLogin.width
    flags: Qt.FramelessWindowHint | Qt.WindowStaysOnTopHint

    color: "#3b3b3b"
    onClosing: {
        if (!controller.isLoged)
            demo.close()
    }

    property bool isLoading: false

    LoginController {
        id: controller

        onLoginSuccess: {
            demo.selectedComponent = demo.sectionTitles[0]
            loginPage.close()
        }

        onLoginFailed: {
            passwordError.visible = true
            loginPage.isLoading = false
        }

        Component.onCompleted: {
            controller.login(loginName.text, loginPassword.text)
            loginPage.isLoading = true
        }
    }

    View {
        id: viewLogin
        anchors.centerIn: parent

        width: Units.dp(350)
        height: columnLogin.implicitHeight + Units.dp(32)

        backgroundColor: Theme.primaryDarkColor

        elevation: 1
        radius: Units.dp(2)

        ColumnLayout {
            id: columnLogin

            anchors {
                fill: parent
                topMargin: Units.dp(16)
                bottomMargin: Units.dp(16)
            }

            Image {
                anchors {
                    left: parent.left
                    right: parent.right
                    margins: Units.dp(16)
                }
                fillMode: Image.PreserveAspectFit
                source: "qrc:/Logo/Title.png"
                height: Units.dp(100)
                horizontalAlignment: Qt.AlignHCenter
            }

            Item {
                Layout.fillWidth: true
                Layout.preferredHeight: Units.dp(8)
            }

            Label {
                anchors {
                    left: parent.left
                    right: parent.right
                    margins: Units.dp(16)
                }

                style: "title"
                text: "Welcome to GrappBox. Please login."

                color: Theme.dark.textColor
            }

            Item {
                Layout.fillWidth: true
                Layout.preferredHeight: Units.dp(8)
            }

            Label {
                id: passwordError
                anchors {
                    left: parent.left
                    right: parent.right
                    margins: Units.dp(16)
                }

                visible: false

                style: "body2"
                text: "Your login or your password is invalid."

                color: Theme.primaryColor
            }

            Item {
                Layout.fillWidth: true
                Layout.preferredHeight: Units.dp(8)
            }

            ListItem.Standard {
                action: Icon {
                    anchors.centerIn: parent
                    name: "action/account_circle"
                    color: Theme.dark.iconColor
                }

                content: TextField {
                    id: loginName
                    anchors.centerIn: parent
                    width: parent.width

                    text: "leo.nadeau@epitech.eu"
                    placeholderText: "Login"
                    floatingLabel: true
                    color: Theme.dark.textColor
                    textColor: Theme.dark.textColor
                }
            }

            ListItem.Standard {
                action: Icon {
                    anchors.centerIn: parent
                    name: "action/lock"
                    color: Theme.dark.iconColor
                }

                content: TextField {
                    id: loginPassword
                    anchors.centerIn: parent
                    width: parent.width
                    echoMode: TextInput.Password
                    placeholderText: "Password"
                    floatingLabel: true
                    text: "nadeau_l"
                    color: Theme.dark.textColor
                    textColor: Theme.dark.textColor
                }
            }

            Item {
                Layout.fillWidth: true
                Layout.preferredHeight: Units.dp(8)
            }

            RowLayout {
                Layout.alignment: Qt.AlignRight
                spacing: Units.dp(8)

                anchors {
                    right: parent.right
                    margins: Units.dp(16)
                }

                Button {
                    text: "Quit"
                    textColor: Theme.dark.textColor
                    onClicked: loginPage.close()
                }

                Button {
                    text: "Sign in"
                    textColor: Theme.primaryColor
                    onClicked: {
                        controller.login(loginName.text, loginPassword.text)
                        loginPage.isLoading = true
                    }
                }
            }
        }

        Rectangle {
            anchors.fill: parent
            visible: loginPage.isLoading

            radius: Units.dp(2)
            color: Theme.primaryDarkColor
            opacity: 0.5
        }

        ProgressCircle {
            anchors.centerIn: parent
            visible: loginPage.isLoading
        }
    }
}

ApplicationWindow {
    id: demo

    title: "GrappBox"
    visible: !loginPage.visible && controller.isLoged

    theme {
        primaryColor: "#c0392b"
        primaryDarkColor: "#3b3b3b"
        accentColor: "#3c3b3b"
        tabHighlightColor: "white"
    }

    property var sectionTitles: [ "Dashboard", "Calendar", "Whiteboard", "Timeline", "BugTracker", "Cloud", "Gantt" ]

    property string previousSelectedComponent: ""
    property string selectedComponent: ""//sectionTitles[5]

    initialPage:
    TabbedPage {
        id: page

        title: "GrappBox"

        enabled: !loginPage.visible

        actionBar.maxActionCount: 3

        actions: [

            Action {
                iconName: "action/settings"
                name: "Settings"
                hoverAnimation: true
            },

            Action {
                iconName: "social/notifications"
                name: "Notification"
                hoverAnimation: true
            },


            Action {
                iconName: "action/language"
                name: "Language"
            },

            Action {
                iconName: "action/account_circle"
                name: "Accounts"

                onTriggered: {
                    demo.previousSelectedComponent = demo.selectedComponent
                    demo.selectedComponent = "UserSettings"
                }
            }
        ]

        backAction: navDrawer.action

        NavigationDrawer {
            id: navDrawer

            enabled: page.width < Units.dp(1000)

            Flickable {
                anchors.fill: parent

                contentHeight: Math.max(content.implicitHeight, height)

                Column {
                    id: content
                    anchors.fill: parent

                    ListItem.Subheader {
                       text: "Menu"
                    }

                    Repeater {
                        model: demo.sectionTitles
                        delegate: ListItem.Standard {
                            text: modelData
                            selected: modelData == demo.selectedComponent
                            visible: SDataManager.hasProject || index <= 1
                            onClicked: {
                                demo.previousSelectedComponent = demo.selectedComponent
                                demo.selectedComponent = modelData
                                navDrawer.close()
                            }
                        }
                    }
                }
            }
        }

        Loader {
            id: largeLoader
            anchors.fill: parent
            sourceComponent: tabDelegate

            visible: active
            active: true
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

            Sidebar {
                id: sidebar

                expanded: !navDrawer.enabled

                Column {
                    width: parent.width

                    Repeater {
                        model: demo.sectionTitles
                        delegate: ListItem.Standard {
                            text: modelData
                            selected: modelData == demo.selectedComponent
                            enabled: SDataManager.hasProject || index <= 1
                            onClicked: {
                                demo.previousSelectedComponent = demo.selectedComponent
                                demo.selectedComponent = modelData
                            }
                        }
                    }
                }
            }
            Flickable {
                id: flickable
                anchors {
                    left: sidebar.right
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
                            example.item.finishedLoad()
                        }
                    }
                    function returnPage() {
                        demo.selectedComponent = demo.previousSelectedComponent
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
