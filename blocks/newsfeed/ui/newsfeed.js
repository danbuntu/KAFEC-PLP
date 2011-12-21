var xhierarchyuifolder='',xhierarchystrexpand='',xhierarchystrcollapse='',xhierarchystrfeed='',xhierarchypixpath='';
var xhierarchyfilter=new Array();
var xhierarchydblclick=function(id) {};

function xhierarchy_init_root(root) {
	var rootDiv=document.getElementById('o'+root);
	rootDiv.folderpath='/';
	rootDiv.folderid=root;
	rootDiv.type='folder';
	rootDiv.haschildren=true;
	rootDiv.onselectstart=function() { return false; };
	rootDiv.ondrag=function() { return false; };
}

// Stops it displaying newsfeeds with then given ID
function xhierarchy_filter_feeds(ids) {
    xhierarchyfilter=ids;
}

// Expands the given folder ID
function xhierarchy_expand(folderid) {
    var completion=null;
    if(arguments.length>=2) {
      completion=arguments[1];
    }
    // OK get list from server
    do_get_request(xhierarchyuifolder+'/xml_folderlist.php?folder='+folderid,
        function(d) {
				    // Find element where we're putting the new items
				    var ul=document.getElementById('l'+folderid);
				    var parent=document.getElementById('o'+folderid);
				    
            // Clear existing content
            while(ul.firstChild) {
                ul.removeChild(ul.firstChild);
            }
            var empty=true;
            // Add new stuff
            for(var el=d.documentElement.firstChild;el!=null;el=el.nextSibling) {
                if(el.nodeName!='subfolder' && el.nodeName!='feed') continue;
                empty=false;
                var id=el.getAttribute('id'),name=el.getAttribute('name');
                var li=document.createElement('li');
                ul.appendChild(li);
                var div=document.createElement('div');
                li.appendChild(div);
                // For some reason in IE you have to set this property at every level:
								div.onselectstart=function() { return false; };
                
                if(el.nodeName=='subfolder') {
                    div.setAttribute('id','o'+id);
                    
                    div.type='folder';
                    div.style.fontWeight='bold';
                    div.folderid=id;
                    div.folderpath=parent.folderpath + (parent.folderpath=='/' ? name : '/'+name);
                    div.haschildren=el.getAttribute('haschildren')=='yes';
                    
                    div.expanded=false;
                    div.onclick=xhierarchy_clickfunction(div);
                    div.tabIndex=0;
                    div.onfocus=div.onclick;
                    
                    var img=document.createElement('img');
                    if(div.haschildren) {
		                    div.ondblclick=xhierarchy_dblclickfunction(div);
		                    div.onkeypress=xhierarchy_keypressfunction(div);
		                    
		                    img.alt=xhierarchystrexpand;                    
		                    img.src=xhierarchyuifolder+'/expand.gif';
                    } else {
                        img.alt='';
		                    img.src=xhierarchyuifolder+'/empty.gif';
                    }            
                    div.img=img;        
                    div.appendChild(img);
                    div.appendChild(document.createTextNode(' '+name));
                    var newul=document.createElement('ul');
                    newul.setAttribute('id','l'+id);
                    li.appendChild(newul);
                } else if(el.nodeName=='feed') {
                    div.setAttribute('id','e'+id);
                    
                    div.type='feed';
                    div.newsfeedid=id;
                    
                    var stop=false;
                    for(var filter=0;filter<xhierarchyfilter.length;filter++) {
                    		if(xhierarchyfilter[filter]==Number(id)) {
                    		    stop=true;
                    		    break;
                    		}
                    }
		                if(!stop) {
		                    div.tabIndex=0;
		                    div.onclick=xhierarchy_clickfunction(div);
		                    div.onfocus=div.onclick;
		                    div.ondblclick=xhierarchy_dblclickfunction(div);    
                    } else {
                        div.className='transparent';
                    }

                    var img=document.createElement('img');
                    img.alt=xhierarchystrfeed;                    
                    img.src=xhierarchyuifolder+'/feed.gif';
                    div.appendChild(img);
                    
                    div.appendChild(document.createTextNode(' '+name));
                }
            }
            parent.expanded=true;
            if(parent.img && !empty) {
		            parent.img.src=xhierarchyuifolder+'/collapse.gif';
				        parent.img.alt=xhierarchystrcollapse;                    
			      }
                        
            if(completion) {
	            	completion();
            }
        });
}

function xhierarchy_set_dblclick_action(fn) {
		xhierarchydblclick=fn;
}

function xhierarchy_clickfunction(div) {
	  return function() { xhierarchy_select(div.id); }
}
function xhierarchy_dblclickfunction(div) {
    return function() { 
        if(div.type=='folder') {
		        if(div.expanded) xhierarchy_collapse(div.folderid); 
		        else xhierarchy_expand(div.folderid); 
        } else if(div.type=='feed') {
        		xhierarchydblclick(div.feedid);
        }
    };
}
function xhierarchy_keypressfunction(div) {
		return function(e) {
     		var input;
     		if(window.event) {
      			input=window.event.keyCode;
     		} else {
     				input=e.which;		                    				
     		} 
     		if(input==13 || input==32) {
     				div.ondblclick();
     				return false;
     		}
     		return true;
    };     
}

// Collapses the given folder ID
function xhierarchy_collapse(folderid) {
	  // Find element where we're putting the new items
	  var ul=document.getElementById('l'+folderid);
	  var parent=document.getElementById('o'+folderid);
				    
    // Clear existing content
    while(ul.firstChild) {
        ul.removeChild(ul.firstChild);
    }

    parent.expanded=false;
    parent.img.src=xhierarchyuifolder+'/expand.gif';
		parent.img.alt=xhierarchystrexpand;                    
    
}


// Currently selected folder or feed 
var xhierarchy_selected=null;

// Listener that gets notifified when selection changes.
// function(itemid)
var xhierarchy_select_listener=null;

function xhierarchy_register_select_listener(listener) {
    xhierarchy_select_listener=listener;
}

function xhierarchy_get_selected() {
		return xhierarchy_selected;
}

function xhierarchy_select(itemid) {

    var el=document.getElementById(itemid);
    if(!el) return;
    
    if(xhierarchy_selected) {
      if(xhierarchy_selected==el) return;
      xhierarchy_selected.style.background='transparent';
      xhierarchy_selected.style.color='black';
    }
    
    xhierarchy_selected=el;
    el.style.background='black';
    el.style.color='white';
    el.focus();
    
    // Go fix any hidden fields
    var inputs=document.getElementsByTagName('input');
    for(var i=0;i<inputs.length;i++) {
        if(inputs[i].type=='hidden' && inputs[i].name=='folderid') {
            inputs[i].value=(el.type=='folder') ? el.folderid : '';
        }
        if(inputs[i].type=='hidden' && inputs[i].name=='newsfeedid') {
            inputs[i].value=(el.type=='feed') ? el.newsfeedid : '';
        }
    }

    if(xhierarchy_select_listener) {
        xhierarchy_select_listener();
    }
}

function xhierarchy_ensure_expanded(idpath,completion) {
		// Find out how far we've got so far
		var gotall=true;
	  for(var i=1;i<idpath.length;i++) {
	    	var folder=document.getElementById('o'+idpath[i]);
	    	if(!folder) {
	    	    gotall=false;
			    	break;
	    	}
	    	if(!folder.expanded) {
	    			i++; // So that we expand this one next time, not its parent
	    	    gotall=false;
	    			break;
	    	}
		}
		if(gotall) {
				// Got everything!
				completion();
				return;
		}
		
		// OK, so this is the first one we don't have. Expand the previous one
		xhierarchy_expand(idpath[i-1],function() {
				xhierarchy_ensure_expanded(idpath,completion);
		});
}

function xhierarchy_select_folder(idpath) {
    xhierarchy_ensure_expanded(idpath,function() {    
	   	  document.getElementById('o'+idpath[0]).style.visibility='visible';
	   	  document.getElementById('l'+idpath[0]).style.visibility='visible';
	   	  xhierarchy_select('o'+idpath[idpath.length-1]);
    });
}
function xhierarchy_select_feed(idpath,feedid) {
    xhierarchy_ensure_expanded(idpath,function() {
	   	  document.getElementById('o'+idpath[0]).style.visibility='visible';
	   	  document.getElementById('l'+idpath[0]).style.visibility='visible';
	   	  xhierarchy_select('e'+feedid);
    });
}

function util_enable(enable,list) {
    for(var i=0;i<list.length;i++) {
      	document.getElementById(list[i]).disabled=!enable;
    }
}

function do_get_request(url,target) {
    var rq=get_xml_request();
    rq.onreadystatechange=function() { 
        if(rq.readyState==4 && rq.status == 200) {
            target(rq.responseXML); 
        }
	  }
		rq.open('GET',url,true);
    rq.send(null);    
}

function do_post_request(url,params,target) {
    var rq=get_xml_request();
    rq.onreadystatechange=function() { 
        if(rq.readyState==4 && rq.status == 200) {
            target(rq.responseXML); 
        }
	  }
    rq.setRequestHeader("Content-Type",
			"application/x-www-form-urlencoded; charset=UTF-8");
		rq.open('POST',url,true);
    rq.send(params);    
}

function get_xml_request() {
    if (window.XMLHttpRequest) { // Cross-browser
        return new XMLHttpRequest();
    } else if (window.ActiveXObject) { // IE
        try {
            return new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                return new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
        }
    }
    throw new Exception('Failed to obtain XMLHttpRequest object');
}