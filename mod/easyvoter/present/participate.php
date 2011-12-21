<?php
//Character set header///////////////////////////////////////////////////////
header('Content-type: text/html; charset=UTF-8');
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: present/participate.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: For the processing of the present options
////////////////////////////////////////////////////////////////////////////
require_once("../../../config.php");
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF HTTP_XMLHTTPCALLER HEADER NOT FOUND	
if(!isset($_SERVER['HTTP_XMLHTTPCALLER'])||$_SERVER['HTTP_XMLHTTPCALLER']!=='present.php'){
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////

    require_once("../lib.php");
	require_once('../styles/default.php');
	global $USER;
	
	$cid = optional_param('cid', 1, PARAM_INT); //Course ID
	$inid = optional_param('inid', 0, PARAM_INT); //Instance ID
	$sid = optional_param('sid', 0, PARAM_INT); //Slide ID
	$action = optional_param('action', '', PARAM_TEXT); //Action to take
	$response = optional_param('response', '', PARAM_TEXT); //Participant response

//IN CASE RESPONSE IS INTERPRETED AS ANYTHING OTHER THAN UTF-8, FORCE CHARACTER SET
  if(function_exists('mb_detect_encoding')){
	  if(mb_detect_encoding($response)!=='UTF-8'){
		$response = utf8_encode($response);
	  }
  }else{
  	$response = utf8_encode($response);
  }
//////////////////////////////////////////////////////////////////////////// 

	require_login($cid);
	
	switch(strtolower($action)){
	case 'currentslide':
		$iReturn = '-1';
		if(is_numeric($inid)){
			if(($iNumeral = get_field('easyvoter_present', 'numeral', 'instance' , $inid))!==FALSE){
				$iReturn = $iNumeral;
			}
		}
		echo $iReturn;
		break;
	case 'isrecorded':
		if(is_numeric($sid)){
			if(get_field('easyvoter_responses', 'id', 'participant', $USER->id, 'slideid', $sid)){
				echo 'TRUE';
			}else{
				echo 'FALSE';
			}
		}else{
			echo get_string('easyvoteraccessdenied', 'easyvoter');
		}
		break;
	case 'response':
		if(is_numeric($sid)&&$response!=''){
			$sSQL = 'id='.$sid;
			if($oSlide = get_record_select('easyvoter_slides', $sSQL, 'id,instance,type,control')){
				if(!get_field('easyvoter_responses', 'id', 'participant', $USER->id, 'slideid', $sid)){
					$aResponse = easyvoter_slideTypes($oSlide->type,"".$response,$oSlide->control);
					if($aResponse!==FALSE&&$aResponse[0]!=''){
						$oResponse = new object;
						$oResponse->instance = $oSlide->instance;
						$oResponse->slideid = $oSlide->id;
						$oResponse->participant = $USER->id;
						$oResponse->fullname = $USER->firstname.' '.$USER->lastname;
						$oResponse->response = $aResponse[0];
						$oResponse->timecreated = time();
						insert_record('easyvoter_responses', $oResponse);
						echo get_string('easyvoterresponserecorded', 'easyvoter');
					}
				}else{
					echo get_string('easyvoterresponserecorded', 'easyvoter');
				}
			}else{
				echo get_string('easyvoteraccessdenied', 'easyvoter');
			}
		}else{
			echo get_string('easyvoteraccessdenied', 'easyvoter');
		}
		break;
	default:
	//GET SLIDE
		if(is_numeric($sid)){
			$sSlideSQL = 'id='.$sid;
			//CHECK THAT THE SLIDE CAN ADVANCE
			if($oSlide = get_record_select('easyvoter_slides', $sSlideSQL, 'instance,numeral,name,content')){
				//CHECK IF INSTANCE STILL EXSISTS AND NOT CLOSED BY PRESENTER
				$sInstanceSQL = 'instance='.$oSlide->instance;
				if($oInstance = get_record_select('easyvoter_present', $sInstanceSQL, 'numeral')){
					if($oSlide->numeral==$oInstance->numeral){
						echo'
							<div id="slidename">'.$oSlide->name.'</div>
							<div id="slidecontent">'.$oSlide->content.'</div>
						';
					}else{
						echo get_string('easyvoteraccessdenied', 'easyvoter');
					}
				}else{
					echo get_string('easyvoterendedbypresenter', 'easyvoter');
				}
			}else{
				echo get_string('easyvoternoslides', 'easyvoter');
			}
		}else{
			echo get_string('easyvoternoslides', 'easyvoter');
		}
	}//END
?>