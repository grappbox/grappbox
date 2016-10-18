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

    function finishedLoad()
    {

    }

    Component.onCompleted: {
        user = SDataManager.user
    }

    FileDialog {
        id: importFile
        modality: Qt.WindowModal
        title: "Please choose an image"
        folder: shortcuts.home
        selectExisting: true
        selectMultiple: false
        selectFolder: false
        nameFilters: ["Image files (*.jpeg *.jpg *.png)"]
        selectedNameFilter: "Image files (*.jpeg *.jpg *.png)"

        onAccepted: {
            avatarProject.avatarId = DataImageProvider.loadNewDataImage(fileUrl)
        }
        onRejected: {
            visible = false
        }
        visible: false
    }

    Dialog {
        id: alertAccess
        width: Units. dp(300)
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

    Flickable {

        id: flickableScroll
        anchors.fill: parent
        contentHeight: Math.max(viewForm.height + Units. dp(64), height)

        View {
            id: viewForm
            anchors.centerIn: parent
            anchors.top: parent.top
            anchors.topMargin: Units. dp(32)

            width: Units. dp(500)
            height: columnUserSettings.implicitHeight + Units. dp(32)

            elevation: 1
            radius: Units. dp(2)

            ColumnLayout {
                id: columnUserSettings

                anchors {
                    fill: parent
                    topMargin: Units. dp(16)
                    bottomMargin: Units. dp(16)
                }

                Label {
                    id: titlePopup

                    anchors {
                        left: parent.left
                        right: parent.right
                        margins: Units. dp(16)
                    }

                    style: "title"
                    text: "Account settings"
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        name: "action/account_circle"
                    }

                    content: RowLayout {
                        anchors.centerIn: parent
                        width: parent.width
                        spacing: Units. dp(8)

                        TextField {
                            id: userFirstName
                            placeholderText: "First name"
                            floatingLabel: true
                            text: user.firstName
                            hasError: text == ""
                            onTextChanged: {
                                user.firstName = text
                            }
                        }

                        TextField {
                            id: userLastName
                            placeholderText: "Last name"
                            floatingLabel: true
                            text: user.lastName
                            hasError: text == ""
                            onTextChanged: {
                                user.lastName = text
                            }
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                ListItem.Standard {
                    action: Icon {
                        anchors.centerIn: parent
                        name: "social/cake"
                    }

                    content: Row {
                        id: dateRow
                        height: dayText.height
                        property date birthdayDate: isNaN(user.birthday.getTime()) ? new Date(1970, 0, 1) : user.birthday

                        function updateDate() {
                            if (dayText.hasError || monthText.hasError || yearText.hasError || dayText.text == "" || monthText.text == "" || yearText.text == "")
                                return
                            user.birthday = new Date(parseInt(yearText.text), parseInt(monthText.text) - 1, parseInt(dayText.text))
                            console.log(user.birthday)
                            console.log(parseInt(yearText.text))
                            console.log(parseInt(monthText.text))
                            console.log(parseInt(dayText.text))
                        }

                        Component.onCompleted: {
                            dayText.text = (dateRow.birthdayDate.getDate()).toString()
                            monthText.text = (dateRow.birthdayDate.getMonth()).toString()
                            yearText.text = (dateRow.birthdayDate.getFullYear()).toString()
                        }

                        TextField {
                            id: dayText
                            width: Units. dp(64)

                            inputMethodHints: Qt.ImhDigitsOnly
                            validator: IntValidator{}

                            onTextChanged: {
                                dateRow.updateDate()
                            }

                            Component.onCompleted: {
                                hasError = Qt.binding(function() {
                                    var item = new Date(parseInt(yearText.text), parseInt(monthText.text) - 1, 0).getDate()
                                    if (parseInt(text) > item)
                                        return true
                                    return parseInt(text) === 0
                                })
                            }
                        }

                        Item {
                            width: Units. dp(8)
                        }

                        Label {
                            text: "/"
                            anchors.verticalCenter: parent.verticalCenter
                        }

                        Item {
                            width: Units. dp(8)
                        }

                        TextField {
                            id: monthText

                            width: Units. dp(64)

                            inputMethodHints: Qt.ImhDigitsOnly
                            validator: IntValidator{}

                            onTextChanged: {
                                dateRow.updateDate()
                            }

                            hasError: parseInt(text) > 12 || parseInt(text) === 0
                        }

                        Item {
                            width: Units. dp(8)
                        }

                        Label {
                            text: "/"
                            anchors.verticalCenter: parent.verticalCenter
                        }

                        Item {
                            width: Units. dp(8)
                        }

                        TextField {
                            id: yearText

                            width: Units. dp(64)

                            inputMethodHints: Qt.ImhDigitsOnly
                            validator: IntValidator{}

                            onTextChanged: {
                                dateRow.updateDate()
                            }

                            hasError: parseInt(text) > new Date().getFullYear() || parseInt(text) < 1901
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
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
                    Layout.preferredHeight: Units. dp(8)
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
                        onTextChanged: {
                            user.mail = text
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
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
                    Layout.preferredHeight: Units. dp(8)
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
                    Layout.preferredHeight: Units. dp(8)
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
                        text: user.twitter
                        onTextChanged: {
                            user.twitter = text
                        }
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
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
                        text: user.viadeo
                        onTextChanged: {
                            user.viadeo = text
                        }
                    }
                }
                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
                }

                Label {
                    id: avatarTitle

                    anchors {
                        left: parent.left
                        right: parent.right
                        margins: Units. dp(16)
                    }

                    style: "title"
                    text: "Avatar"
                }

                Row {
                    height: avatarProject.height

                    spacing: Units. dp(16)

                    ImageAsync {
                        id: avatarProject

                        width: Units. dp(128)
                        height: Units. dp(128)

                        function idChanged(id) {
                            if (id >= 0 && avatarId.indexOf("tmp#") == -1)
                            {
                                avatarId = "user#" + id
                                avatarDate = user.id
                            }
                        }

                        Component.onCompleted: {
                            user.id.connect(idChanged)
                        }
                    }

                    Button {
                        text: "Modify avatar"
                        elevation: 1
                        anchors.verticalCenter: parent.verticalCenter

                        onClicked: {
                            importFile.open()
                        }
                    }
                }

                Label {
                    id: password

                    anchors {
                        left: parent.left
                        right: parent.right
                        margins: Units. dp(16)
                    }

                    style: "title"
                    text: "Password"
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
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
                        echoMode: TextInput.Password
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
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
                        echoMode: TextInput.Password
                        hasError: labelConfirmError.visible
                    }
                }

                Item {
                    Layout.fillWidth: true
                    Layout.preferredHeight: Units. dp(8)
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
                        placeholderText: "Confirm password"
                        floatingLabel: true
                        echoMode: TextInput.Password
                        hasError: labelConfirmError.visible
                    }

                    secondaryItem: Label {
                        id: labelConfirmError
                        anchors.verticalCenter: parent.verticalCenter
                        text: "Password doesn't match"
                        color: Theme.primaryColor
                        visible: confirmNewPassword.text !== "" && confirmNewPassword.text !== newPassword.text
                        enabled: visible
                    }
                }

                RowLayout {
                    Layout.alignment: Qt.AlignRight
                    spacing: Units. dp(8)

                    anchors {
                        right: parent.right
                        margins: Units. dp(16)
                    }

                    Button {
                        text: "Save"
                        enabled: email.text != "" && userFirstName.text != "" && userLastName.text != "" && !labelConfirmError.visible
                        textColor: Theme.primaryColor
                        onClicked: {
                            var avatar = "";
                            if (avatarProject.avatarId.indexOf("tmp#") != -1)
                            {
                                avatar = DataImageProvider.get64BasedImage(avatarProject.avatarId)
                                DataImageProvider.replaceImageFromTmp(avatarProject.avatarId, "user#" + user.id)
                            }

                            var oldPasswordVal = ""
                            var newPasswordVal = ""
                            if (confirmNewPassword.text != "")
                            {
                                oldPasswordVal = previousPassword.text
                                newPasswordVal = newPassword.text
                            }
                            dateRow.updateDate()
                            userModel.setUserModel(user, oldPasswordVal, newPasswordVal, avatar)
                        }
                    }
                }
            }
        }

    }

}

