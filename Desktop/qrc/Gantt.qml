import QtQuick 2.0
import QtQuick.Layouts 1.1
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem
import QtQuick.Controls 1.3 as Controls
import GrappBoxController 1.0

Item {
    id: gantt

    property var mouseCursor
    property int heightOfElement: 24

    property var currentDate: new Date()

    function finishedLoad()
    {
        ganttModel.loadTasks()
    }

    function addDate(date, number)
    {
        var ret = new Date()
        ret.setDate(date.getDate() + number)
        return ret
    }

    GanttModel {
        id: ganttModel

        Component.onCompleted: {
            ganttModel.loadTasks()
            ganttModel.loadTaskTag()
            SDataManager.updateCurrentProject()
        }

        onTasksChanged: {
            ganttView.setTask(tasks)
        }
    }

    GanttView {
        id: ganttView
        anchors.fill: parent

        sizeX: 100
        sizeY: 40
        sizeYTop: 25
        minSizeWeek: 30
        minSizeYear: 3
        rectangleColor: "#2980b9"
        cursorY: 0
        cursorX: 0
        spaceTask: 3
        spaceCutArrow: 10
        sizeTaskBar: 200
        numberOfDraw: 100

        Component.onCompleted: {
            ganttView.update()
        }
    }

    MouseArea {
        anchors.fill: parent

        property double lastX: 150000
        property bool mouseLeftClicked: false

        cursorShape: Qt.ClosedHandCursor

        hoverEnabled: true
        onWheel: {
            ganttView.sizeX += wheel.angleDelta.y / 120
            if (ganttView.sizeX < ganttView.minSizeYear)
                ganttView.sizeX = ganttView.minSizeYear
            else if (ganttView.sizeX > 100)
                ganttView.sizeX = 100
        }

        onPressed: {
            mouseLeftClicked = true
            ganttView.onClic(Qt.point(mouseX, mouseY))
        }

        onReleased: {
            mouseLeftClicked = false
            ganttView.onRelease(Qt.point(mouseX, mouseY))
        }

        onDoubleClicked: {
            ganttView.onDoubleClic(Qt.point(mouseX, mouseY))
        }

        onMouseXChanged: {
            mouseCursor.cursorShape = ganttView.refreshTypeAction(Qt.point(mouseX, mouseY), pressedButtons & Qt.LeftButton)
            if (form.visible)
                mouseCursor.cursorShape = Qt.ArrowCursor
            if (pressedButtons & Qt.LeftButton)
                ganttView.onMove(Qt.point(mouseX, mouseY))
        }

        onMouseYChanged: {
            mouseCursor.cursorShape = ganttView.refreshTypeAction(Qt.point(mouseX, mouseY), pressedButtons & Qt.LeftButton)
            if (form.visible)
                mouseCursor.cursorShape = Qt.ArrowCursor
            if (pressedButtons & Qt.LeftButton)
                ganttView.onMove(Qt.point(mouseX, mouseY))
        }
    }

    ActionButton {
        anchors {
            right: parent.right
            bottom: parent.bottom
            margins: Units. dp(32)
        }

        iconName: "content/add"

        onClicked: {
            taskFormDialog.show()
        }
    }


    /*TaskForm {
        id: form
        anchors.fill: parent
        modelTaskName: ganttModel.taskName
        modelTaskTag: ganttModel.taskTags

        onUpdateTaskTag: {
            ganttModel.loadTaskTag()
        }

        onModifyTask: {
            // Here modify the task
        }

        onAddTask: {
            // Here add the task
        }

        onAddTag: {
            ganttModel.addTag(tagName)
        }

        onRemoveTag: {
            ganttModel.removeTag(tagId)
        }
    }*/

}

