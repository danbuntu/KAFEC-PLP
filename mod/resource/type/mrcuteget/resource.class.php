<?php //$Id$

$CFG->repository = get_config(null, 'block_mrcute_repository');
$CFG->repositorywebroot = get_config(null, 'block_mrcute_repositorywebroot');

class resource_mrcuteget extends resource_base
{

    var $parameters;  //Attribute of this class where we'll store all the IMS deploy preferences

    function resource_mrcuteget($cmid=0)
	{
    /// super constructor
        parent::resource_base($cmid);

    /// prevent notice
        if (empty($this->resource->alltext)) {
            $this->resource->alltext='';
        }
    /// set own attributes
        $this->parameters = $this->alltext2parameters($this->resource->alltext);

    /// navigation menu forces other settings
        if ($this->parameters->navigationmenu) {
            $this->parameters->tableofcontents = 0;
            $this->parameters->navigationuparrow = 0;
            $this->parameters->skipsubmenus = 1;
        }

		
    /// Is it in the repository material or not?
        if (isset($this->resource->reference)) {
            $file = $this->resource->reference;
            if ($file[0] == '#') {
                $this->isrepository = true;
                $file = ltrim($file, '#');
                $this->resource->reference = $file;
            } else {
                $this->isrepository = false;
            }
        } else {
            $this->isrepository = false;
        }
		
    }
	
	function display()
    {
        global $CFG, $THEME, $USER;
		
        require_once($CFG->libdir.'/filelib.php');

    /// Set up generic stuff first, including checking for access
        parent::display();
		
	    /// Set up some shorthand variables
        $cm = $this->cm;
        $course = $this->course;
        $resource = $this->resource;

    /// Fetch parameters
        $inpopup = optional_param('inpopup', 0, PARAM_BOOL);
        $page    = optional_param('page', 0, PARAM_INT);
        $frameset= optional_param('frameset', '', PARAM_ALPHA);

    /// Init some variables
        $errorcode = 0;
        $buttontext = 0;
        $querystring = '';
        $resourcetype = '';
        $mimetype = mimeinfo("type", $resource->reference);
        $pagetitle = strip_tags($course->shortname.': '.format_string($resource->name));

        $formatoptions = new object();
        $formatoptions->noclean = true;

    /// Cache this per request
        static $items;
		
		///
		/// JORUM HACK... Temporary?!
		///
		if (substr($this->resource->reference, 0, 6) == 'JORUM#'){
		//	echo '<a href="'.substr($this->resource->reference, 6, strlen($this->resource->reference)).'">'.$this->resource->name.'</a>';
			echo '<script>window.location.replace(\''.substr($this->resource->reference, 6, strlen($this->resource->reference)).'\')</script>';
			return;
		}
		///end jorum hack
		
		///
		/// Display NLN
		///
		if (substr($this->resource->reference, 0, 4) == 'NLN#'){
			
			require($CFG->dirroot.'/mod/resource/type/nln/resource.class.php');
			$resourceclass = 'resource_'.$resource->type;
			$resourceinstance = new resource_nln($cm->id);
			
			$nlnref = substr($this->resource->reference, 5, strlen($this->resource->reference));
			$nlnref = substr($nlnref, 0, strlen($nlnref)-1);
			
			$resourceinstance->resource->reference = $nlnref;
			
			$resourceinstance->display();

			return;
		}
		///end nln hack
		
		
		//visibility
		$visible = get_field('resource_ims', "visible", 'filepath', $this->resource->reference);
		if( !file_exists($CFG->repository.$this->resource->reference.'/imsmanifest.xml') ){
            print_header($pagetitle, $course->fullname, format_string($resource->name), "", "", true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm));
			notice("This IMS Content Package has been deleted.");
			return;
        } else if ($visible == 0) {
            print_header($pagetitle, $course->fullname, format_string($resource->name), "", "", true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm));
			notice ("This IMS Content Package has been hidden.");
			return;
		}
		
		


    /// Check for errors
        $errorcode = $this->check4errors($resource->reference, $course, $resource);

    /// If there are any error, show it instead of the resource page
        if ($errorcode) {
            if (!has_capability('moodle/course:activityvisibility', get_context_instance(CONTEXT_COURSE, $course->id))) {
            /// Resource not available page
                $errortext = get_string('resourcenotavailable','resource');
            } else {
            /// Depending of the error, show different messages and pages
                if ($errorcode ==1) {
                    $errortext = get_string('invalidfiletype','error', $resource->reference);
                } else if ($errorcode == 2) {
                    $errortext = get_string('filenotfound','error', $resource->reference);
                } else if ($errorcode == 3) {
                    $errortext = get_string('packagenotdeplyed','resource');
                } else if ($errorcode == 4) {
                    $errortext = get_string('packagechanged','resource');
                } else if ($errorcode == 5) {
                    $errortext = get_string('packagenotdeplyed','resource'); // no button though since from repository.
                }
            }
        /// Display the error and exit
            if ($inpopup) {
                print_header($pagetitle, $course->fullname.' : '.$resource->name);
            } else {
                print_header($pagetitle, $course->fullname, format_string($resource->name), "", "", true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm));
            }
            print_simple_box_start('center', '60%');
            echo '<p align="center">'.$errortext.'</p>';
        /// If errors were 3 or 4 and isteacheredit(), show the deploy button
            if (has_capability('moodle/course:manageactivities', get_context_instance(CONTEXT_COURSE, $course->id)) && ($errorcode == 3 || $errorcode == 4)) {
                $link = 'type/ims/deploy.php';
                $options['courseid'] = $course->id;
                $options['cmid'] = $cm->id;
                $options['file'] = $resource->reference;
                $options['sesskey'] = $USER->sesskey;
                $options['inpopup'] = $inpopup;
                if ($errorcode == 3) {
                    $label = get_string ('deploy', 'resource');
                } else if ($errorcode == 4) {
                     $label = get_string ('redeploy', 'resource');
                }
                $method='post';
            /// Let's go with the button
                echo '<center>';
                print_single_button($link, $options, $label, $method);
                echo '</center>';
            }
            print_simple_box_end();
        /// Close button if inpopup
            if ($inpopup) {
                close_window_button();
            }

            print_footer();
            exit;
        }

    /// Load serialized IMS CP index to memory only once.
        if (empty($items)) {
            if (!$this->isrepository) {
                $resourcedir = $CFG->dataroot.'/'.$course->id.'/'.$CFG->moddata.'/resource/'.$resource->id;
				if (!$items = ims_load_serialized_file($resourcedir.'/moodle_inx.ser')) {
					error (get_string('errorreadingfile', 'error', 'moodle_inx.ser'));
				}
            } else {
                $resourcedir = $CFG->repository . $resource->reference;
	            if ($deployindex = get_record_select('resource_ims', "filepath='".$resource->reference."'", 'deploy')) {
	                $items = unserialize($deployindex->deploy);
				} else {
					error ("This IMS Content Package is not deployed.");
	            }
           }
        }

    /// Check whether this is supposed to be a popup, but was called directly

        if (empty($frameset) && $resource->popup && !$inpopup) {    /// Make a page and a pop-up window

            print_header($pagetitle, $course->fullname, format_string($resource->name), "", "", true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm));

            echo "\n<script type=\"text/javascript\">";
            echo "\n<!--\n";
            echo "openpopup('/mod/resource/view.php?inpopup=true&id={$cm->id}','resource{$resource->id}','{$resource->popup}');\n";
            echo "\n-->\n";
            echo '</script>';

            if (trim(strip_tags($resource->summary))) {
                print_simple_box(format_text($resource->summary, FORMAT_MOODLE, $formatoptions), "center");
            }

            $link = "<a href=\"$CFG->wwwroot/mod/resource/view.php?inpopup=true&amp;id={$cm->id}\" target=\"resource{$resource->id}\" onclick=\"return openpopup('/mod/resource/view.php?inpopup=true&amp;id={$cm->id}', 'resource{$resource->id}','{$resource->popup}');\">".format_string($resource->name,true)."</a>";

            echo "<p>&nbsp;</p>";
            echo '<p align="center">';
            print_string('popupresource', 'resource');
            echo '<br />';
            print_string('popupresourcelink', 'resource', $link);
            echo "</p>";

            print_footer($course);
            exit;
        }


    /// No frames or framesets anymore, except iframe. in print_ims, iframe filled.
    /// needs callback to this file to display table of contents in the iframe so
    /// $frameset = 'toc' leads to output of toc and blank or 'ims' produces the
    /// iframe.
        if (empty($frameset) || $frameset=='ims') {

        /// Conditional argument to pass to IMS JavaScript. Need to be global to retrieve it from our custom javascript! :-(
            global $jsarg;
            $jsarg = 'false';
            if (!empty($this->parameters->navigationmenu)) {
                $jsarg = 'true';
            }
        /// Define $CFG->javascript to use our custom javascript. Save the original one to add it from ours. Global too! :-(
            global $standard_javascript;
            $standard_javascript = $CFG->javascript;  // Save original javascript file
            $CFG->javascript = $CFG->dirroot.'/mod/resource/type/ims/javascript.php';  //Use our custom IMS javascript code

        /// moodle header
            if ($resource->popup) {
                //print_header($pagetitle, $course->fullname.' : '.$resource->name);
                print_header();
            } else {
                print_header($pagetitle, $course->fullname, format_string($resource->name), "", "", true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm, "parent"));
			}
        /// content - this produces everything else
            $this->print_ims($cm, $course, $items, $resource, $page);
        /// Moodle footer is back! Now using the DOMContentLoaded event (see resize.js) to trigger the resize
        /// no Moodle footer (because we cannot insert there the resize script).
        /// echo "</div></div><script type=\"text/javascript\">resizeiframe($jsarg);</script></body></html>";
        /// print_footer();
            echo "</div></div></body></html>";

        /// log it.
            add_to_log($course->id, "resource", "view", "view.php?id={$cm->id}", $resource->id, $cm->id);
            exit;
        }

        if ($frameset == 'toc') {
            print_header();
            $this->print_toc($items, $resource, $page);
            echo '</div></div></body></html>';
            exit;
        }
    }

	/// Function print_ims prints nearly the whole page. Stupid name subject to change :-)
    function print_ims($cm, $course, $items, $resource, $page) {
        global $CFG;

    /// Set the correct contentframe id based on $this->parameters->navigationmenu
        if (!empty($this->parameters->navigationmenu)) {
            $contentframe = 'ims-contentframe';
        } else {
            $contentframe = 'ims-contentframe-no-nav';
        }

    /// Calculate the file.php correct url
        if (!$this->isrepository) {
            if ($CFG->slasharguments) {
                $fileurl = "{$CFG->wwwroot}/file.php/{$course->id}/{$CFG->moddata}/resource/{$resource->id}";
            } else {
                $fileurl = "{$CFG->wwwroot}/file.php?file=/{$course->id}/{$CFG->moddata}/resource/{$resource->id}";
            }
        }
        else {
            $fileurl = $CFG->repositorywebroot . $resource->reference;
        }


    /// Calculate the view.php correct url
        $viewurl = "view.php?id={$cm->id}&amp;type={$resource->type}&amp;frameset=toc&amp;page=";


    /// Decide what to show (full toc, partial toc or package file)
        $fullurl = '';
        if (empty($page) && !empty($this->parameters->tableofcontents)) {
        /// Full toc contents
            $fullurl = $viewurl.$page;
        } else {
            if (empty($page)) {
            /// If no page and no toc, set page 1 unless skipping submenus, in which case fast forward:
                $page = 1;
                if (!empty($this->parameters->skipsubmenus)) {
                    while (empty($items[$page]->href) && !empty($items[$page])) {
                        $page++;
                    }
                }
            }
            if (empty($items[$page]->href)) {
            /// The page hasn't href, then partial toc contents
                $fullurl = $viewurl.$page;
            } else {
            /// The page has href, then its own file contents
            /// but considering if it seems to be an external url or a internal one
                if (strpos($items[$page]->href, '//') !== false) {
                /// External URL
                    $fullurl = $items[$page]->href;
                } else {
                /// Internal URL, use file.php
                    $fullurl = $fileurl.'/'.$items[$page]->href;
                }
            }
        }

    /// print navigation buttons if needed
        if (!empty($this->parameters->navigationbuttons)) {
            $this->print_nav($items, $resource, $page);
        }

        echo '<div id="ims-containerdiv">';
    /// adds side navigation bar if needed. must also adjust width of iframe to accomodate
        if (!empty($this->parameters->navigationmenu)) {
            echo "<div id=\"ims-menudiv\">"; $this->print_navmenu($items, $resource, $page); echo "</div>";
        }

    /// prints iframe filled with $fullurl
        echo "<iframe id=\"".$contentframe."\" name=\"".$contentframe."\" src=\"{$fullurl}\" title=\"".get_string('modulename','resource')."\">Your browser does not support inline frames or is currently configured not to display inline frames. Content can be viewed at {$fullurl}</iframe>"; //Content frame
        echo '</div>';
    }

	
	/// Prints side navigation menu. This is just the full TOC with no surround.
    function print_navmenu($items, $resource, $page=0) {
        echo ims_generate_toc ($items, $resource, 0, $page);
    }

	
	function setup_elements(&$mform)
	{
        global $CFG, $RESOURCE_WINDOW_OPTIONS;

		//$mform->addElement('text', 'reference', '', array('size'=>'48', 'id'=>'id_reference_value'));
		$mform->addElement('text', 'reference', '', array('size'=>'48'));
		//display Find button
		$url = "/mod/resource/type/mrcuteget/finder.php";
        $options = 'menubar=0,location=0,scrollbars,resizable,width=750,height=500';
        //$options = 'toolbar=yes,menubar=1,location=0,scrollbars,resizable,width=750,height=500';
		$fullscreen = 0;
		$buttonattributes = array("onclick"=>"return openpopup('$url', 'finder', '$options', $fullscreen);");
		$mform->addElement('button', 'finder', "Find materials", $buttonattributes);
		
		HTML_QuickForm::registerRule('required', 'regex', '/.?/');

		$mform->addRule('name', null, 'required', null, 'client');
		
        $mform->addElement('header', 'displaysettings', 'Options');

        $woptions = array(0 => get_string('pagewindow', 'resource'), 1 => get_string('newwindow', 'resource'));
        $mform->addElement('select', 'windowpopup', get_string('display', 'resource'), $woptions);
        $mform->setDefault('windowpopup', !empty($CFG->resource_popup));

        foreach ($RESOURCE_WINDOW_OPTIONS as $option) {
            if ($option == 'height' or $option == 'width') {
                $mform->addElement('text', $option, get_string('new'.$option, 'resource'), array('size'=>'4'));
                $mform->setDefault($option, $CFG->{'resource_popup'.$option});
                $mform->disabledIf($option, 'windowpopup', 'eq', 0);
            } else {
                $mform->addElement('checkbox', $option, get_string('new'.$option, 'resource'));
                $mform->setDefault($option, $CFG->{'resource_popup'.$option});
                $mform->disabledIf($option, 'windowpopup', 'eq', 0);
            }
            $mform->setAdvanced($option);
        }

       // $mform->addElement('header', 'parameters', get_string('parameters', 'resource'));

        $mform->addElement('selectyesno', 'param_navigationmenu', get_string('navigationmenu', 'resource'));
        $mform->setDefault('param_navigationmenu', 1);
		$mform->setAdvanced('param_navigationmenu');

        $mform->addElement('selectyesno', 'param_tableofcontents', get_string('tableofcontents', 'resource'));
        $mform->disabledIf('param_tableofcontents', 'param_navigationmenu', 'eq', 1);
        $mform->setDefault('param_tableofcontents', 0);
		$mform->setAdvanced('param_tableofcontents');


        $mform->addElement('selectyesno', 'param_navigationbuttons', get_string('navigationbuttons', 'resource'));
        $mform->setDefault('param_navigationbuttons', 0);
		$mform->setAdvanced('param_navigationbuttons');

        $mform->addElement('selectyesno', 'param_skipsubmenus', get_string('skipsubmenus', 'resource'));
        $mform->setDefault('param_skipsubmenus', 1);
        $mform->disabledIf('param_skipsubmenus', 'param_navigationmenu', 'eq', 1);
		$mform->setAdvanced('param_skipsubmenus');

        $mform->addElement('selectyesno', 'param_navigationupbutton', get_string('navigationup', 'resource'));
        $mform->setDefault('param_navigationupbutton', 1);
        $mform->disabledIf('param_navigationupbutton', 'param_navigationmenu', 'eq', 1);
		$mform->setAdvanced('param_navigationupbutton');

		
	}

/**
    * Add new instance of file resource
    *
    * Create alltext field before calling base class function.
    *
    * @param    resource object
    */
	function add_instance($resource)
	{
		$this->_postprocess($resource);
		return parent::add_instance($resource);
	}

/**
    * Update instance of file resource
    *
    * Create alltext field before calling base class function.
    *
    * @param    resource object
    */
    function update_instance($resource)
	{
        $this->_postprocess($resource);
        return parent::update_instance($resource);
    }

/** Delete instance of IMS-CP resource
     *
     * Delete all the resource
     * @param    resource object
     */
    function delete_instance($resource) {
        return parent::delete_instance($resource);
    }

	
	function _postprocess(&$resource)
	{
		global $RESOURCE_WINDOW_OPTIONS;
		$alloptions = $RESOURCE_WINDOW_OPTIONS;

		if ($resource->windowpopup) {
			$optionlist = array();
			foreach ($alloptions as $option) {
				$optionlist[] = $option."=".$resource->$option;
				unset($resource->$option);
			}
			$resource->popup = implode(',', $optionlist);
			unset($resource->windowpopup);

		} else {
			$resource->popup = '';
		}
		/// Load parameters to this->parameters
		$this->parameters = $this->form2parameters($resource);
		/// Save parameters into the alltext field
		$resource->alltext = $this->parameters2alltext($this->parameters);
	}

	
 /*** This function checks for errors in the status or deployment of the IMS
    * Content Package returning an error code:
    * 1 = Not a .zip file.
    * 2 = Zip file doesn't exist
    * 3 = Package not deployed.
    * 4 = Package has changed since deployed.
    * If the IMS CP is one from the central repository, then we instead check
    * with the following codes:
    * 5 = Not deployed. Since repository is central must be admin to deploy so terminate
    */
    function check4errors($file, $course, $resource)
	{
        global $CFG;

        if ($this->isrepository) {
        /// Calculate the path were the IMS package must be deployed
            $deploydir = $CFG->repository . $file;

        /// Confirm that the IMS package has been deployed in the database
            if (!get_record_select('resource_ims', "filepath='".$file."'", 'deploy')) {
                return 5;    //Error
            }
        }
        else {
        /// Check for zip file type
            $mimetype = mimeinfo("type", $file);
            if ($mimetype != "application/zip") {
                return 1;    //Error
            }

        /// Check if the uploaded file exists
            if (!file_exists($CFG->dataroot.'/'.$course->id.'/'.$file)) {
                return 2;    //Error
            }

        /// Calculate the path were the IMS package must be deployed
            $deploydir = $CFG->dataroot.'/'.$course->id.'/'.$CFG->moddata.'/resource/'.$resource->id;

/*//OBSELETE???, now db based
        /// Confirm that the IMS package has been deployed. These files must exist if
        /// the package is deployed: moodle_index.ser and moodle_hash.ser
		if (!file_exists($deploydir.'/moodle_inx.ser') ||
                !file_exists($deploydir.'/moodle_hash.ser')) {
                return 3;    //Error
            }
*/

        /// If teacheredit, make, hash check. It's the md5 of the name of the file
        /// plus its size and modification date
        /// not sure if this capability is suitable
            if (has_capability('moodle/course:manageactivities', get_context_instance(CONTEXT_COURSE, $course->id))) {
                if (!$this->checkpackagehash($file, $course, $resource)) {
                    return 4;
                }
            }
        }

    /// We've arrived here. Everything is ok
        return 0;
    }

/***
    * This function will convert all the parameters configured in the resource form
    * to a this->parameter attribute (object)
    */
    function form2parameters($resource)
	{
        $parameters = new stdClass;
        $parameters->tableofcontents = isset($resource->param_tableofcontents) ? $resource->param_tableofcontents : 0;
        $parameters->navigationbuttons = $resource->param_navigationbuttons;
        $parameters->skipsubmenus = isset($resource->param_skipsubmenus) ? $resource->param_skipsubmenus : 0;
        $parameters->navigationmenu = $resource->param_navigationmenu;
        $parameters->navigationupbutton = isset($resource->param_navigationupbutton) ? $resource->param_navigationupbutton : 0;

        return $parameters;
    }

/***
    * This function converts the this->parameters attribute (object) to the format
    * needed to save them in the alltext field to store all the special configuration
    * of this resource type
    */
    function parameters2alltext($parameters)
	{
        $optionlist = array();

        $optionlist[] = 'tableofcontents='.$parameters->tableofcontents;
        $optionlist[] = 'navigationbuttons='.$parameters->navigationbuttons;
        $optionlist[] = 'skipsubmenus='.$parameters->skipsubmenus;
        $optionlist[] = 'navigationmenu='.$parameters->navigationmenu;
        $optionlist[] = 'navigationupbutton='.$parameters->navigationupbutton;

        return implode(',', $optionlist);
    }

/***
    * This function converts parameters stored in the alltext field to the proper
    * this->parameters object storing the special configuration of this resource type
    */
    function alltext2parameters($alltext) {
        /// set parameter defaults
        $alltextfield = new stdClass();
        $alltextfield->tableofcontents=0;
        $alltextfield->navigationbuttons=0;
        $alltextfield->navigationmenu=1;
        $alltextfield->skipsubmenus=1;
        $alltextfield->navigationupbutton=1;

    /// load up any stored parameters
        if (!empty($alltext)) {
            $parray = explode(',', $alltext);
            foreach ($parray as $key => $fieldstring) {
                $field = explode('=', $fieldstring);
                $alltextfield->$field[0] = $field[1];
            }
        }

        return $alltextfield;
    }


}
/*
function ims_file2var ($file)
{
	$status = true;
	$var = '';
	$fp = fopen($file, 'r')
		or $status = false;
	if ($status) {
	   while ($data = fread($fp, 4096)) {
		   $var = $var.$data;
	   }
	   fclose($fp);
	}
	if (!$status) {
		$var = false;
	}
	return $var;
}
*/
    /*** This function will generate the TOC file for the package
     *   from an specified parent to be used in the view of the IMS
     *   Now hilights 'selected page' also.
     */
    function ims_generate_toc($items, $resource, $page=0, $selected_page = -1)
	{
        global $CFG;

        $contents = '';

    /// Configure links behaviour
        $fullurl = $CFG->wwwroot.'/mod/resource/view.php?r='.$resource->id.'&amp;frameset=ims&amp;page=';

    /// Iterate over items to build the menu
        $currlevel = 0;
        $currorder = 0;
        $endlevel  = 0;
        $openlielement = false;
        foreach ($items as $item) {
            if (!is_object($item)) {
                continue;
            }
        /// Skip pages until we arrive to $page
            if ($item->id < $page) {
                continue;
            }
        /// Arrive to page, we store its level
            if ($item->id == $page) {
                $endlevel = $item->level;
                continue;
            }
        /// We are after page and inside it (level > endlevel)
            if ($item->id > $page && $item->level > $endlevel) {
            /// Start Level
                if ($item->level > $currlevel) {
                    $contents .= '<ol class="listlevel_'.$item->level.'">';
                    $openlielement = false;
                }
            /// End Level
                if ($item->level < $currlevel) {
                    $contents .= '</li>';
                    $contents .= '</ol>';
                }
            /// If we have some openlielement, just close it
                if ($openlielement) {
                    $contents .= '</li>';
                }
            /// Add item
                $contents .= '<li>';
                if (!empty($item->href)) {
                    if ($item->id == $selected_page) $contents .= '<div id="ims-toc-selected">';
                    $contents .= '<a href="'.$fullurl.$item->id.'" target="_parent">'.$item->title.'</a>';
                    if ($item->id == $selected_page) $contents .= '</div>';
                } else {
                    $contents .= $item->title;
                }
                $currlevel = $item->level;
                $openlielement = true;
                continue;
            }
        /// We have reached endlevel, exit
            if ($item->id > $page && $item->level <= $endlevel) {
                break;
            }
        }
    /// Close up to $endlevel
        for ($i=$currlevel;$i>$endlevel;$i--) {
            $contents .= '</li>';
            $contents .= '</ol>';
        }

        return $contents;
    }


?>