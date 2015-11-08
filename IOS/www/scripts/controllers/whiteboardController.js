/*
    Summary: WHITEBOARD LIST & WHITEBOARD Controllers
*/

angular.module('GrappBox.controllers')
// WHITEBOARD LIST
.controller('WhiteboardsCtrl', function ($scope, $ionicModal) {

    //Search for addWhiteboardModal.html ng-template in whiteboards.html
    $ionicModal.fromTemplateUrl('addWhiteboardModal.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function (modal) {
        $scope.modal = modal;
    });

    //Open the modal
    $scope.openAddWhiteboardModal = function () {
        $scope.modal.show();
    };

    //Close the modal
    $scope.closeWhiteboardModal = function () {
        $scope.modal.hide();
    };

    //Destroy the modal
    $scope.$on('$destroy', function () {
        $scope.modal.remove();
    });

    //Add a whiteboard in the list
    $scope.addWhiteboard = function (elem) {
        $scope.whiteboardsTab.push({
            id: 4,
            name: elem.title
        });
        $scope.modal.hide();
        elem.title = "";
    };

    //Delete a whiteboard
    $scope.onWhiteboardDelete = function (whiteboard) {
        $scope.whiteboardsTab.splice($scope.whiteboardsTab.indexOf(whiteboard), 1);
    };

    $scope.whiteboardsTab = [
    {
        id: 1,
        name: "Whiteboard"
    },
    {
        id: 2,
        name: "Whiteboard"
    },
    {
        id: 3,
        name: "Whiteboard"
    }];
})

// WHITEBOARD
.controller('WhiteboardCtrl', function ($scope, $ionicPopover, $ionicPopup, $ionicScrollDelegate) {
    var width = 2000; //4096;
    var height = 2000; //2160;

    var canvas = new fabric.Canvas('canvasWhiteboard');

    //Colors options in the popover
    var colorsPopup = '<ion-popover-view style="height: 250px; width: 265px"><ion-content><div>' +
                     '<button class="button button-icon ion-record"' + // "ion-record" is the round ionic font for colors
                     'ng-repeat="colorDisplayed in colorTab"' + // "ng-repeat" goes in whiteboard.html and picks in colorsTab list below the good color to display
                     'style="color: {{colorDisplayed.colorHex}}; font-size: 36.5px"' + // "style" creates a "color" CSS element and assigns it the "colorDisplayed" found in previous ng-repeat "color"
                     'ng-click="changeBrushColor(colorDisplayed.colorHex)"></button>' + // "changeBrushColor" allows to send the good color to be picked after the previously assigned color
                     '</div></ion-content></ion-popover-view>';

    //Shapes options in the popover
    //Rectangle filled
    var shapesPopup = '<ion-popover-view style="height:110px"><ion-content><div>' +
                     '<button class="button button-icon ion-stop"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawRect()"></button>' +

                     //Rectangle empty
                     '<button class="button button-icon ion-android-checkbox-outline-blank"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawRectEmpty()"></button>' +

                     //Ellipse filled
                     '<button class="button button-icon ion-record"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawEllipse()"></button>' +

                     //Ellipse empty
                     '<button class="button button-icon ion-ios-circle-outline"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawEllipseEmpty()"></button>' +

                     //Diamond filled
                     '<button class="button button-icon ion-ios-play"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawDiamond()"></button>' +

                     //Diamond empty
                     '<button class="button button-icon ion-ios-play-outline"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawDiamondEmpty()"></button>' +

                     //Triangle filled
                     '<button class="button button-icon ion-arrow-up-b"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawTriangle()"></button>' +

                     //Triangle empty
                     '<button class="button button-icon ion-ios-arrow-up"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawTriangleEmpty()"></button>' +

                     //Line
                     '<button class="button button-icon ion-minus-round"' +
                     'style="color: {{brushcolor}}; font-size: 36.5px"' +
                     'ng-click="drawLine()"></button>' +
                     '</div></ion-content></ion-popover-view>';

    //Draws options in the popover
    var drawPopup = '<ion-popover-view style="height:110px;"><ion-content><div>' +
                     '<button class="button button-icon"' +
                     'style="font-size: 25px"' +
                     'ng-repeat="brushsize in brushSizeTab"' +
                     'ng-click="changeBrushSize(brushsize.brushSize)">{{brushsize.brushSize}}</button>' +
                     '</div></ion-content></ion-popover-view>';

    //Text options in the popover
    //Normal 
    var textPopup = '<ion-popover-view style="height:70px;"><ion-content><div>' +
                     '<button class="button button-icon"' +
                     'style="font-size: 25px"' +
                     'ng-click="addText(&quot;normal&quot;)">Text</button>' +

                     //Italic
                     '<button class="button button-icon"' +
                     'style="font-size: 25px; font-style: italic"' +
                     'ng-click="addText(&quot;italic&quot;)">Italic</button>' +

                     //Bold
                     '<button class="button button-icon"' +
                     'style="font-size: 25px"' +
                     'ng-click="addText(&quot;bold&quot;)"><b>Bold</b></button>' +
                     '</div></ion-content></ion-popover-view>';

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
    /*$scope.drawingMode = function () {
        if (canvas.isDrawingMode == true)
            canvas.isDrawingMode = false; //Exit drawing mode
        else {
            canvas.off('mouse:down');
            canvas.isDrawingMode = true; //Enter drawing mode
        }
    }*/

    //Get "colorsPopup" html template
    $scope.popoverColors = $ionicPopover.fromTemplate(colorsPopup, {
        scope: $scope
    });

    //Get "shapesPopup" html template
    $scope.popoverShapes = $ionicPopover.fromTemplate(shapesPopup, {
        scope: $scope
    });

    //Get "drawPopup" html template
    $scope.popoverDraw = $ionicPopover.fromTemplate(drawPopup, {
        scope: $scope
    });

    //Get "textPopup" html template
    $scope.popoverText = $ionicPopover.fromTemplate(textPopup, {
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

    //Show draw popover
    $scope.openDrawPopover = function ($event) {
        $scope.popoverDraw.show($event);
    };

    //Show text popover
    $scope.openTextPopover = function ($event) {
        $scope.popoverText.show($event);
    }

    //List of colors
    $scope.colorTab = [
        { colorHex: "#F44336" },
        { colorHex: "#E91E63" },
        { colorHex: "#9C27B0" },
        { colorHex: "#673AB7" },
        { colorHex: "#3F51B5" },
        { colorHex: "#2196F3" },
        { colorHex: "#03A9F4" },
        { colorHex: "#00BCD4" },
        { colorHex: "#009688" },
        { colorHex: "#4CAF50" },
        { colorHex: "#8BC34A" },
        { colorHex: "#CDDC39" },
        { colorHex: "#FFEB3B" },
        { colorHex: "#FFC107" },
        { colorHex: "#FF9800" },
        { colorHex: "#FF5722" },
        { colorHex: "#795548" },
        { colorHex: "#607D8B" },
        { colorHex: "#FFFFFF" },
        { colorHex: "#EEEEEE" },
        { colorHex: "#BDBDBD" },
        { colorHex: "#9E9E9E" },
        { colorHex: "#757575" },
        { colorHex: "#424242" },
        { colorHex: "#000000" }
    ]

    $scope.brushSizeTab = [
        { brushSize: "0.5" },
        { brushSize: "1" },
        { brushSize: "1.5" },
        { brushSize: "2" },
        { brushSize: "2.5" },
        { brushSize: "3" },
        { brushSize: "4" },
        { brushSize: "5" },
    ]

    //Change brush color
    $scope.changeBrushColor = function (colorChosen) {
        canvas.freeDrawingBrush.color = colorChosen;
        $scope.brushcolor = colorChosen; //Set brush color to the new color chosen
        $scope.popoverColors.hide();
    }

    //Change brush size
    $scope.changeBrushSize = function (brushSize) {
        canvas.freeDrawingBrush.width = brushSize;
        canvas.off('mouse:down');
        canvas.isDrawingMode = true;
        $ionicScrollDelegate.freezeAllScrolls(true);
        $scope.popoverDraw.hide();
    }

    //Draw Rectangle shape
    $scope.drawRect = function () {
        var mouse_pos = { x: 0, y: 0 };

        $ionicScrollDelegate.freezeAllScrolls(true);
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

    //Draw Rectangle empty shape
    $scope.drawRectEmpty = function () {
        var mouse_pos = { x: 0, y: 0 };

        $ionicScrollDelegate.freezeAllScrolls(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Rect({
                top: mouse_pos.y - 15,
                left: mouse_pos.x - 20,
                width: 40,
                height: 30,
                stroke: $scope.brushcolor,
                fill: '',
                selectable: true,
                evented: false
            }));
        });
        $scope.popoverShapes.hide();
    }

    //Draw Ellipse shape
    $scope.drawEllipse = function () {
        var mouse_pos = { x: 0, y: 0 };

        $ionicScrollDelegate.freezeAllScrolls(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Ellipse({
                top: mouse_pos.y - 30,
                left: mouse_pos.x - 40,
                fill: $scope.brushcolor,
                rx: 40, ry: 30,
                selectable: true,
                evented: false
            }));
        });
        $scope.popoverShapes.hide();
    }

    //Draw Ellipse empty shape
    $scope.drawEllipseEmpty = function () {
        var mouse_pos = { x: 0, y: 0 };

        $ionicScrollDelegate.freezeAllScrolls(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Ellipse({
                top: mouse_pos.y - 30,
                left: mouse_pos.x - 40,
                stroke: $scope.brushcolor,
                fill: '',
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

        $ionicScrollDelegate.freezeAllScrolls(true);
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

    //Draw Triangle empty shape
    $scope.drawTriangleEmpty = function () {
        var mouse_pos = { x: 0, y: 0 };

        $ionicScrollDelegate.freezeAllScrolls(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Triangle({
                top: mouse_pos.y - 15,
                left: mouse_pos.x - 15,
                width: 30,
                height: 30,
                stroke: $scope.brushcolor,
                fill: '',
                selectable: true,
                evented: false
            }));
        });
        $scope.popoverShapes.hide();
    }

    //Draw Diamond shape
    $scope.drawDiamond = function () {
        var mouse_pos = { x: 0, y: 0 };

        $ionicScrollDelegate.freezeAllScrolls(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Polygon(
                [
                    { x: 25, y: 0 },
                    { x: 50, y: 50 },
                    { x: 25, y: 100 },
                    { x: 0, y: 50 }
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

    //Draw Diamond empty shape
    $scope.drawDiamondEmpty = function () {
        var mouse_pos = { x: 0, y: 0 };

        $ionicScrollDelegate.freezeAllScrolls(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) {
            mouse_pos = canvas.getPointer(e.e);
            canvas.add(new fabric.Polygon(
                [
                    { x: 25, y: 0 },
                    { x: 50, y: 50 },
                    { x: 25, y: 100 },
                    { x: 0, y: 50 }
                ],
                {
                    top: mouse_pos.y - 50,
                    left: mouse_pos.x - 25,
                    stroke: $scope.brushcolor,
                    fill: '',
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
        var Started = false;
        var StartX = 0;
        var StartY = 0;

        $ionicScrollDelegate.freezeAllScrolls(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        canvas.observe('mouse:down', function (e) { LineMouseDown(e); });
        canvas.observe('mouse:up', function (e) { LineMouseUp(e); });

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

    $scope.addText = function (textStyle) {
        //Prevent user from being in drawing mode while adding text
        canvas.isDrawingMode = false;
        $ionicScrollDelegate.freezeAllScrolls(true);

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
            var mouse_pos = { x: 0, y: 0 };

            canvas.off('mouse:down');
            canvas.isDrawingMode = false;
            canvas.observe('mouse:down', function (e) {
                mouse_pos = canvas.getPointer(e.e);

                canvas.add(new fabric.Text(input, {
                    top: mouse_pos.y - 15,
                    left: mouse_pos.x - 15,
                    fontFamily: 'Helvetica',
                    fontStyle: textStyle,
                    fill: $scope.brushcolor, //Use the current selected color
                    selectable: true, //Make the text draggable
                    evented: false
                }));
            });
            $scope.popoverText.hide();
        });
    }

    $scope.moveOn = function (moveOn) {
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        $ionicScrollDelegate.freezeAllScrolls(false);
    }

    /*$scope.zoomIn = function () {
        var scale_factor = 1.2;
        var objects = canvas.getObjects();

        for (var i in objects) {
            var scaleX = objects[i].scaleX;
            var scaleY = objects[i].scaleY;
            var left = objects[i].left;
            var top = objects[i].top;

            var tempScaleX = scaleX * scale_factor;
            var tempScaleY = scaleY * scale_factor;
            var tempLeft = left * scale_factor;
            var tempTop = top * scale_factor;

            objects[i].scaleX = tempScaleX;
            objects[i].scaleY = tempScaleY;
            objects[i].left = tempLeft;
            objects[i].top = tempTop;

            objects[i].setCoords();
        }
        canvas.renderAll();
    }

    $scope.zoomOut = function () {
        var scale_factor = 1.2;
        var objects = canvas.getObjects();

        for (var i in objects) {
            var scaleX = objects[i].scaleX;
            var scaleY = objects[i].scaleY;
            var left = objects[i].left;
            var top = objects[i].top;

            var tempScaleX = scaleX * (1 / scale_factor);
            var tempScaleY = scaleY * (1 / scale_factor);
            var tempLeft = left * (1 / scale_factor);
            var tempTop = top * (1 / scale_factor);

            objects[i].scaleX = tempScaleX;
            objects[i].scaleY = tempScaleY;
            objects[i].left = tempLeft;
            objects[i].top = tempTop;

            objects[i].setCoords();
        }
        canvas.renderAll();
    }*/
})