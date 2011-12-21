function set_value(location,name,description) {
	opener.document.getElementById('id_reference').value = location;
	opener.document.getElementById('id_name').value = name;
	
	var	fram = opener.document.getElementsByTagName('iframe')[0];
	var oDoc = fram.contentWindow || fram.contentDocument;
	oDoc.document.body.innerHTML = rawurldecode(description);

	window.close();
}

function rawurldecode( str ) {
	var histogram = {};
	var ret = str.toString(); 
	var replacer = function(search, replace, str) {
	var tmp_arr = [];
	tmp_arr = str.split(search);
	return tmp_arr.join(replace);
	};

	histogram["'"]   = '%27';
	histogram['(']   = '%28';
	histogram[')']   = '%29';
	histogram['*']   = '%2A';
	histogram['~']   = '%7E';
	histogram['!']   = '%21';

	for (replace in histogram) {
	search = histogram[replace];
	ret = replacer(search, replace, ret)
	}

	ret = ret.replace(/%([a-fA-F][0-9a-fA-F])/g, function (all, hex) {return String.fromCharCode('0x'+hex);});
	ret = decodeURIComponent(ret);
	return ret;
}
