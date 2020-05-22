<?php
	
	require_once '../../' . 'tekbox_config.php';
	
	
	require_once CAS_CONFIG_FILE_PATH;
	require_once PHPCAS_FILE_PATH;
	require_once TEKBOX_ACCESS_ELEMENTS_FILE_PATH;
	

phpCAS::setDebug();

phpCAS::setVerbose(true);

phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

phpCAS::setNoCasServerValidation();

phpCAS::forceAuthentication();

?>
<html> 
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>TekBox Access</title>
		
		<link type = "text/css" rel = "stylesheet" href = "../../style.css">
		
	</head> 
	<body> 
		<div id = "centerFrame">
			<div id="head">
				<div id="headcontcage">
					<div id="headconttable">
						<div id="headcontframe">
						
							<div id="dashboardcage">
								<div id="dashboardframe">
									<a href="../" id="headdashboardlink">
										<span id="headTekBox"><b>TekBox</b></span>
										<span id="orangeDashboard">Access</span>
									</a>
								</div>
							</div>
	
							<div id="headlinkcage">
								<div class="headlinkframe">
									<a href="../orders"  class="headlink"id="headlinkcurrent">
									Orders
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../help/"  class="headlink" id="headlink2">
									Help
									</a>
								</div>
							</div>
							
							<div id = "signoutCage">
								<div id = "signoutFrame">
									<a href="../sign-out/" class="headlink" id = "headSignoutLinkClean">
										Sign Out
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				
	
			</div>
			<div id = "centerContent">
		<?php
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Authentication results
		$accessElements = new TekBoxAccessElements();
		
		
		if ($osuEmail = phpCAS::getAttribute("eduPersonPrincipalName")) {
			// found email, so look for orders with this email.
			
			$accessElements->ordersListSorted($osuEmail);
		}
		
				
		?> 
			</div>
		</div>
	</body> 
</html>