import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtQuick.Controls 1.3 as Controls
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0
import QtQuick.Controls.Styles 1.3 as Styles

View {
    id: tagView
    anchors.left: parent.left
    anchors.right: parent.right
    anchors.leftMargin: Units.dp(16)
    height: expanded ? flowTag.implicitHeight + Units.dp(16) : 0

    property bool expanded: false
    property bool editMode: false
    property GanttModel ganttModel
    property alias repeaterTags: repeater
    property var toAdd: []
    property var toRemove: []

    Behavior on height {
        NumberAnimation {
            duration: 200
        }
    }

    function tagAddedFunc(id)
    {
        console.log("NEW TAG : ", id)
        for (var item in ganttModel.taskTags)
        {
            console.log(ganttModel.taskTags[item].id)
            if (ganttModel.taskTags[item].id === id)
            {
                console.log("Add")
                toAdd.push(ganttModel.taskTags[item])
                repeaterAdded.model = toAdd
                break
            }
        }
    }

    Component.onCompleted: {
        ganttModel.tagAdded.connect(tagView.tagAddedFunc)
    }

    Dialog {
        id: addTagDialog

        title: "Add a new tag"

        Behavior on height {
            NumberAnimation { duration: 200 }
        }

        Controls.ExclusiveGroup {
            id: tagChoiceGroup
        }

        property bool canAddExisting: ganttModel.taskTags.length > repeater.model.length + toAdd.length - toRemove.length

        RadioButton {
            id: addAnExistingTag
            visible: addTagDialog.canAddExisting
            checked: visible
            text: "Add an existing tag"
            canToggle: false
            exclusiveGroup: tagChoiceGroup
        }

        MenuField {
            id: chooseTag
            model: []
            property var completeModel: []
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: Units.dp(32)
            visible: addAnExistingTag.checked && addTagDialog.canAddExisting
        }

        RadioButton {
            id: addANewTag
            visible: addTagDialog.canAddExisting
            checked: false
            text: "Add a new tag"
            canToggle: false
            exclusiveGroup: tagChoiceGroup
        }

        TextField {
            id: tag
            visible: addANewTag.checked || !addTagDialog.canAddExisting
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: !addTagDialog.canAddExisting ? Units.dp(32) : Units.dp(0)
            width: parent.width
            placeholderText: "Tag name"
        }

        Item {
            visible: tag.visible
            anchors.left: parent.left
            anchors.right: parent.right
            anchors.leftMargin: !addTagDialog.canAddExisting ? Units.dp(32) : Units.dp(0)
            height: choicer.visible ? choicer.height : colorChoicer.height

            Rectangle {
                id: colorChoicer
                anchors.left: parent.left
                anchors.verticalCenter: parent.verticalCenter
                width: Units.dp(32)
                height: Units.dp(32)
                radius: width / 2

                color: "#9E58DC"

                MouseArea {
                    anchors.fill: parent

                    onClicked: {
                        choicer.visible = true
                    }
                }
            }

            TagColorChoicer {
                id: choicer
                anchors.left: colorChoicer.left
                anchors.verticalCenter: colorChoicer.verticalCenter

                onChooseColor: {
                    colorChoicer.color = color
                }
            }
        }

        onRejected: tag.text = ""

        onAccepted: {
            if (tag.visible)
                ganttModel.addTag(tag.text, colorChoicer.color)
            else
            {
                toAdd.push(chooseTag.completeModel[chooseTag.selectedIndex])
                repeaterAdded.model = toAdd
            }
            tag.text = ""
        }

        onOpened: {
            var completeModel = []
            var modelText = []
            for (var item in ganttModel.taskTags)
            {
                var ignore = false
                for (var itemD in repeater.model)
                {
                    if (ganttModel.taskTags[item].id === repeater.model[itemD].id)
                    {
                        if (toRemove.indexOf(ganttModel.taskTags[item].id) != -1)
                            continue
                        ignore = true
                        break
                    }
                }
                for (var itemDA in toAdd)
                {
                    if (ganttModel.taskTags[item].id === toAdd[itemD].id)
                    {
                        ignore = true
                        break
                    }
                }
                if (ignore)
                    continue
                completeModel.push(ganttModel.taskTags[item])
                modelText.push(ganttModel.taskTags[item].name)
            }
            chooseTag.completeModel = completeModel
            chooseTag.model = modelText
        }

        positiveButtonText: tag.visible ? "Create and Add" : "Add"
        negativeButtonText: "Cancel"
    }

    Flow {
        id: flowTag
        anchors.fill: parent
        anchors.topMargin: Units.dp(8)
        anchors.bottomMargin: Units.dp(8)

        spacing: Units.dp(8)

        Repeater {
            id: repeater
            model: []
            delegate: Button {
                text: modelData.name
                elevation: 1
                textColor: "#FFF"
                backgroundColor: modelData.color
            }
        }

        Repeater {
            id: repeaterAdded
            model: toAdd
            delegate: Button {
                text: modelData.name
                elevation: 1
                textColor: "#FFF"
                backgroundColor: modelData.color
            }
        }

        IconButton {
            Layout.alignment: Qt.AlignVCenter
            iconName: "content/add_circle_outline"
            onClicked: {
                addTagDialog.show()
            }
        }
    }
}
