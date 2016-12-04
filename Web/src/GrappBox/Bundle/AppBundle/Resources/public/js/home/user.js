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
    $("#form-message").removeClass("show");

    var preorder = Cookies.get("G_PREORDER");
    if (preorder != undefined) {
      switch(window.atob(preorder)) {
        case "_success":
        $("#form-message p").html("You are successfully registered.<br>We'll get in touch soon, thanks a lot!");
        break;

        case "_already":
        $("#form-message p").html("This email is already registered.<br>Be patient!");
        break;

        case "_critical":
        $("#form-message p").html("Something is wrong with GrappBox.<br>Please give us a minute, and try again.");
        break;

        case "_denied":
        $("#form-message p").text("You must login to GrappBox to continue.");
        break;

        default:
        break;
      }
    }

    var login = Cookies.get("G_LOGIN");
    if (login != undefined && window.atob(login) != "_success") {
      switch(window.atob(login)) {
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

      Cookies.remove("G_PREORDER", { path: "/" });      
      Cookies.remove("G_LOGIN", { path: "/" });
      Cookies.remove("G_TOKEN", { path: "/" });
      Cookies.remove("G_ID", { path: "/" });

      $("#form-message").addClass("show");
    }
  };

  // Start point
  getUserStatus();
});