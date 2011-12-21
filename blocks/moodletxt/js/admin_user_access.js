    <!--
    //<![CDATA[

    /**
     * Javascript file for the user admin page
     * Yes, I *am* going to come back and make this more modular at some point,
     * but it works for now. ;-)
     *
     * @version 2007090412
     * @since 2007051112
     */

    /**
     * Function to expand/collapse list nodes in the course/user tree.
     * Switches node display styles from "block" to "none" and back.
     *
     * @param nodeLists The (one length) array of lists to be collapsed
     * @param expandCollapse A reference to the expand/collapse image icon
     * @version 2007053112
     * @since 2007051112
     */

    function switchDisplay(nodeLists, expandCollapse) {

        if (nodeLists.length > 0) {

            if (nodeLists[0].style.display == 'block') {

                expandCollapse.setAttribute('src', 'pix/select_expand.gif');
                expandCollapse.setAttribute('alt', 'Expand node');
                expandCollapse.setAttribute('title', 'Expand node');

                nodeLists[0].style.display = 'none';

            } else {

                expandCollapse.setAttribute('src', 'pix/select_collapse.gif');
                expandCollapse.setAttribute('alt', 'Collapse node');
                expandCollapse.setAttribute('title', 'Collapse node');

                nodeLists[0].style.display = 'block';

            }

        }

    }

    /**
     * Function to expand/collapse container nodes in the course/user tree
     * Switches node display styles from "block" to "none" and back.
     *
     * @param container The list container node to expand/collapse
     * @param nodeType The type of node being referenced
     * @param nodeID The ID of the node being referenced
     * @version 2007053112
     * @since 2007051112
     */

    function expandNode(container, nodeType, nodeID) {

        nodeLists = container.getElementsByTagName('ul');
        expandCollapse = container.getElementsByTagName('img')[0];

        if (nodeLists.length > 0) {

            switchDisplay(nodeLists, expandCollapse);

            return;

        } else {

            // Change expand/collapse icon
            expandCollapse.setAttribute('src', 'pix/select_collapse.gif');
            expandCollapse.setAttribute('alt', 'Collapse node');
            expandCollapse.setAttribute('title', 'Collapse node');


            getChildren(container, nodeType, nodeID);

        }

    }

    /**
     * Function to get the child nodes of a given user tree node
     *
     * @param container The list container node to get children for
     * @param nodeType The type of node being referenced
     * @param nodeID The ID of the node being referenced
     * @version 2007053112
     * @since 2007051112
     */

    function getChildren(container, nodeType, nodeID) {

        var url = "getusers.php";

        // Create and send request
        xmlhttp.open("POST", url, true);
        xmlhttp.onreadystatechange = receiveChildren;
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send('fetchtype=children&nodetype=' + nodeType + '&nodeID=' + nodeID + '&getadmins=1');

        // Add "loading" notice
        var loadingList     = document.createElement('ul');
        var loadingElement  = document.createElement('li');
        var loadingText     = document.createTextNode('Loading...');

        loadingList.className = 'mdltxt_usertree_course';
        loadingList.style.display = 'block';
        loadingElement.appendChild(loadingText);
        loadingElement.style.listStyleImage = 'url(' + wwwroot + '/blocks/moodletxt/pix/ajax-loader.gif)';
        loadingList.appendChild(loadingElement);
        container.appendChild(loadingList);

    }

    /**
     * Function that receives the child node information
     * passed back from the database and put it into the tree
     *
     * @version 2007053112
     * @since 2007051112
     */

    function receiveChildren() {

        if (xmlhttp.readyState == 4) {

            var nodeSetXML = xmlhttp.responseXML.getElementsByTagName('UserTreeNodes')[0];

            var parentnodes = nodeSetXML.getElementsByTagName('ParentNode');

            if (parentnodes.length != 1) {

                alert('One of the nodes selected on the user tree failed to load.  Please try again.');

            }

            var parentNodeID = parentnodes[0].getElementsByTagName('ParentNodeID')[0].childNodes[0].nodeValue;
            var parentNodeType = parentnodes[0].getElementsByTagName('ParentNodeType')[0].childNodes[0].nodeValue;
            var parentElement = document.getElementById(parentNodeType + 'container' + parentNodeID);

            // Clear out parent element
            clearContainer(parentElement);

            var listID = parentNodeType + 'list' + parentNodeID;

            // Create list element
            var outputlist = document.createElement('ul');
            outputlist.setAttribute('id', listID);

            if (parentNodeType == 'course') {

                outputlist.className = 'mdltxt_usertree_course';

            } else {

                outputlist.className = 'mdltxt_usertree_category';

            }

            // Add nodes to list in correct order
            // Separate functions allow for control of formatting and content added
            // without massive if-elses or switches

            if (parentNodeType == 'course') {

                // Add admins to list
                addChildAdmins(nodeSetXML, outputlist);

                // Add teachers to list
                addChildTeachers(nodeSetXML, outputlist);

            } else {

                // Add categories to list
                addChildCategories(nodeSetXML, outputlist);

                // Add courses to list
                addChildCourses(nodeSetXML, outputlist);

            }

            // Check to see if list is blank
            if (outputlist.childNodes.length == 0) {

                var noRecordsID = parentNodeType + 'listnorecords' + parentNodeID;
                var noRecordsElement = createListElement(noRecordsID, 'No records found to display.');

                noRecordsElement.className = 'mdltxt_usertree_norecords';

                outputlist.appendChild(noRecordsElement);

            }

            // Output final list to screen
            outputlist.style.display = 'block';
            parentElement.appendChild(outputlist);

        }

    }

    /**
     * Function to nuke all elements within the give list
     *
     * @param parentElement The list to nuke
     * @version 2007053112
     * @since 2007051112
     */

    function clearContainer(parentElement) {

        // Get all unwanted child lists and kill them off
        var childlists = parentElement.getElementsByTagName('ul');

        childSetLen = childlists.length;

        if (childSetLen > 0) {

            for (var x = 0; x < childSetLen; x++) {

                parentElement.removeChild(childlists[0]);

            }

        }

    }

    /**
     * Function to add all the admins found in an XML result set
     * to the user tree under the given list node
     *
     * @param xmlobj The XML object to get admins from
     * @param list A reference to the list object to add admins to
     * @version 2007053112
     * @since 2007051112
     */

    function addChildAdmins(xmlobj, list) {

        var parentElement = xmlobj.getElementsByTagName('ParentNode');
        var courseID = parentElement[0].getElementsByTagName('ParentNodeID')[0].childNodes[0].nodeValue;

        var adminElements = xmlobj.getElementsByTagName('Admin');

        if (adminElements.length > 0) {

            for (var x = 0; x < adminElements.length; x++) {

                var adminID = adminElements[x].getElementsByTagName('AdminID')[0].childNodes[0].nodeValue;
                var adminfn = adminElements[x].getElementsByTagName('FirstName')[0].childNodes[0].nodeValue;
                var adminln = adminElements[x].getElementsByTagName('LastName')[0].childNodes[0].nodeValue;
                var adminun = adminElements[x].getElementsByTagName('Username')[0].childNodes[0].nodeValue;
                var adminName = adminln + ', ' + adminfn + ' (' + adminun + ')';

                var listElement = createListElement('admincontainer' + adminID, adminName, "getUserAccessDetails('" + adminID + "', '" + courseID + "');");
                listElement.className = 'mdltxt_usertree_admin';

                list.appendChild(listElement);

            }

        }

    }

    /**
     * Function to add all the teachers found in an XML result set
     * to the user tree under the given list node
     *
     * @param xmlobj The XML object to get teachers from
     * @param list A reference to the list object to add teachers to
     * @version 2007053112
     * @since 2007051112
     */

    function addChildTeachers(xmlobj, list) {

        var parentElement = xmlobj.getElementsByTagName('ParentNode');
        var courseID = parentElement[0].getElementsByTagName('ParentNodeID')[0].childNodes[0].nodeValue;

        var teacherElements = xmlobj.getElementsByTagName('Teacher');

        if (teacherElements.length > 0) {

            for (var x = 0; x < teacherElements.length; x++) {

                var teacherID = teacherElements[x].getElementsByTagName('TeacherID')[0].childNodes[0].nodeValue;
                var teacherfn = teacherElements[x].getElementsByTagName('FirstName')[0].childNodes[0].nodeValue;
                var teacherln = teacherElements[x].getElementsByTagName('LastName')[0].childNodes[0].nodeValue;
                var teacherun = teacherElements[x].getElementsByTagName('Username')[0].childNodes[0].nodeValue;
                var teacherName = teacherln + ', ' + teacherfn + ' (' + teacherun + ')';

                var listElement = createListElement('teachercontainer' + teacherID, teacherName, "getUserAccessDetails('" + teacherID + "', '" + courseID + "');");

                list.appendChild(listElement);

            }

        }

    }

    /**
     * Function to add all the categories found in an XML result set
     * to the user tree under the given list node
     *
     * @param xmlobj The XML object to get categories from
     * @param list A reference to the list object to add categories to
     * @version 2007053112
     * @since 2007051112
     */

    function addChildCategories(xmlobj, list) {

        var categoryElements = xmlobj.getElementsByTagName('Category');

        if (categoryElements.length > 0) {

            for (var x = 0; x < categoryElements.length; x++) {

                var categoryID = categoryElements[x].getElementsByTagName('CategoryID')[0].childNodes[0].nodeValue;
                var categoryName = categoryElements[x].getElementsByTagName('CategoryName')[0].childNodes[0].nodeValue;

                var listElement = createListElement('categorycontainer' + categoryID, categoryName, '', "expandNode(document.getElementById('categorycontainer" + categoryID + "'), 'category', '" + categoryID + "');");

                list.appendChild(listElement);

            }

        }

    }

    /**
     * Function to add all the courses found in an XML result set
     * to the user tree under the given list node
     *
     * @param xmlobj The XML object to get courses from
     * @param list A reference to the list object to add courses to
     * @version 2007053112
     * @since 2007051112
     */

    function addChildCourses(xmlobj, list) {

        var courseElements = xmlobj.getElementsByTagName('Course');

        if (courseElements.length > 0) {

            for (var x = 0; x < courseElements.length; x++) {

                var courseID = courseElements[x].getElementsByTagName('CourseID')[0].childNodes[0].nodeValue;
                var courseName = courseElements[x].getElementsByTagName('FullName')[0].childNodes[0].nodeValue;

                var listElement = createListElement('coursecontainer' + courseID, courseName, "getCourseAccessDetails('" + courseID + "');", "expandNode(document.getElementById('coursecontainer" + courseID + "'), 'course', '" + courseID + "');");

                list.appendChild(listElement);


            }

        }

    }

    /**
     * Build a list container node for new branches of the tree
     *
     * @param elementID The ID of this new node
     * @param nodeText The textual header of this container node
     * @param accessLink The link to click to display access rights at this level (can be blank)
     * @expandLink The link for the expand/collapse image at the left of the header (can be blank)
     * @version 2007053112
     * @since 2007051112
     */

    function createListElement(elementID, nodeText, accessLink, expandLink) {

        var listElement = document.createElement('li');
        var listText = document.createTextNode(nodeText);

        listElement.setAttribute('id', elementID);

        if (typeof(expandLink) != 'undefined') {

            var listExpand = document.createElement('img');
            listExpand.src = 'pix/select_expand.gif';
            listExpand.className = 'mdltxt_usertree_expand';
            listExpand.style.width = '15px';
            listExpand.style.height = '15px';
            listExpand.setAttribute('alt', 'Expand node');

            var listLink = document.createElement('a');

            listLink.setAttribute('href', 'javascript:' + expandLink);
            //listLink.setAttribute('onclick', expandLink);

            listLink.appendChild(listExpand);
            listElement.appendChild(listLink);
            listElement.appendChild(document.createTextNode(' '));

        }

        if ((typeof(accessLink) == 'undefined') ||
            accessLink == '') {

            listElement.appendChild(listText);

        } else {

            var listLink2 = document.createElement('a');
            listLink2.setAttribute('href', 'javascript:' + accessLink);

            listLink2.appendChild(listText);
            listElement.appendChild(listLink2);

        }

        return listElement;

    }

    /**
     * Function to wipe child elements from the user list
     *
     * @version 2007053112
     * @since 2007051112
     */

    function clearUserBoxes() {

        var userList = document.getElementById('userlist');

        // Get all unwanted child lists and kill them off
        var listelements = userList.getElementsByTagName('option');

        listlen = listelements.length;

        if (listlen > 0) {

            for (var x = 0; x < listlen; x++) {

                userList.removeChild(listelements[0]);

            }

        }

        activateEditAccess();

    }

    /**
     * Function to clear out the user access form
     *
     * @version 2007053112
     * @since 2007051112
     */

    function clearUserForm() {

        var nameDisplay = document.getElementById('userFormName');
        var usernameDisplay = document.getElementById('userFormMoodleID');
        var courseDisplay = document.getElementById('userFormCourse');
        var accessList = document.getElementById('useraccountaccess');
        var filterList = document.getElementById('userinboundfilters');
        var remAccessButton = document.getElementById('removeaccessbutton');
        var grantAccessButton = document.getElementById('grantaccessbutton');
        var remFilterButton = document.getElementById('userdelfilterbutton');
        var keywordInput = document.getElementById('userfilterkeyword');
        var phoneInput = document.getElementById('userfilterphone');

        // Check that a text node exists
        if (nameDisplay.childNodes.length == 0 ||
            usernameDisplay.childNodes.length == 0 ||
            courseDisplay.childNodes.length == 0) {

            nameDisplay.appendChild(document.createTextNode(''));
            usernameDisplay.appendChild(document.createTextNode(''));
            courseDisplay.appendChild(document.createTextNode(''));

        }

        // Clear text nodes
        nameDisplay.childNodes[0].nodeValue = '';
        usernameDisplay.childNodes[0].nodeValue = '';
        courseDisplay.childNodes[0].nodeValue = '';

        keywordInput.value = '';
        phoneInput.value = '';

        // Disable buttons
/*        remAccessButton.disabled = true;
        grantAccessButton.disabled = true;
        remFilterButton.disabled = true;*/

// Turned these off until I can figure out a way to
// correctly clear selections from select boxes

        // Clear list
        optionSet = accessList.getElementsByTagName('option');
        optionSet2 = filterList.getElementsByTagName('option');

        for (var x = 0; x < optionSet.length; x++) {

            accessList.removeChild(optionSet[x]);

        }

        for (var x = 0; x < optionSet2.length; x++) {

            filterList.removeChild(optionSet2[x]);

        }

    }

    /**
     * Function to clear out the course access form
     *
     * @version 2007053112
     * @since 2007051112
     */

    function clearCourseForm() {

        var nameDisplay = document.getElementById('courseaccessname');
        var accessList = document.getElementById('courseaccountaccess');

        // Check that a text node exists
        if (nameDisplay.childNodes.length == 0) {

            nameDisplay.appendChild(document.createTextNode(''));

        }

        // Clear text nodes
        nameDisplay.childNodes[0].nodeValue = '';

        // Get all unwanted child lists and kill them off
        var listelements = accessList.getElementsByTagName('option');

        listlen = listelements.length;

        if (listlen > 0) {

            for (var x = 0; x < listlen; x++) {

                accessList.removeChild(listelements[0]);

            }

        }

    }

    /**
     * Hide all loading/access panels on the right of the access form
     *
     * @version 2007053112
     * @since 2007051112
     */

    function hideAccessPanels() {

        // Get access forms and nuke 'em
        var loadingForm = document.getElementById('mdltxt_usertree_loadingPanel');
        var accessForm = document.getElementById('mdltxt_usertree_userAccessPanel');
        var courseAccessForm = document.getElementById('mdltxt_usertree_courseAccessPanel');

        loadingForm.style.display = 'none';
        accessForm.style.display = 'none';
        courseAccessForm.style.display = 'none';

    }

    /**
     * Get user-level access details for display
     *
     * @param userID The ID of the user to get acess details for
     * @param courseID The course on which to search for outbound access
     * @version 2007053112
     * @since 2007051112
     */

    function getUserAccessDetails(userID, courseID) {

        url = "getusers.php";

        xmlhttp.open("POST", url, true);
        xmlhttp.onreadystatechange = receiveUserAccessDetails;
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send('fetchtype=user&userid=' + userID + '&courseid=' + courseID);

        hideAccessPanels();

        // Get access form and check for visibility
        var accessForm = document.getElementById('mdltxt_usertree_loadingPanel');

        accessForm.style.display = 'block';

        // Get access boxes
        var accessList = document.getElementById('useraccountaccess');
        var accountList = document.getElementById('usertxttoolsaccounts');

        accessList.disabled = true;
        accountList.disabled = true;

    }

    /**
     * Function to receive user-level access details and display them
     * on the user access form
     *
     * @version 2007053112
     * @since 2007051112
     */

    function receiveUserAccessDetails() {

        // Set up page id links
        var nameDisplay = document.getElementById('userFormName');
        var usernameDisplay = document.getElementById('userFormMoodleID');
        var courseDisplay = document.getElementById('userFormCourse');
        var accessList = document.getElementById('useraccountaccess');
        var accountList = document.getElementById('usertxttoolsaccounts');
        var removeUserID = document.getElementById('removeaccessuserid');
        var grantUserID = document.getElementById('grantaccessuserid');
        var removeCourseID = document.getElementById('removecourseid');
        var grantCourseID = document.getElementById('grantcourseid');
        var filterList = document.getElementById('userinboundfilters');
        var delFilterUserID = document.getElementById('delfilteruserid');
        var delFilterCourseID = document.getElementById('delfiltercourseid');
        var addFilterUserID = document.getElementById('addfilteruserid');
        var addFilterCourseID = document.getElementById('addfiltercourseid');

        clearUserForm();

        hideAccessPanels();

        var accessForm = document.getElementById('mdltxt_usertree_userAccessPanel');

        accessForm.style.display = 'block';

        if (accountList.options.length > 0) {

            accountList.disabled = false;

        }

        if (xmlhttp.readyState == 4) {

            teachers = xmlhttp.responseXML.getElementsByTagName('Teacher');

            if (teachers.length > 0) {

                // Get values to display
                var display = teachers[0].getElementsByTagName('Lastname')[0].childNodes[0].nodeValue + ', ' + teachers[0].getElementsByTagName('Firstname')[0].childNodes[0].nodeValue;

                var userID = teachers[0].getElementsByTagName('UserID')[0].childNodes[0].nodeValue;
                var courseID = teachers[0].getElementsByTagName('Course')[0].getElementsByTagName('CourseID')[0].childNodes[0].nodeValue;

                nameDisplay.childNodes[0].nodeValue = display;
                usernameDisplay.childNodes[0].nodeValue = teachers[0].getElementsByTagName('Username')[0].childNodes[0].nodeValue;
                removeUserID.value = userID;
                grantUserID.value = userID;
                delFilterUserID.value = userID;
                delFilterCourseID.value = courseID;
                addFilterUserID.value = userID;
                addFilterCourseID.value = courseID;
                removeCourseID.value = courseID;
                grantCourseID.value = courseID;

                // Get course details
                var courses = teachers[0].getElementsByTagName('Course');

                var coursetext = '';

                for (var x = 0; x < courses.length; x++) {

                    var currentcourse = courses[x].getElementsByTagName('ShortName')[0].childNodes[0].nodeValue;

                    coursetext += ' ' + currentcourse;

                }

                courseDisplay.childNodes[0].nodeValue = coursetext;


                }

            // Get access details
            var accounts = teachers[0].getElementsByTagName('Account');

            if (accounts.length > 0) {

                accessList.disabled = false;

                for (var x = 0; x < accounts.length; x++) {

                    var newOption = document.createElement('option');
                    newOption.setAttribute('value', accounts[x].getElementsByTagName('LinkID')[0].childNodes[0].nodeValue);
                    newOption.text = accounts[x].getElementsByTagName('Username')[0].childNodes[0].nodeValue;

                    // Add option to list
                    try {

                        accessList.add(newOption, null);

                    } catch (ex) {

                        accessList.add(newOption); // Stupid IE. Bane of my life!

                    }

                }

            } else {

                var newOption = document.createElement('option');
                newOption.setAttribute('value', '');
                newOption.text = 'No access found.';

                try {

                    accessList.add(newOption, null);

                } catch (ex) {

                    accessList.add(newOption); // Stupid IE. Bane of my life!

                }

            }

            // Get filter details
            var filters = teachers[0].getElementsByTagName('InboundFilter');

            if (filters.length > 0) {

                filterList.disabled = false;

                for (var x = 0; x < filters.length; x++) {

                    var newOption = document.createElement('option');
                    newOption.setAttribute('value', filters[x].getElementsByTagName('FilterID')[0].childNodes[0].nodeValue);
                    var filterType = filters[x].getElementsByTagName('FilterType')[0].childNodes[0].nodeValue;
                    var filterValue = filters[x].getElementsByTagName('FilterValue')[0].childNodes[0].nodeValue;
                    var filterAcc = filters[x].getElementsByTagName('InboundAccountUsername')[0].childNodes[0].nodeValue;
                    newOption.text = filterType + ': ' + filterValue + ' (' + filterAcc + ')';

                    // Add option to list
                    try {

                        filterList.add(newOption, null);

                    } catch (ex) {

                        filterList.add(newOption); // Stupid IE. Bane of my life!

                    }

                }

            } else {

                var newOption = document.createElement('option');
                newOption.setAttribute('value', '');
                newOption.text = 'No access found.';

                try {

                    filterList.add(newOption, null);

                } catch (ex) {

                    filterList.add(newOption); // Stupid IE. Bane of my life!

                }

            }

        }

    }

    /**
     * Function to get course-level access details from the DB
     *
     * @param courseID The course to get access details for
     * @version 2007053112
     * @since 2007051112
     */

    function getCourseAccessDetails(courseID) {

        url = "getusers.php";

        xmlhttp.open("POST", url, true);
        xmlhttp.onreadystatechange = receiveCourseAccessDetails;
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send('fetchtype=course&courseid=' + courseID);

        hideAccessPanels();

        // Get access form and check for visibility
        var accessForm = document.getElementById('mdltxt_usertree_loadingPanel');
        accessForm.style.display = 'block';

        // Get access boxes
        var accessList = document.getElementById('courseaccountaccess');
        var accountList = document.getElementById('coursetxttoolsaccounts');

        accessList.disabled = true;
        accountList.disabled = true;

    }

    /**
     * Function to receive XML course-level access details from the DB
     * and shove them into the course access form
     *
     * @version 2007053112
     * @since 2007051112
     */

    function receiveCourseAccessDetails() {

        // Set up page id links
        var nameDisplay = document.getElementById('courseaccessname');
        var accessList = document.getElementById('courseaccountaccess');
        var accountList = document.getElementById('coursetxttoolsaccounts');
        var removeUserID = document.getElementById('courseremoveaccessuserid');
        var grantUserID = document.getElementById('coursegrantaccessuserid');
        var removeCourseID = document.getElementById('courseremovecourseid');
        var grantCourseID = document.getElementById('coursegrantcourseid');
        var accessForm = document.getElementById('mdltxt_usertree_courseAccessPanel');

        hideAccessPanels();

        accessForm.style.display = 'block';

        if (accountList.options.length > 0) {

            accountList.disabled = false;

        }

        if (xmlhttp.readyState == 4) {

            courses = xmlhttp.responseXML.getElementsByTagName('Course');

            if (courses.length > 0) {

                clearCourseForm();

                // Get values to display
                var display = courses[0].getElementsByTagName('FullName')[0].childNodes[0].nodeValue;
                var courseID = courses[0].getElementsByTagName('CourseID')[0].childNodes[0].nodeValue;

                nameDisplay.childNodes[0].nodeValue = display;
                removeCourseID.value = courseID;
                grantCourseID.value = courseID;

            }

            // Get access details
            var accounts = courses[0].getElementsByTagName('Account');

            if (accounts.length > 0) {

                accessList.disabled = false;

                for (var x = 0; x < accounts.length; x++) {

                    var newOption = document.createElement('option');
                    newOption.setAttribute('value', accounts[x].getElementsByTagName('AccountID')[0].childNodes[0].nodeValue);
                    newOption.text = accounts[x].getElementsByTagName('Username')[0].childNodes[0].nodeValue;

                    // Add option to list
                    try {

                        accessList.add(newOption, null);

                    } catch (ex) {

                        accessList.add(newOption); // Stupid IE. Bane of my life!

                    }

                }

            } else {

                var newOption = document.createElement('option');
                newOption.setAttribute('value', '');
                newOption.text = 'No access found.';

                try {

                    accessList.add(newOption, null);

                } catch (ex) {

                    accessList.add(newOption); // Stupid IE. Bane of my life!

                }

            }

        }

    }


    /**
     * Activate onscreen controls for editing accees details
     *
     * @version 2007053112
     * @since 2007051112
     */

    function activateEditAccess() {

        var editSelect = document.getElementById('userlist');
        var editButton = document.getElementById('editaccessbutton');
        var removeButton = document.getElementById('removeallaccessbutton');

        // Check for selected element
        if (editSelect.selectedIndex == -1) {

            // Disable buttons
            editButton.disabled = true;
            removeButton.disabled = true;

        } else {

            editButton.disabled = false;
            removeButton.disabled = false;

        }

    }

    /**
     * Activate onscreen controls for removing user access
     *
     * @param selectBox A reference to the select box to search
     * @version 2007053112
     * @since 2007051112
     */

    function activateRemoveControls(selectBox) {

        var removeButton = document.getElementById('removeaccessbutton');

        if (selectBox.selectedIndex == -1) {

            removeButton.disabled = true;

        } else {

            removeButton.disabled = false;

        }

    }

    /**
     * Activate onscreen controls for granting user access
     *
     * @param selectBox A reference to the select box to search
     * @version 2007053112
     * @since 2007051112
     */

    function activateGrantControls(selectBox) {

        var grantButton = document.getElementById('grantaccessbutton');

        if (selectBox.selectedIndex == -1) {

            grantButton.disabled = true;

        } else {

            grantButton.disabled = false;

        }

    }

    /**
     * Activate onscreen controls for removing course level access
     *
     * @param selectBox A reference to the select box to search
     * @version 2007053112
     * @since 2007051112
     */

    function activateCsRemoveControls(selectBox) {

        var removeButton = document.getElementById('removecourseaccessbutton');

        if (selectBox.selectedIndex == -1) {

            removeButton.disabled = true;

        } else {

            removeButton.disabled = false;

        }

    }

    /**
     * Activate onscreen controls for granting course level access
     *
     * @param selectBox A reference to the select box to search
     * @version 2007053112
     * @since 2007051112
     */

    function activateCsGrantControls(selectBox) {

        var grantButton = document.getElementById('grantcourseaccessbutton');

        if (selectBox.selectedIndex == -1) {

            grantButton.disabled = true;

        } else {

            grantButton.disabled = false;

        }

    }

    /**
     * Activate onscreen controls for removing user level inbound filters
     *
     * @param selectBox A reference to the select box to search
     * @version 2007053112
     * @since 2007051112
     */

    function actUserFilterDelControls(selectBox) {

        var delFilterButton = document.getElementById('userdelfilterbutton');

        if (selectBox.selectedIndex == -1) {

            delFilterButton.disabled = true;

        } else {

            delFilterButton.disabled = false;

        }

    }

    /**
     * Confirm that the user wants to remove all access from a given user
     *
     * @param selectBox A reference to the select box to search
     * @version 2007053112
     * @since 2007051112
     */

    function confirmRemoveAll(form) {

        var confirmremove = confirm("Are you sure you wish to remove all access for this user on this course?");

        return confirmremove;

    }

    function lockUserAccessForm(lockState) {

        document.getElementById('useraccountaccess').disabled = lockState;
        document.getElementById('removeaccessbutton').disabled = lockState;
        document.getElementById('usertxttoolsaccounts').disabled = lockState;
        document.getElementById('grantaccessbutton').disabled = lockState;
        document.getElementById('userinboundfilters').disabled = lockState;
        document.getElementById('userdelfilterbutton').disabled = lockState;
        document.getElementById('userfilteraccounts').disabled = lockState;
        document.getElementById('userfilterswkeyword').disabled = lockState;
        document.getElementById('userfilterswphone').disabled = lockState;
        document.getElementById('userfilterkeyword').disabled = lockState;
        document.getElementById('userfilterphone').disabled = lockState;
        document.getElementById('useraddfilterbutton').disabled = lockState;

    }

    function lockCourseAccessForm(lockState) {

        document.getElementById('courseaccountaccess').disabled = lockState;
        document.getElementById('removecourseaccessbutton').disabled = lockState;
        document.getElementById('coursetxttoolsaccounts').disabled = lockState;
        document.getElementById('grantcourseaccessbutton').disabled = lockState;

    }

    function setLoadingHeader(accessPanel) {

        var accessHeader = null;

        if (accessPanel == 'user') {

            accessHeader = document.getElementById('useraccessheader').childNodes[0];

        } else if (accessPanel == 'course') {

            accessHeader = document.getElementById('courseaccessheader').childNodes[0];

        } else {

            alert("Trying to set header that does not exist.  What you tryin' to do, you crazy fool!");

        }

        accessHeader.nodeValue = accessHeader.nodeValue + ' - Loading...';

    }

    function resetLoadingHeader(accessPanel) {

        var accessHeader = null;

        if (accessPanel == 'user') {

            accessHeader = document.getElementById('useraccessheader').childNodes[0];

        } else if (accessPanel == 'course') {

            accessHeader = document.getElementById('courseaccessheader').childNodes[0];

        } else {

            alert("Trying to set header that does not exist.  What you tryin' to do, you crazy fool!");

        }

        var headerValue = accessHeader.nodeValue;
        accessHeader.nodeValue = headerValue.substring(0, headerValue.length - 13);

    }

    function submitRemAccess() {

        var userid = document.getElementById('removeaccessuserid').value;
        var courseid = document.getElementById('removecourseid').value;
        var accountid = document.getElementById('useraccountaccess').value;

        lockUserAccessForm(true);
        setLoadingHeader('user');

        xmlhttp.open("POST", proc_url, true);
        xmlhttp.onreadystatechange = receiveRemAccess;
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send('vkey=' + vkey + '&formid=removeaccess&userID=' + userid + '&courseID=' + courseid +
                        '&accountaccess=' + accountid);

        return false;

    }

    function receiveRemAccess() {

        if (xmlhttp.readyState == 4) {

            var userid = document.getElementById('removeaccessuserid').value;
            var courseid = document.getElementById('removecourseid').value;

            lockUserAccessForm(false);
            resetLoadingHeader('user');

            getUserAccessDetails(userid, courseid);

        }

    }

    function submitGrantAccess() {

        var userid = document.getElementById('removeaccessuserid').value;
        var courseid = document.getElementById('removecourseid').value;
        var accountid = document.getElementById('usertxttoolsaccounts').value;

        lockUserAccessForm(true);
        setLoadingHeader('user');

        xmlhttp.open("POST", proc_url, true);
        xmlhttp.onreadystatechange = receiveGrantAccess;
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send('vkey=' + vkey + '&formid=grantaccess&userID=' + userid + '&courseID=' + courseid +
                        '&txttoolsaccounts=' + accountid);

        return false;

    }

    function receiveGrantAccess() {

        if (xmlhttp.readyState == 4) {

            var userid = document.getElementById('grantaccessuserid').value;
            var courseid = document.getElementById('grantcourseid').value;

            lockUserAccessForm(false);
            resetLoadingHeader('user');

            getUserAccessDetails(userid, courseid);

        }

    }

    function submitRemFilter() {

        var userid = document.getElementById('delfilteruserid').value;
        var filterid = document.getElementById('userinboundfilters').value;

        lockUserAccessForm(true);
        setLoadingHeader('user');

        xmlhttp.open("POST", proc_url, true);
        xmlhttp.onreadystatechange = receiveRemFilter;
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send('vkey=' + vkey + '&formid=deleteuserfilter&delfilteruserid=' + userid +
                        '&userinboundfilters=' + filterid);

        return false;

    }

    function receiveRemFilter() {

        if (xmlhttp.readyState == 4) {

            var userid = document.getElementById('delfilteruserid').value;
            var courseid = document.getElementById('delfiltercourseid').value;

            lockUserAccessForm(false);
            resetLoadingHeader('user');

            getUserAccessDetails(userid, courseid);

        }

    }

    function submitAddFilter() {

        var userid = document.getElementById('addfilteruserid').value;
        var accountid = document.getElementById('userfilteraccounts').value;
        var filterkeyword = document.getElementById('userfilterkeyword').value;
        var filterphone = document.getElementById('userfilterphone').value;
        var filterGroup = document.forms['userfilterformadd'].elements['filtertype'];
        var filterType = '';

        for (var x = 0; x < filterGroup.length; x++) {

            if (filterGroup[x].checked)
                filterType = filterGroup[x].value;

        }

        lockUserAccessForm(true);
        setLoadingHeader('user');

        xmlhttp.open("POST", proc_url, true);
        xmlhttp.onreadystatechange = receiveAddFilter;
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send('vkey=' + vkey + '&formid=adduserfilter&addfilteruserid=' + userid +
                        '&userfilteraccounts=' + accountid + '&filtertype=' + filterType +
                        '&userfilterkeyword=' + filterkeyword + '&userfilterphone=' + filterphone.replace('+', '%2B'));

        return false;

    }

    function receiveAddFilter() {

        if (xmlhttp.readyState == 4) {

            var userid = document.getElementById('addfilteruserid').value;
            var courseid = document.getElementById('addfiltercourseid').value;

            lockUserAccessForm(false);
            resetLoadingHeader('user');

            getUserAccessDetails(userid, courseid);

        }

    }

    function submitCourseRem() {

        var courseid = document.getElementById('courseremovecourseid').value;
        var accountid = document.getElementById('courseaccountaccess').value;

        lockCourseAccessForm(true);
        setLoadingHeader('course');

        xmlhttp.open("POST", proc_url, true);
        xmlhttp.onreadystatechange = receiveCourseRem;
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send('vkey=' + vkey + '&formid=courseremoveaccess&courseID=' + courseid + '&accountid=' + accountid);

        return false;

    }

    function receiveCourseRem() {

        if (xmlhttp.readyState == 4) {

            var courseid = document.getElementById('courseremovecourseid').value;

            lockCourseAccessForm(false);
            resetLoadingHeader('course');

            getCourseAccessDetails(courseid);

        }

    }

    function submitCourseGrant() {

        var courseid = document.getElementById('coursegrantcourseid').value;
        var accountid = document.getElementById('coursetxttoolsaccounts').value;

        lockCourseAccessForm(true);
        setLoadingHeader('course');

        xmlhttp.open("POST", proc_url, true);
        xmlhttp.onreadystatechange = receiveCourseGrant;
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send('vkey=' + vkey + '&formid=coursegrantaccess&courseID=' + courseid + '&accountid=' + accountid);

        return false;

    }

    function receiveCourseGrant() {

        if (xmlhttp.readyState == 4) {

            var courseid = document.getElementById('coursegrantcourseid').value;

            lockCourseAccessForm(false);
            resetLoadingHeader('course');

            getCourseAccessDetails(courseid);

        }

    }

//]]>
//-->
