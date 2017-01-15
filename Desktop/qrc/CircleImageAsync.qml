import QtQuick 2.0
import Material.Extras 0.1
import GrappBoxController 1.0

CircleImage {
    source: "image://api/project#default"

    property string avatarId
    property date avatarDate

    property bool isConnected: false

    function onLoaded(id, url) {
        if (id === avatarId)
        {
            console.log("LOADED IMAGE for ", url)
            source = url
            DataImageProvider.changed.disconnect(onLoaded)
        }
    }


    function loadParameters() {
        console.log("Starting load parameters (", isConnected, ")");
        if (avatarId === undefined)
            return
        console.log("Load parameters ok");
        if (!isConnected)
        {
            console.log("Data id is not loaded");
            isConnected = true
            console.log("Connect callback")
            DataImageProvider.changed.connect(onLoaded)
            DataImageProvider.loadDataFromId(avatarId)
        }
        else
            console.warn("No possibility to load image.");
    }

    onAvatarIdChanged: {
        isConnected = false
        loadParameters()
    }

    Component.onCompleted: loadParameters()
}

