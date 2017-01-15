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
        console.log("Starting load parameters");
        if (avatarId === undefined)
            return
        console.log("Load parameters ok");
        if (DataImageProvider.isDataIdLoaded(avatarId))
            source = "image://api/" + avatarId
        else if (!isConnected)
        {
            isConnected = true
            DataImageProvider.loadNewDataImage(avatarId)
            console.log("Connect callback")
            DataImageProvider.changed.connect(onLoaded)
        }
    }

    onAvatarIdChanged: loadParameters()

    Component.onCompleted: loadParameters()
}

