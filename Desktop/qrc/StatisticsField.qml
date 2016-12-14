import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles
import QtCharts 2.0

View {
    property alias text: sub.text
    property alias subText: sub.subText
    property alias icon: iconSub.name

    height: Units.dp(48)

    elevation: 1

    ListItem.Subtitled {
        anchors.fill: parent
        id: sub
        interactive: false

        secondaryItem: Icon {
            id: iconSub
            anchors.verticalCenter: parent.verticalCenter
        }
    }
}
