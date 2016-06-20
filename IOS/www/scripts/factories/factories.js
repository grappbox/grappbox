/*
    Summary: API RESTful page using ngResource and factories
*/

angular.module('GrappBox.factories', [])

/*
********************* TOAST *********************
*/
.factory('Toast', function ($rootScope, $timeout, $ionicLoading, $cordovaToast) {
    return {
        show: function (message, duration, position) {
            message = message || "There was a problem...";
            duration = duration || 'short';
            position = position || 'bottom';

            if (document.URL.indexOf('http://') === -1 && document.URL.indexOf('https://') === -1) {
                console.log("NO CORDOVA !");
                // Use the Cordova Toast plugin
                $cordovaToast.show(message, duration, position);
            }
            else {
                if (duration == 'short') {
                    duration = 2000;
                }
                else {
                    duration = 5000;
                }
                $ionicLoading.show({ template: message, noBackdrop: true, duration: duration });
            }
        }
    };
})