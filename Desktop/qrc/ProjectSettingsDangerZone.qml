import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

Column {
    anchors.margins: Units. dp(16)
    property ProjectSettingsModel projectSettingsModel

    Dialog {
        id: deleteProjectConfirmation
        title: ""
        text: "Enter DELETE in the input and press OK to delete the project"

        TextField {
            id: confirmation
            anchors.left: parent.left
            anchors.right: parent.right
            placeholderText: "Deletion confirmation"
            hasError: text != "" && text != "DELETE"
        }

        hasActions: true

        positiveButtonText: "OK"
        negativeButtonText: "DISCARD"

        onAccepted: {
            confirmation.text = ""
        }

        onRejected: {
            confirmation.text = ""
        }

        Component.onCompleted: {
            positiveButton.enabled = Qt.binding(function() { return confirmation.text == "DELETE" })
        }
    }

    ListItem.Subheader {
        text: "Change secure folder password"
    }

    Separator {}

    TextField {
        id: oldPassword
        anchors.left: parent.left
        anchors.right: parent.right
        placeholderText: "Old password"
        floatingLabel: true
        echoMode: TextInput.Password
    }

    Separator {}

    TextField {
        id: newPassword
        anchors.left: parent.left
        anchors.right: parent.right
        placeholderText: "New password"
        floatingLabel: true
        echoMode: TextInput.Password
        hasError: passwordErrorMessage.visible
    }

    Separator {}

    TextField {
        id: newPasswordConfirm
        anchors.left: parent.left
        anchors.right: parent.right
        placeholderText: "Confirm new password"
        floatingLabel: true
        echoMode: TextInput.Password
        hasError: passwordErrorMessage.visible
    }

    Separator {}

    Item {

        anchors.left: parent.left
        anchors.right: parent.right
        height: Math.max(passwordErrorMessage.height, changePassword.height)

        Label {
            id: passwordErrorMessage
            anchors.left: parent.left
            anchors.verticalCenter: parent.verticalCenter
            text: "The new password and the confirmation doesn't match."
            color: Theme.primaryColor

            visible: newPassword.text != "" && newPasswordConfirm.text != "" && newPassword.text != newPasswordConfirm.text
        }

        Button {
            id: changePassword

            anchors.right: parent.right
            anchors.verticalCenter: parent.verticalCenter

            text: "Change"
            enabled: newPassword.text != "" && newPasswordConfirm.text != "" && oldPassword.text != "" && newPassword.text == newPasswordConfirm.text
            textColor: Theme.primaryColor
        }

    }

    Separator { height: Units. dp(16) }

    ListItem.Subheader {
        text: "Project destruction"
    }

    Separator {}

    Label {
        anchors.left: parent.left
        anchors.right: parent.right
        text: "This function is not yet implemented."//"If you click on the button bellow named \"Destroy the project\" you will have 7 days to retrieve it. After that the project will be destroyed and you will not be able to retrieve it. Use this button only if you are sure of you !"
        style: "body2"
        wrapMode: Text.Wrap
        color: Theme.primaryColor
    }

    Separator {}

    Button {
        anchors.horizontalCenter: parent.horizontalCenter
        text: "Destroy the project"
        backgroundColor: Theme.primaryColor
        textColor: Theme.dark.textColor
        elevation: 1
        enabled: false

        onClicked: deleteProjectConfirmation.show()
    }

    Separator {}

    Label {
        anchors.left: parent.left
        anchors.right: parent.right
        text: "This function is not yet implemented."//"If you click on the button bellow named \"Leave the project\" you will leave the project and not be able to undo this action. An other member of your project will have to invite you if you want to be part of the project again."
        style: "body2"
        wrapMode: Text.Wrap
        color: Theme.primaryColor
    }

    Separator {}

    Button {
        anchors.horizontalCenter: parent.horizontalCenter
        text: "Leave the project"
        backgroundColor: Theme.primaryColor
        textColor: Theme.dark.textColor
        elevation: 1
        enabled: false
    }
}

