<!--
//<![CDATA[

    /**
     * File to contain commonly used Javascript functionality
     * for use across moodletxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @version 2010062312
     * @since 2007082112
     */

    // Character limit of a GSM message - changes to 70 if the user enables unicode sending
    var MESSAGE_CHARACTER_LIMIT_GSM = 160;
    var MESSAGE_CHARACTER_LIMIT_UNICODE = 70;
    var MESSAGE_CHARACTER_LIMIT = MESSAGE_CHARACTER_LIMIT_GSM;

    var TIME_BETWEEN_CHECKS = 1000;

    var keyupTimer;  // Was tempted to call this the ey-up timer.  Oh aye, lad.
    var checkTimeHasElapsed = true;

    /**
     * Extends the basic string object to provide a trim() function. Cool!
     * @link http://javascript.crockford.com/remedial.html
     */
    String.prototype.trim = function () {
    return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
    };

    /**
     * Allows us to insert given text into a textarea
     * at the caret's current position
     * @link http://technology.hostei.com/?p=3
     */
    $.fn.insertAtCaret = function (tagName) {
        return this.each(function(){
            if (document.selection) {
                //IE support
		this.focus();
		sel = document.selection.createRange();
		sel.text = tagName;
		this.focus();
            } else if (this.selectionStart || this.selectionStart == '0') {
                //MOZILLA/NETSCAPE support
                startPos = this.selectionStart;
                endPos = this.selectionEnd;
		scrollTop = this.scrollTop;
		this.value = this.value.substring(0, startPos) + tagName + this.value.substring(endPos,this.value.length);
		this.focus();
		this.selectionStart = startPos + tagName.length;
		this.selectionEnd = startPos + tagName.length;
		this.scrollTop = scrollTop;
            } else {
                this.value += tagName;
		this.focus();
            }
	});
    };


    /**
     * Validate input in phone fields. Returning false prevents bad character being echoed to screen.
     * 48 - 57 are numbers
     * 43 is the + sign
     * 8 is backspace
     * 0 is for system keys
     * @param event The JS event being triggered
     * @return bool
     */
    function validatePhoneInput(event) {

        /*
         * 48 - 57 are numbers
         * 43 is the + sign
         * 8 is backspace
         * 0 is for system keys
         */

        if ((event.which >= 48 && event.which <= 57) ||
            event.which == 43 ||
            event.which == 8 ||
            event.which == 0) {

            return true;

        } else {

            return false;

        }

    }

    /**
     * Validate input in name fields. Returning false prevents bad character being echoed to screen.
     * 48 - 57 are numbers
     * 43 is the + sign
     * 8 is backspace
     * 0 is for system keys
     * 65-90 and 97-122 are upper and lower case letters
     * @param event The JS event being triggered
     * @return bool
     */
    function validateNameInput(event) {

        if ((event.which >= 97 && event.which <= 122) ||
            (event.which >= 65 && event.which <= 90) ||
            event.which == 45 ||
            event.which == 32 ||
            event.which == 8 ||
            event.which == 0) {

            return true;

        } else {

            return false;

        }

    }

    /**
     * Emulate getElementById if it does not exist
     * ---SHOULD NOT BE USED---
     * Use jQuery instead
     * @deprecated
     */
    if (!document.getElementById) {

        if (document.all) {

            document.getElementById = function(id) {

                return document.all[id];

            }

        } else if (document.layers) {

            document.getElementById = function(id) {

                return document.layers[id];

            }

        } else {

            document.getElementById = function(id) {
                return null;
            }

        }

    }

    /**
     * Pads out a given field with 0s.  Built for making yyyy-mm-dd date stamps.
     * @todo Modify to allow for any character to be used in padding?
     */
    function zeroPad(num,count) {

        var numZeropad = num + '';

        while(numZeropad.length < count) {
            numZeropad = "0" + numZeropad;
        }

        return numZeropad;
        
    }


    /**
     * Check to see if the message box onscreen contains
     * non-GSM characters, and alert if it does
     * @param jQuery $messageElement Message box object
     * @param object optionSet JS object representing options
     * @version 2010062312
     * @since 2010062312
     */
    function isUnicode($messageElement, optionSet) {

        var defaultOptions = {
            alertElement            :   '#unicodeMessage',          // Element ID of container in which to show text alert
            CPMElement              :   '#charactersPerMessage'     // Element ID of "characters per message" counter
        };

        var options = $.extend(defaultOptions, optionSet);

        // Prevent other checks taking place while this one is running
        checkTimeHasElapsed = false;

        // Make sure parameters are jQuery
        $messageElement = $($messageElement);
        $alertElement = $(options.alertElement);
        $CPMElement = $(options.CPMElement);

        if ($messageElement.val().length > 0) {

            var requestJSON = $.toJSON({
                checkString    :   $messageElement.val()
            });

            previousBorderWidth = $messageElement.css('border-width');

            // Query servlet with message text
            $.getJSON('sendmessage_unicode.php',
                { json : requestJSON },
                function(json) {

                    // If unicode was detected, warn the user
                    if (json.isUnicode == 'true') {

                        MESSAGE_CHARACTER_LIMIT = MESSAGE_CHARACTER_LIMIT_UNICODE;

                        // Animate text box border to attract attention
                        $messageElement.css('border-width', '4px');
                        $messageElement.everyTime(10, function(){
                            $messageElement.animate({
                                borderTopColor      : '#FF0000',
                                borderRightColor    : '#FF0000',
                                borderBottomColor   : '#FF0000',
                                borderLeftColor     : '#FF0000'
                            }, 1000).animate({
                                borderTopColor      : '#000000',
                                borderRightColor    : '#000000',
                                borderBottomColor   : '#000000',
                                borderLeftColor     : '#000000'
                            }, 1000);
                        }, 2);

                        // Highlight "characters per message" and show text alert
                        // (If jQuery elements are not specified this will fail silently)
                        $alertElement.text(language['warnunicode']);

                    } else {

                        // Message is GSM 03.38 compatible - form should be unaffected
                        MESSAGE_CHARACTER_LIMIT = MESSAGE_CHARACTER_LIMIT_GSM;

                        $messageElement.css('border-color', 'black');
                        $messageElement.css('border-width', previousBorderWidth);

                        $alertElement.text('');
                    }

                    $CPMElement.text(MESSAGE_CHARACTER_LIMIT);

                }

            );

        }
        
        $CPMElement.text(MESSAGE_CHARACTER_LIMIT);
        
        // Prevent any further unicode checks occurring within the next second (+keyup latency)
        // Prevents hammering on the website/database
        setTimeout(function() {checkTimeHasElapsed = true;}, TIME_BETWEEN_CHECKS);

    }

    /**
     * Function to update message boxes within
     * the module as text is entered itno them
     * @param jQuery $messageObject jQuery object for message box
     * @param object actionSet Parameter set for operations
     * @version 2010062312
     * @since 2010062312
     */
    function updateMessageBox($messageObject, actionSet) {

        // Default parameters
        var defaultActionSet = {
            checkMessageChars       :   true,                           // Update "characters remaining"
            checkForUnicode         :   true,                           // Check text box for unicode content
            characterCountElements  :   {
                characterCounterId      :   '#charsUsed',               // Element ID of character counter
                confirmCounterId        :   '#confirmCharsUsed',        // Element ID of character counter on confirm page
                messageCounterId        :   '#confirmMessagesUsed'      // Element ID of message counter on confirm page
           },
           unicodeDetectElements    :   {
                unicodeMessageSpanId    :   '#unicodeMessage',          // Element ID of container in which to show text alert
                CPMElement              :   '#charactersPerMessage'     // Element ID of "characters per message" counter
           }
        };


        $messageObject = $($messageObject);
        actionSet = $.extend(defaultActionSet, actionSet);
        var messageLength = $messageObject.val().length;

        // Can only check for unicode if enough time has elapsed since the last one
        if (actionSet.checkForUnicode && checkTimeHasElapsed) {

            // Unicode check fires 500ms after the user stops typing
            clearTimeout(keyupTimer);
            keyupTimer = setTimeout(function() {
                isUnicode($messageObject, actionSet.unicodeDetectElements)
            }, 500);

        }

        if (actionSet.checkMessageChars) {

            $(actionSet.characterCountElements.characterCounterId).val(messageLength);
            $(actionSet.characterCountElements.confirmCounterId).text(messageLength);
            $(actionSet.characterCountElements.messageCounterId).text(Math.ceil(messageLength / MESSAGE_CHARACTER_LIMIT));

        }

    }


//]]>
//-->