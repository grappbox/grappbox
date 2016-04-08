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
    }

    function addDate(date, number)
    {
        var ret = new Date()
        ret.setDate(date.getDate() + number)
        return ret
    }

    // Side of the gantt
    Item {
        id: taskItem
    }

    // Body of the gantt
    MouseArea {
        anchors.fill: parent

        property double lastX: 150000

        onWheel: {
            ganttView.sizeX += wheel.angleDelta.y / 120
            if (ganttView.sizeX < ganttView.minSizeYear)
                ganttView.sizeX = ganttView.minSizeYear
            else if (ganttView.sizeX > 100)
                ganttView.sizeX = 100
        }

        onReleased: {
            lastX = 150000
        }

        onMouseXChanged: {
            console.log("X changed ! : " + mouse.x)
            if (lastX == 150000)
            {
                lastX = mouseX
                return
            }
            ganttView.cursorX += mouseX - lastX
            lastX = mouseX
        }
    }

    GanttView
    {
        id: ganttView
        anchors.fill: parent

        sizeX: 100
        sizeY: 25
        minSizeWeek: 30
        minSizeYear: 3
        rectangleColor: "red"
        cursorY: 0
        cursorX: 0
        numberOfDraw: 100
        Component.onCompleted: {
            console.log("Component is loaded !")
            ganttView.update()
        }
    }
}

