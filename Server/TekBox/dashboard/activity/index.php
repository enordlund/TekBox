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
		<title>TekBox Dashboard - Activity</title>
		
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
									<a href="../users/"  class="headlink" id="headlink2">
									Users
									</a>
								</div>
								<div class="headlinkframe">
									<a href="./"  class="headlink" id="headlinkcurrent">
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
			
			
			
			
			echo "[filter]<br>";
			
			
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
							$boxNamesFromUUID = array();
							// keeping track of box clusters as well
							$boxClusterUUIDfromUUID = array();
							
							
							
							// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ store cluster names/UUIDs for filtering
							$clusterUUIDArray = array();
							$clusterUUIDArrayIndex = 0;
							
							$clusterNameFromUUID = array();
							
							// look for boxes belonging to the clusters
							while ($clusterRow = mysqli_fetch_row($clustersResult)) {
								// store box UUID values for activity query
								
								$clusterUUID = $clusterRow[1];
								$clusterName = $clusterRow[0];
								
								$clusterUUIDArray[$clusterUUIDArrayIndex] = $clusterUUID;
								$clusterNameFromUUID["$clusterUUID"] = $clusterName;
								
								$clusterUUIDArrayIndex += 1;
								
								$boxesQuery = "
									SELECT * 
									FROM `TekBox-Boxes` 
									WHERE `Cluster-UUID` = '$clusterUUID'
								";
								
								if ($boxesResult = mysqli_query($mysqli, $boxesQuery)) {
									// query was successful
									
									$boxesCount = mysqli_num_rows($boxesResult);
									
									if ($boxesCount > 0) {
										// boxes were found for the cluster UUID
										
										while ($boxRow = mysqli_fetch_row($boxesResult)) {
											// add the UUID to the array
											
											$boxUUID = $boxRow[1];
											
											$boxUUIDArray[$boxUUIDArrayIndex] = $boxUUID;
											
											$boxName = $boxRow[0];
											
											$boxNamesFromUUID["$boxUUID"] = $boxName;
											
											$boxClusterUUID = $boxRow[4];
											
											$boxClusterUUIDfromUUID["$boxUUID"] = $boxClusterUUID;
											
											$boxUUIDArrayIndex += 1;
										}
									} else {
										echo "No lockers were found for location $clusterNameFromUUID[$clusterUUID]." . "<br>";
									}
								} else {
									echo "Query failed for boxes." . "<br>";
								}
							}
							
							$boxUUIDArrayCount = count($boxUUIDArray);
							
							if ($boxUUIDArrayCount > 0) {
								// boxes were found in the clusters. Construct query for activity
								
								//echo "Found $boxUUIDArrayCount boxes." . "<br>";
								
								$boxUUID = $boxUUIDArray[0];
								
								$requestsQuery = "
									SELECT * 
									FROM `TekBox-Requests` 
									WHERE `DateTime` IS NOT NULL 
									AND ((`Box-UUID` = '$boxUUID'
								";
								
								// finish constructing the query with all boxUUID values
								$boxUUIDArrayIndex = 1;
								
								while ($boxUUIDArrayIndex < $boxUUIDArrayCount) {
									
									//echo "adding to query";
									// add the next uuid to the query
									$boxUUID = $boxUUIDArray[$boxUUIDArrayIndex];
									
									$requestsQuery .= " OR `Box-UUID` = '$boxUUID'";
									
									$boxUUIDArrayIndex += 1;
								}
								
								$locationUID = $clusterUUIDArray[0];
								// sort in reverse-chronological order, finishing the query
								$requestsQuery .= ")
									OR (`Location-UID` = '$locationUID'
								";
								
								// finish constructing the query with all boxUUID values
								$locationUIDArrayIndex = 1;
								$locationUIDArrayCount = count($clusterUUIDArray);
								
								while ($locationUIDArrayIndex < $locationUIDArrayCount) {
									
									//echo "adding to query";
									// add the next uuid to the query
									$locationUID = $clusterUUIDArray[$locationUIDArrayIndex];
									
									$requestsQuery .= " OR `Location-UID` = '$locationUID'";
									
									$locationUIDArrayIndex += 1;
								}
								
								$requestsQuery .= "))
									ORDER BY  `DateTime` DESC
								";
								//echo "Query: $requestsQuery" . "<br>";
								
								// query for activity
								if ($requestsResult = mysqli_query($mysqli, $requestsQuery)) {
									// query was successful
									$requestsCount = mysqli_num_rows($requestsResult);
									
									if ($requestsCount > 0) {
										// requests were found
										
										// ~~~~~~~~~~~~~~~~~~~~~~~ time to print the activity to the page.
										
										$dateProgress = date("Y");
								
										$thisYear = date("Y");
										
										$yesterday = date("F j, Y", time() - 60 * 60 * 24);
										
										
										while ($requestRow = mysqli_fetch_row($requestsResult)) {
											// printing row to page
											
											$rowDateTime = $requestRow[2];
											
											$dt = new DateTime($rowDateTime);
											
											$rowDate = $dt->format('F j, Y');
											
											$rowYear = $dt->format('Y');
											
											$rowTime = $dt->format('g:i a');
											
											
											if ($dateProgress != $rowDate) {
												// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Make day before today "yesterday"
												
												if ($dateProgress != date("Y")) {
													print "<br>";
												}
												
												
												if ($rowDate == $today) {
													print "<h3>Today</h3>";
												} else if ($rowDate == $yesterday) {
													print "<h3>Yesterday</h3>";
												} else if ($rowYear == $thisYear) {
													$currentDate = $dt->format('F j');
													print "<h3>$currentDate</h3>";
												} else {
													print "<h3>$rowDate</h3>";
													
												}
												
												$dateProgress = $rowDate;
											}
											
											$boxUUID = $requestRow[0];
											
											$boxName = $boxNamesFromUUID["$boxUUID"];
											
											$clusterUUID = $boxClusterUUIDfromUUID["$boxUUID"];
											
											if ($requestRow[10] != NULL) {
												$clusterUUID = $requestRow[10];
											}
											
											$clusterName = $clusterNameFromUUID["$clusterUUID"];
											
											print "<div class = \"activityRow\">$rowTime — $boxName ($clusterName)";
											
											// look for errors
											if ($requestRow[7]) {
												
												$rowError = $requestRow[7];
												
												$rowErrorString = "Error: $rowError";
												
												if ($rowError == "AUTH") {
													$rowErrorString = "Authentication Error";
												} else if ($rowError == "CONF") {
													$rowErrorString = "Confirmation Error";
												}
												
												echo "<span class=\"StatusBoxRed\">$rowErrorString</span>";
											} else {
												// print the result of the successful request
												
												$requestType = $requestRow[3];
												
												$serverResponse = $requestRow[5];
												$rowConfirmation = $requestRow[6];
												
												if ($requestType == "PRESS") {
													
													if ($rowConfirmation == "LOCK") {
														print " was locked.";
													} else if ($rowConfirmation == "UNLOCK") {
														print " was unlocked.";
													} else if ($serverResponse != $rowConfirmation) {
														// no/unexpected confirmation is also a confirmation error
														echo "<span class=\"StatusBoxRed\">Confirmation Error</span>";
														
														// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~this wasn't previously caught, so add the error to the server
													} else {
														// other errors
														
													}
													
													
													
													
												} else if ($requestType == "DISARM") {
													if ($serverResponse != $rowConfirmation) {
														// this is basically impossible
														echo "<span class=\"StatusBoxRed\">Confirmation Error</span>";
													} else {
														$minutesString = "$serverResponse";
														
														if ($minutesString != "1") {
															$minutesString .= " minutes";
														} else {
															$minutesString .= " minute";
														}
														
														print " was disarmed for $minutesString";
														// print the name on the account which approved the request
														$modifierUID = $requestRow[9];
														
														
														$modifierQuery = "
															SELECT * 
															FROM `TekBox-Users` 
															WHERE `OSUUID` = '$modifierUID'
															LIMIT 1
														";
														
														if ($modifierResult = mysqli_query($mysqli, $modifierQuery)) {
															$modifierCount = mysqli_num_rows($modifierResult);
															
															if ($modifierCount > 0) {
																// found the modifier
																$modifierRow = mysqli_fetch_row($modifierResult);
																
																$modifierName = $modifierRow[0];
																
																print " by $modifierName.";
															} else {
																echo "<span class=\"StatusBoxRed\">User ID Not Found</span>";
															}
														} else {
															print " by an unknown user... [query failed]";
														}
														
													}
													
													
												} else if ($requestType == "DISARM_PICKUP") {
													if ($serverResponse != $rowConfirmation) {
														// this is basically impossible
														echo "<span class=\"StatusBoxRed\">Confirmation Error</span>";
													} else {
														$minutesString = "$serverResponse";
														
														if ($minutesString != "1") {
															$minutesString .= " minutes";
														} else {
															$minutesString .= " minute";
														}
														
														print " was disarmed for $minutesString";
														// print the name on the account which approved the request
														$modifierUID = $requestRow[9];
														
														
														$modifierQuery = "
															SELECT * 
															FROM `TekBox-Orders` 
															WHERE `Customer-Email` = '$modifierUID'
															LIMIT 1
														";
														
														if ($modifierResult = mysqli_query($mysqli, $modifierQuery)) {
															$modifierCount = mysqli_num_rows($modifierResult);
															
															if ($modifierCount > 0) {
																// found the modifier
																$modifierRow = mysqli_fetch_row($modifierResult);
																
																$modifierName = $modifierRow[2];
																
																$orderNumber = $modifierRow[1];
																
																print " by customer, $modifierName, for Order #$orderNumber.";
															} else {
																echo "<span class=\"StatusBoxRed\">Order History Not Found</span>";
															}
														} else {
															print " by an unknown customer... [query failed]";
														}
														
													}
													
													
												} else if ($requestType == "JOINED") {
													$modifierUID = $requestRow[9];
													
													
													$modifierQuery = "
														SELECT * 
														FROM `TekBox-Users` 
														WHERE `OSUUID` = '$modifierUID'
														LIMIT 1
													";
													
													if ($modifierResult = mysqli_query($mysqli, $modifierQuery)) {
														$modifierCount = mysqli_num_rows($modifierResult);
														
														if ($modifierCount > 0) {
															// found the modifier
															$modifierRow = mysqli_fetch_row($modifierResult);
															
															$modifierName = $modifierRow[0];
															
															print " $modifierName joined as a";
															
															if ($serverResponse == "ADMIN") {
																print "n administrator.";
															} else if ($serverResponse == "MANAGER") {
																print " manager.";
															} else {
																print "n unknown type.";
															}
														} else {
															echo "<span class=\"StatusBoxRed\">User ID Not Found</span>";
														}
													} else {
														print " by an unknown user... [query failed]";
													}
													
												} else if ($requestType == "TEST") {
													// locker connected to wifi
													if ($serverResponse != $rowConfirmation) {
														// this is basically impossible
														echo "<span class=\"StatusBoxRed\">Confirmation Error</span>";
													} else {
														
														print " connected to Wi-Fi.";
														
													}
													
												} else {
													echo "<span class=\"StatusBoxRed\">Unrecognized Request</span>";
												}
												
												
												
												
												
											}
											
											if ($requestRow[8]) {
												// New IP
												echo "<span class=\"StatusBoxGray\">New IP Address</span>";
											}
											
											echo "</div>";
										}
										
										
										
									} else {
										echo "No activity found for your boxes." . "<br>";
									}
								} else {
									echo "Query failed for activity." . "<br>";
								}
								
								
							} else {
								echo "No boxes were found for your account." . "<br>";
							}
						} else {
							echo "No locations were found for your account." . "<br>";
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
		<div id = "footerSeparator">
		</div>
		<div id = "footer">
			<div id = "siteMap">
				Site Map
			</div>
			<div id = "footerInfo">
				Info
			</div>
		</div>
	</body> 
</html>