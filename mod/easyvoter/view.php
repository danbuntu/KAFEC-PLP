<?php  // $Id: view.php,v 1.6 2007/09/03 12:23:36 jamiesensei Exp $
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: view.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page prints a particular instance of easyVoter
//       Alterations & additions marked with *****(version)
////////////////////////////////////////////////////////////////////////////

    require_once("../../config.php");
    require_once("lib.php");

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a  = optional_param('a', 0, PARAM_INT);  // easyvoter ID

    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            print_error('invalidrequest');
        }

        if (! $course = get_record("course", "id", $cm->course)) {
            print_error('invalidrequest');
        }

        if (! $easyvoter = get_record("easyvoter", "id", $cm->instance)) {
            print_error('invalidrequest');
        }

    } else {
        if (! $easyvoter = get_record("easyvoter", "id", $a)) {
            print_error('invalidrequest');
        }
        if (! $course = get_record("course", "id", $easyvoter->course)) {
            print_error('invalidrequest');
        }
        if (! $cm = get_coursemodule_from_instance("easyvoter", $easyvoter->id, $course->id)) {
            print_error('invalidrequest');
        }
    }

    require_login($course->id);

    add_to_log($course->id, "easyvoter", "view", "view.php?id=$cm->id", "$easyvoter->id");

/// Print the page header
	$streasyvoters = get_string("modulenameplural", "easyvoter");
	$streasyvoter  = get_string("modulename", "easyvoter");

	$navlinks = array();
	$navlinks[] = array('name' => $streasyvoters, 'link' => "index.php?id=$course->id", 'type' => 'activity');
	$navlinks[] = array('name' => format_string($easyvoter->name), 'link' => '', 'type' => 'activityinstance');
	
	$navigation = build_navigation($navlinks);
	
	print_header_simple(format_string($easyvoter->name), "", $navigation, "", "", true, update_module_button($cm->id, $course->id, $streasyvoter), navmenu($course, $cm));
	
/// Print the main part of the page

	//EASYVOTER STYLES
	require_once('styles/default.php');
	
	//INCLUDE JAVASCRIPT FUNCTION LIBRARY
	echo '<script type="text/javascript" src="scripts/functionlibrary.js"></script>';
	
	//CHECK IF CURRENTLY BEING PRESENTED
	$aActive = easyvoter_isActive($easyvoter->id);

	echo '<div style="'.$sStyleCenter.'"><div class="headingblock header" style="'.$sStyleHeadingBlock.'">'.get_string('easyvoterdetails', 'easyvoter').'</div><div class="generalbox" style="'.$sStyleContentBlock.'">';
	//CHECK USER ROLE ACCESS
	$iSlideNum = easyvoter_numberOfSlides($easyvoter->id);
	$iResultsRecorded = easyvoter_resultsRecorded($easyvoter->id);
	if(easyvoter_isPresenter($cm->id)){
		echo '<table>
			<tr style="'.$sStyleHidden.'"><th scope="col">'.get_string('easyvoterproperties', 'easyvoter').'</th><th scope="col">'.get_string('easyvotervalue', 'easyvoter').'</th></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvotertitle', 'easyvoter').'</span></td><td>'.$easyvoter->name.'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoterdescription', 'easyvoter').'</span></td><td>'.$easyvoter->intro.'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvotermaxparticipantstitle', 'easyvoter').'</span></td><td>'.$easyvoter->maxparticipants.'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoternoofslides', 'easyvoter').'</span></td><td>'.$iSlideNum.'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoterresultsrecorded', 'easyvoter').'</span></td><td>'.$iResultsRecorded.'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoteruserstatus', 'easyvoter').'</span></td><td>'.get_string('easyvoterpresenter', 'easyvoter').'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoterpresentationstatus', 'easyvoter').'</span></td><td>
		';
			if($aActive){	
				if($aActive['id']!=$USER->id){
					echo '<span style="'.$sRedText.'">'.get_string('easyvoterinuseby', 'easyvoter').$aActive['fullname'].get_string('easyvoterinuseoptions', 'easyvoter').'</span>';
				}else{
					echo '<span style="'.$sRedText.'">'.get_string('easyvotercurrentlypresenting', 'easyvoter').'</span>';
				}
			}else{
				echo '<span style="'.$sRedText.'">'.get_string('easyvoternotinuse', 'easyvoter').'</span>';
			}
		echo '
			</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoteroptions', 'easyvoter').'</span></td><td>
		';
		//START & PREVIEW OPTION DEPENDENT ON NUMBER OR QUESTIONS AND IF HIDDEN
		if($iSlideNum>0){
			echo '<a href="javascript:popupWin(\''.$CFG->wwwroot.'/mod/easyvoter/preview.php?id='.$cm->id.'\',\'preview'.$easyvoter->id.'\',true)">'.get_string('easyvoterpreview', 'easyvoter').'</a> | ';
		}
		//IF NOT ACTIVE
		if($iSlideNum>0&&(!$aActive||$aActive['id']==$USER->id)){
			echo '<a href="javascript:popupWin(\''.$CFG->wwwroot.'/mod/easyvoter/present.php?id='.$cm->id.'\',\'present'.$easyvoter->id.'\',true)">'.(($aActive)?get_string('easyvotercontinue', 'easyvoter'):get_string('easyvoterstart', 'easyvoter')).'</a> | ';
		}
		//IF NOT ACTIVE
		if(!$aActive){
			echo '<a href="'.$CFG->wwwroot.'/mod/easyvoter/editslides.php?id='.$cm->id.'">'.get_string('easyvotereditslides', 'easyvoter').'</a> | ';
		}
		//RESULTS OPTION DEPENDENT ON NUMBER OF TIMES USED
		if($iResultsRecorded>0){
			echo '<a href="'.$CFG->wwwroot.'/mod/easyvoter/results.php?id='.$cm->id.'">'.get_string('easyvoterresults', 'easyvoter').'</a> | ';
		}
		//HIDE OPTION DEPENDENT ON HIDDEN OR UNHIDDEN AND IF NOT ACTIVE
		if(!$aActive){
			echo '<a href="'.$CFG->wwwroot.'/mod/easyvoter/slides/showhide.php?id='.$cm->id.'">';
			if($cm->visible>0){
				echo get_string('easyvoterhide', 'easyvoter');
			}else{
				echo get_string('easyvotershow', 'easyvoter');
			}
			echo '</a> | ';
		}	
		
		echo'
		<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.get_string('easyvotercancel', 'easyvoter').'</a>		
		</td></tr></table>
		';
		
		//ALWAYS DEFAULT GROUPMODE TO 0
		if($cm->groupmode>0){
			easyvoter_forceGroupMode($cm);
		}
	}else{
		//PARTICIPANT VIEW
		$bReady = FALSE;
		$bContinue = FALSE;
		
		//CHECK MAX PATICIPANTS NOT REACHED
		$iParticipants = count_records('easyvoter_responses','instance',$easyvoter->id,'slideid','0');
		if($aActive){
			if(count_records('easyvoter_responses','instance',$easyvoter->id,'slideid','0','participant',$USER->id)>0){
				$bReady = TRUE;
				$bContinue = TRUE;
			}else{
				if($iParticipants<$easyvoter->maxparticipants){
					$bReady = TRUE;
				}
			}
		}
		
		echo '<table>
			<tr style="'.$sStyleHidden.'"><th scope="col">'.get_string('easyvoterproperties', 'easyvoter').'</th><th scope="col">'.get_string('easyvotervalue', 'easyvoter').'</th></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvotertitle', 'easyvoter').'</span></td><td>'.$easyvoter->name.'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoterdescription', 'easyvoter').'</span></td><td>
		';
			if($bReady){
				echo $easyvoter->intro;
			}else{
				if($iParticipants<$easyvoter->maxparticipants){
					echo '<span style="'.$sStyleSubHeading.'"><span style="'.$sRedText.'">'.get_string('easyvoterunavailable', 'easyvoter').'</span><br />'.get_string('easyvoterunavailabledesc', 'easyvoter').'</span>';
				}else{
					echo '<span style="'.$sStyleSubHeading.'"><span style="'.$sRedText.'">'.get_string('easyvoterunavailable', 'easyvoter').'</span><br />'.get_string('easyvotermaxreacheddesc', 'easyvoter').'</span>';
				}
			}
		echo '
			</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoternoofslides', 'easyvoter').'</span></td><td>'.$iSlideNum.'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoteruserstatus', 'easyvoter').'</span></td><td>'.get_string('easyvoterparticipant', 'easyvoter').'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoteroptions', 'easyvoter').'</span></td><td>
		';
		//START OPTION DEPENDENT ON IF ACTIVE
		if($bReady){
			echo '<a href="javascript:popupWin(\''.$CFG->wwwroot.'/mod/easyvoter/present.php?id='.$cm->id.'\',\'participate'.$easyvoter->id.'\',true)">'.(($bContinue)?get_string('easyvotercontinue', 'easyvoter'):get_string('easyvoterstart', 'easyvoter')).'</a> | ';
		}else{
			echo '<a href="'.$CFG->wwwroot.'/mod/easyvoter/view.php?id='.$cm->id.'">'.get_string('easyvoterretry', 'easyvoter').'</a> | ';
		}
		echo'<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.get_string('easyvotercancel', 'easyvoter').'</a>		
		</td></tr></table>';
	}
	echo "</div></div>";

/// Finish the page
    print_footer($course);
?>
