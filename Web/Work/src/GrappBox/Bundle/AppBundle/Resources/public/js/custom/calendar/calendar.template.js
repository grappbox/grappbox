/*
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/**
* Template override
* APP calendar provider
*
* Use this template to override the third-party-provided one (angularjs-boostrap-calendar).
* It remove the "Week number" tooltip on the left (useless, causing errors and breaking design).
* Copy and past it into the library file (find the template with the same name/number, and override it).
*/


/***/ },
/* 17 */
/***/ function(module, exports) {

  module.exports = "<div\n  mwl-droppable\n  on-drop=\"vm.handleEventDrop(dropData.event, day.date, dropData.draggedFromDate)\"\n  class=\"cal-month-day {[{ day.cssClass }]}\"\n  ng-class=\"{\n    'cal-day-outmonth': !day.inMonth,\n    'cal-day-inmonth': day.inMonth,\n    'cal-day-weekend': day.isWeekend,\n    'cal-day-past': day.isPast,\n    'cal-day-today': day.isToday,\n    'cal-day-future': day.isFuture\n  }\">\n\n  <small\n    class=\"cal-events-num badge badge-important pull-left\"\n    ng-show=\"day.badgeTotal > 0\"\n    ng-bind=\"day.badgeTotal\">\n  </small>\n\n  <span\n    class=\"pull-right\"\n    data-cal-date\n    ng-click=\"vm.calendarCtrl.dateClicked(day.date)\"\n    ng-bind=\"day.label\">\n  </span>\n\n  <div class=\"cal-day-tick\" ng-show=\"dayIndex === vm.openDayIndex && vm.view[vm.openDayIndex].events.length > 0 && !vm.slideBoxDisabled\">\n    <i class=\"glyphicon glyphicon-chevron-up\"></i>\n    <i class=\"fa fa-chevron-up\"></i>\n  </div>\n\n  <ng-include src=\"vm.calendarConfig.templates.calendarMonthCellEvents\"></ng-include>\n\n</div>\n\n</div>\n";
