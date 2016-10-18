import QtQuick 2.4
import QtQuick.Layouts 1.1
import QtQuick.Dialogs 1.2
import QtGraphicalEffects 1.0
import Material 0.2
import Material.ListItems 0.1 as ListItem
import Material.Extras 0.1
import GrappBoxController 1.0

Item {

    property bool onRight: false
    property alias title: titleLabel.text
    property alias description: descriptionLabel.text
    property alias information: infoLabel.text
    property alias avatarId: iconMiddle.avatarId
    property alias avatarDate: iconMiddle.avatarDate
    property bool large: true
    property double animationTime: 100

    onLargeChanged: {
        if (large)
        {
            iconItem.anchors.horizontalCenter = this.horizontalCenter
            iconItem.anchors.left = undefined
        }
        else
        {
            iconItem.anchors.left = this.left
            iconItem.anchors.horizontalCenter = undefined
        }
        if (onRight || !large)
        {
            textView.anchors.left = iconItem.right
            textView.anchors.right = this.right
        }
        else
        {
            textView.anchors.right = iconItem.left
            textView.anchors.left = this.left
        }
    }

    signal readMore();

    height: Math.max(iconItem.height, textView.height)

    Item {

        id: iconItem

        anchors.top: parent.top

        width: large ? Units. dp(60) : Units. dp(40)
        height: width

        Image {
            id: border
            source: Qt.resolvedUrl("qrc:/images/qrc/images/circle.png");
            anchors.fill: parent
        }

        Image {
            id: background
            source: Qt.resolvedUrl("qrc:/images/qrc/images/circle.png");
            anchors.fill: parent
            anchors.margins: iconMiddle.anchors.margins
            averageColor: Theme.accentColor
        }

        ColorOverlay {
            anchors.fill: background
            source: background
            color: Theme.primaryColor
        }

        CircleImageAsync {
            id: iconMiddle

            source: "image://api/user#default"

            anchors.fill: parent
            anchors.margins: Units. dp(4)
        }

        Component.onCompleted: {
            if (large)
            {
                iconItem.anchors.horizontalCenter = parent.horizontalCenter
            }
            else
            {
                anchors.left = parent.left
            }
        }

    }

    View {
        id: textView

        Component.onCompleted: {
            if (onRight || !large)
            {
                anchors.left = iconItem.right
                anchors.right = parent.right
            }
            else
            {
                anchors.right = iconItem.left
                anchors.left = parent.left
            }
            anchors.leftMargin = Units. dp(8)
            anchors.rightMargin = Units. dp(8)
        }

        anchors.top: parent.top

        height: columnL.implicitHeight + Units. dp(32)

        elevation: 1

        Column {

            id: columnL

            anchors {
                top: parent.top
                topMargin: Units. dp(8)
                left: parent.left
                right: parent.right
            }

            Label {
                id: titleLabel

                anchors {
                    left: parent.left
                    right: parent.right
                    margins: Units. dp(8)
                }

                style: "title"
            }

            Item {
                width: parent.width
                height: Units. dp(8)
            }

            Label {
                id: descriptionLabel

                anchors {
                    left: parent.left
                    leftMargin: Units. dp(16)
                }

                width: parent.width - Units. dp(32)
                wrapMode: Text.Wrap

                style: "body2"
            }

            Item {
                width: parent.width
                height: Units. dp(8)
            }

            Item {

                anchors {
                    left: parent.left
                    right: parent.right
                    margins: Units. dp(16)
                }

                height: Units. dp(32)

                Label {
                    id: infoLabel

                    anchors {
                        left: parent.left
                        verticalCenter: parent.verticalCenter
                    }

                    height: parent.height

                    style: "body1"
                }

                Button {
                    id: readMoreButton

                    anchors {
                        right: parent.right
                        verticalCenter: parent.verticalCenter
                    }

                    height: parent.height

                    elevation: 1
                    text: "Read more"

                    onClicked: {
                        readMore();
                    }
                }
            }
        }
    }


}

