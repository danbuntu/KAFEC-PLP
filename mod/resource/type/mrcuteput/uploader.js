function moveUp(element) {
  for(i = 0; i < element.options.length; i++) {
    if(element.options[i].selected == true) {
      if(i != 0) {
        var temp = new Option(element.options[i-1].text,element.options[i-1].value);
        var temp2 = new Option(element.options[i].text,element.options[i].value);
        element.options[i-1] = temp2;
        element.options[i-1].selected = true;
        element.options[i] = temp;
      }
    }
  }
}
function moveDown(element) {
  for(i = (element.options.length - 1); i >= 0; i--) {
    if(element.options[i].selected == true) {
      if(i != (element.options.length - 1)) {
        var temp = new Option(element.options[i+1].text,element.options[i+1].value);
        var temp2 = new Option(element.options[i].text,element.options[i].value);
        element.options[i+1] = temp2;
        element.options[i+1].selected = true;
        element.options[i] = temp;
      }
    }
  }
}
function removeItem(element)
{
  var selIndex = element.selectedIndex;
  if (selIndex != -1) {
    for(i=element.length-1; i>=0; i--)
    {
      if(element.options[i].selected)
      {
		var fileinput = element.options[i].value;
		
		if (fileinput.substring(0,6) == 'files_' && fileinput.substring(7,8) == ':' ) {
			var todelete = document.getElementsByName(fileinput.substring(0,7))[0];
			todelete.parentNode.removeChild(todelete);
		}
        element.options[i] = null;
      }
    }
    if (element.length > 0) {
      element.selectedIndex = selIndex == 0 ? 0 : selIndex - 1;
    }
  }

}






	  function addModalEvent(obj ,evt, fnc)
      {
        if (obj.addEventListener)
          obj.addEventListener(evt,fnc,false);
        else if (obj.attachEvent)
          obj.attachEvent('on'+evt,fnc);
        else
          return false;
        return true;
      }

      function removeModalEvent(obj ,evt, fnc)
      {
        if (obj.removeEventListener)
          obj.removeEventListener(evt,fnc,false);
        else if (obj.detachEvent)
          obj.detachEvent('on'+evt,fnc);
        else
          return false;
        return true;
      }

      //----------

      function appendElement(node,tag,id,htm)
      {
        var ne = document.createElement(tag);
        if(id) ne.id = id;
        if(htm) ne.innerHTML = htm;
        node.appendChild(ne);
      }

      //----------

      function showPopup(p)
      {
		  //if IE6 then hide select box to account for retarded z-index handling
		  if(Browser.Engine.trident && Browser.Engine.version == 4){
			var selectbox = document.getElementById('id_items');
			selectbox.style.visibility = "hidden";
		  }
		  		  
		pu = document.getElementById(p);
		pu.style.top  = (document.body.scrollTop+((document.body.clientHeight/2)-100))+"px";
		pu.style.left = (document.body.scrollLeft+((document.body.clientWidth-250)/2))+"px";
        greyout(true);
        document.getElementById(p).style.display = 'block';
      }

      function hidePopup(p)
      {
		  //if IE6 then hide select box to account for retarded z-index handling
		  if(Browser.Engine.trident && Browser.Engine.version == 4){
			var selectbox = document.getElementById('id_items');
			selectbox.style.visibility = "visible";
		  }
		  
        greyout(false);
        document.getElementById(p).style.display = 'none';
      }

      //----------

      function greyout(d,z)
      {
        var obj = document.getElementById('greyout');

        if(!obj)
        {
          appendElement(document.body,'div','greyout');
          obj = document.getElementById('greyout');
          obj.style.position = 'absolute';
          obj.style.top = '0px';
          obj.style.left = '0px';
          obj.style.background = '#111';
          obj.style.opacity = '.7';
          obj.style.filter = 'alpha(opacity=70)';
        }
        if(d)
        {
          var ch = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
          var cw = document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.clientWidth;
          var sh = document.documentElement.scrollHeight ? document.documentElement.scrollHeight : document.body.scrollHeight;
          if(document.body.scrollHeight) sh = Math.max(sh,document.body.scrollHeight)
          var sw = document.documentElement.scrollWidth ? document.documentElement.scrollWidth : document.body.scrollWidth;
          if(document.body.scrollWidth) sh = Math.max(sh,document.body.scrollWidth);
          var wh = window.innerHeight ? window.innerHeight : document.body.offsetHeight;
          if(!z){ z = 50 }
          obj.style.zIndex = z;
          obj.style.height = Math.max(wh,Math.max(sh,ch))+'px';
          obj.style.width  = Math.max(sw,cw)+'px';
          obj.style.display = 'block';
          addModalEvent(window,'resize',greyoutResize);
        }
        else
        {
          obj.style.display = 'none';   
          removeModalEvent(window,'resize',greyoutResize);
        }
      }

      function greyoutResize()
      {
        var ch = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
        var cw = document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.clientWidth;
        var sh = document.documentElement.scrollHeight ? document.documentElement.scrollHeight : document.body.scrollHeight;
        if(document.body.scrollHeight) sh = Math.max(sh,document.body.scrollHeight)
        var sw = document.documentElement.scrollWidth ? document.documentElement.scrollWidth : document.body.scrollWidth;
        if(document.body.scrollWidth) sh = Math.max(sh,document.body.scrollWidth)
        var wh = window.innerHeight ? window.innerHeight : document.body.offsetHeight;
        var obj = document.getElementById('greyout');
        obj.style.height = ch+'px';
        obj.style.width  = cw+'px';
        obj.style.height = Math.max(wh,Math.max(sh,ch))+'px';
        obj.style.width  = Math.max(sw,cw)+'px';
      }









function enableMultiple(show)
{
	var objItems = document.getElementById('id_items');
	var objItemControls = document.getElementById('itemControls');
	if(show){
		objItemControls.style.display = 'block';
		objItems.size = 5;
		document.getElementsByName('checkmultiple')[0].disabled = true;
	//}else{
	//	objItemControls.style.display = 'none';
	//	objItems.size = 1;
	}
}

window.addEvent('domready', function(){
	//no limit, use default element name suffix, don't remove path from file name, disable empty file input
	new MultiUpload( $( 'mform1' ).files, null, null, null, true );
});

window.addEvent('load', function(e){
	//some browsers remember options, so reset them
	document.getElementsByName('checkmultiple')[0].disabled = false;
	document.getElementsByName('checkmultiple')[0].checked = false;
	
	$( 'mform1' ).addEvent('submit', function(e){
				//select all items in mulitple select box
				var items = $('id_items');
				for (i=0; i<items.length; i++) {
					items.options[i].selected = true; 
				}
				return true;				
			});
});

function addURL() {
	var title = document.getElementsByName('urltitle')[0];
	var url = document.getElementsByName('url')[0];
	var objItems = document.getElementById('id_items');
	var option = document.createElement('option');
	option.innerHTML  = title.value;
	option.value = title.value + '&lt;' + url.value+'&gt;';

	objItems.appendChild(option); 
	objItems.size = objItems.size + 1;

	title.value = '';
	url.value = 'http://';
	
	hidePopup('urlpopup');
}