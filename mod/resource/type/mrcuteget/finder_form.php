<?php
/**
 * $Id$
 * Defines the form used to search the ims repository
 */

require_once ($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/accesslib.php');


//get users roles to compare with $CFG->repositoryroles
$roles = get_user_roles(get_record('context', 'id', CONTEXT_SYSTEM), $USER->id);
foreach($roles as $role) { $userroles[] = $role->shortname;	}

//compare roles allowed to validate with user's assigned roles
$hasconfigrole = false;
foreach( explode(",",$CFG->repositoryroles) as $configrole){
	if ( in_array($configrole, $userroles) ){ $hasconfigrole = true; }
}

//if admin then allow validation (Draft / Final)
if (
		has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM, SITEID))
		OR $hasconfigrole
	) {
	$validate = true;
} else {
	$validate = false;
}


class mod_resource_ims_mod_form extends moodleform
{
    function definition()
	{
        global $CFG, $SESSION, $validate;
        $mform =& $this->_form;
		
		$mform->addElement('hidden',	'blockmode');

		$mform->addElement('header', '', 'Find materials');

		//search text box
        $mform->addElement('text', 'search', get_string('searchfor', 'resource_mrcuteget'), array('size'=>'35'));

		//checkboxes to choose search scope
		$searchin	= array();
		$searchin[] = &MoodleQuickForm::createElement('checkbox', 'title',			'', get_string('title', 'resource_mrcuteget'));
		$searchin[] = &MoodleQuickForm::createElement('checkbox', 'keywords',		'', get_string('keywords', 'resource_mrcuteget'));
		$searchin[] = &MoodleQuickForm::createElement('checkbox', 'description',	'', get_string('description', 'resource_mrcuteget'));
		$mform->addGroup($searchin, 'searchin', get_string('searchin', 'resource_mrcuteget'), array(' '), false);
		$mform->setAdvanced('searchin');

		//checkboxes to choose search scope
		$externalsearch	= array();
		$externalsearch[] = &MoodleQuickForm::createElement('checkbox', 'jorum',	'', 'JORUM');
		
		if(isset($CFG->mrcuteenablenln) && $CFG->mrcuteenablenln){
			$externalsearch[] = &MoodleQuickForm::createElement('checkbox', 'nln',		'', 'NLN');
		}
		
		$mform->addGroup($externalsearch, 'externalsearch', 'External search', array(' '), false);
		$mform->setAdvanced('externalsearch');

		//set defaults
		$mform->setDefault('title',			1);
		$mform->setDefault('keywords',		1);
		$mform->setDefault('description',	0);

		//author text box
        $mform->addElement('text', 'author', get_string('author', 'resource_mrcuteget'), array('size'=>'35'));
		$mform->setAdvanced('author');

		$sortby = array();
		$sortby['name']	= get_string('name', 'resource_mrcuteget');
		$sortby['modified']	= get_string('modifieddate', 'resource_mrcuteget');
		$sortby['created']	= get_string('createddate', 'resource_mrcuteget');
	    $mform->addElement('select', 'sortby', get_string('sortby', 'resource_mrcuteget'), $sortby);
		$mform->setAdvanced('sortby');

		if ($validate){
			$visibility = array();
			$visibility['all']		= get_string('all', 'resource_mrcuteget');
			$visibility['hidden']	= get_string('hidden', 'resource_mrcuteget');
			$visibility['nothidden']= get_string('nothidden', 'resource_mrcuteget');
			$mform->addElement('select', 'visibility', get_string('visibility', 'resource_mrcuteget'), $visibility);
			$mform->setAdvanced('visibility');
		}
        
		$mform->addElement('header', 'categorytitle', 'Categories');
		//echo '<div style="overflow: auto;height:100px;">';
		$path = $CFG->repository;
		$dir_handle = @opendir($path) or die("Unable to open $path");


		$baseurl =	"finder.php?".
			//"blockmode=$blockmode&amp;".
			//"perpage=$recordsperpage&amp;".
			"sesskey=".sesskey()."&amp;".
			"_qf__mod_resource_ims_mod_form=1&amp;".
			"search=&amp;".
			"sortby=name&amp;".
			"category%5B%5D=";
		
		$categorygroup = array();
		while ($file = readdir($dir_handle)) 
		{
			if(
				is_dir($CFG->repository."/".$file) && 
				!file_exists("$CFG->repository/$file/imsmanifest.xml") && 
				substr($file,0,1) != "."
				)
			{
				$categorygroup[] = &MoodleQuickForm::createElement('advcheckbox', "category[]", null, "<a href=\"$baseurl$file\">$file</a>", null, array(null, $file));
			}
		}
		closedir($dir_handle);
		//echo "</div>";
		$mform->addGroup($categorygroup, 'categorygroup', '', array(' '), false);


		
		//submit button
		$this->add_action_buttons(true, get_string('search', 'resource_mrcuteget'));
		

	}

}
?>