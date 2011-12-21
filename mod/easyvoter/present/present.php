<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: present/present.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: For the processing of the present options
////////////////////////////////////////////////////////////////////////////
require_once("../../../config.php");
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF HTTP_XMLHTTPCALLER HEADER NOT FOUND	
if(!isset($_SERVER['HTTP_XMLHTTPCALLER'])||($_SERVER['HTTP_XMLHTTPCALLER']!=='preview.php'&&$_SERVER['HTTP_XMLHTTPCALLER']!=='present.php')){
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////

    require_once("../lib.php");
	require_once('../styles/default.php');
	
	$cid = optional_param('cid', 1, PARAM_INT); //Course ID
	$cmid = optional_param('cmid', 0, PARAM_INT); //Course Module ID
	$sid = optional_param('sid', 0, PARAM_INT); //Slide ID
	$action = optional_param('action', '', PARAM_TEXT); //Action to take
	$presentanon = optional_param('presentanon', '1', PARAM_INT); //Display Participant Name
	
	require_login($cid);
	
	if(easyvoter_isPresenter($cmid)){
		switch(strtolower($action)){
		case 'responses':
			if($_SERVER['HTTP_XMLHTTPCALLER']!=='preview.php'&&is_numeric($sid)){
				$sSlidesSQL = 'id='.$sid;
				if($sid<1){
					$oSlide = new object;
					$oSlide->id = 0;
					$oSlide->type = 'intro';
					$oSlide->instance=get_field('course_modules','instance','id',$cmid);
				}else{
					$oSlide = get_record_select('easyvoter_slides', $sSlidesSQL, 'id,instance,type,answer,control');
				}
				
				if($oSlide){
					$sResponsesSQL = 'instance='.$oSlide->instance.' and slideid='.$oSlide->id;
					if($aResponses = get_records_select('easyvoter_responses',$sResponsesSQL,'timecreated ASC','fullname,response')){
						echo'<div>'.count($aResponses).' '.get_string('easyvotertotalresponses', 'easyvoter').'</div>';
						$aOuputGraph = array(0,0,0,0);
						$sOuputResponses = '<table id="responsetable" summary="'.get_string('easyvoteresponseview', 'easyvoter').'">
									<tr>
										<th class="thheading">'.get_string('easyvoterfullname', 'easyvoter').'</th><th class="thheading">'.get_string('easyvoterresponse', 'easyvoter').'</th>
									</tr>
						';
						$iLoop = 0;
						foreach($aResponses as $oReponse){
				 			$sOuputResponses .= ($iLoop%2<1)?'<tr class="trlight">':'<tr class="trdark">';
							//OUTPUT FULLNAME IF NOT SET TO ANONYMOUS
					 	 	if($presentanon){
								$sOuputResponses .= '<td>'.get_string('easyvoterparticipant', 'easyvoter').($iLoop+1).'</td>';
							}else{
								$sOuputResponses .= '<td>'.htmlentities($oReponse->fullname).'</td>';
							}
							
							if($oSlide->type=='intro'){
								$sOuputResponses .= '<td class="greentext">'.get_string('easyvotersignedin', 'easyvoter').'</td></tr>';
							}else{
								$sOuputResponses .= '<td>'.htmlentities($oReponse->response).'</td></tr>';
							}
							
							//GRAPH ENTRIES
							if($oSlide->type=='mcho'||$oSlide->type=='true'||$oSlide->type=='nume'){
								if($oSlide->type!='nume'){
									switch(strtolower($oReponse->response)){
										case 'a':
											$aOuputGraph[0]++;
											break;
										case 'true':
											$aOuputGraph[0]++;
											break;
										case 'b':
											$aOuputGraph[1]++;
											break;
										case 'false':
											$aOuputGraph[1]++;
											break;
										case 'c':
											$aOuputGraph[2]++;
											break;
										default:
											$aOuputGraph[3]++;
									}
								}else{
									if($oSlide->answer!=''){
										if(is_numeric($oReponse->response)&&$oReponse->response==$oSlide->answer){
											$aOuputGraph[0]++;
										}else{
											$aOuputGraph[1]++;
										}
									}
								}
							}
							$iLoop++;
						}
						$sOuputResponses .= '</table>';

						//OUTPUT GRAPH
						if($oSlide->type=='mcho'||$oSlide->type=='true'||($oSlide->type=='nume'&&$oSlide->answer!='')){
							
							$iGraphHeight = 220;
							$iHeightIncrement = floor($iGraphHeight/count($aResponses));
							$iWidth = 122;
							
							switch($oSlide->type){
								case 'mcho':
									$iLoopCount = $oSlide->control;
									$aTitles = array(get_string('easyvotera', 'easyvoter'),get_string('easyvoterb', 'easyvoter'),get_string('easyvoterc', 'easyvoter'),get_string('easyvoterd', 'easyvoter'));									
									break;
								case 'true':
									$iLoopCount = 2;
									$aTitles = array(get_string('easyvotertrue', 'easyvoter'),get_string('easyvoterfalse', 'easyvoter'));									
									break;
								default:
									$iLoopCount = 2;
									$aTitles = array(get_string('easyvotercorrect', 'easyvoter'),get_string('easyvoterincorrect', 'easyvoter'));	
							}
							
							$iMaxBar = 0;
							$sBars = '';
							for($iLoop=0;$iLoop<$iLoopCount;$iLoop++){
								$iSize = $iHeightIncrement*$aOuputGraph[$iLoop];
								$sBars .= '<img src="images/default/bar.gif" alt="'.$aTitles[$iLoop].'" class="bar'.$iLoop.'" style="height:'.$iSize.'px;" />';
								if($iSize>$iMaxBar){
									$iMaxBar = $iSize;
								}
							}
							
							//MARGIN_TOP IS USED TO PUSH GRAPHBOTTOM TO BOTTOM OF THE RESPONSEGRAPH DIV - ABSOLUTE POSITIONING CAUSED DISPLAY BUGS
							echo '<div id="responsegraph" style="width:'.($iWidth*$iLoopCount).'px;"><div id="graphbottom" style="margin-top:'.($iGraphHeight-$iMaxBar+10).'px">';
							echo $sBars;
							for($iLoop=0;$iLoop<$iLoopCount;$iLoop++){
								echo '<div class="graphtext">'.$aTitles[$iLoop].'<br />'.$aOuputGraph[$iLoop].'</div>';
							}
							echo '</div></div>';
						}
						
						//OUTPUT RESPONSES
						echo $sOuputResponses;
						
					}else{
						echo get_string('easyvoternoresponses', 'easyvoter');
					}
				}else{
					echo get_string('easyvoternoresponses', 'easyvoter');
				}
			}else{
				echo get_string('easyvoternoresponses', 'easyvoter');
			}
			break;
		case 'reset':
			//IF PREVIEW DO NOT UPDATE INSTANCE TABLE
			if($_SERVER['HTTP_XMLHTTPCALLER']!=='preview.php'&&($iInstance=get_field('course_modules','instance','id',$cmid))){
				$oInstance = new object;
				if($oInstance->id=get_field('easyvoter_present','id','instance',$iInstance,'presenter',$USER->id)){
					$oInstance->numeral = 0;
					$oInstance->timemodified = time();
					if(update_record('easyvoter_present',$oInstance)){
						echo 'TRUE';
					}else{
						echo 'FALSE';
					}			
				}else{
					echo 'FALSE';
				}
			}else{
				echo 'FALSE';
			}
			break;				
		default:
		//GET SLIDE
			if(is_numeric($sid)){
				$sSQL = 'id='.$sid;
				if($oSlide = get_record_select('easyvoter_slides', $sSQL, 'instance,numeral,name,content')){
					$oInstance = new object;
					//IF PREVIEW DO NOT UPDATE INSTANCE TABLE
					if($_SERVER['HTTP_XMLHTTPCALLER']!=='preview.php'&&($oInstance->id=get_field('easyvoter_present','id','instance',$oSlide->instance,'presenter',$USER->id))){
						$oInstance->numeral = $oSlide->numeral;
						$oInstance->timemodified = time();
						update_record('easyvoter_present',$oInstance);
					}
					echo'
						<div id="slidename">'.$oSlide->name.'</div>
						<div id="slidecontent">'.$oSlide->content.'</div>
					';
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