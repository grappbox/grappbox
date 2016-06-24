/*
    Summary: WHITEBOARD Controller
*/

angular.module('GrappBox.controllers')

// WHITEBOARD
.controller('WhiteboardCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicPopover, $ionicPopup, $ionicScrollDelegate, $interval, Whiteboard) {

    $scope.projectId = $stateParams.projectId;
    $scope.whiteboardName = $stateParams.whiteboardName;
    $scope.whiteboardId = $stateParams.whiteboardId;
    console.log("PROJECTID = " + $scope.projectId);
    console.log("WHITEBOARDID = " + $scope.whiteboardId);

    var width = 3840; //3840;
    var height = 2160; //2160;

    var canvas = new fabric.Canvas('canvasWhiteboard');

    canvas.selection = false;
    fabric.Object.prototype.selectable = false; //Prevent drawing objects to be draggable or clickable

    //Saving by both manners prevents from errors
    canvas.setHeight(height);
    canvas.setWidth(width);
    canvas.width = width;
    canvas.height = height;

    canvas.isDrawingMode = false;
    $scope.brushSize = 2; //Set brush size to by default
    canvas.freeDrawingBrush.width = $scope.brushSize; //Size of the drawing brush
    $scope.brushcolor = '#000000'; //Set brushcolor to black at the beginning

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

    // Cancel interval when quitting view
    /*$scope.$on("$ionicView.leave", function () {
        $interval.cancel(myInterval);
    });

    var myInterval = $interval(function () {
        $scope.OpenWhiteboard();
    }, 3000);*/

    // Button move
    $scope.moveOn = function (moveOn) {
        canvas.off('mouse:down');
        canvas.off('mouse:up');
        canvas.isDrawingMode = false;
        $ionicScrollDelegate.freezeScroll(false);
    }

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
        $ionicScrollDelegate.freezeScroll(true);
        $scope.popoverDraw.hide();
        //Send a handwriting to API
        canvas.on('path:created', function (e) {
            $scope.PushOnWhiteboard("HANDWRITE", $scope.brushcolor, "", $scope.brushSize, "", "", "", e.path.canvas.freeDrawingBrush._points);
        });
    }

    /*
    ** SHAPES
    */
    //Draw Rectangle shape
    $scope.drawRect = function (isTransparent) {
        var started = false;
        var rect;
        var mouse_pos_init;
        var mouse_pos;
        var real_init;
        var width;
        var height;

        $ionicScrollDelegate.freezeScroll(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;

        canvas.on('mouse:down', function (option) {
            started = true;
            mouse_pos_init = canvas.getPointer(option.e);

            rect = new fabric.Rect({
                top: mouse_pos_init.y,
                left: mouse_pos_init.x,
                width: 0,
                height: 0,
                fill: isTransparent ? 'transparent' : $scope.brushcolor,
                stroke: $scope.brushcolor,
            });
            canvas.add(rect);
        });

        canvas.on('mouse:move', function (option) {
            if (!started) return;
            mouse_pos = canvas.getPointer(option.e);
            real_init = { x: 0, y: 0 };
            real_init.x = mouse_pos_init.x;
            real_init.y = mouse_pos_init.y;
            width = mouse_pos.x - mouse_pos_init.x;
            height = mouse_pos.y - mouse_pos_init.y;
            // We take abs to always show a positive rectangle (or fabric crash)
            if (width < 0) {
                real_init.x = mouse_pos_init.x - Math.abs(width);
            }
            if (height < 0) {
                real_init.y = mouse_pos_init.y - Math.abs(height);
            }
            rect.set({
                'top': real_init.y,
                'left': real_init.x,
                'width': width,
                'height': height
            });
            canvas.renderAll();
        });

        canvas.on('mouse:up', function (option) {
            started = false;
            var positionStart = { "x": mouse_pos_init.x, "y": mouse_pos_init.y };
            if (width > 0)
                real_init.x = mouse_pos.x;
            if (height > 0)
                real_init.y = mouse_pos.y;
            var positionEnd = { "x": real_init.x, "y": real_init.y };
            $scope.PushOnWhiteboard("RECTANGLE", $scope.brushcolor, $scope.brushcolor, $scope.brushSize, positionStart, positionEnd);
            //canvas.remove(rect);
        });
        $scope.popoverShapes.hide();
    }

    //Draw Ellipse shape
    $scope.drawEllipse = function (isTransparent) {
        var started = false;
        var ellipse;
        var mouse_pos_init;
        var mouse_pos;
        var real_init;
        var width;
        var height;

        $ionicScrollDelegate.freezeScroll(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;

        canvas.on('mouse:down', function (option) {
            started = true;
            mouse_pos_init = canvas.getPointer(option.e);
            ellipse = new fabric.Ellipse({
                top: mouse_pos_init.y,
                left: mouse_pos_init.x,
                fill: isTransparent ? 'transparent' : $scope.brushcolor,
                stroke: $scope.brushcolor,
                rx: 0,
                ry: 0
            });
            canvas.add(ellipse);
        });

        canvas.on('mouse:move', function (option) {
            if (!started) return;
            mouse_pos = canvas.getPointer(option.e);
            real_init = { x: 0, y: 0 };
            real_init.x = mouse_pos_init.x;
            real_init.y = mouse_pos_init.y;
            width = mouse_pos.x - mouse_pos_init.x;
            height = mouse_pos.y - mouse_pos_init.y;
            // We take abs to always show a positive ellipse (or fabric crash)
            if (width < 0) {
                real_init.x = mouse_pos_init.x - Math.abs(width);
            }
            if (height < 0) {
                real_init.y = mouse_pos_init.y - Math.abs(height);
            }
            ellipse.set({
                'top': real_init.y,
                'left': real_init.x,
                'rx': Math.abs(width / 2),
                'ry': Math.abs(height / 2)
            });
            canvas.renderAll();
        });

        canvas.on('mouse:up', function (option) {
            started = false;
            var positionStart = { "x": mouse_pos_init.x, "y": mouse_pos_init.y };
            if (width > 0)
                real_init.x = mouse_pos.x;
            if (height > 0)
                real_init.y = mouse_pos.y;
            var positionEnd = { "x": real_init.x, "y": real_init.y };
            var radius = { "x": Math.abs(real_init.x - mouse_pos_init.x) / 2, "y": Math.abs(real_init.y - mouse_pos_init.y) / 2 };
            $scope.PushOnWhiteboard("ELLIPSE", $scope.brushcolor, $scope.brushcolor, $scope.brushSize, positionStart, positionEnd, radius);
        });
        $scope.popoverShapes.hide();
    }

    //Draw Diamond shape
    $scope.drawDiamond = function (isTransparent) {
        var started = false;
        var diamond;
        var mouse_pos_init;
        var mouse_pos;
        var width;
        var height;
        var points;

        $ionicScrollDelegate.freezeScroll(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;

        canvas.on('mouse:down', function (option) {
            started = true;
            mouse_pos_init = canvas.getPointer(option.e);
            points = [
                { x: 1, y: 0 },
                { x: 2, y: 2 },
                { x: 1, y: 4 },
                { x: 0, y: 2 }];
            diamond = new fabric.Polygon(points, {
                top: mouse_pos_init.y,
                left: mouse_pos_init.x,
                fill: isTransparent ? 'transparent' : $scope.brushcolor,
                hasBorders: true,
                hasControls: false,
                hasRotatingPoint: false,
                lockMovementX: true,
                lockMovementY: true,
            });
            canvas.add(diamond);
        });

        canvas.on('mouse:move', function (option) {
            if (!started) return;
            mouse_pos = canvas.getPointer(option.e);
            width = mouse_pos.x - mouse_pos_init.x;
            height = mouse_pos.y - mouse_pos_init.y;
            points = [
                { x: width / 2, y: 0 },
                { x: mouse_pos.x - mouse_pos_init.x, y: height / 2 },
                { x: width / 2, y: mouse_pos.y - mouse_pos_init.y },
                { x: 0, y: height / 2 }
            ];
            diamond.set({ points: points });
            canvas.renderAll();
        });

        canvas.on('mouse:up', function (option) {
            started = false;
            var positionStart = { "x": mouse_pos_init.x, "y": mouse_pos_init.y };
            var positionEnd = { "x": mouse_pos.x, "y": mouse_pos.y };
            $scope.PushOnWhiteboard("DIAMOND", $scope.brushcolor, $scope.brushcolor, $scope.brushSize, positionStart, positionEnd);

        });
        $scope.popoverShapes.hide();
    }

    //Draw Line
    $scope.drawLine = function () {
        var started = false;
        var line;
        var mouse_pos_init;
        var mouse_pos;
        var points;

        $ionicScrollDelegate.freezeScroll(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;

        canvas.on('mouse:down', function (option) {
            started = true;
            mouse_pos_init = canvas.getPointer(option.e);
            points = [mouse_pos_init.x, mouse_pos_init.y, mouse_pos_init.x, mouse_pos_init.y];
            line = new fabric.Line(points, {
                stroke: $scope.brushcolor,
                strokeWidth: $scope.brushSize
            });
            canvas.add(line);
        });

        canvas.on('mouse:move', function (option) {
            if (!started) return;
            mouse_pos = canvas.getPointer(option.e);
            line.set({
                x2: mouse_pos.x,
                y2: mouse_pos.y
            });
            canvas.renderAll();
        });

        canvas.on('mouse:up', function (e) {
            started = false;
            var positionStart = { "x": mouse_pos_init.x, "y": mouse_pos_init.y };
            var positionEnd = { "x": mouse_pos.x, "y": mouse_pos.y };
            $scope.PushOnWhiteboard("LINE", $scope.brushcolor, "", $scope.brushSize, positionStart, positionEnd);
        });
        $scope.popoverShapes.hide();
    }

    //Erase an object
    $scope.undoLastObject = function () {
        var started = false;
        var mouse_pos;

        $ionicScrollDelegate.freezeScroll(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;

        canvas.on('mouse:down', function (option) {
            started = true;
            mouse_pos = canvas.getPointer(option.e);
        });

        canvas.on('mouse:up', function (e) {
            started = false;
            console.log("mouse_pos.x" + mouse_pos.x);
            console.log("mouse_pos.y" + mouse_pos.y);
            $scope.DeleteObject(mouse_pos);
        });
        $scope.popoverShapes.hide();

        //var canvas_objects = canvas._objects; //Get all objects
        //var last = canvas_objects[canvas_objects.length - 1]; //Select the last object
        //canvas.remove(last); //Remove the last object
        //canvas.renderAll();
    }

    // Add a text
    $scope.addText = function () {
        //Prevent user from being in drawing mode while adding text
        canvas.isDrawingMode = false;
        $ionicScrollDelegate.freezeScroll(true);

        $scope.textAdd = {};
        $scope.textAdd.fontWeight = 'normal';
        $scope.textAdd.fontStyle = 'normal';
        $scope.textAdd.fontSize = 30;

        //Ionic popup used to prompt user to enter text
        var myPopup = $ionicPopup.show({
            templateUrl: 'addTextToWhiteboard.html',
            title: 'Enter Text',
            subTitle: '',
            scope: $scope,
            buttons: [{
                text: 'Cancel'
            },
            {
                text: '<b>Save</b>',
                type: 'button-stable',
                onTap: function (e) {
                    if (!$scope.textAdd.input)
                        e.preventDefault();
                    return $scope.textAdd.input;
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
                    top: mouse_pos.y,
                    left: mouse_pos.x,
                    fontFamily: 'Roboto',
                    fontWeight: $scope.textAdd.fontWeight,
                    fontStyle: $scope.textAdd.fontStyle,
                    fontSize: $scope.textAdd.fontSize,
                    fill: $scope.brushcolor, //Use the current selected color
                    selectable: false, //Make the text draggable
                    evented: false
                }));
                var positionStart = { "x": mouse_pos.x, "y": mouse_pos.y };
                var positionEnd = { "x": mouse_pos.x, "y": mouse_pos.y };
                $scope.PushOnWhiteboard("TEXT", $scope.brushcolor, "", "", positionStart, positionEnd, "", "", input, $scope.textAdd.fontSize, $scope.textAdd.fontStyle == "italic" ? true : false, $scope.textAdd.fontWeight == "bold" ? true : false);
            });
            //$scope.popoverText.hide();
        });
    }

    /*
    ** API REQUESTS
    */

    /*
    ** Delete an object on whiteboard
    ** Method: PUT
    */
    $scope.deleteObjectData = {};
    $scope.DeleteObject = function (mouse_pos) {
        //$rootScope.showLoading();
        Whiteboard.DeleteObject().update({
            data: {
                whiteboardId: $scope.whiteboardId,
                token: $rootScope.userDatas.token,
                center: { x: mouse_pos.x, y: mouse_pos.y },
                radius: 10
            }
        }).$promise
            .then(function (data) {
                console.log('Delete object successful !');
                console.log(data.data);
                $scope.deleteObjectData = data.data.array;
            })
            .catch(function (error) {
                console.error('Delete object failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Open a whiteboard
    ** Method: GET
    */
    $scope.openWhiteboardData = {};
    $scope.objects = {};
    $scope.OpenWhiteboard = function () {
        //$rootScope.showLoading();
        Whiteboard.Open().get({
            id: $scope.whiteboardId,
            token: $rootScope.userDatas.token,
        }).$promise
            .then(function (data) {
                console.log('Open whiteboard successful !');
                console.log(data.data);
                $scope.openWhiteboardData = data.data;
                $scope.objects = data.data.content;
                $scope.addOnWhiteboard($scope.objects);
            })
            .catch(function (error) {
                console.error('Open whiteboards failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                //$scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    $scope.addOnWhiteboard = function (obj) {
        canvas.clear();
        for (var i = 0; i < obj.length; i++) {
            if (obj[i].object.type == "RECTANGLE") {
                var rect = new fabric.Rect({
                    top: obj[i].object.positionStart.y > obj[i].object.positionEnd.y ? obj[i].object.positionEnd.y : obj[i].object.positionStart.y,
                    left: obj[i].object.positionStart.x > obj[i].object.positionEnd.x ? obj[i].object.positionEnd.x : obj[i].object.positionStart.x,
                    width: obj[i].object.positionEnd.x - obj[i].object.positionStart.x,
                    height: obj[i].object.positionEnd.y - obj[i].object.positionStart.y,
                    fill: obj[i].object.background,
                    stroke: obj[i].object.color,
                    selectable: false,
                    evented: false
                });
                canvas.add(rect);
            }
            else if (obj[i].object.type == "ELLIPSE") {
                var ellipse = new fabric.Ellipse({
                    top: obj[i].object.positionStart.y > obj[i].object.positionEnd.y ? obj[i].object.positionEnd.y : obj[i].object.positionStart.y,
                    left: obj[i].object.positionStart.x > obj[i].object.positionEnd.x ? obj[i].object.positionEnd.x : obj[i].object.positionStart.x,
                    fill: obj[i].object.background,
                    stroke: obj[i].object.color,
                    rx: obj[i].object.radius.x,
                    ry: obj[i].object.radius.y,
                    selectable: false,
                    evented: false
                });
                canvas.add(ellipse);
            }
            else if (obj[i].object.type == "LINE") {
                var points = [
                    obj[i].object.positionStart.x,
                    obj[i].object.positionStart.y,
                    obj[i].object.positionEnd.x,
                    obj[i].object.positionEnd.y];
                var line = new fabric.Line(points, {
                    stroke: obj[i].object.color,
                    strokeWidth: obj[i].object.lineWeight,
                    selectable: false,
                    evented: false
                });
                canvas.add(line);
            }
            else if (obj[i].object.type == "HANDWRITE") {
                var handPoints = "";
                handPoints += "M ";
                handPoints += obj[i].object.points[0].x;
                handPoints += " ";
                handPoints += obj[i].object.points[0].y;
                for (var j = 1; j < obj[i].object.points.length; j++) {
                    handPoints += " L ";
                    handPoints += obj[i].object.points[j].x;
                    handPoints += " ";
                    handPoints += obj[i].object.points[j].y;
                }
                var handwrite = new fabric.Path(handPoints, {
                    fill: false,
                    stroke: obj[i].object.color,
                    strokeWidth: obj[i].object.lineWeight,
                    selectable: false,
                    evented: false
                });
                canvas.add(handwrite);
            }
            else if (obj[i].object.type == "DIAMOND") {
                var width = obj[i].object.positionEnd.x - obj[i].object.positionStart.x;
                var height = obj[i].object.positionEnd.y - obj[i].object.positionStart.y;
                var points = [
                    { x: width / 2, y: 0 },
                    { x: obj[i].object.positionEnd.x - obj[i].object.positionStart.x, y: height / 2 },
                    { x: width / 2, y: obj[i].object.positionEnd.y - obj[i].object.positionStart.y },
                    { x: 0, y: height / 2 }
                ]
                var diamond = new fabric.Polygon(points, {
                    top: obj[i].object.positionStart.y > obj[i].object.positionEnd.y ? obj[i].object.positionEnd.y : obj[i].object.positionStart.y,
                    left: obj[i].object.positionStart.x > obj[i].object.positionEnd.x ? obj[i].object.positionEnd.x : obj[i].object.positionStart.x,
                    fill: obj[i].object.background,
                    stroke: obj[i].object.color,
                    hasBorders: true,
                    hasControls: false,
                    hasRotatingPoint: false,
                    lockMovementX: true,
                    lockMovementY: true,
                    selectable: false,
                    evented: false
                });
                canvas.add(diamond);
            }
            else if (obj[i].object.type == "TEXT") {
                canvas.add(new fabric.Text(obj[i].object.text, {
                    top: obj[i].object.positionStart.y,
                    left: obj[i].object.positionStart.x,
                    fontFamily: 'Roboto',
                    fontStyle: obj[i].object.isItalic == true ? "italic" : "normal",
                    fontWeight: obj[i].object.isBold == true ? "bold" : "normal",
                    fontSize: obj[i].object.size,
                    fill: obj[i].object.color,
                    selectable: false,
                    evented: false
                }));
            }
        }
        canvas.renderAll();
    }

    $scope.OpenWhiteboard();

    /*
    ** Push a modification on whiteboard
    ** Method: PUT
    */
    $scope.pushOnWhiteboardData = {};
    $scope.PushOnWhiteboard = function (type, color, background, lineWeight, positionStart, positionEnd, radius, points, text, size, isItalic, isBold) {
        //$rootScope.showLoading();
        canvas.off('mouse:up');
        Whiteboard.Push().update({
            id: $scope.whiteboardId,
            data: {
                id: $scope.whiteboardId,
                token: $rootScope.userDatas.token,
                modification: "add",
                object: {
                    type: type,
                    color: color,
                    background: background ? background : "",
                    lineWeight: lineWeight ? lineWeight : "",
                    positionStart: positionStart ? positionStart : "",
                    positionEnd: positionEnd ? positionEnd : "",
                    points: points ? points : "",
                    radius: radius ? radius : "",
                    text: text ? text : "",
                    size: size ? size : "",
                    isItalic: isItalic ? isItalic : "",
                    isBold: isBold ? isBold : ""
                }
            }
        }).$promise
            .then(function (data) {
                console.log('Push on whiteboard successful !');
                console.log(data.data);
                $scope.pushOnWhiteboardData = data.data;
            })
            .catch(function (error) {
                console.error('Push on whiteboard failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** Pull modifications on whiteboard
    ** Method: POST
    */
    $scope.pullFromWhiteboardData = {};
    $scope.pullFromWhiteboard = function () {
        //$rootScope.showLoading();
        Whiteboard.Pull().save({
            data: {
                id: $scope.whiteboardId,
                token: $rootScope.userDatas.token,
                lastUpdate: ""
            }
        }).$promise
            .then(function (data) {
                console.log('List whiteboards successful !');
                console.log(data.data.array);
                $scope.pullFromWhiteboardData = data.data.array;
            })
            .catch(function (error) {
                console.error('List whiteboards failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                //$rootScope.hideLoading();
            })
    }

    /*
    ** POPOVERS
    */
    //Get "colorsPopup" html templateUrl in whiteboard.html
    $scope.popoverColors = $ionicPopover.fromTemplateUrl('colorsPopup.html', {
        scope: $scope
    }).then(function (popoverColors) {
        $scope.popoverColors = popoverColors;
    });

    //Get "shapesPopup" html templateUrl in whiteboard.html
    $scope.popoverShapes = $ionicPopover.fromTemplateUrl("shapesPopup.html", {
        scope: $scope
    }).then(function (popoverShapes) {
        $scope.popoverShapes = popoverShapes;
    });

    //Get "drawPopup" html templateUrl in whiteboard.html
    $scope.popoverDraw = $ionicPopover.fromTemplateUrl("drawPopup.html", {
        scope: $scope
    }).then(function (popoverDraw) {
        $scope.popoverDraw = popoverDraw;
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

})