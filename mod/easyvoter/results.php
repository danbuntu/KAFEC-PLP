<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: results.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page displays results archive
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY $sPageCaller VARIABLE MUST BE PRESENT TO LOAD FORM FILES
$sPageCaller = "results.php";
////////////////////////////////////////////////////////////////////////////

    require_once("../../config.php");
    require_once("lib.php");
		
    $id = optional_param('id', 0, PARAM_INT); // Course Module ID
	$delete = optional_param('delete', 0, PARAM_INT); // Course Module ID

    if (! $cm = get_record("course_modules", "id", $id)) {
        print_error('invalidrequest');
    }

    if (! $course = get_record("course", "id", $cm->course)) {
        print_error('invalidrequest');
    }
	
    if (! $easyvoter = get_record("easyvoter", "id", $cm->instance)) {
        print_error('invalidrequest');
    }
		
   	require_login($course->id);
   
   	//CHECK THERE ARE RESULTS TO DISPLAY
   	$iResultsRecorded = easyvoter_resultsRecorded($easyvoter->id);
	
   	//CHECK USER ROLE ACCESS
  	if(easyvoter_isPresenter($cm->id)&&$iResultsRecorded>0){
	
	//GET RESULTS
	$results = get_records("easyvoter_results", "instance", $easyvoter->id, 'timecreated DESC');
	
	if(is_numeric($delete)&&$delete>0){
		foreach($results as $key => $oResult){
			if($oResult->id==$delete&&($USER->id==$oResult->presenter||easyvoter_isAdmin())){
				if(easyvoter_deleteResult($oResult->id)){
					unset($results[$key]);
				}
				break;
			}
		}
	}
	
	//WRITE TO LOG AND REDIRECT IF LAST RESULTS WAS DELETED
	if(count($results)<$iResultsRecorded){
		add_to_log($course->id, "easyvoter", "delete result", "view.php?id=$cm->id", "$easyvoter->name");
		if(count($results)<1){
			redirect($CFG->wwwroot.'/mod/easyvoter/view.php?id='.$cm->id);
		}
	}else{
		add_to_log($course->id, "easyvoter", "view results", "view.php?id=$cm->id", "$easyvoter->name");
	}

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
	
	echo '<div style="'.$sStyleCenter.'"><div class="headingblock header" style="'.$sStyleHeadingBlock.'">'.get_string('easyvoterdetails', 'easyvoter').'</div><div class="generalbox" style="'.$sStyleContentBlock.'">
		<table>
			<tr style="'.$sStyleHidden.'"><th scope="col">'.get_string('easyvoterproperties', 'easyvoter').'</th><th scope="col">'.get_string('easyvotervalue', 'easyvoter').'</th></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvotertitle', 'easyvoter').'</span></td><td>'.$easyvoter->name.'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoterdescription', 'easyvoter').'</span></td><td>'.$easyvoter->intro.'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoterresultsrecorded', 'easyvoter').'</span></td><td>'.count($results).'</td></tr>
			<tr><td style="'.$sTDText.'"><span style="'.$sStyleSubHeading.'">'.get_string('easyvoteroptions', 'easyvoter').'</span></td><td>
			<a href="'.$CFG->wwwroot.'/mod/easyvoter/view.php?id='.$cm->id.'">'.get_string('easyvotercancel', 'easyvoter').'</a></td></tr>
		</table>
		</div></div>
		
		<div style="'.$sStyleCenter.'"><div class="headingblock header" style="'.$sStyleHeadingBlock.'">'.get_string('easyvoterresults', 'easyvoter').'</div><div class="generalbox" style="'.$sStyleContentBlock.'">
			<table style="width:100%">
				<tr><th scope="col" style="width:20%">'.get_string('easyvoterdate', 'easyvoter').'</th><th scope="col" style="width:10%">'.get_string('easyvoterslides', 'easyvoter').'</th><th scope="col" style="width:15%">'.get_string('easyvoteranonymous', 'easyvoter').'</th><th scope="col" style="width:15%">'.get_string('easyvoterparticipants', 'easyvoter').'</th><th scope="col" style="width:20%">'.get_string('easyvoterpresenter', 'easyvoter').'</th><th scope="col" style="width:20%">'.get_string('easyvoteroptions', 'easyvoter').'</th></tr>
		';
			$iLoop = 0;
		  	foreach($results as $oResult){
				if($iLoop%2===0){
					$sRow = $sStyleDarkRow;
				}else{
					$sRow = $sStyleLightRow;
				}
				$iLoop++;
				echo'<tr style="'.$sRow.'"><td>'.date('d F Y H:i',$oResult->timecreated).'</td><td>'.$oResult->slides.'</td><td>'.(($oResult->resultsanon)?get_string('easyvoteryes', 'easyvoter'):get_string('easyvoterno', 'easyvoter')).'</td><td>'.$oResult->participants.'</td><td>'.easyvoter_userFullname($oResult->presenter).'</td><td style="'.$sStyleRight.'"><a href="'.$CFG->wwwroot.'/file.php/'.$course->id.'/moddata/easyvoter/'.$easyvoter->id.'/'.$oResult->resultsfile.'">'.get_string('easyvoterdownloadcsv', 'easyvoter').'</a>'.(($USER->id==$oResult->presenter||easyvoter_isAdmin())?' | <a href="javascript:deleteResult('.$cm->id.','.$oResult->id.')">'.get_string('easyvoterdelete', 'easyvoter').'</a></td></tr>':'');
			}
		echo '
			</table>
		</div></div>
		
		<script type="text/javascript">
			//<![CDATA[
				function deleteResult(cmid,resultsid){
					if(typeof(cmid)==="number"&&typeof(resultsid)==="number"){
						if(confirm("'.get_string('easyvoterconfirmdeleteresults', 'easyvoter').'")){
							window.location = "results.php?id="+cmid+"&amp;delete="+resultsid;
						}
					}
				}
			//]]>
		</script>
		';
	}else{
		redirect($CFG->wwwroot.'/mod/easyvoter/view.php?id='.$cm->id);
	}
							
/// Finish the page
    print_footer($course);
?>
