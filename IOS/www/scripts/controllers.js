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
.controller('WhiteboardCtrl', function ($scope) {
})

// SETTINGS
.controller('SettingsCtrl', function ($scope) {
})