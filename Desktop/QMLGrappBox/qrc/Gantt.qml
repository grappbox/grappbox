import QtQuick 2.0
import QtQuick.Layouts 1.1
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem
import QtQuick.Controls 1.3 as Controls
import GrappBoxController 1.0

Item {
    id: gantt

    property int heightOfElement: 24

    property var currentDate: new Date()

    function finishedLoad()
    {
        console.log(Qt.formatDate(gantt.addDate(currentDate, 1), "dd.MM"))
    }

    function addDate(date, number)
    {
        var ret = new Date()
        ret.setDate(date.getDate() + number)
        return ret
    }

    // Top of the gantt
    // Jalon drawer
    Item {
        id: jalonItem
    }
    // Date drawer
    View {
        id: dateItem

        anchors {
            left: parent.left
            right: parent.right
            top: parent.top
        }

        MouseArea {
            id: dateMouseArea
            anchors.fill: parent
        }

        Item {
            RowLayout {
                id: dateContain
                anchors.fill: parent
                Repeater {
                    model: 30
                    delegate: Label {
                        text: Qt.formatDate(gantt.addDate(currentDate, index), "dd.MM")

                    }
                }
            }
        }
    }
    // Side of the gantt
    Item {
        id: taskItem
    }
    // Body of the gantt
    Item {
        id: bodyItem
    }
}

