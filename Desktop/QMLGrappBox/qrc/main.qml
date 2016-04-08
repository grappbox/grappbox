import QtQuick 2.5
import QtQuick.Window 2.2
import QtQuick.Layouts 1.1
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem
import GrappBoxController 1.0

Item {
Window {
    id: loginPage

    visible: true

    color: "#3b3b3b"
    onClosing: {
        if (!controller.isLoged)
            demo.close()
    }

    property bool isLoading: false

    LoginController {
        id: controller

        onLoginSuccess: {
            demo.selectedComponent = demo.sectionTitles[6]
            loginPage.close()
        }

        onLoginFailed: {
            passwordError.visible = true
            loginPage.isLoading = false
        }
    }

    View {
        anchors.centerIn: parent

        width: Units.dp(350)
        height: columnLogin.implicitHeight + Units.dp(32)


        elevation: 1
        radius: Units.dp(2)

        ColumnLayout {
            id: columnLogin

            anchors {
                fill: parent
                topMargin: Units.dp(16)
                bottomMargin: Units.dp(16)
            }

            Label {
                anchors {
                    left: parent.left
                    right: parent.right
                    margins: Units.dp(16)
                }

                style: "title"
                text: "Please login"
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

                color: Theme.accentColor
            }

            Item {
                Layout.fillWidth: true
                Layout.preferredHeight: Units.dp(8)
            }

            ListItem.Standard {
                action: Icon {
                    anchors.centerIn: parent
                    name: "action/account_circle"
                }

                content: TextField {
                    id: loginName
                    anchors.centerIn: parent
                    width: parent.width

                    text: "leo.nadeau@epitech.eu"
                }
            }

            ListItem.Standard {
                action: Icon {
                    anchors.centerIn: parent
                    name: "action/lock"
                }

                content: TextField {
                    id: loginPassword
                    anchors.centerIn: parent
                    width: parent.width
                    echoMode: TextInput.Password
                    text: "nadeau_l"
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
                    textColor: Theme.accentColor
                    onClicked: loginPage.close()
                }

                Button {
                    text: "Done"
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

    property string selectedComponent: ""//sectionTitles[5]

    initialPage:
        /*
*/
    TabbedPage {
        id: page

        title: "GrappBox"

        enabled: !loginPage.visible

        actionBar.maxActionCount: 2

        actions: [

            Action {
                iconName: "action/settings"
                name: "Settings"
                hoverAnimation: true
            },

            Action {
                iconName: "action/language"
                name: "Language"
            },

            Action {
                iconName: "action/account_circle"
                name: "Accounts"
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
                            onClicked: {
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
                            onClicked: {
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
                            example.item.finishedLoad()
                        }
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
}
}
