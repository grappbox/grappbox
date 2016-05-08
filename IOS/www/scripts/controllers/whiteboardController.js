/*
    Summary: WHITEBOARD LIST & WHITEBOARD Controllers
*/

angular.module('GrappBox.controllers')

// WHITEBOARD LIST
.controller('WhiteboardsCtrl', function ($scope, $rootScope, $state, $stateParams, $ionicPopup, Whiteboard) {

    // UNCOMMENT AFTER REDO THE PROJECT SELECTION BEFORE DASHBOARD
    //$scope.projectId = $stateParams.projectId;
    $scope.projectId = 18;

    //Refresher
    $scope.doRefresh = function () {
        $scope.ListWhiteboards();
        console.log("View refreshed !");
    }

    $scope.whiteboardName = {};

    /*
    ** List all whiteboards
    ** Method: GET
    */
    $scope.whiteboardsInfo = {};
    $scope.ListWhiteboards = function () {
        $rootScope.showLoading();
        Whiteboard.List().get({
            token: $rootScope.userDatas.token,
            projectId: $scope.projectId
        }).$promise
            .then(function (data) {
                console.log('List whiteboards successful !');
                console.log(data.data.array);
                $scope.whiteboardsInfo = data.data.array;
            })
            .catch(function (error) {
                console.error('List whiteboards failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
            })
    }
    $scope.ListWhiteboards();

    /*
    ** Create a new whiteboard
    ** Method: POST
    */
    $scope.createWhiteboardData = {};
    $scope.CreateWhiteboard = function () {
        $rootScope.showLoading();
        Whiteboard.Create().save({
            data: {
                token: $rootScope.userDatas.token,
                projectId: $scope.projectId,
                whiteboardName: $scope.whiteboardName.name
            }
        }).$promise
            .then(function (data) {
                console.log('Create whiteboard successful !');
                console.log(data.data);
                $scope.createWhiteboardData = data.data;
            })
            .catch(function (error) {
                console.error('Create whiteboard failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
                $scope.ListWhiteboards();
            })
    }

    // Enter whiteboard name popup
    $scope.showNameWhiteboardPopup = function () {
        var myPopup = $ionicPopup.show({
            template: '<input type="text" placeholder="Name for whiteboard" ng-model="whiteboardName.name">',
            title: 'Choose Name',
            scope: $scope,
            buttons: [
              { text: 'Cancel' },
              {
                  text: '<b>Save</b>',
                  type: 'button-positive',
                  onTap: function (e) {
                      if (!$scope.whiteboardName.name) {
                          // Don't allow the user to close unless he enters file password
                          e.preventDefault();
                      } else {
                          return $scope.whiteboardName;
                      }
                  }
              }]
        })
        .then(function (res) {
            if (res && res.name) {
                if ($scope.whiteboardName.name != 'undefined')
                    $scope.CreateWhiteboard();
            }
        });
    };

    /*
    ** Delete a whiteboard
    ** Method: DELETE
    */
    $scope.DeleteWhiteboard = function (whiteboard) {
        $rootScope.showLoading();
        Whiteboard.Delete().delete({
            token: $rootScope.userDatas.token,
            id: whiteboard.id
        }).$promise
            .then(function (data) {
                console.log('Delete whiteboard successful !');
                console.log(data.info);
            })
            .catch(function (error) {
                console.error('Delete whiteboard failed ! Reason: ' + error.status + ' ' + error.statusText);
                console.error(error);
            })
            .finally(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $rootScope.hideLoading();
                $scope.ListWhiteboards();
            })
    }
})

// WHITEBOARD
.controller('WhiteboardCtrl', function ($scope, $ionicPopover, $ionicPopup, $ionicScrollDelegate) {
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
    canvas.freeDrawingBrush.width = 6; //Size of the drawing brush
    $scope.brushcolor = '#000000'; //Set brushcolor to black at the beginning

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
        $ionicScrollDelegate.freezeScroll(true);
        $scope.popoverDraw.hide();
    }

    //Draw Rectangle shape
    $scope.drawRect = function (isTransparent) {
        var started = false;
        var rect;
        var mouse_pos_init;

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
            var mouse_pos = canvas.getPointer(option.e);
            rect.set({ 'width': mouse_pos.x - mouse_pos_init.x, 'height': mouse_pos.y - mouse_pos_init.y });
            canvas.renderAll();
        });

        canvas.on('mouse:up', function (option) {
            started = false;
        });
        $scope.popoverShapes.hide();
    }

    //Draw Ellipse shape
    $scope.drawEllipse = function (isTransparent) {
        var started = false;
        var ellipse;
        var mouse_pos_init;

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
            var mouse_pos = canvas.getPointer(option.e);
            ellipse.set({ 'rx': mouse_pos.x - mouse_pos_init.x, 'ry': mouse_pos.y - mouse_pos_init.y });
            canvas.renderAll();
        });

        canvas.on('mouse:up', function (option) {
            started = false;
        });
        $scope.popoverShapes.hide();
    }

    //Draw Triangle shape
    $scope.drawTriangle = function (isTransparent) {
        var started = false;
        var triangle;
        var mouse_pos_init;

        $ionicScrollDelegate.freezeScroll(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;

        canvas.on('mouse:down', function (option) {
            started = true;
            mouse_pos_init = canvas.getPointer(option.e);
            triangle = new fabric.Triangle({
                top: mouse_pos_init.y,
                left: mouse_pos_init.x,
                width: 0.1,
                height: 0.1,
                fill: isTransparent ? 'transparent' : $scope.brushcolor,
                stroke: $scope.brushcolor
            });
            canvas.add(triangle);
        });

        canvas.on('mouse:move', function (option) {
            if (!started) return;
            var mouse_pos = canvas.getPointer(option.e);
            triangle.set({ 'width': mouse_pos.x - mouse_pos_init.x, 'height': mouse_pos.y - mouse_pos_init.y });
            canvas.renderAll();
        });

        canvas.on('mouse:up', function (option) {
            started = false;
        });
        $scope.popoverShapes.hide();
    }

    //Draw Diamond shape
    /*$scope.drawDiamond = function (isTransparent) {
        var started = false;
        var diamond;
        var mouse_pos_init;

        $ionicScrollDelegate.freezeScroll(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;

        canvas.on('mouse:down', function (option) {
            started = true;
            mouse_pos_init = canvas.getPointer(option.e);
            diamond = new fabric.Polygon(
                [*/
    /*{ x: 25, y: 0 },
    { x: 50, y: 50 },
    { x: 25, y: 100 },
    { x: 0, y: 50 }*/
    /*{ x: 0, y: 0 },
    { x: 0, y: 0 },
    { x: 0, y: 0 },
    { x: 0, y: 0 }
],
{
    top: mouse_pos_init.y,
    left: mouse_pos_init.x,
    fill: isTransparent ? 'transparent' : $scope.brushcolor,
    /*hasBorders: false,
    hasControls: false,
    hasRotatingPoint: false,
    lockMovementX: true,
    lockMovementY: true,
});
canvas.add(diamond);
});

canvas.on('mouse:move', function (option) {
if (!started) return;
var mouse_pos = canvas.getPointer(option.e);
diamond.set({ 'x': mouse_pos.x - mouse_pos_init.x, 'y': mouse_pos.y - mouse_pos_init.y });
canvas.renderAll();
});

canvas.on('mouse:up', function (option) {
started = false;
});
$scope.popoverShapes.hide();
}*/

    //Draw Line
    $scope.drawLine = function () {
        var started = false;
        var line;

        $ionicScrollDelegate.freezeScroll(true);
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;

        canvas.on('mouse:down', function (option) {
            started = true;
            var mouse_pos = canvas.getPointer(option.e);
            var points = [mouse_pos.x, mouse_pos.y, mouse_pos.x, mouse_pos.y];
            line = new fabric.Line(points, {
                stroke: $scope.brushcolor,
                strokeWidth: 6
            });
            canvas.add(line);
        });

        canvas.on('mouse:move', function (option) {
            if (!started) return;
            var mouse_pos = canvas.getPointer(option.e);
            line.set({ x2: mouse_pos.x, y2: mouse_pos.y });
            canvas.renderAll();
        });

        canvas.on('mouse:up', function (e) {
            started = false;
        });
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
        $ionicScrollDelegate.freezeScroll(true);

        $scope.textAdd = {};

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
                    top: mouse_pos.y - 15,
                    left: mouse_pos.x - 15,
                    fontFamily: 'Roboto',
                    fontStyle: function () {
                        if (!$scope.textAdd.fontStyle)
                            return 'normal'; //Default style
                        return $scope.textAdd.fontStyle;
                    },
                    fontSize: function () {
                        if (!$scope.textAdd.fontSize)
                            return 30; //Default size
                        return $scope.textAdd.fontSize;
                    },
                    fill: $scope.brushcolor, //Use the current selected color
                    selectable: true, //Make the text draggable
                    evented: false
                }));
            });
            //$scope.popoverText.hide();
        });
    }

    $scope.moveOn = function (moveOn) {
        canvas.off('mouse:down');
        canvas.isDrawingMode = false;
        $ionicScrollDelegate.freezeScroll(false);
    }
})