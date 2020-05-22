<?php
	require_once '../../' . 'tekbox_config.php';
	
	
	require_once CAS_CONFIG_FILE_PATH;
	require_once PHPCAS_FILE_PATH;

phpCAS::setDebug();

phpCAS::setVerbose(true);

phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

phpCAS::setNoCasServerValidation();

phpCAS::forceAuthentication();

?>
<html> 
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>TekBox Dashboard - Users</title>
		
		<link type = "text/css" rel = "stylesheet" href = "../../style.css">
		
		<script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		
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
										<span id="orangedashboard">Dashboard</span>
									</a>
								</div>
							</div>
	
							<div id="headlinkcage">
								<div class="headlinkframe">
									<a href="../lockers/"  class="headlink"id="headlink1">
									Lockers
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../orders/"  class="headlink" id="headlink2">
									Orders
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../users/"  class="headlink" id="headlinkCurrent">
									Users
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../activity/"  class="headlink" id="headlink3">
									Activity
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../help/"  class="headlink" id="headlink4">
									Help
									</a>
								</div>
							</div>
							
							<div id = "signoutCage">
								<div id = "signoutFrame">
									<a href="../sign-out/" class="headlink" id = "headSignoutLink">
										Sign Out
									</a>
								</div>
							</div>
							<div id = "accountCage">
								<div id = "accountLinkFrame">
									<a href="../account/" class = "headlink" id="headAccountLink">
										Account
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
		$osuUID = phpCAS::getAttribute("osuuid");//"87654321";
		
		
		// connecting to database
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if ($mysqli->connect_errno) {
		    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		} else {
			
			//echo 'Successfully connected to database!' . "<br>";
			
			$ip = $_SERVER['REMOTE_ADDR'];
			
			$dateTime = date("Y-m-d H:i:s");
			
			$today = date("F j, Y");
			
			
			//print "<h3>Today</h3>";
			
			//$hash = password_hash("thisISaPassw0rd", PASSWORD_DEFAULT);
			
			//print "Hash: $hash" . "<br>";
			
			
			
			
			
			
			
			// Get user
			$userQuery = "
				SELECT * 
				FROM `TekBox-Users` 
				WHERE `OSUUID` = '$osuUID'
				LIMIT 1
			";
			
			
			if ($userResult = mysqli_query($mysqli, $userQuery)) {
				$userCount = mysqli_num_rows($userResult);
				
				if ($userCount > 0) {
					$userRow = mysqli_fetch_row($userResult);
					
					//$userName = $userRow[0];
					//echo "<h3>Hello, $userName!</h3>";
					
					
					// found the user, so look for the user's clusters
					$clustersQuery = "
						SELECT * 
						FROM `TekBox-Clusters` 
						WHERE `Admin1-UUID` = '$osuUID'
						OR `Admin2-UUID` = '$osuUID'
						OR `Admin3-UUID` = '$osuUID'
						OR `Admin4-UUID` = '$osuUID'
						OR `Admin5-UUID` = '$osuUID'
					";
					
					if ($clustersResult = mysqli_query($mysqli, $clustersQuery)) {
						$clustersCount = mysqli_num_rows($clustersResult);
						
						if ($clustersCount > 0) {
							// the user does have permissions to administrate the resulting cluster(s)
							
							
							
							// list managers in alphabetical order
							
							
							
							
							
							/*
							
							
							// store box UUID values for activity query
							$boxUUIDArray = array();
							$boxUUIDArrayIndex = 0;
							
							// keeping track of box names in parallel for printing to page.
							$boxNameFromUUID = array();
							// keeping track of box clusters as well
							$boxClusterUUIDfromUUID = array();
							
							
							
							
							$boxIsLoadedFromUUID = array();
							
							
							
							$boxOrderIsNullFromUUID = array();
							
							
							
							// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ store cluster names/UUIDs for filtering
							$clusterUUIDArray = array();
							$clusterUUIDArrayIndex = 0;
							
							$clusterNameFromUUID = array();
							*/
							
							echo "Locations with Administrator privileges are listed below." . "<br>";
							
							$nameFromUID = array();
							//$boxNameFromUUID["$boxUUID"] = $boxName;
							
							
							// look for boxes belonging to the clusters
							while ($clusterRow = mysqli_fetch_row($clustersResult)) {
								// store box UUID values for activity query
								
								$locationUID = $clusterRow[1];
								$clusterName = $clusterRow[0];
								
								$clusterAdministratorsArray = array();
								$clusterManagersArray = array();
								
								
								
								// populate administrators array
								$firstAdminColumn = 8;
								$maxNumberOfAdministrators = 5;
								for ($adminColumn = $firstAdminColumn; $adminColumn < ($firstAdminColumn + $maxNumberOfAdministrators); $adminColumn++) {
									$adminUID = $clusterRow[$adminColumn];
									
									if (strlen($adminUID) > 0) {
										// admin exists
										
										
										
										// get name of user for later
										$nameQuery = "
											SELECT * 
											FROM `TekBox-Users` 
											WHERE `OSUUID` = '$adminUID'
										";
										
										if ($nameResult = mysqli_query($mysqli, $nameQuery)) {
											$nameCount = mysqli_num_rows($nameResult);
											
											if ($nameCount > 0) {
												// successful query
												array_push($clusterAdministratorsArray, $adminUID);
												
												
												$nameRow = mysqli_fetch_row($nameResult);
												
												
												$nameFromUID["$adminUID"] = $nameRow[0];
												
											} else {
												echo "Name for UID $adminUID not found...";
											}
										} else {
											echo "Query for name for UID $adminUID failed...";
										}
										
										
									}
								}
								
								
								// populate managers array
								$firstManagerColumn = $firstAdminColumn + $maxNumberOfAdministrators;
								$maxNumberOfManagers = 10;
								for ($managerColumn = $firstManagerColumn; $managerColumn < ($firstManagerColumn + $maxNumberOfManagers); $managerColumn++) {
									$managerUID = $clusterRow[$managerColumn];
									
									if (strlen($managerUID) > 0) {
										// manager exists
										
										// get name of user for later
										$nameQuery = "
											SELECT * 
											FROM `TekBox-Users` 
											WHERE `OSUUID` = '$managerUID'
										";
										
										if ($nameResult = mysqli_query($mysqli, $nameQuery)) {
											$nameCount = mysqli_num_rows($nameResult);
											
											if ($nameCount > 0) {
												// successful query
												array_push($clusterManagersArray, $managerUID);
												
												
												$nameRow = mysqli_fetch_row($nameResult);
												
												
												$nameFromUID["$managerUID"] = $nameRow[0];
												
											} else {
												echo "Name for UID $managerUID not found...";
											}
										} else {
											echo "Query for name for UID $managerUID failed...";
										}
									}
								}
								
								echo "<h3>$clusterName</h3>";
								echo "<button class = \"orangeButton\" onclick=\"window.location.href = '../users/invite?locationUID=$locationUID';\">Invite New User</button>";
								echo "<div class = \"Cluster\">";
								
								// list administrators
								echo "<h4>Administrators</h4>";
								
								//echo "<button class = \"orangeButton\" onclick=\"window.location.href = '../disarm?clusterUUID=$clusterUUID&boxUUID=$boxUUID';\">+</button>";
								
								
								$adminCount = count($clusterAdministratorsArray);
								if ($adminCount > 0) {
									for ($adminRow = 0; $adminRow < $adminCount; $adminRow++) {
										
										$adminUID = $clusterAdministratorsArray[$adminRow];
										
										$adminName = $nameFromUID[$adminUID];
										
										
										
										
										if ($adminUID == $osuUID) {
											print "<div class = \"activityRow\"><a href = \"../account/\">$adminName</a>";
											
											echo " (your account)";
										} else {
											print "<div class = \"activityRow\"><a href = \"./user?uid=$adminUID\">$adminName</a>";
										}
										
										echo "</div><br>";
									}
								} else {
									echo "No administrators..." . "<br>";
								}
								
								
								// list managers
								echo "<h4>Managers</h4>";
								
								$managerCount = count($clusterManagersArray);
								if ($managerCount > 0) {
									for ($managerRow = 0; $managerRow < $managerCount; $managerRow++) {
										
										$managerUID = $clusterManagersArray[$managerRow];
										
										$managerName = $nameFromUID[$managerUID];
										
										print "<div class = \"activityRow\"><a href = \"./user?uid=$managerUID\">$managerName</a>";
										
										if ($managerUID == $osuUID) {
											echo " (you)";
										}
										
										echo "</div><br>";
									}
								} else {
									echo "No managers..." . "<br>";
								}
								
								
								// end of cluster
								echo "</div>";
								
								
							}
							
							
						} else {
							echo "No administered locations were found for your account." . "<br>";
						}
					}
					
					
					
				} else {
					echo "Account could not be found." . "<br>";
				}
			}
			
			
			
			
			
		}
		
		
		
		$mysqli->close();
		
		
		?> 
			</div>
		</div>
	</body> 
</html>