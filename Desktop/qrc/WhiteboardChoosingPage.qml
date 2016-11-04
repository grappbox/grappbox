import QtQuick 2.0
import QtQuick.Layouts 1.1
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem
import QtQuick.Controls 1.3 as Controls
import GrappBoxController 1.0

Item {

    property var cursor
    property WhiteboardModel whiteModel

    Dialog {
        id: newWhiteboardDialog
        title: "Create a new whiteboard"
        hasActions: true

        negativeButtonText: "Cancel"
        positiveButtonText: "Create"

        positiveButton.enabled: name.text != ""

        TextField {
            id: name
            width: parent.width
            placeholderText: "Name of the whiteboard"
        }

        onAccepted: {
            whiteModel.createWhiteboard(name.text)
            name.text = ""
        }

        onRejected: {
            name.text = ""
        }
    }

    Dialog {
        id: confirmDelete
        title: "Are you sure you want to delete ?"
        text: "This action cannot be undone."

        negativeButtonText: "Cancel"
        positiveButtonText: "Delete"

        property int id: -1

        onAccepted: {
            whiteModel.deleteWhiteboard(id)
        }
    }

    Flickable {

        id: dashboardFlick

        anchors.fill: parent

        contentHeight: Math.max(flowProject.height + Units.dp(200), parent.height)

        Label {
            id: welcomeLabel
            text: "Choose a whiteboard to go in or create one"
            style: "title"
            anchors.horizontalCenter: parent.horizontalCenter
            anchors.top: parent.top
            anchors.topMargin: Units. dp(32)
        }

        Button {
            id: createProjectButton
            text: "Create a whiteboard"
            elevation: 1
            backgroundColor: Theme.primaryColor
            anchors.horizontalCenter: parent.horizontalCenter
            anchors.top: welcomeLabel.bottom
            anchors.topMargin: Units. dp(32)

            onClicked: {
                newWhiteboardDialog.open()
            }
        }

        Flow {
            id: flowProject
            anchors.top : createProjectButton.bottom
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.topMargin: Units. dp(32)
            property int rowCount: elements.count === 0 ? 0 : (parent.width - 16) / (elements.itemAt(0).width + spacing)
            property int rowWidth: elements.count === 0 ? parent.width - 16 : rowCount * elements.itemAt(0).width + (rowCount - 1) * spacing
            property int mar: (parent.width - rowWidth) / 2
            spacing: Units. dp(16)
            anchors.leftMargin: mar
            anchors.rightMargin: mar

            Repeater {
                id: elements
                model: whiteModel.whiteboardList
                delegate: Item {
                    width: Units.dp(300)
                    height: title.height + creationDate.height + editDate.height + rowProjectButton.height
                    View {
                        anchors.fill: parent
                        elevation: viewMouseArea.containsMouse ? 2 : 1
                        radius: Units.dp(2)

                        MouseArea {
                            id: viewMouseArea
                            anchors.fill: parent

                            hoverEnabled: true

                            onClicked: {
                                if (mouse.button == Qt.LeftButton)
                                {
                                    whiteModel.openWhiteboard(modelData.id)
                                }
                            }

                            onHoveredChanged: {
                                if (containsMouse)
                                    cursor.cursorShape = Qt.PointingHandCursor
                                else
                                    cursor.cursorShape = Qt.ArrowCursor
                            }

                            Component.onCompleted: {
                                if (containsMouse)
                                    cursor.cursorShape = Qt.PointingHandCursor
                                else
                                    cursor.cursorShape = Qt.ArrowCursor
                            }
                        }

                        View {
                            id: title
                            anchors.left: parent.left
                            anchors.right: parent.right
                            anchors.top: parent.top
                            height: Units.dp(48)
                            elevation: 1

                            Label {
                                anchors.fill: parent
                                style: "title"
                                anchors.margins: Units.dp(8)
                                text: modelData.title
                            }
                        }

                        ListItem.Standard {
                            id: creationDate
                            anchors.top: title.bottom
                            anchors.left: parent.left
                            anchors.right: parent.right

                            action: Icon {
                                anchors.centerIn: parent
                                name: "action/query_builder"
                            }

                            content: Label {
                                anchors.centerIn: parent
                                width: parent.width
                                text: Qt.formatDateTime(modelData.creationDate, "dddd, MMMM dd - hh:mm AP")
                            }
                        }

                        ListItem.Standard {
                            id: editDate
                            anchors.top: creationDate.bottom
                            anchors.left: parent.left
                            anchors.right: parent.right
                            action: Icon {
                                anchors.centerIn: parent
                                name: "content/create"
                            }

                            content: Label {
                                anchors.centerIn: parent
                                width: parent.width
                                text: Qt.formatDateTime(modelData.editDate, "dddd, MMMM dd - hh:mm AP")
                            }
                        }

                        RowLayout {
                            id: rowProjectButton
                            Layout.alignment: Qt.AlignRight
                            spacing: Units. dp(8)
                            anchors.bottom: parent.bottom
                            anchors.right: parent.right
                            anchors.rightMargin: Units. dp(16)

                            height: openProject.height

                            Button {
                                text: "Delete"
                                textColor: Theme.accentColor

                                onClicked: {
                                    confirmDelete.id = modelData.id
                                    confirmDelete.open()
                                }
                            }

                            Button {
                                id: openProject
                                text: "Open"
                                textColor: Theme.primaryColor

                                onClicked: {
                                    whiteModel.openWhiteboard(modelData.id)
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

