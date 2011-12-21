<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: present/header.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Present options fotter
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF $sPageCaller VARIABLE NOT FOUND	
if(!isset($sPageCaller)||$sPageCaller!=='present.php'){
	require_once("../../../config.php");
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////
echo '
	</div>
	</div>
	<script type="text/javascript">
	//<![CDATA[	
		//FORCE WINDOW TO KEEP FOCUS
		window.onblur = selfFocus;
	//]]>
	</script>
    </body>
	</html>
';
?>