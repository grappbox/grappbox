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
    id: statisticsBar
    elevation: 1
    width: Units.dp(500)
    property alias widthBar: chartView.width
    property var colorsUsed: ["#F44336", "#E91E63", "#9C27B0", "#673AB7", "#3F51B5", "#2196F3", "#03A9F4", "#00BCD4", "#009688", "#4CAF50", "#8BC34A", "#CDDC39", "#FFEB3B", "#FFC107", "#FF9800", "#FF5722", "#795548", "#607D8B"]
    property alias titleText: title.text
    property alias subtitleText: subtitle.text
    property double minValue: 0
    property double maxValue: 100
    property bool calculateMaxValue: true

    property var dataCategories: []
    property var dataValues: []

    Component.onCompleted: {
        var realCategories = [];
        for (var i = 0; i < dataValues.length; ++i)
            realCategories.push(dataValues[i].label);
        axisXBar.categories = realCategories;
        var maxValueCal = -1
        for (var i = 0; i < dataCategories.length; ++i)
        {
            var nb = [];
            for (var j = 0; j < dataValues.length; ++j)
            {
                if (maxValue == -1 || maxValueCal < dataValues[j].value[i])
                    maxValueCal = dataValues[j].value[i];
                nb.push(dataValues[j].value[i]);
            }
            seriesBar.append(dataCategories[i], nb);
        }
        if (calculateMaxValue)
        {
            maxValue = Math.ceil(maxValueCal / 5) * 5;
        }
    }

    Column {
        id: columnPieChart
        anchors.fill: parent
        anchors.margins: Units.dp(16)

        Label {
            id: title
            text: "Chart title"
            style: "title"
        }

        Item {
            height: Units.dp(8)
            anchors.left: parent.left
            anchors.right: parent.right
        }

        Label {
            id: subtitle
            text: "Sub title"
            style: "body1"
        }

        Separator {}

        Item {
            width: parent.width
            height: chartView.height

            ChartView {
                id: chartView
                anchors.margins: Units.dp(16)
                anchors.verticalCenter: parent.verticalCenter
                anchors.left: parent.left
                antialiasing: true
                legend.visible: false
                width: Units.dp(300)
                height: statisticsBar.height - Units.dp(80)

                onSeriesAdded: {
                }

                Component.onCompleted: {

                }

                BarSeries {
                    id: seriesBar

                    axisX: BarCategoryAxis {
                        id: axisXBar
                    }

                    axisY: ValueAxis {
                        id: axisYBar
                        min: minValue
                        max: maxValue
                    }

                    onBarsetsAdded: {
                        var barset = seriesBar.at(seriesBar.count - 1);
                        barset.color = colorsUsed[seriesBar.count - 1]
                        chartLegend.addSeries(barset.label, barset.color)
                    }
                }
            }

            CustomLegendChart {
                id: chartLegend
                anchors.left: chartView.right
                anchors.right: parent.right
                anchors.top: parent.top
                anchors.bottom: parent.bottom
                anchors.margins: Units.dp(16)
            }
        }
    }
}
