<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: present/present_slides.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Present slides
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF $sPageCaller VARIABLE NOT FOUND	
if(!isset($sPageCaller)||$sPageCaller!=='present.php'){
	require_once("../../../config.php");
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////

echo '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>'.get_string('easyvoterparticipate', 'easyvoter').$easyvoter->name.'</title>
    <link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/mod/easyvoter/styles/default.css" />
	<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/easyvoter/scripts/divsliderclass.js"></script>
	<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/easyvoter/scripts/functionlibrary.js"></script>
	<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/easyvoter/scripts/ajaxclass.js"></script>
    </head>
    <body>
	<div id="slideminusheightaction" onmousedown="slideHeightDown(\'minus\')" onmouseout="slideHeightUp()" onmouseup="slideHeightUp()" title="'.get_string('easyvoterdecrease', 'easyvoter').'"><img class="plusminus" src="images/default/minus.gif" alt="'.get_string('easyvoterdecrease', 'easyvoter').'" />'.get_string('easyvoterdecrease', 'easyvoter').'</div>
	<div id="slideplusheightaction" onmousedown="slideHeightDown(\'plus\')" onmouseout="slideHeightUp()" onmouseup="slideHeightUp()" title="'.get_string('easyvoterincrease', 'easyvoter').'"><img class="plusminus" src="images/default/plus.gif" alt="'.get_string('easyvoterincrease', 'easyvoter').'" />'.get_string('easyvoterincrease', 'easyvoter').'</div>
	<div id="slidenameaction" onclick="slideDiv(aSliderArray[1])" title="'.get_string('easyvoteropenclose', 'easyvoter').'"><img id="slidenamebutton" class="plusminus" src="images/default/minus.gif" alt="'.get_string('easyvoteropenclose', 'easyvoter').'" />'.get_string('easyvoterslidename', 'easyvoter').'</div>
	<div id="titleaction" onclick="slideDiv(aSliderArray[0])" title="'.get_string('easyvoteropenclose', 'easyvoter').'"><img id="titlebutton" class="plusminus" src="images/default/minus.gif" alt="'.get_string('easyvoteropenclose', 'easyvoter').'" />'.get_string('easyvotertitle', 'easyvoter').'</div>
	
	<div id="header">
		<div id="title">'.$easyvoter->name.'</div>
	</div>
	
	<div id="content">
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
		var iCurrentSlide = -1;
	
		//SLIDERS
		//SLIDE HEIGHT///////////////////////////////////////////////
		document.getElementById(\'slidecontent\').style.height = (screen.height/2)+\'px\';
		//SLIDE HEIGHT///////////////////////////////////////////////
		var aSliderArray = [];
		aSliderArray[0] = new animatedDivSlider(\'title\',1000,false,true);
		aSliderArray[1] = new animatedDivSlider(\'slidename\',1000,false,true);
		aSliderArray[2] = new animatedDivSlider(\'slidecontent\',1000,false,true);
		
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
			}
		}
		
		function getSlide(send){
			if(iCurrentSlide<1){
				document.getElementById(\'slidename\').innerHTML = "'.get_string('easyvoterintro', 'easyvoter').'";
				';
				$aNewLineArray = array("\r\n", "\n\r", "\n", "\r");
				$sIntro = str_replace($aNewLineArray,'',$easyvoter->intro);
				$sIntro = addslashes($sIntro);
				echo '
				document.getElementById(\'slidecontent\').innerHTML = \'<div id="intro">'.$sIntro.'</div>\';
				document.getElementById(\'slidenumbering\').innerHTML = "'.get_string('easyvoterintro', 'easyvoter').'";
				setType();
			}else{
				if(oSlide.ajaxSupported()){
					if(typeof(send)!==\'undefined\'){
						oSlide.ajaxSend(\'get\',\'present/participate.php?cid='.$course->id.'&sid=\'+sSlideArray[iCurrentSlide][0]);
						setTimeout(\'getSlide()\',iTimerDelay);
					}else{
						if(oSlide.ajaxReadyState()<4){
							setTimeout(\'getSlide()\',iTimerDelay);
						}else{
							var sOutputString = oSlide.ajaxReturn();
							if(sOutputString!=\''.get_string('easyvoteraccessdenied', 'easyvoter').'\'&&sOutputString!=\''.get_string('easyvoternoslides', 'easyvoter').'\'){
								if(sOutputString!=\''.get_string('easyvoterendedbypresenter', 'easyvoter').'\'){
									document.getElementById(\'slide\').innerHTML = sOutputString;
									document.getElementById(\'slidenumbering\').innerHTML = "'.get_string('easyvoterslide', 'easyvoter').'"+iCurrentSlide+" '.get_string('easyvoterof', 'easyvoter').'"+(iTotalSlides-1);
									aSliderArray[2].setHeight(0);
									isRecorded(\'1\');
								}else{
									//IF CLOSED BY PRESENTER
									refreshParent();
									window.close();
								}
							}
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
			if(typeof(aSliderArray[2])===\'object\'){
				if(aSliderArray[2].isIdle()){
					if(sPlusMinus!==""){
					aSliderArray[2].setHeight(50,sPlusMinus);
					setTimeout(\'slideHeight()\',iTimerDelay);
					}
				}
			}
		}
		//SLIDE HEIGHT///////////////////////////////////////////////
		
		function setType(recorded){
			if(typeof(recorded)===\'undefined\'){
				recorded = \'FALSE\';
			}
			var iDefaultWidth = 352;
			var iDefaultHeight = 88;
			document.getElementById(\'navigation\').style.height = iDefaultHeight+\'px\';
			document.getElementById(\'navigation\').style.visibility = \'visible\';

			if(recorded!=\'FALSE\'){
				document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
				document.getElementById(\'navigation\').innerHTML = "'.get_string('easyvoterresponserecorded', 'easyvoter').'";
			}else{
				switch(sSlideArray[iCurrentSlide][1]){
					case \'nume\':
						document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<form onsubmit="return false;"><div class="largesquarebutton"><input type="text" name="answer" id="id_answer" /><input type="button" value="'.get_string('easyvotersubmitanswer', 'easyvoter').'" onclick="sendResponse(document.forms[0].elements[0].value)" class="inputbutton" name="submitanswer" id="id_submitanswer" /></div></form>\';
						break;
					case \'mcho\':
						document.getElementById(\'navigation\').style.width = (iDefaultWidth/4)*sSlideArray[iCurrentSlide][2]+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<div class="roundbutton" onclick="sendResponse(\\\'A\\\')"><span class="greentext">'.get_string('easyvotera', 'easyvoter').'</span></div>\';
						document.getElementById(\'navigation\').innerHTML += \'<div class="roundbutton" onclick="sendResponse(\\\'B\\\')"><span class="bluetext">'.get_string('easyvoterb', 'easyvoter').'</span></div>\';
						if(sSlideArray[iCurrentSlide][2]>2){
							document.getElementById(\'navigation\').innerHTML += \'<div class="roundbutton" onclick="sendResponse(\\\'C\\\')"><span class="redtext">'.get_string('easyvoterc', 'easyvoter').'</span></div>\';
						}
						if(sSlideArray[iCurrentSlide][2]>3){
							document.getElementById(\'navigation\').innerHTML += \'<div class="roundbutton" onclick="sendResponse(\\\'D\\\')"><span class="yellowtext">'.get_string('easyvoterd', 'easyvoter').'</span></div>\';
						}
						break;
					case \'text\':
						document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<form onsubmit="return false;"><div class="largesquarebutton"><input type="text" name="answer" id="id_answer" /><input type="button" value="'.get_string('easyvotersubmitanswer', 'easyvoter').'" onclick="sendResponse(document.forms[0].elements[0].value)" class="inputbutton" name="submitanswer" id="id_submitanswer" /></div></form>\';
						break;
					case \'true\':
						document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<div class="squarebutton" onclick="sendResponse(\\\'TRUE\\\')"><span class="greentext">'.get_string('easyvotertrue', 'easyvoter').'</span></div> <div class="squarebutton" onclick="sendResponse(\\\'FALSE\\\')"><span class="redtext">'.get_string('easyvoterfalse', 'easyvoter').'</span></div>\';
						break;
					default:
						document.getElementById(\'navigation\').style.width = iDefaultWidth+\'px\';
						document.getElementById(\'navigation\').innerHTML = \'<div class="infosquarebutton">'.get_string('easyvoterinfotype', 'easyvoter').'</div>\';
				}
			}
		}
		
		function sendResponse(response){
			if(oResponses.ajaxSupported()){
				if(typeof(response)!==\'undefined\'){
					oResponses.ajaxSend(\'get\',\'present/participate.php?cid='.$course->id.'&sid=\'+sSlideArray[iCurrentSlide][0]+\'&action=response\'+\'&response=\'+encodeURIComponent(response));
					setTimeout(\'sendResponse()\',iTimerDelay);
				}else{
					if(oResponses.ajaxReadyState()<4){
						setTimeout(\'sendResponse()\',iTimerDelay);
					}else{
						if(oResponses.ajaxReturn()==\''.get_string('easyvoterresponserecorded', 'easyvoter').'\'){
							setType(\'TRUE\');
						}
					}
				}
			}
		}

		function isRecorded(send){
			if(oResponses.ajaxSupported()){
				if(typeof(send)!==\'undefined\'){
					oResponses.ajaxSend(\'get\',\'present/participate.php?cid='.$course->id.'&&sid=\'+sSlideArray[iCurrentSlide][0]+\'&action=isrecorded\');
					setTimeout(\'isRecorded()\',iTimerDelay);
				}else{
					if(oResponses.ajaxReadyState()<4){
						setTimeout(\'isRecorded()\',iTimerDelay);
					}else{
						setType(oResponses.ajaxReturn());
					}
				}
			}
		}

		function refresh(checkcurrent){
			if(oSlide.ajaxSupported()){
				if(typeof(checkcurrent)!==\'undefined\'){
					oSlide.ajaxSend(\'get\',\'present/participate.php?cid='.$course->id.'&inid='.$easyvoter->id.'&action=currentslide\');
					setTimeout(\'refresh()\',iTimerDelay);
				}else{
					if(oSlide.ajaxReadyState()<4){
						setTimeout(\'refresh()\',iTimerDelay);
					}else{
						var iNumeral = oSlide.ajaxReturn();
						if(iNumeral<0){
							//IF NO INSTANCE FOUND
							refreshParent();
							window.close();
						}else{
							if(iCurrentSlide != iNumeral){
								iCurrentSlide = iNumeral;
								getSlide(\'1\');
							}
							setTimeout("refresh(\'1\')",'.$CFG->easyvoter_refreshrate.');
						}
					}
				}
			}	
		}

		if(oSlide.ajaxSupported()&&oResponses.ajaxSupported()){
			oSlide.ajaxAdditionalHeaders(\'XMLHTTPCaller=\'+sPageCaller);
			oResponses.ajaxAdditionalHeaders(\'XMLHTTPCaller=\'+sPageCaller);
			refresh(\'1\');
		}else{
			document.getElementById(\'slide\').innerHTML = "'.get_string('easyvoternoajax', 'easyvoter').'";
		}
		
		//FORCE PARENT REFRESH ONUNLOAD
		window.onunload = refreshParent;

		//FORCE WINDOW TO KEEP FOCUS
		window.onblur = selfFocus;
	//]]>
	</script>
	</body>
	</html>	
';
?>