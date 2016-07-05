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

    property var purcentWidth: [0.34, 0.33, 0.33]
    property ProjectSettingsModel projectSettingsModel

    Component.onCompleted: {

    }

    ListItem.Subheader {
        text: "Add a new user"
    }

    Separator {}

    Row {
        height: addUser.height
        width: parent.width

        spacing: Units.dp(16)

        TextField {
            id: email

            width: parent.width - addUser.width - parent.spacing

            placeholderText: "E-mail of the new user"
            floatingLabel: true

            Component.onCompleted: {
                hasError = Qt.binding(function() {
                    if (email.text == "")
                        return false
                    var mailValidator = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return !mailValidator.test(email.text)
                })
            }
        }

        Button {
            id: addUser

            enabled: !email.hasError && email.text != ""

            text: "Add user"
            elevation: 1
            anchors.verticalCenter: parent.verticalCenter

            onClicked: {
                projectSettingsModel.addUser(email.text)
            }
        }
    }

    Separator {}

    ListItem.Subheader {
        text: "Registered users"
    }

    Separator {}

    Row {
        id: rowTabHeader
        anchors.left: parent.left
        anchors.right: parent.right
        anchors.leftMargin: Units.dp(8)
        anchors.rightMargin: Units.dp(8)
        height: Math.max(Units.dp(32), implicitHeight)
        spacing: Units.dp(8)

        Repeater {
            model: [
                ["action/label_outline", "Name"],
                ["action/loyalty", "Role"],
                ["social/person", "Remove from project"]
            ]

            delegate: Item {
                width: parent.width * purcentWidth[index]
                height: parent.height

                Icon {
                    id: iconTitle
                    anchors.left: parent.left
                    anchors.verticalCenter: parent.verticalCenter
                    name: modelData[0]
                }

                Label {
                    id: labelTitle
                    anchors.left: iconTitle.right
                    anchors.right: parent.right
                    anchors.leftMargin: Units.dp(8)
                    anchors.verticalCenter: parent.verticalCenter
                    text: modelData[1]
                    style: "body2"
                    wrapMode: Text.Wrap
                }
            }
        }
    }

    Rectangle {
        height: Units.dp(2)
        color: "#dddddd"
        anchors.left: parent.left
        anchors.right: parent.right
    }

    Repeater {
        model: projectSettingsModel.project.users
        delegate: Item {
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(8)
            anchors.rightMargin: Units.dp(8)
            height: Math.max(Units.dp(24), rowItem.implicitHeight + Units.dp(16))

            Row {
                id: rowItem
                anchors.left: parent.left
                anchors.right: parent.right
                anchors.leftMargin: Units.dp(8)
                anchors.rightMargin: Units.dp(8)
                anchors.bottom: parent.bottom
                height: parent.height

                spacing: Units.dp(8)

                Label {
                    id: nameUser
                    anchors.margins: Units.dp(8)
                    width: parent.width * purcentWidth[0]
                    anchors.verticalCenter: parent.verticalCenter
                    text: modelData.firstName + " " + modelData.lastName
                    style: "body1"
                    wrapMode: Text.Wrap
                }

                MenuField {
                    id: roleUser
                    model: []

                    property var completeModel: []

                    width: parent.width * purcentWidth[1]

                    onItemSelected: {
                        if (completeModel[selectedIndex].id !== modelData.roleId) {
                            console.log("Change user !")
                            projectSettingsModel.changeRoleUser(modelData.id, completeModel[selectedIndex].id, modelData.roleId === -1 ? 0 : modelData.roleId)
                        }
                    }

                    function hasRoleChanged(idRole) {
                        var i = 0
                        for (var item in projectSettingsModel.roles)
                        {
                            if (projectSettingsModel.roles[item].id === modelData.roleId)
                            {
                                console.log("Change role for ", modelData.firstName, " to ", projectSettingsModel.roles[item].name)
                                selectedIndex = i
                                break
                            }
                            i++
                        }
                    }

                    Component.onCompleted: {
                        modelData.roleIdChanged.connect(hasRoleChanged)
                        completeModel = Qt.binding(function() {
                            var ret = []
                            for (var item in projectSettingsModel.roles)
                            {
                                ret.push(projectSettingsModel.roles[item])
                            }
                            return ret
                        })
                        model = Qt.binding(function() {
                            var ret = []
                            for (var item in completeModel)
                            {
                                ret.push(completeModel[item].name)
                            }
                            return ret
                        })
                    }
                }

                Button {
                    id: deleteUser
                    anchors.margins: Units.dp(8)
                    width: parent.width * purcentWidth[2]
                    anchors.verticalCenter: parent.verticalCenter
                    text: "Remove"

                    onClicked: {
                        projectSettingsModel.deleteUser(modelData.id)
                    }
                }
            }

            Rectangle {
                height: Units.dp(1)
                color: "#dddddd"
                anchors.left: parent.left
                anchors.right: parent.right
                anchors.bottom: parent.bottom
            }
        }
    }
}

