/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* ======================================================== */
/* ==================== LOGIN/REGISTER ==================== */
/* ======================================================== */

$(document).ready(function() {
  "use strict";

  // IE WORKAROUND
  if (!window.btoa) {
    window.btoa = function(str) {
      return Base64.encode(str);
    }
  }

  // IE WORKAROUND
  if (!window.atob) {
    window.atob = function(str) {
      return Base64.decode(str);
    }
  }

  // Routine definition
  // Set alert message depending on cookies content
  function getUserStatus() {
    var status = Cookies.get("LOGIN");

    $("#form-message").removeClass("show");
    if (status != undefined && window.atob(status) != "_success") {
      switch(window.atob(status)) {
        case "_already":
        $("#form-message p").text("This email is already registered. Please login to your account, or try with another email adress.");
        break;

        case "_badlogin":
        $("#form-message p").text("Sorry, GrappBox didn't recognize the email you entered.");
        break;

        case "_badpassword":
        $("#form-message p").text("The email and password you entered don't match.");
        break;

        case "_critical":
        $("#form-message p").text("Something is wrong with GrappBox. Please give us a minute, and try again.");
        break;

        case "_denied":
        $("#form-message p").text("You must login to GrappBox to continue.");
        break;

        case "_mismatch":
        $("#form-message p").text("You password and confirmation password do not match. Please try again.");
        break;

        default:
        break;
      }
      Cookies.remove("LOGIN", { path: "/" });
      Cookies.remove("TOKEN", { path: "/" });
      Cookies.remove("ID", { path: "/" });

      $("#form-message").addClass("show");
    }
  }

  // Start point
  getUserStatus();

});