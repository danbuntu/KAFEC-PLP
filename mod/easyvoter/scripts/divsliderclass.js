// JavaScript Document

//START :animatedDivSlider ENCAPULATED OBJECT
function animatedDivSlider(divid,duration,initiallyclosed,dynamicfonts){
//VARIABLES	
	var thisObject = this;
	var sDivId = divid;
	var iMaxSize = 0;
	var sAction = 'close';
	var iAnimTimeout = 50;
	var iStartTime = 0;
	var iElapsedTime = 0;
	var iDuration = 1000;
	if(typeof(duration)==='number'){
		iDuration = duration;
	}
	var iCurrentSize = 0;
	var bSupported = false;
	var bInitiallyClosed = true;
	if(typeof(initiallyclosed)==='boolean'){
		bInitiallyClosed = initiallyclosed;
	}
	var bAccomodateDynamicFontSize = true;
	if(typeof(dynamicfonts)==='boolean'){
		bAccomodateDynamicFontSize = dynamicfonts;
	}
	var oAnimTimer = null;
	var iSafeGuard = 0;

//PROPERTIES / ACCESSIBLE METHODS
	thisObject.getProp = getProp;
	thisObject.isIdle = isIdle;
	thisObject.slide = slide;
	thisObject.animate = animate;
	thisObject.setHeight = setHeight;
	thisObject.forceReset = forceReset;

//SETUP
	bSupported = checkSupport();
	initialise();
	
//PRIVATE FUNCTION:checkSupport	
	function checkSupport(){
		bIsSupported = false;
		if(typeof(document.getElementById(sDivId))!='undefined'){
			var oElement = document.getElementById(sDivId);
			if(typeof(oElement.innerHTML)!='undefined'){
				var sLocation = ''+window.location;
				if(typeof(oElement.style.height)!='undefined'||typeof(oElement.offsetHeight)!='undefined'){
					bIsSupported = true;
				}
			}
		}
		return bIsSupported;
	}
////////////////////////
//PRIVATE FUNCTION:initialise
	function initialise(){
		if(bSupported){
			if(typeof(document.getElementById(sDivId).offsetHeight)!='undefined'){
				iMaxSize = parseInt(document.getElementById(sDivId).offsetHeight);
			}else{
				iMaxSize = parseInt(document.getElementById(sDivId).style.height);
			}
			document.getElementById(sDivId).style.overflow = 'hidden';
			if(!bInitiallyClosed){
				iCurrentSize = iMaxSize;
				sAction = 'open';
			}			
			document.getElementById(sDivId).style.height=iCurrentSize+'px';
		}
	}
////////////////////////
//PRIVATE FUNCTION:easeInOut
//ADDAPTED FOR USE FROM ROBERT PENNERS SINUSOIDAL EASING - robertpenner.com
	function easeInOut(elapsedtime,slideduration,beginheight,endheight){
		return -endheight/2 * (Math.cos(Math.PI*elapsedtime/slideduration) - 1) + beginheight;
	}
////////////////////////
//PUBLIC METHOD:isIdle
	function isIdle(){
		if(oAnimTimer==null){
			return true;
		}else{
			return false;	
		}
	}
////////////////////////
//PUBLIC METHOD:getProp
	function getProp(prop){
		switch(prop){
			case'divid':
				return sDivId;
				break;
			case'duration':
				return iDuration;
				break;
			case'initiallyclosed':
				return bInitiallyClosed;
				break;
			case'accessibleweb':
				return sAccessibleWebsite;
				break;
			case'dynamicfonts':
				return bAccomodateDynamicFontSize;
				break;
			case'maxsize':
				return iMaxSize;
				break;
			case 'action':
				return sAction;
				break;
			default:
				return undefined;
		}
	}
////////////////////////
//PUBLIC METHOD:direction
	function slide(force){
		if(bSupported){
			if(oAnimTimer==null){		
				//FORCE
				if(typeof(force)!='undefined'&&force!=''){
					if(force=='expand'){
						sAction = 'close';
					}
					if(force=='collapse'){
						sAction = 'open';
					}			
				}
				//ACTION
				if(sAction!='open'){
					sAction = 'open';
					iSafeGuard = -1;
				}else{
					sAction = 'close';
					iSafeGuard = iMaxSize+1;
				//CHANGE OVERFLOW INCASE TEXT SIZE IS INCREASED//////////////
				if(bAccomodateDynamicFontSize){
					document.getElementById(sDivId).style.overflow = 'hidden';
					document.getElementById(sDivId).scrollTop = 0;
				}
				/////////////////////////////////////////////////////////////
				}
				iStartTime = new Date().getTime();
				thisObject.animate();
			}
		}
	}

////////////////////////
//PUBLIC METHOD:animate
	function animate(){	
		if(bSupported){
			
			iElapsedTime = new Date().getTime()-iStartTime;
			
			if(sAction!='close'){
				//MAKE SURE NOT ALREADY CLOSED
				if(iCurrentSize!=iMaxSize){
					iCurrentSize = easeInOut(iElapsedTime,iDuration,0,iMaxSize);
					iCurrentSize = Math.ceil(iCurrentSize);
					
					if(iCurrentSize<iMaxSize&&iSafeGuard<iCurrentSize){
						document.getElementById(sDivId).style.height = iCurrentSize+'px';
						oAnimTimer=setTimeout(function(){thisObject.animate()},iAnimTimeout);
						iSafeGuard = iCurrentSize;
					}else{
						iCurrentSize=iMaxSize;
						document.getElementById(sDivId).style.height = iMaxSize+'px';
						//CHANGE OVERFLOW INCASE TEXT SIZE IS INCREASED///////////////
						if(bAccomodateDynamicFontSize){
							document.getElementById(sDivId).style.overflow = 'auto';
							document.getElementById(sDivId).scrollTop = 0;
						}
						//////////////////////////////////////////////////////////////
						oAnimTimer=null;
					}
				}
			}else{	
				//MAKE SURE NOT ALREADY OPEN
				if(iCurrentSize!=0){
					iCurrentSize = easeInOut(iElapsedTime,iDuration,iMaxSize,-iMaxSize);
					iCurrentSize = Math.floor(iCurrentSize);
		
					if(iCurrentSize>0&&iSafeGuard>iCurrentSize){
						document.getElementById(sDivId).style.height = iCurrentSize+'px';
						oAnimTimer=setTimeout(function(){thisObject.animate()},iAnimTimeout);
						iSafeGuard = iCurrentSize;
					}else{
						iCurrentSize=0;
						document.getElementById(sDivId).style.height = iCurrentSize+'px';
						oAnimTimer=null;
					}
				}
			}
		}
	}
////////////////////////
//PUBLIC METHOD:setHeight
function setHeight(pixels,plusminus){
	if(bSupported){
		if(oAnimTimer==null&&typeof(pixels)=='number'){
			if(pixels>0){
				if(plusminus=='plus'){
					iMaxSize += pixels;
					if((iCurrentSize+pixels)==iMaxSize){
						iCurrentSize += pixels;
						document.getElementById(sDivId).style.height = iCurrentSize+'px';
					}
				}else{
					if((iMaxSize-pixels)>pixels){
						iMaxSize -= pixels;
					}
					if((iMaxSize+pixels)==iCurrentSize){
						iCurrentSize -= pixels;
						document.getElementById(sDivId).style.height = iCurrentSize+'px';
					}
				}
			}else{
				document.getElementById(sDivId).style.height = iCurrentSize+'px';
			}
		}
	}
}
////////////////////////
//PUBLIC METHOD:forceReset
function forceReset(){
	if(bSupported){
		oAnimTimer=null;
		if(bInitiallyClosed){
			iCurrentSize = 0;
			sAction = 'close';
			if(bAccomodateDynamicFontSize){
				document.getElementById(sDivId).style.overflow = 'hidden';
				document.getElementById(sDivId).scrollTop = 0;
			}
		}else{
			iCurrentSize = iMaxSize;
			sAction = 'open';
			if(bAccomodateDynamicFontSize){
				document.getElementById(sDivId).style.overflow = 'auto';
				document.getElementById(sDivId).scrollTop = 0;
			}
		}
		document.getElementById(sDivId).style.height = iCurrentSize+'px';
	}
}
////////////////////////
}
//END :animatedDivSlider ENCAPULATED OBJECT
