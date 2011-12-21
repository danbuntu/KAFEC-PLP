//<![CDATA[

//SWAP IMAGE FUNCTION
function swapImage(imageover,swapimage){
	if(typeof(document.getElementById(imageover))==='object'){
		document.getElementById(imageover).src=swapimage;
	}
}


//POPUP WINDOW FUNCTION
var aWin = [];
function popupWin(page,name,fullscreen){
	if(typeof(page)==='string'&&typeof(name)==='string'&&typeof(fullscreen)==='boolean'){
		if(aWin[name]&&aWin[name].closed!==true&&aWin[name].closed!==undefined){
			aWin[name].focus();
			aWin[name].moveTo(0,0);
		}else{
			aWin[name] = null;
			if(fullscreen){
				//FULLSCREEN NOT SET AS THIS SEEMS TO SEND TILE BAR OFF SCREEN WHEN moveTo(0,0) FUNCTION CALLED IN IE7
				//SO SCREEN HEIGHT AND WIDTH IS USED INSTEAD
				aWin[name] = window.open(page,name,'channelmode=0,directories=0,fullscreen=0,height='+screen.height+',left=0,location=0,menubar=0,resizable=0,scrollbars=1,status=0,titlebar=0,toolbar=0,top=0,width='+screen.width+'',true);
			}else{
				aWin[name] = window.open(page,name);
			}
		}
	}
}

//FORCE PARENT REFRESH
function refreshParent(){
	if(typeof(window.parent.opener)==='object'){
		window.parent.opener.window.location.reload();
	}
}

//DRAW FOCUS BACK TO SELF AN PLACE WINDOW IN TOP LEFT CORNER
function selfFocus(){
	//IE FIRES ONBLUR IF FORM ELEMENT IS CLICKED CHECK activeElemnt FOR IE AND ONLY FIRE IS activeElemnt ID IS EMPTY
	//IGNORED BY BROWSERS NOT SUPPORTING activeElement
	if(document.activeElement){
		if(document.activeElement.id==''){
			window.focus();
			oSelfFocusTimer = setTimeout('window.moveTo(0,0)',250);	
		}
	}else{
		window.focus();
		oSelfFocusTimer = setTimeout('window.moveTo(0,0)',250);		
	}
}
		
//]]>