import QtQuick 2.0
import GrappBoxController 1.0

Image {
    source: "image://api/project#default"

    property string avatarId
    property date avatarDate

    property bool isConnected: false

    function onLoaded(id) {
        if (id === avatarId)
        {
            source = "image://api/" + avatarId
            DataImageProvider.changed.disconnect(onLoaded)
        }
    }

    function loadParameters() {
        if (avatarId === undefined)
            return
        if (avatarDate != undefined && DataImageProvider.isDataIdLoaded(avatarId, avatarDate))
            source = "image://api/" + avatarId
        else if (!isConnected)
        {
            isConnected = true
            console.log("Connect callback")
            DataImageProvider.changed.connect(onLoaded)
        }
    }

    onAvatarIdChanged: loadParameters()

    Component.onCompleted: loadParameters()
}

