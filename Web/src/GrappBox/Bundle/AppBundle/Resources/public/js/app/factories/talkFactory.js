/*
d* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Factory definition
// APP talk message to issue conversion
app.factory("talkFactory", function() {

	/* ==================== INITIALIZATION ==================== */

	// Factory variables initialization
  var isMessageLoaded = false;
  var messageData = { title: "", message: "" };



  /* ==================== ROUTINES ==================== */

  // Routine definition
  // Clear message data and load state
  var _clear = function() {
  	isMessageLoaded = false;
  	messageData = { title: "", message: "" };
  };

  // Routine definition
  // Set message data
  var _setMessage = function(data) {
  	messageData.title = data.title;
  	messageData.message = data.message;
  	isMessageLoaded = true;
	};

  // Routine definition
  // Return message load state
  var _isMessageLoaded = function() {
  	return isMessageLoaded;
  };

  // Routine definition
  // Return message data
  var _getMessageData = function() {
  	return messageData;
  };



  /* ==================== EXECUTION ==================== */

  // Give access to built-in routines
  return {
    clear: function() {
    	_clear();
    },

    setMessage: function(data) {
    	_setMessage(data);
    },

    isMessageLoaded: function() {
    	return _isMessageLoaded();
    },

    getMessageData: function() {
    	return _getMessageData();
    }
  };

});