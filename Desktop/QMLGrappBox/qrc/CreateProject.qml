import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

View {

    id: projectView

    anchors.centerIn: parent

    property DashboardModel model

    ProjectData {
        id: createProjectData

        name: ""
        description: ""
        mail: ""
        facebook: ""
        twitter: ""
        company: ""
        phone: ""
    }

    onVisibleChanged: {
        createProjectData.name = ""
        createProjectData.description = ""
        createProjectData.mail = ""
        createProjectData.facebook = ""
        createProjectData.twitter = ""
        createProjectData.company = ""
        createProjectData.phone = ""
        name.text = ""
        company.text = ""
        phone.text = ""
        facebook.text = ""
        twitter.text = ""
        email.text = ""
        newPassword.text = ""
        newPasswordConfirm.text = ""
        avatarProject.source = Qt.resolvedUrl("qrc:/icons/icons/default-avatar.min.png")
    }

    elevation: 1

    width: Units.dp(500)
    height: mainColumn.implicitHeight + Units.dp(32)

    Column {

        id: mainColumn

        anchors.margins: Units.dp(16)
        anchors.fill: parent

        Label {
            text: "Create a new project"
            style: "title"
        }

        Separator {}

        TextField {
            id: name
            placeholderText: "Name"
            floatingLabel: true
            anchors.left: parent.left
            anchors.right: parent.right
            hasError: text == ""
        }

        Separator {}

        TextArea {
            id: description
            placeHolderText: "Description"
            height: Units.dp(86)
            anchors.left: parent.left
            anchors.right: parent.right
        }

        Separator {}

        Row {
            height: avatarProject.height

            spacing: Units.dp(16)

            Image {
                id: avatarProject

                source: Qt.resolvedUrl("qrc:/icons/icons/default-avatar.min.png")
                width: Units.dp(128)
                height: Units.dp(128)
            }

            Button {
                text: "Modify avatar"
                elevation: 1
                anchors.verticalCenter: parent.verticalCenter
            }
        }

        Separator {}

        TextField {
            id: company
            placeholderText: "Company"
            floatingLabel: true
            anchors.left: parent.left
            anchors.right: parent.right
        }

        Separator {}

        TextField {
            id: email
            placeholderText: "E-Mail"
            floatingLabel: true
            anchors.left: parent.left
            anchors.right: parent.right

            Component.onCompleted: {
                if (email.text == "")
                    return false
                hasError = Qt.binding(function() {
                    var mailValidator = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return !mailValidator.test(email.text)
                })
            }
        }

        Separator {}

        TextField {
            id: phone
            placeholderText: "Phone"
            floatingLabel: true
            anchors.left: parent.left
            anchors.right: parent.right

            Component.onCompleted: {
                if (phone.text == "")
                    return false
                hasError = Qt.binding(function() {
                    var phoneValidator = /^(\+(([0-9]){1,2})[-.])?((((([0-9]){2,3})[-.]){1,2}([0-9]{4,10}))|([0-9]{10}))$/
                    return !phoneValidator.test(phone.text) && phone.text != ""
                })
            }
        }

        Separator {}

        TextField {
            id: facebook
            placeholderText: "Facebook"
            floatingLabel: true
            anchors.left: parent.left
            anchors.right: parent.right

            Component.onCompleted: {
                hasError = Qt.binding(function() {
                    if (facebook.text == "")
                        return false
                    var phoneValidator = /https?\:\/\/(?:www\.)?facebook\.com\/(\d+|[A-Za-z0-9\.]+)\/?/
                    return !phoneValidator.test(facebook.text) && facebook.text != ""
                })
            }
        }

        Separator {}

        TextField {
            id: twitter
            placeholderText: "Twitter"
            floatingLabel: true
            anchors.left: parent.left
            anchors.right: parent.right

            Component.onCompleted: {
                hasError = Qt.binding(function() {
                    if  (twitter.text == "")
                        return false
                    var phoneValidator = /(?:http:\/\/)?(?:www\.)?twitter\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-]*)/
                    return !phoneValidator.test(twitter.text) && twitter.text != ""
                })
            }
        }

        Separator {}

        TextField {
            id: newPassword
            anchors.left: parent.left
            anchors.right: parent.right
            placeholderText: "Secured password"
            floatingLabel: true
            echoMode: TextInput.Password
            hasError: passwordErrorMessage.visible
        }

        Separator {}

        TextField {
            id: newPasswordConfirm
            anchors.left: parent.left
            anchors.right: parent.right
            placeholderText: "Confirm secured password"
            floatingLabel: true
            echoMode: TextInput.Password
            hasError: passwordErrorMessage.visible
        }

        Separator {}

        Label {
            id: passwordErrorMessage
            anchors.left: parent.left
            anchors.right: parent.right
            text: "The new password and the confirmation doesn't match."
            color: Theme.primaryColor

            visible: newPassword.text != "" && newPasswordConfirm.text != "" && newPassword.text != newPasswordConfirm.text
        }

        Separator {}

        RowLayout {
            Layout.alignment: Qt.AlignRight
            spacing: Units.dp(8)

            anchors {
                right: parent.right
                margins: Units.dp(16)
            }

            Button {
                text: "Cancel"

                onClicked: {
                    projectView.visible = false
                }
            }

            Button {
                text: "Save"

                property bool formError: name.hasError || email.hasError || phone.hasError || twitter.hasError || facebook.hasError || newPassword.text == "" || newPassword.text != newPasswordConfirm.text

                textColor: Theme.primaryColor

                enabled: !formError

                onClicked: {
                    createProjectData.name = name.text
                    createProjectData.description = description.text
                    createProjectData.company = company.text
                    createProjectData.mail = email.text
                    createProjectData.facebook = facebook.text
                    createProjectData.twitter = twitter.text
                    createProjectData.phone = phone.text
                    model.addANewProject(createProjectData, newPassword.text)
                    projectView.visible = false
                }
            }
        }
    }
}

