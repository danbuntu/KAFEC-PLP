<?php
/**
 * NLN/Noodle - XTensis
 */
	require_once('../../../../config.php');

	$xtid			= required_param('xtid', PARAM_NOTAGS);
	$title			= optional_param('title', PARAM_NOTAGS);
	$description	= optional_param('description', PARAM_NOTAGS);    

?><html>
	<head>
		<script type="text/javascript">
		function init()
		{
			window.parent.set_value('NLN#<?php echo $xtid; ?>', '<?php echo $title; ?>', '<?php echo rawurlencode($description); ?>');
		}
		</script>
	</head>
	<body onload="init();">
		<noscript>Sorry, you must have scripting enabled to use this feature</noscript>
	</body>
</html>