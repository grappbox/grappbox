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

    ListItem.Subheader {
        text: "Add a new customer access"
    }

    Separator {}

    Row {
        height: addUser.height
        width: parent.width

        spacing: Units.dp(16)

        TextField {
            id: customerName

            width: parent.width - addUser.width - parent.spacing

            placeholderText: "Name for the new customer access"
            floatingLabel: true
        }

        Button {
            id: addUser

            enabled: customerName.text !== ""

            text: "Add access"
            elevation: 1
            anchors.verticalCenter: parent.verticalCenter

            onClicked: {
                projectSettingsModel.addCustomerAccess(customerName.text)
                customerName.text = ""
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
                ["action/loyalty", "Token"],
                ["social/person", "Delete"]
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
        model: projectSettingsModel.customersAccess
        delegate: Item {
            anchors.left: parent ? parent.left : undefined
            anchors.right: parent ? parent.right : undefined
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
                    text: modelData.name
                    style: "body1"
                    wrapMode: Text.Wrap
                }

                Label {
                    id: tokenUser
                    anchors.margins: Units.dp(8)
                    width: parent.width * purcentWidth[0]
                    anchors.verticalCenter: parent.verticalCenter
                    text: modelData.token
                    style: "body1"
                    wrapMode: Text.Wrap
                }

                Button {
                    id: deleteUser
                    anchors.margins: Units.dp(8)
                    width: parent.width * purcentWidth[2]
                    anchors.verticalCenter: parent.verticalCenter
                    text: "Delete"

                    onClicked: {
                        projectSettingsModel.removeCustomerAccess(modelData.id)
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

