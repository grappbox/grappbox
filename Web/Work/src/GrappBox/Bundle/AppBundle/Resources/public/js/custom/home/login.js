/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

/* =============================================== */
/* ==================== LOGIN ==================== */
/* =============================================== */

$(document).ready(function() {

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
  // Set login behavior depending on cookies
  function getLoginState() {
    var loginState = Cookies.get("_LOGIN");

    $("#form-message").removeClass("show");
    if (loginState != undefined && window.atob(loginState) != "_success") {
      switch(window.atob(loginState)) {
        case "_badlogin":
        $("#form-message p").text("Sorry, GrappBox didn't recognize the email you entered.");
        break;

        case "_badpassword":
        $("#form-message p").text("The email and password you entered don't match.");
        break;

        case "_denied":
        $("#form-message p").text("You must login to GrappBox to continue.");
        break;

        default:
        break;
      }
      Cookies.remove("_LOGIN", { path: "/" });
      Cookies.remove("_TOKEN", { path: "/" });
      Cookies.remove("_ID", { path: "/" });

      $("#form-message").addClass("show");
    }
  }

  // Start point
  getLoginState();

});