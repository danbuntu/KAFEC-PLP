<?php
/**
 * NLN/Noodle - XTensis
 */
    require_once('../../../../config.php');
	require_once('../nln/nln_config.php');

	$q = required_param('q', PARAM_NOTAGS);
	
?><html>
<head>
	<title>Launching NLN Materials browser</title>
	<style type="text/css">
		body{
			background-color:#D3D3D3;
			font: "Verdana" 12px;
			color:black;
			font-weight:bold;
			padding:0;margin:0
		}
		h1{
			font-size: 26px;
			font-family: "Trebuchet MS", Bliss, Arial;
			padding: 10px;
			text-transform: lowercase;
			margin: 0px; 
			font-weight:bold;
		}
		.blk{
			background-color:black;
			margin:0;
			padding:0;
			width:100%;
		}
		.n1 {
			color:#F5851F; 
			font-style:italic;
		}
		.n2 {
			color:#636466;
		}
	</style>
	<script type="text/javascript">
		function go()
		{
			document.getElementById('url').value=document.location.href;
			//document.getElementById('currId').value=window.opener.document.getElementById('id_reference').value;
			document.getElementById('noodStart').submit();
		}
	</script>
</head>
<body onload="go();">
	<div class="blk" id="blk">
		<h1><span class="n1">NLN</span><span class="n2">Materials</span></h1>
	</div>
	<p style="padding:20px; font-family: Verdana, Arial;">
		<img align="absmiddle" src="../nln/busy.gif" /> Connecting to the NLN Materials browser&hellip;
	</p>
	<form id="noodStart" action="http://noodle.nln.ac.uk/noodle.asp?act=Start" method="post">
		<input type="hidden" name="orgId" value="<?php echo $CFG->orgID; ?>" />
		<input type="hidden" name="orgPass" value="<?php echo $CFG->orgPass; ?>" />
		<input type="hidden" name="ftReq" value="<?php echo $q; ?>" />
		<input type="hidden" name="currId" id="currId" value="" />
		<input type="hidden" id="url" name="url" value="" />
		<input type="hidden" name="source" value="moodle" />
	</form>
</body>
</html>
