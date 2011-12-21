
Readme file for the NLN Materials Moodle Browser (codename: Noodle)
====== ==== === === === ========= ====== ======= ========== =======

Introduction
------------
Noodle is a plug-in module for Moodle systems that allows Moodle users to find and use NLN Materials with a minimum of effort. The benefits include:
For administrators:
- no need to create and maintain a repository.
- works alongside but completely independently of local repositories.
- simple installation. Does not require "IMS Repository" module or its variants.
- materials are still hosted on the nln.ac.uk site, so users will see all updates and fixes, and you may save bandwidth

For practitioners:
- no need to log-in separately
- powerful search and browse functionality, specifically tailored for the NLN Materials (eg. browse by level)
- easy access to supporting information, such as tutor guides, LO-specific FAQ questions etc.
- no need to leave the Moodle interface
- no need to understand Scorm and related technologies
- no need to deal with any downloading/unzipping/uploading/or installing of files

Full information about Noodle, including any updates and new versions, is available at http://www.nln.ac.uk/?p=Noodle

This is version 0.3

Version Info
------- ----

V0.1 - first release.
V0.2 - fixes compatability with Moodle 1.8, by a modification in line 74 of resource.class.php, checking that the function build_navigation() exists.
V0.3 - fixes a bug that caused resources not to be added to the database in some environments.

Note that the only changes in v0.2 and v0.3 are to the file resource.class.php, so if you have an existing installation you only need to replace that file.

Note that since the bulk of functionality happens on the NLN site, changes in functionality may occur without requiring a new version of Noodle or a new download. Any significant changes of functionality will be explained on the Noodle page of the NLN website.


License
-------
This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.

By installing this software package and using it (or any derivative) to connect to the NLN site, you are also agreeing to the NLN Materials usage license ON BEHALF OF ALL MOODLE USERS WHO WILL BE GIVEN ACCESS TO IT. It is essential to understand the implications of this. Primarily this means that all access to the browser, and any course that includes NLN Materials, must be password-protected by Moodle (or otherwise access-limited) to tutors and learners within your organisation, and never exposed on the public internet, and by installing and exposing Noodle's functionality, you are taking the necessary steps (in technical terms, and through education of staff) that such requirements will be adhered to. For more information please see http://www.nln.ac.uk/?p=Noodle. 

You may modify the files included in this file as you wish, as long as no attempt is made to alter or bypass the authentication mechanism used. The GPL license confers no rights to the www.nln.ac.uk site itself (including the pages from the site exposed by Noodle).


Support
-------
Noodle is being released free of charge as a (hopefully) useful additional service to the NLN Materials community. As such, and given the potential differences between any two Moodle installations, we cannot promise to provide comprehensive support for it. However, we are happy to receive all queries, suggestions and questions on it. A dedicated thread on the Moodle.org forums has been set up for this, and your can also contact the NLN Materials Service help-desk (http://www.nln.ac.uk/?p=Contact). Any specific problems regarding accounts, registration, organisation passwords etc. should always be addressed to the help-desk.


Installation
------------

Note, if you are upgrading an existing Noodle installation, please see the "Version info" section above.

Installation is simple:
1. Create a new folder in your Moodle install called "nln" within "/mod/resource/type/".
2. Unzip the contents of this zip into it.
3. Open the file nln_config.php within that folder and enter the correct values for you organisation ID and organisation password. These can be found in the "Installation" section at http://www.nln.ac.uk/?p=Noodle
4. Open the file "/lang/en_utf8/resource.php" and add the following five lines before the final "?>".

$string['resourcetypenln'] = 'NLN Learning Object';
$string['nln_browse'] = 'Browse the NLN Materials';
$string['nln_browsedescrip'] = 'Click this button to view the NLN Materials browser, which lets you browse, preview, and select an NLN Learning Object';
$string['nln_guid'] = 'NLN Learning Object ID';
$string['nln_required'] = 'Please select an NLN Learning Object by clicking the button below. If you do not wish to an an NLN LO, click the Cancel button below.';

These provide the text for the various custom bits of interface exposed by Noodle - feel free to edit them if you wish. Respectively, they represent:
	1. The entry in the drop-down list of resource types that can be added to a course
	2. The caption of the browse button on the resource page
	3. The pop-up hint when hovering over the browse button
	4. The caption next to the read-only edit box that contains the NLN Learning Object's unique ID
	5. A message to be displayed if the user tries to "OK" the resource page without choosing an NLN LO

Finally, you may wish to visit the "resource defaults" page, to review/edit the default properties for resource pop-up windows (and whether to use a pop-up or embed the resource within the Moodle interface). If you have never visited this page and saved the changes, you may find that no defaults have been set at all. To visit the resource defaults page, from your Moodle home page find the "Site administration" block and navigate through the menu to Modules/Activities/Resource.

Technical Description
--------- -----------
Moodle has a flexible architecture for adding new resource types. The installation of Noodle simply adds a new available resource type, available for practitioners by choosing "NLN Learning Object" from the options available in the "add a resource" drop-down list. Installation requires no modifications to the database structure within Moodle, and does not interfere with any local repository, or any NLN materials already downloaded/installed/deployed via any other method. When a practitioner adds a resource of the new "nln" type, a new row is added to the "resources" table. The unique identifier of the LO that was chosen by the practitioner using the NLN browser is stored in the resource's "reference" field. The "popup" and "options" fields describe the display options, using much the same values as the built-in file/web resource type.

The installation contains the following:
 - readme.txt - this file.
 - nln_config.php - to be customised with values that provide authentication to the NLN server
 - resource.class.php - defines the new NLN resource type to Moodle
 - browse_start.php - is launched in a pop-up window, and, while showing a loading screen, passes the configuration values to, and launches, the special version of the NLN site.
 - browse_end.php - launched at the end of the browsing process, uses client-side scripting to populate the main Moodle window with the ID, title and description of the selected NLN LO.

