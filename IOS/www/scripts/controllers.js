/*
    Summary: All controllers are stocked here
    In case we need to add / modify a functionality, controllers are created even if empty
*/

angular.module('GrappBox.controllers', [])

.controller('AppCtrl', function ($scope) {
})

// DASHBOARD
.controller('DashboardCtrl', function ($scope) {
})

.controller('TeamOccupationCtrl', function ($scope) {
    $scope.items = [
        {
            id: 1,
            name: "Allyriane Launois",
            occupation: "Free",
            image: 'images/GrappBox/allyriane_launois.jpg'
        },
        {
            id: 2,
            name: "Frédéric Tan",
            occupation: "Busy",
            image: 'images/GrappBox/frederic_tan.jpg'
        },
        {
            id: 3,
            name: "Léo Nadeau",
            occupation: "Busy",
            image: 'images/GrappBox/leo_nadeau.jpg'
        },
        {
            id: 4,
            name: "Marc Wieser",
            occupation: "Free",
            image: 'images/GrappBox/marc_wieser.jpeg'
        },
        {
            id: 5,
            name: "Pierre Feytout",
            occupation: "Busy",
            image: 'images/GrappBox/pierre_feytout.jpg'
        },
        {
            id: 6,
            name: "Pierre Hofman",
            occupation: "Busy",
            image: 'images/GrappBox/pierre_hofman.jpg'
        },
        {
            id: 7,
            name: "Roland Hemmer",
            occupation: "Busy",
            image: 'images/GrappBox/roland_hemmer.jpg'
        },
        {
            id: 8,
            name: "Valentin Mougenot",
            occupation: "Free",
            image: 'images/GrappBox/valentin_mougenot.jpg'
        }
    ];
})

.controller('NextMeetingsCtrl', function ($scope) {
    $scope.items = [
        {
            id: 1,
            type: "Client Meeting",
            date: "2015/10/06",
            hour: "01:00 pm",
            image: 'images/NextMeetings/Client_Meeting.png'
        },
        {
            id: 2,
            type: "Team Meeting",
            date: "2015/10/06",
            hour: "04:30 pm",
            image: 'images/NextMeetings/Team_Meeting.png'
        },
        {
            id: 3,
            type: "Presentation",
            date: "2015/10/07",
            hour: "01:00 pm",
            image: 'images/NextMeetings/Presentation.png'
        },
        {
            id: 4,
            type: "Exceptional",
            date: "2015/10/08",
            hour: "08:00 pm",
            image: 'images/NextMeetings/Exceptional.png'
        },
        {
            id: 5,
            type: "Relax",
            date: "2015/10/09",
            hour: "06:00 pm",
            image: 'images/NextMeetings/Relax.png'
        },
        {
            id: 6,
            type: "Client Meeting",
            date: "2015/10/10",
            hour: "01:00 pm",
            image: 'images/NextMeetings/Client_Meeting.png'
        }
    ];
})

.controller('GlobalProgressCtrl', function ($scope) {
    $scope.items = [
        {
            id: 1,
            product: "Game Sphere",
            author: "Nivento",
            tel: "(+33)6.01.02.03.04",
            email: "caribou@nivento.ca",
            tasksDone: 21,
            tasksToDo: 42,
            messages: 5,
            problems: 3,
            image: 'images/GlobalProgress/Game_Sphere.png'
        },
        {
            id: 2,
            product: "Goot",
            author: "Goot",
            tel: "(+33)6.01.02.03.04",
            email: "caribou@nivento.ca",
            tasksDone: 21,
            tasksToDo: 42,
            messages: 5,
            problems: 3,
            image: 'images/GlobalProgress/Goot.png'
        },
        {
            id: 3,
            product: "Game Sphere",
            author: "Nivento",
            tel: "(+33)6.01.02.03.04",
            email: "caribou@nivento.ca",
            tasksDone: 21,
            tasksToDo: 42,
            messages: 5,
            problems: 3,
            image: 'images/GlobalProgress/Game_Sphere.png'
        },
        {
            id: 4,
            product: "Game Sphere",
            author: "Nivento",
            tel: "(+33)6.01.02.03.04",
            email: "caribou@nivento.ca",
            tasksDone: 21,
            tasksToDo: 42,
            messages: 5,
            problems: 3,
            image: 'images/GlobalProgress/Game_Sphere.png'
        }
    ];
})

// TIMELINES
.controller('TimelinesCtrl', function ($scope) {
})

// WHITEBOARD

.controller('WhiteboardCtrl', function ($scope, $ionicPopover, $ionicPopup) {
    var width = 500;
    var height = 500;

    var canvas = new fabric.Canvas('canvasWhiteboard');

    canvas.selection = false;
    fabric.Object.prototype.selectable = false; //Prevent drawing objects to be draggable or clickable

    //Saving by both manners prevents from errors
    canvas.setHeight(height);
    canvas.setWidth(width);
    canvas.width = width;
    canvas.height = height;

    canvas.isDrawingMode = false;
    canvas.freeDrawingBrush.width = 6; //Size of the drawing brush
    $scope.brushcolor = '#000000'; //Set brushcolor to black at the beginning

    //Switch On/Off drawing mode
    $scope.drawingMode = function () {
        if (canvas.isDrawingMode == true)
            canvas.isDrawingMode = false; //Exit drawing mode
        else
        {
            canvas.off('mouse:down');
            canvas.isDrawingMode = true; //Enter drawing mode
        }
    }

    //Colors options in the popover
    var colorsPopup = '<ion-popover-view><ion-content><div>' +
                     '<button class="button button-icon ion-record"' + // "ion-record" is the round ionic font for colors
                     'ng-repeat="colorDisplayed in colorTab"' + // "ng-repeat" goes in whiteboard.html and picks in colorsTab list below the good color to display
                     'style="color: {{colorDisplayed.colorHex}}; font-size: 36.5px"' + // "style" creates a "color" CSS element and assigns it the "colorDisplayed" found in previous ng-repeat "color"
                     'ng-click="changeBrushColor(colorDisplayed.colorHex)"></button>' + // "changeBrushColor" allows to send the good color to be picked after the previously assigned color
                     '</div></ion-content></ion-popover-view>';

    //Shapes options in the popover
                     //Rectangle
    var shapesPopup = '<ion-popover-view style="height:50px;"><ion-content><div>' +
                     '<button class="button button-icon ion-stop"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawRect()"></button>' +
                     
                     //Ellipse
                     '<button class="button button-icon ion-record"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawEllipse()"></button>' +

                     //Diamond
                     '<button class="button button-icon ion-ios-play"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawDiamond()"></button>' +

                     //Triangle
                     '<button class="button button-icon ion-arrow-up-b"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawTriangle()"></button>' +

                     //Line
                     '<button class="button button-icon ion-minus-round"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawLine()"></button>' +
                     '</div></ion-content></ion-popover-view>';

    $scope.popoverColors = $ionicPopover.fromTemplate(colorsPopup, {
        scope: $scope
    });

    $scope.popoverShapes = $ionicPopover.fromTemplate(shapesPopup, {
        scope: $scope
    });

    //Show colors popover
    $scope.openColorsPopover = function ($event) {
        $scope.popoverColors.show($event);
    };

    //Show shapes popover
    $scope.openShapesPopover = function ($event) {
        $scope.popoverShapes.show($event);
    };

    //List of colors
    $scope.colorTab = [
        { colorHex: "#ecf0f1" },
        { colorHex: "#95a5a6" },
        { colorHex: "#bdc3c7" },
        { colorHex: "#7f8c8d" },
        { colorHex: "#000000" },
        { colorHex: "#F1A9A0" },
        { colorHex: "#D2527F" },
        { colorHex: "#f1c40f" },
        { colorHex: "#f39c12" },
        { colorHex: "#e67e22" },
        { colorHex: "#d35400" },
        { colorHex: "#e74c3c" },
        { colorHex: "#c0392b" },
        { colorHex: "#6D4C41" },
        { colorHex: "#3E2723" },
        { colorHex: "#1abc9c" },
        { colorHex: "#16a085" },
        { colorHex: "#2ecc71" },
        { colorHex: "#27ae60" },
        { colorHex: "#3498db" },
        { colorHex: "#2980b9" },
        { colorHex: "#34495e" },
        { colorHex: "#2c3e50" },
        { colorHex: "#9b59b6" },
        { colorHex: "#8e44ad" }
    ]

    //Change brush color
    $scope.changeBrushColor = function (colorChosen) {
        canvas.freeDrawingBrush.color = colorChosen;
        $scope.brushcolor = colorChosen; //Set brush color to the new color chosen
        $scope.popoverColors.hide();
    }

    //Draw Rectangle shape
    $scope.drawRect = function () {
        var mouse_pos = { x: 0, y: 0 };

        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Rect({
                top: mouse_pos.y - 15,
                left: mouse_pos.x - 20,
                width: 40,
                height: 30,
                fill: $scope.brushcolor,
                selectable: true,
                evented: false
            }));
        });
        $scope.popoverShapes.hide();
    }

    //Draw Ellipse shape
    $scope.drawEllipse = function () {
        var mouse_pos = { x: 0, y: 0 };

        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Ellipse({
                top: mouse_pos.y - 30,
                left: mouse_pos.x - 40,
                strokeWidth: 10,
                fill: $scope.brushcolor,
                rx: 40, ry: 30,
                selectable: true,
                evented: false
            }));
        });
        $scope.popoverShapes.hide();
    }

    //Draw Triangle shape
    $scope.drawTriangle = function () {
        var mouse_pos = { x: 0, y: 0 };

        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Triangle({
                top: mouse_pos.y - 15,
                left: mouse_pos.x - 15,
                width: 30,
                height: 30,
                fill: $scope.brushcolor,
                selectable: true,
                evented: false
            }));
        });
        $scope.popoverShapes.hide();
    }

    //Draw Diamond shape
    $scope.drawDiamond = function () {
        var mouse_pos = { x: 0, y: 0 };

        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Polygon(
                [
                    {x: 25, y: 0},
                    {x: 50, y: 50},
                    {x: 25, y: 100},
                    {x: 0, y: 50}
                ],
                {
                    top: mouse_pos.y - 50,
                    left: mouse_pos.x - 25,
                    fill: $scope.brushcolor,
                    selected: true,
                    evented: false,
                    hasBorders: false,
                    hasControls: false,
                    hasRotatingPoint: false,
                    lockMovementX: true,
                    lockMovementY: true,
            }));
        });
        $scope.popoverShapes.hide();
    }

    //Draw Line
    $scope.drawLine = function () {
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) { LineMouseDown(e); });
        canvas.observe('mouse:up', function (e) { LineMouseUp(e); });

        var Started = false;
        var StartX = 0;
        var StartY = 0;

        function LineMouseDown(e) {
            var Mouse = canvas.getPointer(e.e);

            Started = true;
            StartX = Mouse.x;
            StartY = Mouse.y;
        }

        function LineMouseUp(e) {
            if (Started) {
                var Mouse = canvas.getPointer(e.e);

                canvas.add(new fabric.Line([StartX, StartY, Mouse.x, Mouse.y],
                    {
                        stroke: $scope.brushcolor,
                        strokeWidth: 6,
                        selectable: false,
                        evented: false
                    }));
                canvas.renderAll();
                canvas.calcOffset();

                Started = false;
            }
        }
        $scope.popoverShapes.hide();
    }

    //Undo last object, drawing or text
    $scope.undoLastObject = function () {
        var canvas_objects = canvas._objects; //Get all objects
        var last = canvas_objects[canvas_objects.length - 1]; //Select the last object
        canvas.remove(last); //Remove the last object
        canvas.renderAll();
    }

    $scope.addText = function () {
        //Prevent user from being in drawing mode while adding text
        canvas.isDrawingMode = false;

        $scope.data = {}

        //Ionic popup used to prompt user to enter text
        var myPopup = $ionicPopup.show({
            template: '<input type="text" ng-model="data.input">',
            title: 'Enter Text',
            subTitle: '',
            scope: $scope,
            buttons: [{
                text: 'Cancel'
            }, {
                text: '<b>Save</b>',
                type: 'button-stable',
                onTap: function (e) {
                    if (!$scope.data.input) {
                        e.preventDefault();
                    } else {
                        return $scope.data.input;
                    }
                }
            }]
        });
        myPopup.then(function (input) {

            //Text in fabric.js
            var t = new fabric.Text(input, {
                left: (width / 3),
                top: 100,
                fontFamily: 'Helvetica',
                fill: $scope.brushcolor, //Use the current selected color
                selectable: true, //Make the text draggable
            });
            canvas.add(t); //Add text to canvas
        });
    }
})


// SETTINGS
.controller('SettingsCtrl', function ($scope) {
})