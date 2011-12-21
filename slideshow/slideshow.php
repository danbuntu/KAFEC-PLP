<?
//PHP SCRIPT: getimages.php
Header("content-type: application/x-javascript");

//This function gets the file names of all images in the current directory
//and ouputs them as a JavaScript array
function returnimages($dirname=".") {
$pattern="(\.jpg$)|(\.png$)|(\.jpeg$)|(\.gif$)"; //valid image extensions
$files = array();
$curimage=0;
if($handle = opendir($dirname)) {
while(false !== ($file = readdir($handle))){
if(eregi($pattern, $file)){ //if this file is a valid image
//Output it as a JavaScript array element
echo 'galleryarray['.$curimage.']="'.$file .'";';
$curimage++;
}
}

closedir($handle);
}
return($files);
}

echo 'var galleryarray=new Array();'; //Define array in JavaScript
returnimages() //Output the array elements containing the image file names
?>
<body>
<div style="width: 170px; height: 160px">
<img id="slideshow" src="pics/bear.gif" />
</div>

<script src="pics/getimages.php"></script>

<script type="text/javascript">

var curimg=0
function rotateimages(){
document.getElementById("slideshow").setAttribute("src", "pics/"+galleryarray[curimg])
curimg=(curimg<galleryarray.length-1)? curimg+1 : 0
}

window.onload=function(){
setInterval("rotateimages()", 2500)
}
</script>

</body>