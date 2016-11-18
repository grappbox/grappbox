/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Factory definition
// APP statistics
app.factory("statisticFactory", function() {

  /* ==================== PIE ==================== */

  // Routine definition
  // Pie creation
  var _pie = function(column, pattern) {
    return {
      "type": "PieChart",
      "data": [],
      "options": {
        "displayExactValues": true,
        "width": "100%",
        "height": "auto",
        "is3D": false,
        "chartArea": { "left": 10, "top": 10, "bottom": 10, "width": "100%", "height": "auto" }
      },
      "formatters": { "number": [{ "columnNum": column, "pattern": pattern }] }
    };
  };



  /* ==================== EXECUTION ==================== */

  // Give access to built-in routines
  return {
    pie: function(column, pattern) {
      return _pie(column, pattern);
    }
  };

});