function error(filename)
{
	var progress = document.getElementById('progressimg');
		progress.setAttribute("src", "images/alert.gif");
	var imserror = document.getElementById('imserror');
		imserror.style.display = "block";
		imserror.innerHTML = '<span style="color:black;">\''+filename+"'</span> is not a valid IMS Zip package";
	document.getElementById('title').value = '';
	document.getElementById('description').value = '';
}
function clearerror()
{
	var imserror = document.getElementById('imserror');
		imserror.style.display = "none";
}
function loading()
{
	var progress = document.getElementById('progressimg');
		progress.style.visibility = "visible";
		progress.setAttribute("src", "images/loading.gif");
}
function success()
{
	document.getElementById("progressimg").setAttribute("src", "images/tick.gif");
}
function packageinfo(title,description,tempfilename)
{
	document.getElementById('title').disabled = true;
	document.getElementById('description').disabled = true;

	document.getElementById('title').value = title;
	document.getElementById('description').value = rawurldecode(description);
	document.getElementById('tempfilename').value = tempfilename;
}
function upload(upload_fieldid)
{
	clearerror();
	loading();

	var upload_field = document.getElementById(upload_fieldid);
		upload_field.blur();
    var re_text = /\.zip/i;
    var filename = upload_field.value;
    if (filename.search(re_text) == -1)
    {
		error( filename.substring( filename.lastIndexOf("\\")+1 ) );
		return false;
	}

	upload_field.form.submit();
	return true;
}
function submitpackage()
{
	document.getElementById('title').disabled = false;
	document.getElementById('description').disabled = false;
}
