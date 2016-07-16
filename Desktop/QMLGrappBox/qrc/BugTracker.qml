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
    id: bugTrackerItem
    property var mouseCursor

    property var purcentWidth: [0.33, 0.25, 0.25, 0.17]

    function finishedLoad() {
        bugModel.loadTags()
        bugModel.loadOpenTickets()
        bugModel.loadClosedTickets()
        bugModel.loadYoursTickets()
    }

    BugTrackerModel {
        id: bugModel

        onError: {
            bugTrackerItem.parent.error(title, message)
        }
    }

    Flickable
    {
        id: flickableView
        anchors.fill: parent
        contentHeight: mainView.height + Units.dp(32)

        Item {
            id: mainView
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.top: parent.top

            //elevation: 1
            height: ((state == "CommentView")
                     ? ticketColumn.implicitHeight
                     : (state == "AddView"
                        ? addTicketColumn.implicitHeight
                        : tabColumn.implicitHeight))
                    + Units.dp(32)

            states: [
                State {
                    name: "BugView"
                },
                State {
                    name: "CommentView"
                },
                State {
                    name: "AddView"
                }

            ]

            state: "BugView"

            Behavior on height {
                NumberAnimation { duration: 200 }
            }

            BugTrackerTabView {
                id: tabColumn
                visible: mainView.state == "BugView"
                bugModel: bugModel

                onCreate: {
                    mainView.state = "AddView"
                }
            }

            View {

                anchors.horizontalCenter: parent.horizontalCenter
                anchors.top: parent.top
                anchors.topMargin: Units.dp(16)

                width: Math.min(parent.width - Units.dp(32), 1140)

                visible: mainView.state != "BugView"

                elevation: 1

                Behavior on height {
                    NumberAnimation { duration: 200 }
                }

                height: ((mainView.state == "CommentView")
                         ? ticketColumn.implicitHeight
                         : (mainView.state == "AddView"
                            ? addTicketColumn.implicitHeight
                            : Units.dp(0)))
                        + Units.dp(32)

                BugTrackerTicketView {
                    id: ticketColumn
                    visible: mainView.state == "CommentView"
                    bugModel: bugModel

                    onBack: {
                        mainView.state = "BugView"
                    }
                }

                BugTrackerAddTicketView {
                    id: addTicketColumn
                    visible: mainView.state == "AddView"
                    bugModel: bugModel

                    onBack: {
                        mainView.state = "BugView"
                    }
                }
            }
        }
    }

    Scrollbar {
        flickableItem: flickableView
    }
}

