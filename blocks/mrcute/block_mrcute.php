<?php

class block_mrcute extends block_base
{
	function init()
	{
		$this->title = get_string('blocktitle', 'block_mrcute');
		$this->version = 2009050800;
	}

	function get_content()
	{
		global $CFG, $USER;
		
	    if ($this->content !== null)
		{
	        return $this->content;
	    }

		$strsearch = get_string('search', 'block_mrcute');
        $advancedsearch = get_string('advancedsearch', 'block_mrcute');

	    $this->content = new stdClass;
		$this->content->footer = '';

		$url = "/mod/resource/type/mrcuteget/finder.php?blockmode=1&sesskey=".$USER->sesskey."&_qf__mod_resource_ims_mod_form=1&title=1&keywords=1&author=0&sortby=name&submitbutton=1&search=";
        $options = 'menubar=0,location=0,scrollbars,resizable,width=750,height=500';
		$fullscreen = 0;
		$buttonattributes = "return openpopup('$url'+document.getElementById('id_searchmrcute').value, 'finder', '$options', $fullscreen);";

        $this->content->text  = '<div class="searchform" style="text-align:center;">';
        $this->content->text .= '<form style="display:inline" onsubmit="'.$buttonattributes.'"><fieldset class="invisiblefieldset">';
        $this->content->text .= '<label class="accesshide" for="searchform_search">'.$strsearch.'</label>'.
                                '<input id="id_searchmrcute" name="searchmrcute" type="text" size="16" />';

        $this->content->text .= '<button id="searchform_button" type="submit" title="'.$strsearch.'">'.$strsearch.'</button><br />';

		$url = "/mod/resource/type/mrcuteget/finder.php?blockmode=1";
        $this->content->text .= '<a href="#" onclick="return openpopup(\''.$url.'\', \'finder\', \''.$options.'\', '.$fullscreen.');">'.$advancedsearch.'</a>';

        $this->content->text .= '</fieldset></form></div>';

	    return $this->content;
	}

	function has_config() {
		return true;
	}
	
	function config_save($data)
	{
	  foreach ($data as $name => $value)
	  {
		set_config($name, $value);
	  }
	  return true;
	}
}

?>