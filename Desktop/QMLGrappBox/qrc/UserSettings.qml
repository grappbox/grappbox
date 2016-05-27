import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0

Item {
    id: userSettingsForm
    property UserData user
    property var mouseCursor

    Component.onCompleted: {
        user = SDataManager.user
    }

    Dialog {
        id: alertAccess
        width: Units.dp(300)
        title: "Access error"
        text: "You don't have the access on this directory"
        hasActions: false
        positiveButtonText: "Ok"
    }

    UserModel {
        id: userModel

        onError: {
            alertAccess.title = title
            alertAccess.text = message
            alertAccess.show()
        }

        onUserChangedSuccess: {
            userSettingsForm.parent.info("User correctly modified.")
            userSettingsForm.parent.returnPage()
        }
    }

    Dialog {
        id: datePickerDialog
        hasActions: true
        contentMargins: 0
        floatingActions: true

        DatePicker {
            id: datePicker
            frameVisible: false
            dayAreaBottomMargin : Units.dp(48)
            isLandscape: true
        }

        onAccepted: {
            user.birthday = datePicker.selectedDate
        }
    }

    Flickable {

        id: flickableScroll
        anchors.fill: parent
        contentHeight: Math.max(viewForm.height + Units.dp(64), height)

        View {
            id: viewForm
            anchors.centerIn: parent
            anchors.top: parent.top
            anchors.topMargin: Units.dp(32)

            width: Units.dp(500)
            height: columnUserSettings.implicitHeight + Units.dp(32)

            elevation: 1
            radius: Units.dp(2)

            ColumnLayout {
                id: columnUserSettings

                anchors {
                    fill: parent
                    topMargin: Units.dp(16)
                    bottomMargin: Units.dp(16)
                }

                Label {
                    id: titlePopup

                    anchors {
                        left: parent.left
                        right: parent.right
                        margins: Units.dp(16)
                    }

                    style: "title"
                    text: "Account settings"
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

                    content: RowLayout {
                        anchors.centerIn: parent
                        width: parent.width
                        spacing: Units.dp(8)

                        TextField {
                            id: userFirstName
                            placeholderText: "First name"
                            floatingLabel: true
                            text: user.firstName
                            onTextChanged: {
                                user.firstName = text
                            }
                        }

                        TextField {
                            id: userLastName
                            placeholderText: "Last name"
                            floatingLabel: true
                            text: user.lastName
                            onTextChanged: {
                                user.lastName = text
                            }
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        name: "social/cake"
                    }

                    content: Button {
                        anchors.left: parent.left
                        text: user.birthday.toDateString()
                        elevation: 1

                        onClicked: {
                            datePickerDialog.show()
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        name: "communication/phone"
                    }

                    content: TextField {
                        id: phone
                        anchors.centerIn: parent
                        placeholderText: "Phone"
                        width: parent.width
                        floatingLabel: true
                        text: user.phone
                        onTextChanged: {
                            user.phone = text
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        name: "communication/email"
                    }

                    content: TextField {
                        id: email
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "Email"
                        floatingLabel: true
                        enabled: false
                        text: user.mail
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        name: "social/public"
                    }

                    content: TextField {
                        id: country
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "Country"
                        floatingLabel: true
                        text: user.country
                        onTextChanged: {
                            user.country = text
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        source: Qt.resolvedUrl("qrc:/icons/icons/linkedin-box.svg")
                    }

                    content: TextField {
                        id: linkedIn
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "LinkedIn"
                        floatingLabel: true
                        text: user.linkedin
                        onTextChanged: {
                            user.linkedin = text
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        source: Qt.resolvedUrl("qrc:/icons/icons/twitter-box.svg")
                    }

                    content: TextField {
                        id: twitter
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "Twitter"
                        floatingLabel: true
                        text: user.linkedin
                        onTextChanged: {
                            user.linkedin = text
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        source: Qt.resolvedUrl("qrc:/icons/icons/facebook-box.svg")
                    }

                    content: TextField {
                        id: viadeo
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "Viadeo"
                        floatingLabel: true
                        text: user.linkedin
                        onTextChanged: {
                            user.linkedin = text
                        }
                    }
                }

                Label {
                    id: password

                    anchors {
                        left: parent.left
                        right: parent.right
                        margins: Units.dp(16)
                    }

                    style: "title"
                    text: "Password"
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        name: "action/lock_outline"
                    }

                    content: TextField {
                        id: previousPassword
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "Previous password"
                        floatingLabel: true
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        name: "action/lock"
                    }

                    content: TextField {
                        id: newPassword
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "New password"
                        floatingLabel: true
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units.dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        name: "action/lock"
                    }

                    content: TextField {
                        id: confirmNewPassword
                        anchors.centerIn: parent
                        width: parent.width
                        placeholderText: "New password"
                        floatingLabel: true
                    }
                }

                RowLayout {
                    Layout.alignment: Qt.AlignRight
                    spacing: Units.dp(8)

                    anchors {
                        right: parent.right
                        margins: Units.dp(16)
                    }

                    Button {
                        text: "Save"
                        textColor: Theme.primaryColor
                        onClicked: {
                            userModel.setUserModel(user, "", "")
                        }
                    }
                }
            }
        }

    }

}

