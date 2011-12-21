<?php
/**
 * $Id: preview.php,v 1.2 2008/04/17 08:49:33 afhole Exp $
 * View an IMS Package
 */
    require_once('../../../../config.php');
    require_once('../../lib.php');
    require_once('resource.class.php');
    require_once('../../../../backup/lib.php');
    require_once('../../../../lib/filelib.php');
    require_once('../../../../lib/xmlize.php');
        
    $directory = required_param ('directory', PARAM_PATH);
    $choose = optional_param ('choose', 'id_reference', PARAM_FILE);
    $page = optional_param ('page', 0, PARAM_INT);
	$blockmode	= optional_param('blockmode', 0, PARAM_BOOL);


/// Calculate the path of the IMS CP to be displayed
    $deploydir = $CFG->repository . '/' . $directory;

/// Confirm that the IMS package has been deployed and load serialized IMS CP index to memory only once.
    if (empty($items)) {
        if ($deployindex = get_record_select('resource_ims', "filepath='".$directory."'")) {
			$items = unserialize($deployindex->deploy);
			$items['description'] = $deployindex->description;
		} else {
            $errortext = "Not Deployed";
            print_header();
            print_simple_box_start('center', '60%');
            echo '<p align="center">'.$errortext.'</p>';
            print_footer();
            exit;
        }
    }

/// fast forward to first non-index page
    while (empty($items[$page]->href)) $page++;
    
/// Select direction
    if (get_string('thisdirection') == 'rtl') {
        $direction = ' dir="rtl"';
    } else {
        $direction = ' dir="ltr"';
    }

/// Conditional argument to pass to IMS JavaScript. Need to be global to retrieve it from our custom javascript! :-(
    global $jsarg;
    $jsarg = 'true';
/// Define $CFG->javascript to use our custom javascript. Save the original one to add it from ours. Global too! :-(
    global $standard_javascript;
    $standard_javascript = $CFG->javascript;  // Save original javascript file
    $CFG->javascript = $CFG->dirroot.'/mod/resource/type/ims/javascript.php';  //Use our custom IMS javascript code

/// The output here

/// moodle header
    print_header();
/// content - this produces everything else

/// adds side navigation bar if needed. must also adjust width of iframe to accomodate 
    echo "<div id=\"ims-menudiv\">";  
    preview_buttons($directory, $items, $choose); 
    echo preview_ims_generate_toc($items, $directory, 0, $page); echo "</div>";
    
	
	if (strpos($items[$page]->href, '//') !== false) {
		/// External URL
		$fullurl = $items[$page]->href;
	}else{
		$fullurl = "$CFG->repositorywebroot/$directory/".$items[$page]->href;
	}
	
/// prints iframe filled with $fullurl ;width:".$iframewidth." missing also height=\"420px\"
    echo "<iframe id=\"ims-contentframe\" name=\"ims-contentframe\" src=\"{$fullurl}\"></iframe>"; //Content frame 
/// moodle footer
    echo "</div></div><script type=\"text/javascript\">resizeiframe($jsarg);</script></body></html>";
    
    
    /*** This function will generate the TOC file for the package
     *   from an specified parent to be used in the view of the IMS
     */
    function preview_ims_generate_toc($items, $directory, $page=0, $selected_page) {
        global $CFG, $blockmode;

        $contents = '';

    /// Configure links behaviour
        $fullurl = '?blockmode='.$blockmode.'&amp;directory='.$directory.'&amp;page=';

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
    
    function preview_buttons($directory, $items, $choose='') {
	global $blockmode;
        $strchoose = get_string('choose','resource');
        $strback = get_string('back','resource');
        
        $path = $directory;
        $arr = explode('/', $directory);
        array_pop($arr);
        $directory = implode('/', $arr);
        ?>
        <script type="text/javascript">
        //<![CDATA[
		function set_value(location,name,description) {
			opener.document.getElementById('id_reference').value = location;
			opener.document.getElementById('id_name').value = name;
			
			var	fram = opener.document.getElementsByTagName('iframe')[0];
			var oDoc = fram.contentWindow || fram.contentDocument;
			oDoc.document.body.innerHTML = description;

			window.close();
		}
        //]]>
        </script>
        <?php
        echo "<div id=\"ims_preview_buttons\" style=\"padding:10px;\">".
        //     "(<a href=\"finder.php?directory=$directory&amp;choose=$choose\">$strback</a>) ".
             "(<a href=\"javascript:history.go(-1);\">$strback</a>) ";
		if (!$blockmode){
			echo    "(<a onclick=\"return set_value('#$path', '".$items['title']."', '".str_replace(array("\r\n","\n","\r"), '', $items['description'])."')\" href=\"#\">$strchoose</a>)";
		}
		echo "</div>";
    }
    
?>
