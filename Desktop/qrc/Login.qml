import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

Item {
    id: loginItem

    property var mouseCursor

    function finishedLoad() {
        console.log("Loaded")
    }

    property bool isLoading: false

    LoginController {
        id: controller

        onLoginSuccess: {
            parent.loadPage("Dashboard")
        }

        onLoginFailed: {
            parent.error("Login", "Unable to connect, please verify your email and your password.")
            loginItem.isLoading = false
        }

        Component.onCompleted: {

        }

        onLogoutSuccess: {
            loginPage.show()
        }
    }

    View {
        id: loginForm
        width: 380
        height: titleBlack.height + 64 + columnFields.height
        anchors.verticalCenter: parent.verticalCenter
        anchors.horizontalCenter: parent.horizontalCenter
        elevation: 1

        opacity: 0
        Component.onCompleted: {
            opacity = 1
        }

        Behavior on opacity {
            NumberAnimation {
                duration: 200
            }
        }

        Rectangle {
            id: titleBlack
            anchors.top: parent.top
            anchors.left: parent.left
            anchors.right: parent.right
            height: 50
            color: "#333"

            Item {
                anchors.margins: 10
                anchors.top: parent.top
                anchors.bottom: parent.bottom
                anchors.horizontalCenter: parent.horizontalCenter

                width: 10 + imageTitle.width + textTitle.width

                Image {
                    id: imageTitle
                    anchors.left: parent.left
                    anchors.top: parent.top
                    anchors.bottom: parent.bottom
                    width: height
                    source: "qrc:/Logo/icons/logo-thick.png"
                    Component.onCompleted: {
                        console.log(height)
                        console.log(width)
                    }
                }

                Label {
                    id: textTitle
                    anchors.top: parent.top
                    anchors.bottom: parent.bottom
                    anchors.left: imageTitle.right
                    anchors.leftMargin: 10
                    text: "Sign in to GrappBox"
                    color: "#fff"
                    font.pixelSize: 18
                    verticalAlignment: Text.AlignVCenter
                    //font.weight: 300
                }
            }
        }

        Column {
            id: columnFields
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.top: titleBlack.bottom
            anchors.topMargin: 32
            anchors.bottomMargin: 32
            anchors.leftMargin: 16
            anchors.rightMargin: 16
            spacing: 32

            TextField {
                id: email
                placeholderText: "Email"
                floatingLabel: true
                anchors.left: parent.left
                anchors.right: parent.right
                height: 45
                enabled: !loginItem.isLoading
                text: "leo.nadeau@epitech.eu"
            }

            TextField {
                id: password
                placeholderText: "Password"
                floatingLabel: true
                echoMode: TextInput.Password
                anchors.left: parent.left
                anchors.right: parent.right
                height: 45
                enabled: !loginItem.isLoading
                text: "test"
            }

            Button {
                id: loginButton
                anchors.right: parent.right
                height: 45
                text: "Login"

                textColor: "#4bf"
                enabled: !loginItem.isLoading

                onClicked: {
                    controller.login(email.text, password.text)
                    loginItem.isLoading = true
                }
            }
        }
    }

    View {
        width: 380
        height: 51

        anchors.top: loginForm.bottom
        anchors.horizontalCenter: parent.horizontalCenter
        anchors.topMargin: 16
        elevation: 1

        Label {
            anchors.fill: parent
            verticalAlignment: Text.AlignVCenter
            horizontalAlignment: Text.AlignHCenter
            text: "<html>New to GrappBox ? <a href=\"http://grappbox.com/register\">Create an account</a></html>"

            onLinkActivated: {
                Qt.openUrlExternally(link)
            }
        }
    }

    Rectangle {
        anchors.fill: parent
        visible: loginItem.isLoading

        radius: Units. dp(2)
        color: Theme.primaryDarkColor
        opacity: 0.5
    }

    ProgressCircle {
        anchors.centerIn: parent
        visible: loginItem.isLoading
    }
}

