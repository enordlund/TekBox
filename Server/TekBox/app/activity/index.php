
<?php
	require_once '../../' . 'tekbox_config.php';
	
	require_once APP_NETWORKING_FILE_PATH;
	
	$appData = new AppData();
	
	echo $appData->activityEndpoint();
?> 
