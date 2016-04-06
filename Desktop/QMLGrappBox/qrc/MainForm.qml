import QtQuick 2.5
import QtQuick.Controls 1.3
import QtQuick.Controls.Styles 1.4
import QtQuick.Layouts 1.2
import Material 0.2
import Material.Extras 0.1
import Material.ListItems 0.1 as ListItem

Rectangle {
    id: rectangle1
    property alias mouseArea: mouseArea


    anchors.fill: parent
    width: parent.width

    MouseArea {
        id: mouseArea
        anchors.rightMargin: 0
        anchors.bottomMargin: 0
        anchors.fill: parent
        width: parent.width


    }
}

