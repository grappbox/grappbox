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
        monthText.text = Qt.formatDate(currentDate, "MMMM yyyy")
    }

    function finishedLoad() {
        console.log(currentDate)
        calendarModel.goToMonth(currentDate)
    }

    Component.onCompleted: {
        daySelected = new Date(daySelected.getFullYear(), daySelected.getMonth(), daySelected.getDate())
        currentDate = new Date()
        currentDate.setDate(1)
        console.log(currentDate)
        firstMondayDate = Qt.binding(function() {
            var ret = new Date(currentDate.valueOf())
            ret.setDate(ret.getDate() - ret.getDay())
            return ret
        })
    }

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

        contentHeight: Math.max(viewCalendar.height, eventView.height, parent.height)

        View {
            id: viewCalendar

            visible: !calendarModel.eventMonthLoading

            anchors.top: parent.top
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.margins: Units.dp(8)
            height: columnMain.implicitHeight + Units.dp(32)

            elevation: 1

            Column {
                anchors.topMargin: Units.dp(8)
                anchors.bottomMargin: Units.dp(8)
                anchors.fill: parent
                id: columnMain

                Label {
                    id: monthText
                    anchors.left: parent.left
                    anchors.right: parent.right
                    horizontalAlignment: Text.AlignHCenter
                    verticalAlignment: Text.AlignVCenter
                    text: Qt.formatDate(currentDate, "MMMM yyyy")
                    font.bold: true
                    style: "title"
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
                            currentDate.setMonth(currentDate.getMonth() + 1)
                            firstMondayDate = Qt.binding(function() {
                                var ret = new Date(currentDate.valueOf())
                                ret.setDate(ret.getDate() - ret.getDay())
                                return ret
                            })
                            calendarModel.goToMonth(currentDate)
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
                            console.log("DATE BEFORE : ", currentDate)
                            currentDate.setMonth(currentDate.getMonth() - 1)
                            console.log("DATE AFTER : ", currentDate)
                            firstMondayDate = Qt.binding(function() {
                                var ret = new Date(currentDate.valueOf())
                                ret.setDate(ret.getDate() - ret.getDay())
                                return ret
                            })
                            calendarModel.goToMonth(currentDate)
                        }
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
                    model: [0]

                    Component.onCompleted: {
                        model = Qt.binding(function() {
                            var endMonthDate = currentDate
                            endMonthDate.setDate(0)
                            endMonthDate.setMonth(endMonthDate.getMonth() + 1)
                            var numberOfDays = Math.round(Math.abs(endMonthDate.getTime() - firstMondayDate.getTime()) / (86400000))
                            return numberOfDays >= 35 ? [0, 7, 14, 21, 28, 35] : [0, 7, 14, 21, 28]
                        })
                    }

                    delegate: Row {
                        id: rowWeek
                        anchors.left: parent.left
                        anchors.right: parent.right
                        anchors.leftMargin: Units.dp(8)
                        anchors.rightMargin: Units.dp(8)
                        height: Units.dp(100)

                        property int dayOffset: modelData

                        Repeater {
                            model: [0, 1, 2, 3, 4, 5, 6]
                            delegate: CalendarCase {
                                height: parent.width / 7
                                width: parent.width / 7
                                isSelected: dateInfo === daySelected

                                Component.onCompleted: {
                                    dateInfo = new Date(firstMondayDate.valueOf())
                                    dateInfo.setDate(dateInfo.getDate() + rowWeek.dayOffset + modelData)
                                    numberOfDay = dateInfo.getDate()
                                    isToday = dateInfo.getDate() === todayDate.getDate()
                                    isWeekEnd = modelData == 0 || modelData == 1
                                    calendarModel.eventDayChanged.connect(updateNumbers)
                                }

                                onSelected: {
                                    daySelected = dateInfo
                                    calendarModel.loadEventDay(dateInfo)
                                    taskView.open = true
                                }

                                function updateNumbers()
                                {
                                    numberOfEvent = calendarModel.getEventDayCount(new Date(dateInfo.getFullYear(), dateInfo.getMonth(), dateInfo.getDate()));
                                    console.log(numberOfEvent)
                                }

                                mouseCursor: calendarItem.mouseCursor
                            }
                        }
                    }
                }
            }
        }

        CalendarEventView {
            id: eventView
            anchors.top: parent.top
            anchors.topMargin: Units.dp(32)
            anchors.horizontalCenter: parent.horizontalCenter
            calendarModel: calendarModel
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


    Scrollbar {
        flickableItem: flickableMain
    }

    ActionButton {
        anchors {
            right: parent.right
            bottom: parent.bottom
            margins: Units.dp(32)
        }

        iconName: "content/add"

        onClicked: {
            eventView.onAdd = true
            eventView.onEdit = false
            eventView.visible = true
        }
    }
}
