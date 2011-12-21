<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: present/present_slides.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Present slides
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF $sPageCaller VARIABLE NOT FOUND	
if(!isset($sPageCaller)||($sPageCaller!=='preview.php'&&$sPageCaller!=='present.php')){
	require_once("../../../config.php");
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////
echo '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>
';
	//OUTPUT CORRECT TITLE
	if($sPageCaller==='preview.php'){
    	echo get_string('easyvoterpreview', 'easyvoter');
	}else{
		echo get_string('easyvoterpresent', 'easyvoter');
	}
echo 	
	$easyvoter->name.'</title>
    <link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/mod/easyvoter/styles/default.css" />
	<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/easyvoter/scripts/divsliderclass.js"></script>
	<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/easyvoter/scripts/functionlibrary.js"></script>
	<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/easyvoter/scripts/ajaxclass.js"></script>
    </head>
    <body>
	';
	if($sPageCaller==='preview.php'){
		echo '<div id="closepreviewaction" onclick="window.close()" title="'.get_string('easyvoterclosepreview', 'easyvoter').'"><img id="closepreviewbutton" class="close" src="images/default/close.gif" alt="'.get_string('easyvoterclosepreview', 'easyvoter').'" /><span class="redtext">'.get_string('easyvoterclosepreview', 'easyvoter').'</span></div>';
	}else{
		echo '<div id="quitpresentationaction" onclick="quitPresentation()" title="'.get_string('easyvoterquitpresentation', 'easyvoter').'"><img id="quitpresentationbutton" class="close" src="images/default/close.gif" alt="'.get_string('easyvoterquitpresentation', 'easyvoter').'" /><span class="redtext">'.get_string('easyvoterquitpresentation', 'easyvoter').'</span></div>';
	}
echo '	
	<div id="switchviewresponsesaction" onclick="slideDiv(aSliderArray[2]);slideDiv(aSliderArray[3])" title="'.get_string('easyvoteropenclose', 'easyvoter').'"><img id="responsesbutton" class="plusminus" src="images/default/plus.gif" alt="'.get_string('easyvoteropenclose', 'easyvoter').'" />'.get_string('easyvoteresponseview', 'easyvoter').'</div>
	<div id="switchviewcontentaction" onclick="slideDiv(aSliderArray[2]);slideDiv(aSliderArray[3])" title="'.get_string('easyvoteropenclose', 'easyvoter').'"><img id="slidecontentbutton" class="plusminus" src="images/default/minus.gif" alt="'.get_string('easyvoteropenclose', 'easyvoter').'" />'.get_string('easyvoterslideview', 'easyvoter').'</div>
	<div id="slideminusheightaction" onmousedown="slideHeightDown(\'minus\')" onmouseout="slideHeightUp()" onmouseup="slideHeightUp()" title="'.get_string('easyvoterdecrease', 'easyvoter').'"><img class="plusminus" src="images/default/minus.gif" alt="'.get_string('easyvoterdecrease', 'easyvoter').'" />'.get_string('easyvoterdecrease', 'easyvoter').'</div>
	<div id="slideplusheightaction" onmousedown="slideHeightDown(\'plus\')" onmouseout="slideHeightUp()" onmouseup="slideHeightUp()" title="'.get_string('easyvoterincrease', 'easyvoter').'"><img class="plusminus" src="images/default/plus.gif" alt="'.get_string('easyvoterincrease', 'easyvoter').'" />'.get_string('easyvoterincrease', 'easyvoter').'</div>
	<div id="slidenameaction" onclick="slideDiv(aSliderArray[1])" title="'.get_string('easyvoteropenclose', 'easyvoter').'"><img id="slidenamebutton" class="plusminus" src="images/default/minus.gif" alt="'.get_string('easyvoteropenclose', 'easyvoter').'" />'.get_string('easyvoterslidename', 'easyvoter').'</div>
	<div id="titleaction" onclick="slideDiv(aSliderArray[0])" title="'.get_string('easyvoteropenclose', 'easyvoter').'"><img id="titlebutton" class="plusminus" src="images/default/minus.gif" alt="'.get_string('easyvoteropenclose', 'easyvoter').'" />'.get_string('easyvotertitle', 'easyvoter').'</div>
	
	<div id="header">
		<div id="title">'.$easyvoter->name.'</div>
	</div>
	
	<div id="content">
		<div id="responses">';
			if($sPageCaller==='preview.php'){
				echo '
				<p><img id="a" src="images/default/responses_placeholder.gif" width="200" height="200" alt="'.get_string('easyvoterresponsespreview', 'easyvoter').'" /></p>
				<p>'.get_string('easyvoterresponsespreview', 'easyvoter').'</p>
				';
			}
  echo '</div>
		<div id="slide">
			<div id="slidename">'.get_string('easyvoterloading', 'easyvoter').'</div>
			<div id="slidecontent">'.get_string('easyvoterloading', 'easyvoter').'</div>		
		</div>
	</div>		

	<div id="footer">
		<div id="navigation">&nbsp;</div>
		<div id="slidenumbering">&nbsp;</div>
	</div>	
';

echo '
	<script type="text/javascript">
	//<![CDATA[	
	
		//CURRENT SCRIPT
		var sPageCaller = "'.$sPageCaller.'";
	
		//SLIDES
		var sSlideArray = new Array('.$sArrayString.');
		var iTotalSlides = sSlideArray.length;
';
	//GET CURRENT SLIDE NUMBER INCASE SHOW HAS CONTINUED
	if($sPageCaller==='preview.php'){
		echo 'var iCurrentSlide = -1;';
	}else{
		if(!$numeral = get_field('easyvoter_present','numeral','instance',$easyvoter->id,'presenter',$USER->id)){
			echo 'var iCurrentSlide = -1;';
		}else{
			echo 'var iCurrentSlide = '.($numeral-1).';';
		}
	}
echo '		
		//SLIDERS
		//SLIDE HEIGHT///////////////////////////////////////////////
		document.getElementById(\'responses\').style.height = (screen.height/2)+\'px\';
		document.getElementById(\'slidecontent\').style.height = (screen.height/2)+\'px\';
		//SLIDE HEIGHT///////////////////////////////////////////////
		var aSliderArray = [];
		aSliderArray[0] = new animatedDivSlider(\'title\',1000,false,true);
		aSliderArray[1] = new animatedDivSlider(\'slidename\',1000,false,true);
		aSliderArray[2] = new animatedDivSlider(\'responses\',1000,true,true);
		aSliderArray[3] = new animatedDivSlider(\'slidecontent\',1000,false,true);
		var bResponsesFlag = false;
		
		//AJAX
		var oSlide = new ajaxObject();
		var oResponses = new ajaxObject();
		var iTimerDelay = 250;
		
		function slideDiv(divobject){
			if(typeof(divobject)===\'object\'){
				if(divobject.isIdle()){
					divobject.slide();
					var sSRC = document.getElementById(divobject.getProp(\'divid\')+\'button\').src;
					if(sSRC.indexOf(\'/plus.gif\')>-1){
						swapImage(divobject.getProp(\'divid\')+\'button\',\'images/default/minus.gif\');
					}else if(sSRC.indexOf(\'/minus.gif\')>-1){
						swapImage(divobject.getProp(\'divid\')+\'button\',\'images/default/plus.gif\');
					}
				}
				if(divobject.getProp(\'divid\')!=\'slidecontent\'){
					if(divobject.getProp(\'action\')!=\'close\'){
						bResponsesFlag = true;
					}else{
						bResponsesFlag = false;
					}
					responses(\'1\');
				}
			}
		}
		
		function getSlide(send){
			if(oSlide.ajaxSupported()){
				if(typeof(send)!==\'undefined\'){
					oSlide.ajaxSend(\'get\',\'present/present.php?cid='.$course->id.'&cmid='.$cm->id.'&sid=\'+sSlideArray[iCurrentSlide][0]);
					setTimeout(\'getSlide()\',iTimerDelay);
				}else{
					if(oSlide.ajaxReadyState()<4){
						document.getElementById(\'slide\').innerHTML = "&nbsp;";
						setTimeout(\'getSlide()\',iTimerDelay);
					}else{
						document.getElementById(\'slide\').innerHTML = oSlide.ajaxReturn();
						document.getElementById(\'slidenumbering\').innerHTML = "'.get_string('easyvoterslide', 'easyvoter').'"+iCurrentSlide+" '.get_string('easyvoterof', 'easyvoter').'"+(iTotalSlides-1);
						aSliderArray[1].setHeight(0);
						aSliderArray[3].setHeight(0);
						setType();
					}
				}
			}
		}
		
		function nextSlide(prev){
			if(oSlide.ajaxSupported()){
				if(!oSlide.ajaxReadyState()<4){
					if(typeof(prev)!==\'undefined\'){
						iCurrentSlide--;
					}else{
						iCurrentSlide++;
					}
					if(iCurrentSlide<1){
						iCurrentSlide=0;
						bResponsesFlag = false;
						responses();
						oSlide.ajaxSend(\'get\',\'present/present.php?cid='.$course->id.'&cmid='.$cm->id.'&action=reset\');
						document.getElementById(\'slidename\').innerHTML = "'.get_string('easyvoterintro', 'easyvoter').'";
						';
						$aNewLineArray = array("\r\n", "\n\r", "\n", "\r");
						$sIntro = str_replace($aNewLineArray,'',$easyvoter->intro);
						$sIntro = addslashes($sIntro);
						echo '
						document.getElementById(\'slidecontent\').innerHTML = \'<div id="intro">'.$sIntro.'</div>\';
						document.getElementById(\'slidenumbering\').innerHTML = "'.get_string('easyvoterintro', 'easyvoter').'";
						aSliderArray[2].forceReset();
						aSliderArray[3].forceReset();
						aSliderArray[1].setHeight(0);
						aSliderArray[3].setHeight(0);
						setType();
					}else{
						if(iCurrentSlide<iTotalSlides){
							bResponsesFlag = false;
							responses();
							document.getElementById(\'navigation\').innerHTML = "'.get_string('easyvoterloading', 'easyvoter').'";
							document.getElementById(\'slidename\').innerHTML = "'.get_string('easyvoterloading', 'easyvoter').'";
							document.getElementById(\'slidecontent\').innerHTML = "'.get_string('easyvoterloading', 'easyvoter').'";
							aSliderArray[2].forceReset();
							aSliderArray[3].forceReset();
							getSlide(\'1\');
						}else{
							iCurrentSlide=iTotalSlides-1;
';
							//DONT SAVE AND EXIT IF PREVIEW
							if($sPageCaller==='preview.php'){
								echo '
								window.close();
								';
							}else{
								echo '
							if(confirm(\''.get_string('easyvotershowcomplete', 'easyvoter').'\')){
								window.location = "results/results_output.php?id='.$cm->id.'";
							}								
								';
							}
							

echo '
						}
					}
				}
			}		
		}		
		
		//SLIDE HEIGHT///////////////////////////////////////////////
		var sPlusMinus = "";
		
		function slideHeightUp(){
			sPlusMinus = "";
		}
		
		function slideHeightDown(plusminus){
			sPlusMinus = plusminus;
			slideHeight();
		}	
		
		function slideHeight(){
			if(typeof(aSliderArray[2])===\'object\'&&typeof(aSliderArray[3])===\'object\'){
				if(aSliderArray[2].isIdle()&&aSliderArray[3].isIdle()){
					if(sPlusMinus!==""){
					aSliderArray[2].setHeight(50,sPlusMinus);
					aSliderArray[3].setHeight(50,sPlusMinus);
					setTimeout(\'slideHeight()\',iTimerDelay);
					}
				}
			}
		}
		//SLIDE HEIGHT///////////////////////////////////////////////
		
		function setType(){
			var iDefaultWidth = 352;
			var iDefaultHeight = 88;
			document.getElementById(\'navigation\').style.height = iDefaultHeight+\'px\';
			document.getElementById(\'navigation\').style.visibility = \'visible\';
			if(sPageCaller!=\'preview.php\'){
				if(iCurrentSlide<1){
						document.getElementById(\'navigation\').style.width = (iDefaultWidth/2)+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<div class="squarebutton" onclick="nextSlide()"><span class="greentext">'.get_string('easyvoternext', 'easyvoter').'</span></div>\';
				}else{
						document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<div class="squarebutton" onclick="nextSlide(\\\'previous\\\')"><span class="redtext">'.get_string('easyvoterprevious', 'easyvoter').'</span></div> <div class="squarebutton" onclick="nextSlide()"><span class="greentext">'.get_string('easyvoternext', 'easyvoter').'</span></div>\';		
				}
			}else{
				switch(sSlideArray[iCurrentSlide][1]){
					case \'nume\':
						document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<form><div class="largesquarebutton"><input type="text" name="answer" /><input type="button" value="'.get_string('easyvotersubmitanswer', 'easyvoter').'" onclick="nextSlide()" class="inputbutton" /></div></form>\';
						break;
					case \'mcho\':
						document.getElementById(\'navigation\').style.width = (iDefaultWidth/4)*sSlideArray[iCurrentSlide][2]+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<div class="roundbutton" onclick="nextSlide()"><span class="greentext">'.get_string('easyvotera', 'easyvoter').'</span></div>\';
						document.getElementById(\'navigation\').innerHTML += \'<div class="roundbutton" onclick="nextSlide()"><span class="bluetext">'.get_string('easyvoterb', 'easyvoter').'</span></div>\';
						if(sSlideArray[iCurrentSlide][2]>2){
							document.getElementById(\'navigation\').innerHTML += \'<div class="roundbutton" onclick="nextSlide()"><span class="redtext">'.get_string('easyvoterc', 'easyvoter').'</span></div>\';
						}
						if(sSlideArray[iCurrentSlide][2]>3){
							document.getElementById(\'navigation\').innerHTML += \'<div class="roundbutton" onclick="nextSlide()"><span class="yellowtext">'.get_string('easyvoterd', 'easyvoter').'</span></div>\';
						}
						break;
					case \'text\':
						document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<form><div class="largesquarebutton"><input type="text" name="answer" /><input type="button" value="'.get_string('easyvotersubmitanswer', 'easyvoter').'" onclick="nextSlide()" class="inputbutton" /></div></form>\';
						break;
					case \'true\':
						document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<div class="squarebutton" onclick="nextSlide()"><span class="greentext">'.get_string('easyvotertrue', 'easyvoter').'</span></div> <div class="squarebutton" onclick="nextSlide()"><span class="redtext">'.get_string('easyvoterfalse', 'easyvoter').'</span></div>\';
						break;
					default:
						document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<div class="infosquarebutton" onclick="nextSlide()">'.get_string('easyvoterinfotype', 'easyvoter').'</div>\';
				}
			}
		}
		
		function quitPresentation(){
			if(iCurrentSlide<(iTotalSlides-1)){
				if(confirm(\''.get_string('easyvoterquitshow', 'easyvoter').'\')){
					window.close();
				}
			}else{
				nextSlide();
			}
			return false;
		}
		
		function responses(send){
			if(sPageCaller!=\'preview.php\'){
				if(bResponsesFlag){
					if(sSlideArray[iCurrentSlide][1]!=\'info\'){
						if(oResponses.ajaxSupported()){
							if(typeof(send)!==\'undefined\'){
								oResponses.ajaxSend(\'get\',\'present/present.php?cid='.$course->id.'&cmid='.$cm->id.'&sid=\'+sSlideArray[iCurrentSlide][0]+\'&action=responses&presentanon='.(isset($oFormData->presentanon)?$oFormData->presentanon:'1').'\');
								setTimeout(\'responses()\',iTimerDelay);
							}else{
								if(oResponses.ajaxReadyState()<4){
									setTimeout(\'responses()\',iTimerDelay);
								}else{
									document.getElementById(\'responses\').innerHTML = oResponses.ajaxReturn();
									setTimeout("responses(\'1\')",'.$CFG->easyvoter_refreshrate.');
								}
							}
						}else{
							document.getElementById(\'responses\').innerHTML = "'.get_string('easyvoternoajax', 'easyvoter').'";
						}
					}else{
						document.getElementById(\'responses\').innerHTML = \'<p><img id="a" src="images/default/responses_placeholder.gif" width="200" height="200" alt="'.get_string('easyvoternorecordedfortype', 'easyvoter').'" /></p><p>'.get_string('easyvoternorecordedfortype', 'easyvoter').'</p>\';
					}
				}else{
					if(sSlideArray[iCurrentSlide][1]!=\'info\'){
						document.getElementById(\'responses\').innerHTML = "&nbsp;";
					}else{
						document.getElementById(\'responses\').innerHTML = \'<p><img id="a" src="images/default/responses_placeholder.gif" width="200" height="200" alt="'.get_string('easyvoternorecordedfortype', 'easyvoter').'" /></p><p>'.get_string('easyvoternorecordedfortype', 'easyvoter').'</p>\';
					}		
				}
			}else{
				document.getElementById(\'responses\').innerHTML = \'<p><img id="a" src="images/default/responses_placeholder.gif" width="200" height="200" alt="'.get_string('easyvoterresponsespreview', 'easyvoter').'" /></p><p>'.get_string('easyvoterresponsespreview', 'easyvoter').'</p>\';
			}
		}
		
		if(oSlide.ajaxSupported()&&oResponses.ajaxSupported()){
			oSlide.ajaxAdditionalHeaders(\'XMLHTTPCaller=\'+sPageCaller);
			oResponses.ajaxAdditionalHeaders(\'XMLHTTPCaller=\'+sPageCaller);
			nextSlide();
		}else{
			document.getElementById(\'slide\').innerHTML = "'.get_string('easyvoternoajax', 'easyvoter').'";
		}
		
		//FORCE PARENT REFRESH ONUNLOAD
		if(sPageCaller!=\'preview.php\'){
			window.onunload = refreshParent;
		}
		//FORCE WINDOW TO KEEP FOCUS
		window.onblur = selfFocus;
	//]]>
	</script>
	</body>
	</html>	
';
?>