import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

Item {
    id: calendarItem

    property var mouseCursor

    property var todayDate: new Date()
    property var currentDate: new Date()
    property var firstMondayDate: new Date()
    property var daySelected: new Date()

    onCurrentDateChanged: {
        monthText.text = Qt.formatDate(calendarItem.currentDate, "MMMM yyyy")
        console.log("CURRENT DATE CHANGED : ", calendarItem.currentDate)
    }

    function finishedLoad() {
        console.log(calendarItem.currentDate)
        calendarModel.goToMonth(calendarItem.currentDate)
    }

    Component.onCompleted: {
        daySelected = new Date(daySelected.getFullYear(), daySelected.getMonth(), daySelected.getDate())
        calendarItem.currentDate = new Date()
        calendarItem.currentDate.setDate(1)
        console.log("COMPLETE ! ", calendarItem.currentDate)
        firstMondayDate = new Date(calendarItem.currentDate.getFullYear(), calendarItem.currentDate.getMonth(), calendarItem.currentDate.getDate())
        firstMondayDate.setDate(calendarItem.currentDate.getDate() - calendarItem.currentDate.getDay())
    }

    signal resetSelection()

    CalendarModel {
        id: calendarModel

        onEventDayChanged: {
            console.log("Event day changed")
        }
    }

    Flickable {

        id: flickableMain

        anchors.left: parent.left
        anchors.top: parent.top
        anchors.bottom: parent.bottom
        anchors.right: taskView.left

        contentHeight: Math.max(viewCalendar.height + Units.dp(16), eventView.height, parent.height)

        View {
            id: viewCalendar

            anchors.top: parent.top
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.margins: Units.dp(8)
            height: columnMain.implicitHeight + Units.dp(32)

            elevation: 1

            Column {
                anchors.topMargin: Units.dp(8)
                anchors.bottomMargin: Units.dp(8)
                anchors.left: parent.left
                anchors.right: parent.right
                anchors.top: parent.top
                id: columnMain

                Label {
                    id: monthText
                    anchors.left: parent.left
                    anchors.right: parent.right
                    horizontalAlignment: Text.AlignHCenter
                    verticalAlignment: Text.AlignVCenter
                    text: Qt.formatDate(calendarItem.currentDate, "MMMM yyyy")
                    font.bold: true
                    style: "title"

                    function onDateChange() {
                        console.log("Date ?")
                        text = Qt.formatDate(calendarItem.currentDate, "MMMM yyyy")
                    }
                }

                Separator {}

                Item {
                    anchors.left: parent.left
                    anchors.right: parent.right

                    height: Units.dp(32)

                    Button {
                        id: goToToday
                        text: "TODAY"
                        height: Units.dp(32)
                        anchors.verticalCenter: parent.verticalCenter
                        anchors.horizontalCenter: parent.horizontalCenter

                        onClicked: {
                            calendarItem.currentDate = new Date(calendarItem.todayDate.getFullYear(), calendarItem.todayDate.getMonth(), 1)
                            monthText.text = Qt.formatDate(calendarItem.currentDate, "MMMM yyyy")
                            firstMondayDate = new Date(calendarItem.currentDate.getFullYear(), calendarItem.currentDate.getMonth(), calendarItem.currentDate.getDate())
                            firstMondayDate.setDate(calendarItem.currentDate.getDate() - calendarItem.currentDate.getDay())
                            calendarModel.goToMonth(calendarItem.currentDate)
                        }
                    }

                    Button {
                        id: goNext
                        text: "NEXT MONTH >"
                        height: Units.dp(32)
                        anchors.left: goToToday.right
                        anchors.leftMargin: Units.dp(8)
                        anchors.verticalCenter: parent.verticalCenter
                        textColor: "#44BBFF"
                        onClicked: {
                            console.log("BEFORE C ", calendarItem.currentDate)
                            if (calendarItem.currentDate.getMonth() == 12)
                                calendarItem.currentDate = new Date(calendarItem.currentDate.getFullYear() + 1, 1, 1)
                            else
                                calendarItem.currentDate = new Date(calendarItem.currentDate.getFullYear(), calendarItem.currentDate.getMonth() + 1, 1)
                            console.log("AFTER C ", calendarItem.currentDate)
                            monthText.text = Qt.formatDate(calendarItem.currentDate, "MMMM yyyy")
                            firstMondayDate = new Date(calendarItem.currentDate.getFullYear(), calendarItem.currentDate.getMonth(), calendarItem.currentDate.getDate())
                            firstMondayDate.setDate(calendarItem.currentDate.getDate() - calendarItem.currentDate.getDay())
                            calendarModel.goToMonth(calendarItem.currentDate)
                        }
                    }

                    Button {
                        id: goPrevious
                        text: "< PREVIOUS MONTH"
                        height: Units.dp(32)
                        anchors.right: goToToday.left
                        anchors.rightMargin: Units.dp(8)
                        anchors.verticalCenter: parent.verticalCenter
                        textColor: "#44BBFF"
                        onClicked: {
                            console.log("BEFORE C ", calendarItem.currentDate)
                            calendarItem.currentDate.setMonth(calendarItem.currentDate.getMonth() - 1)
                            console.log("AFTER C ", calendarItem.currentDate)
                            monthText.text = Qt.formatDate(calendarItem.currentDate, "MMMM yyyy")
                            firstMondayDate = new Date(calendarItem.currentDate.getFullYear(), calendarItem.currentDate.getMonth(), calendarItem.currentDate.getDate())
                            firstMondayDate.setDate(calendarItem.currentDate.getDate() - calendarItem.currentDate.getDay())
                            calendarModel.goToMonth(calendarItem.currentDate)
                        }
                    }
                }

                Separator {}

                Button {
                    id: addEventButton

                    backgroundColor: "#44BBFF"
                    textColor: "#FFFFFF"
                    text: "Add a new event"
                    anchors.horizontalCenter: parent.horizontalCenter

                    onClicked: {
                        eventView.onAdd = true
                        eventView.onEdit = false
                        eventView.visible = true
                    }
                }

                Separator {}

                Row {
                    id: rowTabHeader
                    anchors.left: parent.left
                    anchors.right: parent.right
                    anchors.leftMargin: Units.dp(8)
                    anchors.rightMargin: Units.dp(8)
                    height: Math.max(Units.dp(48), implicitHeight)

                    Repeater {
                        model: [
                            "Sunday",
                            "Monday",
                            "Tuesday",
                            "Wednesday",
                            "Thursday",
                            "Friday",
                            "Saturday"
                        ]
                        delegate: Label {
                            width: viewCalendar.width / 7
                            height: parent.height

                            horizontalAlignment: Text.AlignHCenter
                            verticalAlignment: Text.AlignVCenter

                            text: modelData
                            font.bold: true
                            font.pixelSize: 14

                            color: "#666666"
                        }
                    }
                }

                Separator {}

                Repeater {
                    id: caseCalendar
                    model: [0, 7, 14, 21, 28, 35]

                    Component.onCompleted: {
                    }

                    delegate: Row {
                        id: rowWeek
                        anchors.left: parent.left
                        anchors.right: parent.right
                        anchors.leftMargin: Units.dp(8)
                        anchors.rightMargin: Units.dp(8)
                        height: Units.dp(100)

                        property int dayOffset: modelData

                        onWidthChanged: {
                            console.log(width)
                        }

                        Repeater {
                            model: [0, 1, 2, 3, 4, 5, 6]
                            delegate: CalendarCase {
                                height: parent.height
                                width: parent.width / 7
                                isSelected: false

                                visibleCircle: !calendarModel.eventMonthLoading

                                property bool initialized: false

                                function updateInfo() {
                                    dateInfo = new Date(firstMondayDate.valueOf())
                                    dateInfo.setDate(dateInfo.getDate() + rowWeek.dayOffset + modelData)
                                    numberOfDay = dateInfo.getDate()
                                    isToday = dateInfo.getDate() === todayDate.getDate() && dateInfo.getMonth() === todayDate.getMonth() && dateInfo.getFullYear() === todayDate.getFullYear()
                                    isWeekEnd = modelData == 0 || modelData == 6
                                }

                                function removeSelectionCase() {
                                    isSelected = false;
                                    calendarItem.resetSelection.disconnect(removeSelectionCase)
                                }

                                Component.onCompleted: {
                                    updateInfo()
                                    calendarModel.eventDayChanged.connect(updateNumbers)
                                }

                                onSelected: {
                                    calendarItem.resetSelection()
                                    calendarItem.resetSelection.connect(removeSelectionCase)
                                    daySelected = dateInfo
                                    isSelected = true
                                    calendarModel.loadEventDay(dateInfo)
                                    taskView.open = true
                                }

                                function updateNumbers()
                                {
                                    numberOfEvent = calendarModel.getEventDayCount(new Date(dateInfo.getFullYear(), dateInfo.getMonth(), dateInfo.getDate()));
                                    updateInfo();
                                }

                                mouseCursor: calendarItem.mouseCursor
                            }
                        }
                    }
                }
            }
        }

    }

    CalendarTaskView {
        id: taskView
        anchors.top: parent.top
        anchors.bottom: parent.bottom
        anchors.right: parent.right
        calendarModel: calendarModel

        onOpenTask: {
            eventView.eventData = taskdata
            eventView.visible = true
            calendarModel.getEventInfo(taskdata.id)
        }
    }

    CalendarEventView {
        id: eventView
        anchors.top: parent.top
        anchors.topMargin: Units.dp(32)
        anchors.horizontalCenter: parent.horizontalCenter
        calendarModel: calendarModel
    }


    Scrollbar {
        flickableItem: flickableMain
    }
}
