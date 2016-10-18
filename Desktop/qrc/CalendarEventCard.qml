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
    id: mainItem
    anchors.left: parent.left
    anchors.right: parent.right
    height: column.implicitHeight + Units.dp(16)

    backgroundColor: mouseArea.containsMouse ? "#f0f0f0" : "#ffffff"

    property EventModelData eventData

    onEventDataChanged: {
        if (eventData !== undefined)
        {
            titleProject.updateText()
        }
    }

    signal clicked()

    MouseArea {
        id: mouseArea
        anchors.fill: parent
        hoverEnabled: true

        onClicked: {
            mainItem.clicked()
        }
    }

    Column {
        id: column
        spacing: Units.dp(2)
        anchors.margins: Units.dp(8)
        anchors.fill: parent

        Label {
            text: eventData === undefined ? "Title" : eventData.title
            anchors.left: parent.left
            style: "body1"
            font.pixelSize: Units.dp(18)
            font.bold: true
        }

        Label {
            id: titleProject
            text: ""
            anchors.left: parent.left
            anchors.right: parent.right
            font.pixelSize: Units.dp(16)
            font.bold: true

            function updateText() {
                if (eventData === undefined)
                    return
                var projects = SDataManager.projectList
                for (var i = 0; i < projects.length; ++i)
                {
                    if (projects[i].id === eventData.projectId)
                    {
                        text = projects[i].name
                        color = "#FC575E"
                        return
                    }
                }
                text = "Personal"
            }

            Component.onCompleted: {

            }
        }

        Label {
            text: eventData === undefined ? "Description" : eventData.description
            anchors.left: parent.left
            anchors.right: parent.right
            font.pixelSize: Units.dp(14)
            font.bold: true
        }

        Label {
            text: eventData === undefined ? "Times" : Qt.formatDateTime(eventData.beginDate, "yyyy-MM-dd hh:mm") + " - " + Qt.formatDateTime(eventData.beginDate, "yyyy-MM-dd hh:mm")
            anchors.right: parent.right
            font.pixelSize: Units.dp(14)
            style: "caption"
        }
    }

    Rectangle {
        anchors.left: parent.left
        anchors.right: parent.right
        anchors.bottom: parent.bottom
        height: Units.dp(1)
        color: "#dddddd"
    }
}

