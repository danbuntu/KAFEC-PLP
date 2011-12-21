/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: ajax/ajaxclass.js
//NOTES: AJAX class to deal with XMLHTTP Requests
////////////////////////////////////////////////////////////////////////////
function ajaxObject(){
	var oXMLHTTP = null;
	var sReadyState = 0;
	var sStatus = 0;
	var sReturnString = "";
	var aAdditionalHeaders = new Array();
	ajaxInitialise();
	
	//Create Ajax Object
	function ajaxInitialise(){
		try{
			// Firefox, Opera 8.0+, Safari  
			oXMLHTTP = new XMLHttpRequest();  
		}
		catch(e){
			try{
			  // Internet Explorer
			  oXMLHTTP = new ActiveXObject("Msxml2.XMLHTTP");    
			}
		  	catch(e){
					try{
						oXMLHTTP = new ActiveXObject("Microsoft.XMLHTTP");
					}
					catch(e){
				    	oXMLHTTP = null;
					}  
			 }
		}
	}
		
	function ajaxStateChange(){
	//oXMLHTTP.onreadystatechange = function(){
		sReadyState = oXMLHTTP.readyState
		if(oXMLHTTP.readyState>3){
			sStatus = oXMLHTTP.status;
		}
		if(sReadyState>3 && sStatus===200)
	  	{
	  		sReturnString = oXMLHTTP.responseText;
	  	}
	}
	
	this.ajaxSupported = function(){
		if(typeof(oXMLHTTP)!=='object'){
			return false;
		}else{
			return true;
		}
	}
	
	this.ajaxSend = function(method,url,async,form){
		if(typeof(oXMLHTTP)==='object'){
			this.ajaxAbort();
			var sMethod = method.toLowerCase();
			var bAsync = true;
			if(typeof(async)==='boolean'){
				bAsync = async;
			}
				
			sReturnString = "";
				
			if(sMethod!=="post"){
				oXMLHTTP.open('get',url,bAsync);
				oXMLHTTP.onreadystatechange = ajaxStateChange;
				oXMLHTTP.setRequestHeader("Expires", "Thu, 01 Jan 1970 00:00:00 GMT");
				oXMLHTTP.setRequestHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0");
				//ADDITIONAL HEADERS
				var iLoop = 0;
				for(iLoop in aAdditionalHeaders){
					oXMLHTTP.setRequestHeader(aAdditionalHeaders[iLoop][0], aAdditionalHeaders[iLoop][1]);
				}
				oXMLHTTP.send(null);
			}else{
				var oForm = "";
				var sParams = "";
				if(typeof(form)==='string'||typeof(form)==='number'){
					oForm = document.forms[form];
				}else{
					oForm = document.forms[0];
				}
				for(iLoop=0;iLoop<oForm.elements.length;iLoop++){
					sParams = sParams+oForm.elements[iLoop].name;
					sParams = sParams+"="+escape(oForm.elements[iLoop].value)+"&";
				}
				if(sParams!==""){
					sParams = sParams.substr(0,sParams.length-1);
				}
				oXMLHTTP.open('post',url,bAsync);
				//ADDITIONAL HEADERS
				var iLoop = 0;
				for(iLoop in aAdditionalHeaders){
					oXMLHTTP.setRequestHeader(aAdditionalHeaders[iLoop][0], aAdditionalHeaders[iLoop][1]);
				}
				oXMLHTTP.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				oXMLHTTP.setRequestHeader("Content-length", sParams.length);
				oXMLHTTP.setRequestHeader("Connection", "close");
				oXMLHTTP.onreadystatechange = ajaxStateChange;
				oXMLHTTP.send(sParams);
			}
		}
	}
	
	this.ajaxReadyState = function(){
		if(typeof(oXMLHTTP)==='object'){
			//0 The request is not initialized 
			//1 The request has been set up 
			//2 The request has been sent 
			//3 The request is in process 
			//4 The request is complete 
			return sReadyState;
		}
	}

	this.ajaxStatus = function(){
		if(typeof(oXMLHTTP)==='object'){
			//200 OK 404 not found etc... 
			return sStatus;
		}
	}
	
	this.ajaxReturn = function(){
		var sString = null;
		if(typeof(oXMLHTTP)==='object'&&oXMLHTTP.readyState>3){
			 sString = sReturnString;
		}
		return sString;
	}
	
	this.ajaxAbort = function(){
		if(typeof(oXMLHTTP)==='object'){
			oXMLHTTP.abort();
			sReadyState = 0;
			sStatus = 0;
			sReturnString = "";
		}
	}
	
	this.ajaxAdditionalHeaders = function(headerlist){
		if(typeof(oXMLHTTP)==='object'){
			if(typeof(headerlist)==='string'){
				var aHeaderPairs = headerlist.split('|');
				var aHeader = null;
				var aAdditionalHeadersTemp = new Array();
				var iLoop = 0;
				for(iLoop in aHeaderPairs){
					aHeader = aHeaderPairs[iLoop].split('=');
					if(aHeader.length==2){
						aAdditionalHeadersTemp[iLoop] = new Array(aHeader[0],aHeader[1]);
						aHeader = null;
					}
				}
				if(aAdditionalHeadersTemp.length>0){
					aAdditionalHeaders = null;
					aAdditionalHeaders = aAdditionalHeadersTemp;
				}
			}
			return aAdditionalHeaders;
		}
	}
}