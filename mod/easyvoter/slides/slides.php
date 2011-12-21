<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: slides/slides.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: For the processing of the slide options (UP | DOWN | DELETE | GETALL)
////////////////////////////////////////////////////////////////////////////
require_once("../../../config.php");
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF HTTP_XMLHTTPCALLER HEADER NOT FOUND	
if(!isset($_SERVER['HTTP_XMLHTTPCALLER'])||$_SERVER['HTTP_XMLHTTPCALLER']!=='editslides.php'){
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////

    require_once("../lib.php");
	require_once('../styles/default.php');
	
	$cid = optional_param('cid', 1, PARAM_INT); //Course ID
	$cmid = optional_param('cmid', 0, PARAM_INT); //Course Module ID
	$inid = optional_param('inid', 0, PARAM_INT); //easyVoter Instance ID
	$sid = optional_param('sid', 0, PARAM_INT); //Slide ID
	$action = optional_param('action', '', PARAM_TEXT); //Action to take
	
	require_login($cid);

//STOP PROCESSING IF CURRENTLY BEING PRESENTED
	if($aPresenter = easyvoter_isActive($inid)){
		exit(get_string('easyvoterinusebyanother', 'easyvoter').$aPresenter['fullname']);
	}
	
	if(easyvoter_isPresenter($cmid)){
		switch(strtolower($action)){
		case 'up':
		//SORT UP
			if(is_numeric($sid)){
				$sSQL = 'id='.$sid;
				if($oSlideOne = get_record_select('easyvoter_slides', $sSQL, 'id,instance,numeral')){
					$sInstanceName = get_field_select('easyvoter','name','id='.$oSlideOne->instance);
					$sSQL = "numeral=".($oSlideOne->numeral-1)." and instance=".$oSlideOne->instance;
					if($oSlideTwo = get_record_select('easyvoter_slides', $sSQL, 'id,numeral')){
						$oSlideOne->numeral = $oSlideOne->numeral-1;
						$oSlideTwo->numeral = $oSlideTwo->numeral+1;
						update_record('easyvoter_slides',$oSlideOne);
						update_record('easyvoter_slides',$oSlideTwo);
					}
					add_to_log($cid, "easyvoter", "sort slide", "editslides.php?id=$cm->id", $sInstanceName);
				}
			}
			break;
		case 'down':
		//SORT DOWN
			if(is_numeric($sid)){
				$sSQL = 'id='.$sid;
				if($oSlideOne = get_record_select('easyvoter_slides', $sSQL, 'id,instance,numeral')){
					$sInstanceName = get_field_select('easyvoter','name','id='.$oSlideOne->instance);
					$sSQL = "numeral=".($oSlideOne->numeral+1)." and instance=".$oSlideOne->instance;
					if($oSlideTwo = get_record_select('easyvoter_slides', $sSQL, 'id,numeral')){
						$oSlideOne->numeral = $oSlideOne->numeral+1;
						$oSlideTwo->numeral = $oSlideTwo->numeral-1;
						update_record('easyvoter_slides',$oSlideOne);
						update_record('easyvoter_slides',$oSlideTwo);
					}
					add_to_log($cid, "easyvoter", "sort slide", "editslides.php?id=$cm->id", $sInstanceName);
				}
			}
			break;
		case 'delete';
		//DELETE
			if(is_numeric($sid)){
				$sSQL = 'id='.$sid;
				if($oSlide = get_record_select('easyvoter_slides', $sSQL, 'id,instance,numeral')){
					$sInstanceName = get_field_select('easyvoter','name','id='.$oSlide->instance);
					delete_records_select('easyvoter_slides', $sSQL);
					$sSQL = 'instance='.$oSlide->instance.' and numeral>'.$oSlide->numeral;
					if($aSlides = get_records_select('easyvoter_slides', $sSQL, 'numeral ASC','id,numeral')){
						foreach($aSlides as $oSlide){
							$oSlide->numeral = $oSlide->numeral-1;
							update_record('easyvoter_slides',$oSlide);
						}
					}
					add_to_log($cid, "easyvoter", "delete slide", "editslides.php?id=$cm->id", $sInstanceName);
				}
			}
			break;
		case 'deleteall';
		//DELETE ALL
			if(is_numeric($inid)){
				if(delete_records('easyvoter_slides', 'instance', $inid)){
					add_to_log($cid, "easyvoter", "delete all slides", "editslides.php?id=$cm->id", $sInstanceName);
				}
			}
			break;
		default:
		//GET ALL
			if(is_numeric($inid)){
				if($aSlides = get_records('easyvoter_slides', 'instance', $inid, 'numeral ASC','id,instance,numeral,name,type')){
					$aSlideTypes = easyvoter_slideTypes();
					echo '<table style="width:100%"><th scope="col" style="width:10%">'.get_string('easyvoterorder', 'easyvoter').'</th><th scope="col" style="width:50%">'.get_string('easyvoterslide', 'easyvoter').'</th><th scope="col" style="width:20%">'.get_string('easyvotertype', 'easyvoter').'</th><th scope="col" style="width:20%;">'.get_string('easyvoteroptions', 'easyvoter').'</th>';						
					foreach($aSlides as $oSlide){
						if($oSlide->numeral%2===0){
							$sRow = $sStyleLightRow;
						}else{
							$sRow = $sStyleDarkRow;
						}
						echo '<tr style="'.$sRow.'"><td>'.$oSlide->numeral.'</td><td>'.$oSlide->name.'</td><td>'.get_string($aSlideTypes[$oSlide->type], 'easyvoter').'</td><td style="'.$sStyleRight.'">';
						if($oSlide->numeral>1&&$oSlide->numeral<count($aSlides)+1){
							echo '<a href="javascript:sortSlide('.$oSlide->id.',\'up\');">'.get_string('easyvoterup', 'easyvoter').'</a> | ';
						}
						if($oSlide->numeral!=count($aSlides)){
							echo '<a href="javascript:sortSlide('.$oSlide->id.',\'down\');">'.get_string('easyvoterdown', 'easyvoter').'</a> | ';
						}
						echo ' <a href="editslides.php?id='.$cmid.'&action=edit&sid='.$oSlide->id.'">'.get_string('easyvoteredit', 'easyvoter').'</a> | <a href="javascript:sortSlide('.$oSlide->id.',\'delete\');">'.get_string('easyvoterdelete', 'easyvoter').'</a></td></tr>';
					}
					echo '</table>';
				}else{
							echo get_string('easyvoternoslides', 'easyvoter');
				}
			}else{
				echo get_string('easyvoternoslides', 'easyvoter');
			}
		}//END SWITCH
	}else{
		echo get_string('easyvoteraccessdenied', 'easyvoter');
	}
?>