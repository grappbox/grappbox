import QtQuick 2.5
import QtQuick.Window 2.2
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.0
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem
import GrappBoxController 1.0

Item {
    id: cloudPage

    property var mouseCursor

    Dialog {
        id: getPasswordDownloadFile
        width: Units. dp(300)
        title: "Need password"

        property bool isDirectory: false
        property bool isSafe: false

        states: [
            State {
                name: "DELETE"
            },
            State {
                name: "DOWNLOAD"
            },
            State {
                name: "UPLOAD"
            },
            State {
                name: "ACCESS"
            }
        ]

        property url pathFileUpload
        property string fileName: "<UNKNOWN_FILE>"

        text: "Please enter the password for the " + (isDirectory ? "directory" : "file") + " : " + fileName

        TextField {
            id: filePassword
            text: ""
            echoMode: TextInput.Password
        }

        hasActions: true

        positiveButtonText: "Validate"
        negativeButtonText: "Cancel"

        onAccepted: {
            if (state == "DELETE")
                controller.deleteFile(fileName, filePassword.text)
            if (state == "DOWNLOAD")
                controller.downloadFile(fileName, filePassword.text)
            if (state == "UPLOAD")
            {
                controller.sendFile(pathFileUpload, filePassword.text)
            }
            if (state == "ACCESS")
                controller.enterDirectory(fileName, filePassword.text)
            filePassword.text = ""
        }
    }

    Dialog {
        id: alertAccess
        width: Units. dp(300)
        title: "Access error"
        text: "You don't have the access on this directory"
        hasActions: false
        positiveButtonText: "Ok"
    }

    Dialog {
        id: directoryName
        width: Units. dp(300)
        title: "New directory"
        text: "Please enter the new directory name"
        hasActions: true

        TextField {
            id: directoryNameText
            width: parent.width
            placeholderText: "New directory"
        }

        onAccepted: {
            controller.createDirectory(directoryNameText.text)
        }

        positiveButtonText: "Create"
        negativeButtonText: "Cancel"
    }

    FileDialog {
        id: importFile
        modality: Qt.WindowModal
        title: "Please choose a file to upload"
        folder: shortcuts.home
        selectExisting: true
        selectMultiple: true
        selectFolder: false
        nameFilters: ["All files (*)"]
        selectedNameFilter: "All files (*)"

        onAccepted: {
            if (!selectMultiple)
            {
                getPasswordDownloadFile.pathFileUpload = fileUrls[0]
                getPasswordDownloadFile.fileName = fileUrls[0]
                getPasswordDownloadFile.state = "UPLOAD"
                getPasswordDownloadFile.isDirectory = false
                getPasswordDownloadFile.isSafe = false
                getPasswordDownloadFile.open()
            }
            else
                controller.sendFiles(fileUrls)
        }
        onRejected: {
            visible = false
        }
        visible: false
    }

    DropArea {
        id: drop

        anchors.fill: parent.fill

        onEntered: {
            console.log("Enter")
            drag.accept(Qt.CopyAction)
        }

        onDropped: {
            console.log(drop.urls);
        }
    }

    CloudController {
        id: controller

        onDirectoryFailedLoad: {
            alertAccess.show()
        }
    }

    property bool isList: false
    property alias currentPath: controller.path
    property var pathList: (currentPath === "/") ? [""] : currentPath.split("/")
    property FileData selectedFile: null

    property alias loading: controller.isLoading

    function finishedLoad()
    {
        controller.loadDirectory()
    }

    Rectangle {
        id: toolBarHeight

        anchors {
            left: parent.left
            right: parent.right
            top: parent.top
        }
        height: 42

        color: Theme.primaryDarkColor
        Row {
            anchors.fill: parent
            //anchors.right: parent.left
            anchors.leftMargin: Units. dp(8)
            anchors.rightMargin: Units. dp(8)
            spacing: 8

            Row {
                spacing: 8

                IconButton {
                    iconName: "navigation/arrow_back"
                    hoverAnimation: true
                    height: toolBarHeight.height
                    color: Theme.dark.iconColor
                    onClicked: controller.goBack()
                }

                IconButton {
                    iconName: "file/create_new_folder"
                    hoverAnimation: true
                    height: toolBarHeight.height
                    color: Theme.dark.iconColor
                    onClicked: directoryName.show()
                }

                IconButton {
                    iconName: "file/file_upload"
                    hoverAnimation: true
                    height: toolBarHeight.height
                    color: Theme.dark.iconColor
                    onClicked: {
                        importFile.selectMultiple = true
                        importFile.open()
                    }
                }

                IconButton {
                    iconName: "action/lock_outline"
                    hoverAnimation: true
                    height: toolBarHeight.height
                    color: Theme.dark.iconColor
                    onClicked: {
                        importFile.selectMultiple = false
                        importFile.open()
                    }
                }
            }

            Row {
                spacing: 4
                Repeater {
                    model: pathList
                    Button {
                        id: buttonPath
                        text: (modelData === "" ? "My cloud" : modelData) + " >"
                        textColor: Theme.dark.textColor
                        onClicked: {
                            controller.goToDirectoryIndex(index)
                        }
                    }
                }
            }
        }
        /*IconButton {
            id: typeViewIcon

            anchors.right: parent.right
            anchors.rightMargin: 8
            iconName: !cloudPage.isList ? "action/view_module" : "action/view_list"
            height: toolBarHeight.height
            color: Theme.dark.iconColor
            onClicked: cloudPage.isList = !cloudPage.isList
        }*/
    }


    Item {
        anchors {
            left: parent.left
            right: parent.right
            bottom: parent.bottom
            top: toolBarHeight.bottom
        }

        Flickable {
            id: bodyCloudList
            anchors.fill: parent

            visible: isList

            clip: true
            contentHeight: Math.max(columnDownload.height, height)

            ColumnLayout {
                id: columnDownload
                spacing: 16

                width: parent.width


                ListItem.Subheader {
                    text: "Directories"
                }

                Repeater {
                    model: controller.directories
                    delegate:
                        ListItem.Standard {
                        text: modelData.fileName
                        valueText: "1h ago"
                        action: Icon {
                            anchors.centerIn: parent
                            name: "file/folder"
                            size: Units. dp(32)
                        }
                        onClicked: {
                            if (selectedFile === modelData)
                            {
                                if (controller.path == "/" && selectedFile.fileName == "Safe")
                                {
                                    getPasswordDownloadFile.state = "ACCESS"
                                    getPasswordDownloadFile.isDirectory = true
                                    getPasswordDownloadFile.isSafe = true
                                    getPasswordDownloadFile.fileName = selectedFile.fileName
                                    getPasswordDownloadFile.open()
                                }
                                else
                                    controller.enterDirectory(selectedFile.fileName)
                            }
                            else
                                selectedFile = modelData
                        }

                    }
                }

                ListItem.Subheader {
                    text: "Files"
                }

                Repeater {
                    model: controller.files
                    delegate:
                        ListItem.Standard {
                        text: modelData.fileName
                        valueText: "30min ago"
                        action: Icon {
                            anchors.centerIn: parent
                            name: "editor/insert_drive_file"
                            size: Units. dp(32)
                        }
                        onClicked: {
                            selectedFile = modelData
                        }
                    }
                }
            }
        }

        Scrollbar {
            flickableItem: bodyCloudList
        }

        Flickable {
            id: bodyCloudFlow
            anchors.fill: parent

            anchors.rightMargin: 8
            anchors.leftMargin: 8
            anchors.topMargin: 16

            visible: !isList

            clip: true
            contentHeight: Math.max(flowDownload.height, height)

            Flow {
                id: flowDownload
                spacing: 16
                width: parent.width


                Repeater {
                    model: controller.directories
                    delegate:
                        IconTextButton {
                            height: 40
                            width: 160
                            text: modelData.fileName
                            iconName: "file/folder"
                            iconColor: Theme.light.iconColor
                            textColor: Theme.primaryDarkColor

                            selected: cloudPage.selectedFile === modelData.fileName

                            onClicked: {
                                selectedFile = modelData
                            }

                            onRightClicked: {
                                selectedFile = modelData
                                rightClickDirectory.open()
                            }

                            onDoubleClicked: {
                                if (controller.path == "/" && modelData.fileName == "Safe")
                                {
                                    console.log("SAFE")
                                    getPasswordDownloadFile.state = "ACCESS"
                                    getPasswordDownloadFile.isDirectory = true
                                    getPasswordDownloadFile.isSafe = true
                                    getPasswordDownloadFile.fileName = modelData.fileName
                                    getPasswordDownloadFile.open()
                                }
                                else
                                    controller.enterDirectory(modelData.fileName)
                            }
                        }
                }

                Repeater {
                    model: controller.files
                    delegate:
                        IconTextButton {
                            id: fileButton
                            height: 40
                            width: 160
                            text: modelData.fileName
                            iconName: "editor/insert_drive_file"
                            iconColor: Theme.light.iconColor
                            textColor: Theme.primaryDarkColor

                            selected: cloudPage.selectedFile === modelData.fileName

                            onClicked: {
                                onClicked: selectedFile = modelData
                            }

                            onRightClicked: {
                                selectedFile = modelData
                                rightClickFile.open()
                            }
                        }
                }
            }
        }

        Scrollbar {
            flickableItem: bodyCloudFlow
        }
    }

    ProgressCircle {
        anchors.centerIn: parent

        visible: cloudPage.loading
    }

    // Download / Upload view
    View {
        id: downloadItem
        visible: true

        height: isOpen ? 332 : headerDownload.height
        width: 350

        radius: 5
        elevation: 1

        property bool isOpen: true

        Behavior on height {
            NumberAnimation { duration: 200 }
        }

        anchors {
            right: parent.right
            bottom: parent.bottom
        }

        Rectangle {
            id: headerDownload
            color: Theme.primaryDarkColor
            anchors {
                left: parent.left
                right: parent.right
                top: parent.top
            }
            height: 32

            IconButton {

                anchors.right: parent.right
                anchors.rightMargin: Units. dp(16)
                anchors.verticalCenter: parent.verticalCenter

                id: open
                iconName: downloadItem.isOpen ? "navigation/arrow_drop_up" : "navigation/arrow_drop_down"

                onClicked: {
                    downloadItem.isOpen = !downloadItem.isOpen
                }
            }
        }

        Flickable {
            id: downloadPage

            anchors.left: parent.left
            anchors.right: parent.right
            anchors.top: headerDownload.bottom
            anchors.bottom: parent.bottom

            clip: true
            contentHeight: Math.max(layoutDownloadPage.height, height)

            Column {

                id: layoutDownloadPage
                anchors.left: parent.left
                anchors.right: parent.right

                Repeater {
                    model: controller.uploadingFiles
                    delegate: ListItem.Standard {
                        id: transitDrawUp
                        text: modelData.fileName

                        anchors.right: parent.right
                        anchors.left: parent.left

                        textColor: Theme.light.textColor

                        secondaryItem:
                            ProgressCircle {
                                indeterminate: modelData.isWaiting
                                minimumValue: 0
                                maximumValue: 100
                                value: modelData.progress
                                width: transitDrawUp.height
                                height: transitDrawUp.height

                                Label {
                                    visible: !modelData.isWaiting
                                    anchors.centerIn: parent
                                    text: Math.round(modelData.progress) + "%"
                                    color: Theme.light.textColor
                                }
                            }

                        action: Icon {
                            anchors.centerIn: parent
                            name: "file/file_upload"
                            size: Units. dp(32)
                            color: Theme.light.iconColor
                        }
                    }
                }

                Repeater {
                    model: controller.downloadingFiles
                    delegate: ListItem.Standard {
                        id: transitDrawDow
                        text: modelData.fileName

                        anchors.right: parent.right
                        anchors.left: parent.left

                        textColor: Theme.light.textColor

                        secondaryItem: ProgressCircle {
                            indeterminate: modelData.isWaiting
                            minimumValue: 0
                            maximumValue: 100
                            value: modelData.progress
                            width: transitDrawDow.height
                            height: transitDrawDow.height

                            Label {
                                visible: !modelData.isWaiting
                                anchors.centerIn: parent
                                text: Math.round(modelData.progress) + "%"
                                color: Theme.light.textColor
                            }
                        }

                        onClicked: {
                            controller.openFile(modelData.url())
                        }

                        action: Icon {
                            anchors.centerIn: parent
                            name: "file/file_download"
                            size: Units. dp(32)
                            color: Theme.light.iconColor
                        }
                    }
                }
            }
        }

        Scrollbar {
            flickableItem: downloadPage
        }
    }

    BottomActionSheet {
        id: rightClickFile

        title: "Action"

        actions: [
            Action {
                iconName: "file/file_download"
                name: "Download"
                onTriggered: {
                    if (selectedFile.isProtected)
                    {
                        getPasswordDownloadFile.fileName = selectedFile.fileName
                        getPasswordDownloadFile.state = "DOWNLOAD"
                        getPasswordDownloadFile.isDirectory = false
                        getPasswordDownloadFile.isSafe = false
                        getPasswordDownloadFile.open()
                    }
                    else
                        controller.downloadFile(selectedFile.fileName)
                }
            },
            Action {
                iconName: "action/delete"
                name: "Delete"
                onTriggered: {
                    if (selectedFile.isProtected)
                    {
                        getPasswordDownloadFile.fileName = selectedFile.fileName
                        getPasswordDownloadFile.state = "DELETE"
                        getPasswordDownloadFile.isDirectory = false
                        getPasswordDownloadFile.isSafe = false
                        getPasswordDownloadFile.open()
                    }
                    else
                        controller.deleteFile(selectedFile.fileName)
                }
            }
        ]
    }

    BottomActionSheet {
        id: rightClickDirectory

        title: "Action"

        actions: [
            Action {
                iconName: "action/delete"
                name: "Delete"
                onTriggered: {
                    controller.deleteFile(selectedFile.fileName)
                }
            }
        ]
    }
}

