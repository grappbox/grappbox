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

    Window {
        id: loginPage

        visible: true

        height: viewLogin.height
        width: viewLogin.width
        flags: Qt.FramelessWindowHint | Qt.WindowStaysOnTopHint | Qt.WindowMinimizeButtonHint | Qt.Window

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
                loginPage.isLoading = false
                loginPage.close()
            }

            onLoginFailed: {
                passwordError.visible = true
                loginPage.isLoading = false
            }

            Component.onCompleted: {
                /*controller.login(loginName.text, loginPassword.text)
                loginPage.isLoading = true*/
            }

            onLogoutSuccess: {
                loginPage.show()
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

        minimumWidth: Units.dp(500)
        minimumHeight: Units.dp(300)

        title: SDataManager.hasProject ? "GrappBox - " + SDataManager.project.name : "GrappBox"
        visible: !loginPage.visible && controller.isLoged

        theme {
            primaryColor: "#c0392b"
            primaryDarkColor: "#3b3b3b"
            accentColor: "#3c3b3b"
            tabHighlightColor: "white"
        }

        property var sectionTitles: [   "Dashboard",
                                        "Calendar",
                                        "Cloud",
                                        "Timeline",
                                        "Bug Tracker",
                                        "Gantt",
                                        "Whiteboard",
                                        "Project Settings" ]

        property var sectionIcon: [     "action/dashboard",
                                        "action/event",
                                        "file/cloud_upload",
                                        "communication/forum",
                                        "action/bug_report",
                                        "action/view_list",
                                        "content/create",
                                        "action/settings" ]

        property string previousSelectedComponent: ""
        property string selectedComponent: ""

        property var arguments
        property string loadPageName

        initialPage:
        TabbedPage {
            id: page

            title: demo.selectedComponent

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
                },

                Action {
                    iconName: "action/work"
                    name: "Change project"

                    onTriggered: {
                        SDataManager.changeProject()
                        demo.selectedComponent = "Dashboard"
                    }
                },

                Action {
                    iconName: "action/power_settings_new"
                    name: "Logout"

                    onTriggered: {
                        controller.logout();
                    }
                }

            ]

            backAction: navDrawer.action

            NavigationDrawer {
                id: navDrawer
                anchors.topMargin: Units.dp(48)
                showing: false
                overlayColor: "transparent"
                dismissOnTap: demo.width < Units.dp(1100)

                Flickable {
                    anchors.fill: parent

                    contentHeight: Math.max(content.implicitHeight, height)

                    Column {
                        id: content
                        anchors.fill: parent

                        Rectangle {

                            anchors.left: parent.left
                            anchors.right: parent.right
                            height: infoMenuColumn.implicitHeight

                            color: "#bdbdbd"

                            IconButton {
                                iconName: "hardware/keyboard_backspace"
                                onClicked: {
                                    navDrawer.close()
                                }
                                anchors.right: parent.right
                                anchors.top: parent.top
                                anchors.margins: Units.dp(20)
                            }

                            Column {

                                id: infoMenuColumn

                                anchors.left: parent.left
                                anchors.right: parent.right
                                anchors.top: parent.top
                                anchors.leftMargin: Units.dp(20)

                                Item {
                                    width: parent.width
                                    height: Units.dp(20)
                                }

                                CircleImageAsync {
                                    height: Units.dp(90)
                                    width: height

                                    function onIdChanged(id) {
                                        if (id === -1)
                                            return
                                        avatarDate = SDataManager.user.avatarDate
                                        avatarId = "user#" + SDataManager.user.id
                                    }

                                    Component.onCompleted: {
                                        SDataManager.user.idChanged.connect(onIdChanged)
                                    }
                                }

                                Item {
                                    width: parent.width
                                    height: Units.dp(20)
                                }

                                Label {
                                    text: SDataManager.user.lastName + " " + SDataManager.user.firstName
                                    style: "body1"
                                }

                                Item {
                                    width: parent.width
                                    height: Units.dp(8)
                                }

                                Label {
                                    text: SDataManager.user.mail
                                    style: "body1"
                                }

                                Item {
                                    width: parent.width
                                    height: Units.dp(8)
                                }

                                Label {
                                    text: SDataManager.hasProject ? SDataManager.project.name : "No project selected"
                                    style: "body2"
                                }

                                Item {
                                    width: parent.width
                                    height: Units.dp(8)
                                }

                                Button {
                                    text: "Change"
                                    visible: SDataManager.hasProject
                                    elevation: 1
                                    onClicked: {
                                        SDataManager.changeProject()
                                        demo.selectedComponent = "Dashboard"
                                    }
                                }

                                Item {
                                    width: parent.width
                                    height: Units.dp(8)
                                }
                            }
                        }

                        Repeater {
                            model: demo.sectionTitles
                            delegate: ListItem.Standard {

                                action: Icon {
                                    anchors.verticalCenter: parent.verticalCenter
                                    name: demo.sectionIcon[index]
                                }

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

                Flickable {
                    id: flickable
                    anchors {
                        left: parent.left
                        right: parent.right
                        top: parent.top
                        bottom: parent.bottom
                        //leftMargin: demo.width < Units.dp(1100) ? Units.dp(0) : navDrawer.width - navDrawer.leftMargin
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
