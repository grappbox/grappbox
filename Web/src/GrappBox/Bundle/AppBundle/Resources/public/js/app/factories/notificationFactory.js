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

    // "Loading" routine
    // Operation in progress
    loading: function(message) {
      if (message)
        Notification.success({ message: message + "<i class=\"material-icons\" aria-hidden=\"true\">more_horiz</i>", delay: 2000 });
      else
        Notification.success({ message: "Loading...<i class=\"material-icons\" aria-hidden=\"true\">more_horiz</i>", delay: 2000 });
    },

    // "Information" routine
    // Operation in progress
    info: function(message) {
      Notification.success({ message: message + "<i class=\"material-icons\" aria-hidden=\"true\">help</i>", delay: 2000 });
    },

    // "Success" routine
    // Operation validated
    success: function(message) {
      Notification.success({ message: message + "<i class=\"material-icons\" aria-hidden=\"true\">check</i>", delay: 2000 });
    },
    
    // "Warning" routine
    // Operation failed, or succeed but with non-blocking errors
    warning: function(message) {
      Notification.warning({ message: message + "<i class=\"material-icons\" aria-hidden=\"true\">warning</i>", delay: 2000 });
    },

    // "Error" routine
    // Operation critically failed    
    error: function() {
      Notification.error({ message: "Someting is wrong with GrappBox. Please give us a minute, and try again.<i class=\"material-icons\" aria-hidden=\"true\">clear</i>", delay: 3000 });
    },
  };

}]);