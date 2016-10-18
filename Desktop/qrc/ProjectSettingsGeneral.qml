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

    TextField {
        id: name
        placeholderText: "Name"
        floatingLabel: true
        anchors.left: parent.left
        anchors.right: parent.right
        text: projectSettingsModel.project.name
        hasError: text == ""
    }

    Separator {}

    TextArea {
        id: description
        placeHolderText: "Description"
        height: Units. dp(86)
        anchors.left: parent.left
        anchors.right: parent.right
        text: projectSettingsModel.project.description
    }

    Separator {}

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
                    avatarId = "project#" + id
                    avatarDate = projectSettingsModel.project.avatarDate
                }
            }

            Component.onCompleted: {
                projectSettingsModel.idProjectChanged.connect(idChanged)
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

    Separator {}

    TextField {
        id: company
        placeholderText: "Company"
        floatingLabel: true
        anchors.left: parent.left
        anchors.right: parent.right
        text: projectSettingsModel.project.company
    }

    Separator {}

    TextField {
        id: email
        placeholderText: "E-Mail"
        floatingLabel: true
        anchors.left: parent.left
        anchors.right: parent.right
        text: projectSettingsModel.project.mail

        Component.onCompleted: {
            hasError = Qt.binding(function() {
                var mailValidator = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return email.text != "" && !mailValidator.test(email.text)
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
        text: projectSettingsModel.project.phone

        Component.onCompleted: {
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
        text: projectSettingsModel.project.facebook

        Component.onCompleted: {
            hasError = Qt.binding(function() {
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
        text: projectSettingsModel.project.twitter

        Component.onCompleted: {
            hasError = Qt.binding(function() {
                var phoneValidator = /(?:http:\/\/)?(?:www\.)?twitter\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-]*)/
                return !phoneValidator.test(twitter.text) && twitter.text != ""
            })
        }
    }

    Separator {}

    RowLayout {
        Layout.alignment: Qt.AlignRight
        spacing: Units. dp(8)

        anchors {
            right: parent.right
            margins: Units. dp(16)
        }

        Button {
            text: "Save"

            property bool formError: name.hasError || email.hasError || phone.hasError || twitter.hasError || facebook.hasError

            textColor: formError ? Theme.primaryColor : Theme.accentColor

            enabled: !formError

            onClicked: {
                var avatar = "";
                if (avatarProject.avatarId.indexOf("tmp#") != -1)
                {
                    avatar = DataImageProvider.get64BasedImage(avatarProject.avatarId)
                    DataImageProvider.replaceImageFromTmp(avatarProject.avatarId, "project#" + projectSettingsModel.idProject)
                }

                projectSettingsModel.modifyInformation(name.text, description.text, company.text, email.text, phone.text, facebook.text, twitter.text, avatar)
            }
        }
    }
}

