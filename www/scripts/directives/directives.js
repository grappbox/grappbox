// All directives are here

angular.module('GrappBox.directives', [])

.directive("passwordVerify", function () {
return {
    require: 'ngModel',
    link: function (scope, elem, attrs, ctrl) {
        if (!attrs.passwordVerify) {
            return;
        }
        scope.$watch(attrs.passwordVerify, function (value) {
            //&& value !== undefined
          if( value === ctrl.$viewValue) {
             ctrl.$setValidity('passwordVerify', true);
             ctrl.$setValidity("parse",undefined);
          }
          else {
             ctrl.$setValidity('passwordVerify', false);
          }
        });
        ctrl.$parsers.push(function (value) {
            var isValid = value === scope.$eval(attrs.passwordVerify);
            ctrl.$setValidity('passwordVerify', isValid);
            return isValid ? value : undefined;
        });
    }
  };
})