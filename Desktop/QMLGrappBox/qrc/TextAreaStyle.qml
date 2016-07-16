import QtQuick 2.0
import QtQuick.Controls.Styles 1.3 as Controls
import QtQuick.Layouts 1.1
import Material 0.2

Controls.TextFieldStyle {
    id: style

    padding {
        left: 0
        right: 0
        top: 0
        bottom: 0
    }

    font {
        pixelSize: Units.dp(16)
    }

    renderType: Text.QtRendering
    placeholderTextColor: "transparent"
    selectedTextColor: "white"
    selectionColor: control.hasOwnProperty("color") ? control.color : Theme.accentColor
    textColor: Theme.light.textColor

    background : Item {
        id: background

        property color color: control.hasOwnProperty("color") ? control.color : Theme.accentColor
        property color errorColor: control.hasOwnProperty("errorColor")
                ? control.errorColor : Palette.colors["red"]["500"]
        property string helperText: control.hasOwnProperty("helperText") ? control.helperText : ""
        property bool floatingLabel: control.hasOwnProperty("floatingLabel") ? control.floatingLabel : ""
        property bool hasError: control.hasOwnProperty("hasError")
                ? control.hasError : characterLimit && control.length > characterLimit
        property int characterLimit: control.hasOwnProperty("characterLimit") ? control.characterLimit : 0
        property bool showBorder: control.hasOwnProperty("showBorder") ? control.showBorder : true

        Rectangle {
            id: underline
            color: background.hasError ? background.errorColor
                                    : control.activeFocus ? background.color
                                                          : Theme.light.hintColor

            height: control.activeFocus ? Units.dp(2) : Units.dp(1)
            visible: background.showBorder

            anchors {
                left: parent.left
                right: parent.right
                bottom: parent.bottom
            }

            Behavior on height {
                NumberAnimation { duration: 200 }
            }

            Behavior on color {
                ColorAnimation { duration: 200 }
            }
        }
    }
}
