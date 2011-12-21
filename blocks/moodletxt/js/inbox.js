    <!--
    //<![CDATA[

    function checkAllBoxes(containerid) {

        var inputs = document.getElementById(containerid).getElementsByTagName('input');

        for (var x = 0; x < inputs.length; x++) {

            var thisinput = inputs[x];

            if (thisinput && thisinput.type == 'checkbox' && thisinput.disabled == false) {

                if (thisinput.checked == false) {

                    thisinput.checked = true;

                }

            }

        }

    }

    function uncheckAllBoxes(containerid) {

        var inputs = document.getElementById(containerid).getElementsByTagName('input');

        for (var x = 0; x < inputs.length; x++) {

            var thisinput = inputs[x];

            if (thisinput && thisinput.type == 'checkbox' && thisinput.disabled == false) {

                if (thisinput.checked == true) {

                    thisinput.checked = false;

                }

            }

        }

    }

    function confirmDelete(recordid) {

        var messageform = document.forms[1];
        var messagecheckbox = document.getElementById('msgchk' + recordid);
        var actionlist = document.getElementById('selectedaction');
        var folderlist = document.getElementById('folderlist');
        var inboxlist = document.getElementById('inboxlist');

        var confirmdelete = confirm('Are you sure you want to delete this message?');

        if (confirmdelete) {

            messagecheckbox.checked = true;

            folderlist.value = '';
            folderlist.disabled = true;

            inboxlist.value = '';
            inboxlist.disabled = true;

            actionlist.value = 'killmaimburn';

            messageform.submit();

        } else {

            return false;

        }

    }

    function selectAction(actionList) {

        var messageform = document.forms[1];
        var folderlist = document.getElementById('folderlist');
        var inboxlist = document.getElementById('inboxlist');

        switch (actionList.value) {

            case 'killmaimburn':

                folderlist.disabled = true;
                inboxlist.disabled = true;

                var confirmdelete = confirm('Are you sure you want to delete the selected messages?');

                if (confirmdelete) {

                    messageform.submit();

                } else {

                    return false;

                }

                break;

            case 'copy':
            case 'move':

                folderlist.disabled = false;
                inboxlist.disabled = false;

                break;

            default:

                folderlist.disabled = true;
                inboxlist.disabled = true;

        }

    }

    function setListSwitch(switchValue) {

        var messageform = document.forms[1];
        var folderinboxswitch = document.getElementById('folderorinbox');

        folderinboxswitch.value = switchValue;
        messageform.submit();

    }

    function jumpFolder() {

        document.forms[0].submit();

    }


    //]]>
    //-->
