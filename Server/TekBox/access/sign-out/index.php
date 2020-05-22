<?php
	require_once '../../' . 'tekbox_config.php';
	
	
	require_once CAS_CONFIG_FILE_PATH;
	require_once PHPCAS_FILE_PATH;
	require_once TEKBOX_ACCESS_ELEMENTS_FILE_PATH;
	

phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
phpCAS::logout();

?>
<html> 
	<head> 
		<title>TekBox Access Sign Out</title>
		
		<link type = "text/css" rel = "stylesheet" href = "../../style.css">
		
	</head> 
	<body> 
		<div id = "centerFrame">
			
			<div id = "centerContent">
				
			</div>
		</div>
	</body> 
</html>