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
    id: itemMain

    property var mouseCursor
    property var args: -1

    function finishedLoad() {
        // Maybe you found this stupid but a bug in the TabBar
        // show the 2nd bar selected but in the code it's the first.
        // This permit to select an other tab and reselect the first
        // in order to have the correct position of the cursor.
        tab.selectedIndex = 1
        tab.selectedIndex = 0

        projectSettingsModel.idProject = args === undefined ? SDataManager.project.id : args
        projectSettingsModel.loadInformation()
    }

    ProjectSettingsModel {
        id: projectSettingsModel

        onHasToQuitProject: {
            itemMain.parent.loadPage("Dashboard", [])
        }
    }

    Flickable {
        id: flickableMain
        anchors.fill: parent
        contentHeight: mainView.height + Units. dp(32)

        View {
            id: mainView
            anchors.top: parent.top
            anchors.topMargin: Units. dp(16)
            anchors.horizontalCenter: parent.horizontalCenter
            width: Math.max(Math.min(parent.width - Units. dp(32), Units. dp(1100)), Units. dp(600))
            elevation: 1

            height: tab.height + Units. dp(32)

            Behavior on height {
                NumberAnimation { duration: 200 }
            }

            Component.onCompleted: {
                height = Qt.binding(function() {
                    if (projectSettingsModel.isLoading)
                        return loadingCircle.height + Units. dp(48) + tab.height
                    switch (tab.selectedIndex)
                    {
                    case 0:
                        return settingsGeneral.implicitHeight + tab.height + Units. dp(32)
                    case 1:
                        return settingsUsers.implicitHeight + tab.height + Units. dp(32)
                    case 2:
                        return settingsRoles.implicitHeight + tab.height + Units. dp(32)
                    case 3:
                        return settingsCustomerAccess.implicitHeight + tab.height + Units. dp(32)
                    case 4:
                        return settingsDangerZone.implicitHeight + tab.height + Units. dp(32)
                    }
                    return tab.height + Units. dp(32)
                })
            }

            ProgressCircle {
                id: loadingCircle
                visible: projectSettingsModel.isLoading
                anchors.top: tab.bottom
                anchors.horizontalCenter: parent.horizontalCenter
                anchors.margins: Units. dp(8)
            }

            ProjectSettingsGeneral {
                id: settingsGeneral
                visible: tab.selectedIndex == 0 && !projectSettingsModel.isLoading
                anchors.top: tab.bottom
                anchors.left: parent.left
                anchors.right: parent.right
                projectSettingsModel: projectSettingsModel
            }

            ProjectSettingsUsers {
                id: settingsUsers
                visible: tab.selectedIndex == 1 && !projectSettingsModel.isLoading
                anchors.top: tab.bottom
                anchors.left: parent.left
                anchors.right: parent.right
                projectSettingsModel: projectSettingsModel
            }

            ProjectSettingsRoles {
                id: settingsRoles
                visible: tab.selectedIndex == 2 && !projectSettingsModel.isLoading
                anchors.top: tab.bottom
                anchors.left: parent.left
                anchors.right: parent.right
                projectSettingsModel: projectSettingsModel

            }

            ProjectSettingsCustomersAccess {
                id: settingsCustomerAccess
                visible: tab.selectedIndex == 3 && !projectSettingsModel.isLoading
                anchors.top: tab.bottom
                anchors.left: parent.left
                anchors.right: parent.right
                projectSettingsModel: projectSettingsModel
            }

            ProjectSettingsDangerZone {
                id: settingsDangerZone
                visible: tab.selectedIndex == 4 && !projectSettingsModel.isLoading
                anchors.top: tab.bottom
                anchors.left: parent.left
                anchors.right: parent.right
                projectSettingsModel: projectSettingsModel
            }

            Rectangle {
                anchors.fill: tab

                color: "white"
            }

            TabBar {
                id: tab

                anchors.left: parent.left
                anchors.right: parent.right

                tabs: [tabGeneral, tabUsers, tabRoles, tabCustomersAccess, tabDangerZone]
                isTabView: false
                fullWidth: true

                highlightColor: Theme.primaryColor

                Tab {
                    id: tabGeneral
                    title: "General"
                }

                Tab {
                    id: tabUsers
                    title: "Users"
                }

                Tab {
                    id: tabRoles
                    title: "Roles"
                }

                Tab {
                    id: tabCustomersAccess
                    title: "Customers Access"
                }

                Tab {
                    id: tabDangerZone
                    title: "Danger Zone"
                }

                centered: true
                isLargeDevice: true
            }

        }

    }

    Scrollbar {
        flickableItem: flickableMain
    }
}

