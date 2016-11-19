/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Factory definition
// APP notifications (actions)
app.factory("notificationFactory", ['Notification', function(Notification) {

  /* ==================== EXECUTION ==================== */

  // Give access to built-in routines
  return {
    // "Clear" routine
    // Clear all notifications
    clear: function() {
      Notification.clearAll();
    },

    // "Success" routine
    // Operation validated
    success: function(message) {
      Notification.success({ message: message + "<i class=\"material-icons\">check</i>", delay: 1000 });
    },
    
    // "Warning" routine
    // Operation failed, or succeed but with non-blocking errors
    warning: function(message) {
      Notification.warning({ message: message + "<i class=\"material-icons\">warning</i>", delay: 2000 });
    },

    // "Error" routine
    // Operation critically failed    
    error: function() {
      Notification.error({ message: "Someting is wrong with GrappBox. Please give us a minute, and try again.<i class=\"material-icons\">clear</i>", delay: 3000 });
    },
  };

}]);