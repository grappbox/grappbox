import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

Row {
    id: mainRow
    height: buttonDateBegin.height + Units.dp(8)

    property var dateBegin
    property var dateEnd

    function getDateBegin() {
        var dateIn = buttonDateBegin.dateIn;
        var timeIn = buttonTimeBegin.timeIn;
        var dateRet = new Date(dateIn.getFullYear(), dateIn.getMonth(), dateIn.getDate(),
                               timeIn.getHours(), timeIn.getMinutes());
        return dateRet;
    }
    function getDateEnd() {
        var dateIn = buttonDateEnd.dateIn;
        var timeIn = buttonTimeEnd.timeIn;
        var dateRet = new Date(dateIn.getFullYear(), dateIn.getMonth(), dateIn.getDate(),
                               timeIn.getHours(), timeIn.getMinutes());
        return dateRet;
    }

    Dialog {
        id: datePicker
        hasActions: true
        contentMargins: 0
        floatingActions: true

        property var objectDate

        DatePicker {
            id: inDatePicker
            frameVisible: false
            dayAreaBottomMargin: Units.dp(48)
            isLandscape: true
        }
        onAccepted: {
            objectDate.dateIn = inDatePicker.selectedDate;
            if (buttonDateBegin.dateIn > buttonDateEnd.dateIn)
                buttonDateEnd.dateIn = new Date(buttonDateBegin.dateIn.getFullYear(), buttonDateBegin.dateIn.getMonth(), buttonDateBegin.dateIn.getDate());
            if (buttonDateBegin.dateIn.getFullYear() === buttonDateEnd.dateIn.getFullYear() &&
                    buttonDateBegin.dateIn.getMonth() === buttonDateEnd.dateIn.getMonth() &&
                    buttonDateBegin.dateIn.getDate() === buttonDateEnd.dateIn.getDate())
            {
                if (buttonTimeBegin.timeIn > buttonTimeEnd.timeIn)
                {
                    buttonTimeBegin.timeIn = new Date(buttonTimeBegin.timeIn.getFullYear(), buttonTimeBegin.timeIn.getMonth(), buttonTimeBegin.timeIn.getDate(), buttonTimeBegin.timeIn.getHours(), buttonTimeBegin.timeIn.getMinutes(), buttonTimeBegin.timeIn.getSeconds())
                }
            }
        }
    }

    Dialog {
        id: timePicker
        hasActions: true
        contentMargins: 0
        floatingActions: true

        property var objectDate

        CalendarTimePicker {
            id: inTimePicker
            prefer24Hour: true
            bottomMargin: Units.dp(48)
        }

        onAccepted: {
            objectDate.timeIn = inTimePicker.getCurrentTime()
            console.log(inTimePicker.getCurrentTime())
            if (buttonDateBegin.dateIn > buttonDateEnd.dateIn)
                buttonDateEnd.dateIn = new Date(buttonDateBegin.dateIn.getFullYear(), buttonDateBegin.dateIn.getMonth(), buttonDateBegin.dateIn.getDate());
            if (buttonDateBegin.dateIn.getFullYear() === buttonDateEnd.dateIn.getFullYear() &&
                    buttonDateBegin.dateIn.getMonth() === buttonDateEnd.dateIn.getMonth() &&
                    buttonDateBegin.dateIn.getDate() === buttonDateEnd.dateIn.getDate())
            {
                if (buttonTimeBegin.timeIn > buttonTimeEnd.timeIn)
                {
                    buttonTimeBegin.timeIn = new Date(buttonTimeBegin.timeIn.getFullYear(), buttonTimeBegin.timeIn.getMonth(), buttonTimeBegin.timeIn.getDate(), buttonTimeBegin.timeIn.getHours(), buttonTimeBegin.timeIn.getMinutes(), buttonTimeBegin.timeIn.getSeconds())
                }
            }
        }
    }

    Label {
        text: "From "
    }

    Item {
        width: Units.dp(8)
        height: parent.height
    }

    Button {
        id: buttonDateBegin
        text: Qt.formatDate(dateIn, "yyyy-MM-dd")

        elevation: 1

        property var dateIn: dateBegin
        onDateInChanged: {
            text = Qt.formatDate(dateIn, "yyyy-MM-dd")
        }

        onClicked: {
            datePicker.objectDate = this
            datePicker.show()
        }
    }

    Item {
        width: Units.dp(8)
        height: parent.height
    }

    Button {
        id: buttonTimeBegin
        text: Qt.formatDateTime(timeIn, "hh:mm ap")

        elevation: 1

        property var timeIn: dateBegin
        onTimeInChanged: {
            text = Qt.formatDateTime(timeIn, "hh:mm ap")
        }

        onClicked: {
            timePicker.objectDate = this
            timePicker.show()
        }
    }

    Item {
        width: Units.dp(8)
        height: parent.height
    }

    Label {
        text: " to "
    }

    Item {
        width: Units.dp(8)
        height: parent.height
    }

    Button {
        id: buttonDateEnd
        text: Qt.formatDate(dateIn, "yyyy-MM-dd")

        elevation: 1

        property var dateIn: dateEnd
        onDateInChanged: {
            text = Qt.formatDate(dateIn, "yyyy-MM-dd")
        }

        onClicked: {
            datePicker.objectDate = this
            datePicker.show()
        }
    }

    Item {
        width: Units.dp(8)
        height: parent.height
    }

    Button {
        id: buttonTimeEnd
        text: Qt.formatDateTime(timeIn, "hh:mm ap")

        elevation: 1

        property var timeIn: dateEnd
        onTimeInChanged: {
            text = Qt.formatDateTime(timeIn, "hh:mm ap")
        }

        onClicked: {
            timePicker.objectDate = this
            timePicker.show()
        }
    }
}
