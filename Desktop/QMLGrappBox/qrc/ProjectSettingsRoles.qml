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

    anchors.margins: Units.dp(16)

    property ProjectSettingsModel projectSettingsModel

    property var rolesName: [
        "Team timeline",
        "Customer timeline",
        "Gantt",
        "Whiteboard",
        "BugTracker",
        "Event",
        "Task",
        "Project settings",
        "Cloud"
    ]

    property var rolesValueNew: [0, 0, 0, 0, 0, 0, 0, 0, 0]

    ListItem.Subheader {
        text: "Add a new role"
    }

    Separator {}

    TextField {
        id: roleName

        anchors.left: parent.left
        anchors.right: parent.right

        placeholderText: "Name of the new role"
        floatingLabel: true
    }

    Separator {}

    Grid {
        id: rowCreateRole
        anchors.left: parent.left
        anchors.right: parent.right
        anchors.margins: Units.dp(8)
        spacing: Units.dp(8)
        columns: 3

        Repeater {
            model: rolesName
            delegate: Column {
                width: (parent.width - parent.spacing * 2) / 3

                Label {
                    text: modelData
                    style: "body2"
                }

                Separator {}

                MenuField {
                    anchors.left: parent.left
                    anchors.right: parent.right
                    model: ["No access", "Read access", "All access"]
                    selectedIndex: rolesValueNew[index]

                    onSelectedIndexChanged: rolesValueNew[index] = selectedIndex
                }
            }
        }
    }

    Separator {}

    Button {
        anchors.right: parent.right
        anchors.rightMargin: Units.dp(16)

        text: "Add"

        enabled: roleName.text != ""

        onClicked: {
            projectSettingsModel.addNewRole(roleName.text, rolesValueNew)

            for (var item in rolesValueNew)
                rolesValueNew[item] = 0
            roleName.text = ""
        }
    }

    Separator {}

    ListItem.Subheader {
        text: "Registered roles"
    }

    Repeater {
        model: projectSettingsModel.roles
        delegate: Item {

            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(8)
            anchors.rightMargin: Units.dp(8)
            anchors.topMargin: Units.dp(8)

            height: viewRoles.height + Units.dp(8)

            View {
                id: viewRoles
                anchors.left: parent.left
                anchors.top: parent.top
                anchors.right: parent.right

                property var modelRight

                Component.onCompleted: modelRight = modelData.accessRight

                elevation: 2

                height: buttonView.implicitHeight + Units.dp(8) + rowItem.implicitHeight + Units.dp(16) + label.height

                Label {
                    id: label
                    anchors.horizontalCenter: parent.horizontalCenter
                    anchors.top: parent.top
                    anchors.topMargin: Units.dp(8)
                    text: modelData.name
                    style: "display1"

                }

                Grid {
                    id: rowItem
                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.top: label.bottom
                    anchors.margins: Units.dp(8)
                    spacing: Units.dp(8)
                    columns: 3

                    Repeater {
                        model: rolesName
                        delegate: Column {
                            width: (parent.width - parent.spacing * 2) / 3

                            Label {
                                text: modelData
                                style: "body2"
                            }

                            Separator {}

                            MenuField {
                                anchors.left: parent.left
                                anchors.right: parent.right
                                model: ["No access", "Read access", "All access"]
                                selectedIndex: viewRoles.modelRight[index]

                                onSelectedIndexChanged: {
                                    viewRoles.modelRight[index] = selectedText == "No access" ? 0 : (selectedText == "Read access" ? 1 : 2)
                                }
                            }
                        }
                    }
                }

                RowLayout {
                    id: buttonView
                    anchors.right: parent.right
                    anchors.rightMargin: Units.dp(16)
                    anchors.top: rowItem.bottom
                    anchors.topMargin: Units.dp(8)
                    spacing: Units.dp(8)

                    height: deleteRole.height

                    Button {
                        id: deleteRole
                        text: "DELETE"
                        textColor: Theme.primaryColor

                        onClicked: {
                            projectSettingsModel.deleteRole(modelData.id)
                        }
                    }

                    Button {
                        id: updateRole
                        text: "UPDATE"

                        onClicked: {
                            projectSettingsModel.updateRole(modelData.id, viewRoles.modelRight)
                        }
                    }
                }
            }

            Separator {}

        }
    }
}

