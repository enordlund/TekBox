<?php
	require_once '../../' . 'tekbox_config.php';
	
	
	require_once CAS_CONFIG_FILE_PATH;
	require_once PHPCAS_FILE_PATH;
/*
require_once '../../cas_config.php';
require_once '../../../../' . $phpcas_path . 'CAS.php';
*/

phpCAS::setDebug();

phpCAS::setVerbose(true);

phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

phpCAS::setNoCasServerValidation();

phpCAS::forceAuthentication();

?>
<html> 
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>TekBox Dashboard - Lockers</title>
		
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
									<a href="./"  class="headlink"id="headlinkcurrent">
									Lockers
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../orders/"  class="headlink" id="headlink2">
									Orders
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../users/"  class="headlink" id="headlink2">
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
						OR `Manager1-UUID` = '$osuUID'
						OR `Manager2-UUID` = '$osuUID'
						OR `Manager3-UUID` = '$osuUID'
						OR `Manager4-UUID` = '$osuUID'
						OR `Manager5-UUID` = '$osuUID'
						OR `Manager6-UUID` = '$osuUID'
						OR `Manager7-UUID` = '$osuUID'
						OR `Manager8-UUID` = '$osuUID'
						OR `Manager9-UUID` = '$osuUID'
						OR `Manager10-UUID` = '$osuUID'
					";
					
					if ($clustersResult = mysqli_query($mysqli, $clustersQuery)) {
						$clustersCount = mysqli_num_rows($clustersResult);
						
						if ($clustersResult > 0) {
							// the user does have permissions to manage/administrate the resulting cluter(s)
							
							// store box UUID values for activity query
							$boxUUIDArray = array();
							$boxUUIDArrayIndex = 0;
							
							// keeping track of box names in parallel for printing to page.
							$boxNameFromUUID = array();
							// keeping track of box clusters as well
							$boxClusterUUIDfromUUID = array();
							
							
							
							
							$boxIsLoadedFromUUID = array();
							
							
							
							$boxOrderIsNullFromUUID = array();
							
							
							
							// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ store cluster names/UIDs for filtering
							$clusterUUIDArray = array();
							$clusterUUIDArrayIndex = 0;
							
							$clusterNameFromUUID = array();
							
							
							echo "Select a locker to manage [sort by admin, then manager permissions for user]";
							
							// look for boxes belonging to the clusters
							while ($clusterRow = mysqli_fetch_row($clustersResult)) {
								// store box UUID values for activity query
								
								$clusterUUID = $clusterRow[1];
								$clusterName = $clusterRow[0];
								
								
								
								echo "<h3>$clusterName</h3>";
								
								echo "<a href = \"./location?locationUID=$clusterUUID\">Edit</a><br>";// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ only if auth is admin for the specific cluster
								
								echo "<div class = \"Cluster\">";
								
								
								$clusterUUIDArray[$clusterUUIDArrayIndex] = $clusterUUID;
								$clusterNameFromUUID["$clusterUUID"] = $clusterName;
								
								$clusterUUIDArrayIndex += 1;
								
								$boxesQuery = "
									SELECT * 
									FROM `TekBox-Boxes` 
									WHERE `Cluster-UUID` = '$clusterUUID'
								";
								
								// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ sort by position in cluster
								
								
								if ($boxesResult = mysqli_query($mysqli, $boxesQuery)) {
									// query was successful
									
									$boxesCount = mysqli_num_rows($boxesResult);
									
									if ($boxesCount > 0) {
										// boxes were found for the cluster UUID
										
										// adding options to page
										
										
										
										// creating 2d array to generate UI of boxes
										$clusterRowsCount = $clusterRow[2];
										$clusterColumnsCount = $clusterRow[3];
										
										
										$clusterArray = array();//this populates the first entry of the parent array
										
										$rowIterator = 0;
										
										while ($rowIterator < $clusterRowsCount) {
											$clusterArray[$rowIterator] = array();
											
											$columnIterator = 0;
											
											while ($columnIterator < $clusterColumnsCount) {
												$clusterArray[$rowIterator][$columnIterator] = -1;
												
												$columnIterator += 1;
											}
											
											$rowIterator += 1;
										}
										
										
										
										
										while ($boxRow = mysqli_fetch_row($boxesResult)) {
											// add the UUID to the array
											
											$boxUUID = $boxRow[1];
											
											$boxUUIDArray[$boxUUIDArrayIndex] = $boxUUID;
											
											$boxName = $boxRow[0];
											
											$boxNameFromUUID["$boxUUID"] = $boxName;
											
											$boxClusterUUID = $boxRow[4];
											
											$boxClusterUUIDfromUUID["$boxUUID"] = $boxClusterUUID;
											
											
											$boxIsLoaded = $boxRow[9];
											
											$boxIsLoadedFromUUID["$boxUUID"] = $boxIsLoaded;
											
											
											$boxOrderUUID = $boxRow[13];
											
											if ($boxOrderUUID == NULL) {
												$boxOrderIsNullFromUUID["$boxUUID"] = true;
											} else {
												$boxOrderIsNullFromUUID["$boxUUID"] = false;
											}
											
											
											
											
											$boxUUIDArrayIndex += 1;
											
											
											
											// populating the UI cluster array
											$boxRowValue = $boxRow[10];
											$boxColumnValue = $boxRow[11];
											
											$clusterArray[$boxRowValue][$boxColumnValue] = $boxUUID;
											
											
											
										}
										
										$clusterRowIterator = 0;
										$clusterColumnIterator = 0;
										
										while ($clusterRowIterator < $clusterRowsCount) {
											echo "<div class = \"BoxRow\">";
											while ($clusterColumnIterator < $clusterColumnsCount) {
												// populate each row with gaps and boxes.
												
												if ($clusterArray[$clusterRowIterator][$clusterColumnIterator] > -1) {
													$boxUUID = $clusterArray[$clusterRowIterator][$clusterColumnIterator];
													
													$boxName = $boxNameFromUUID["$boxUUID"];
													
													$boxStatusString = "<h4 class = \"openBoxText\">Available</h4>";
													
													$boxIsLoaded = $boxIsLoadedFromUUID["$boxUUID"];
													
													$boxOrderIsNull = $boxOrderIsNullFromUUID["$boxUUID"];
													
													if ($boxIsLoaded) {
														if ($boxOrderIsNull) {
															$boxStatusString = "<h4 class = \"loadedBoxText\">Loaded Without Order</h4>";
														} else {
															$boxStatusString = "<h4 class = \"loadedBoxText\">Waiting for Pickup</h4>";
														}
														
													}
													
													// add the box from the uuid in the array
													echo "<a href = \"./locker?locationUID=$clusterUUID&lockerUID=$boxUUID\" class = \"Box\"><div class = \"BoxContent\">$boxName$boxStatusString</div></a>";
												} else {
													// add a gap
													echo "<div class = \"Box\" id = \"boxGap\"></div>";
												}
												
												$clusterColumnIterator += 1;
											}
											
											$clusterColumnIterator = 0;
											
											$clusterRowIterator += 1;
											
											echo "</div>";
										}
										
										
										// end of cluster
										echo "</div>";
									} else {
										echo "No lockers were found." . "<br>";
									}
								} else {
									echo "Query failed for lockers." . "<br>";
								}
							}
							
							
						} else {
							echo "No lockers were found for your account." . "<br>";
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