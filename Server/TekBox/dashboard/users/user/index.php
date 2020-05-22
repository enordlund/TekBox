<?php
	require_once '../../../' . 'tekbox_config.php';
	
	
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
		<title>TekBox Dashboard - User</title>
		
		<link type = "text/css" rel = "stylesheet" href = "../../../style.css">
		
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
									<a href="../../" id="headdashboardlink">
										<span id="headTekBox"><b>TekBox</b></span>
										<span id="orangedashboard">Dashboard</span>
									</a>
								</div>
							</div>
	
							<div id="headlinkcage">
								<div class="headlinkframe">
									<a href="../../lockers"  class="headlink"id="headlink1">
									Lockers
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../orders/"  class="headlink" id="headlink2">
									Orders
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../users/"  class="headlink" id="headlinkCurrent">
									Users
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../activity/"  class="headlink" id="headlink4">
									Activity
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../help/"  class="headlink" id="headlink4">
									Help
									</a>
								</div>
							</div>
							
							<div id = "signoutCage">
								<div id = "signoutFrame">
									<a href="../../sign-out/" class="headlink" id = "headSignoutLink">
										Sign Out
									</a>
								</div>
							</div>
							<div id = "accountCage">
								<div id = "accountLinkFrame">
									<a href="../../account/" class = "headlink" id="headAccountLink">
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
			
			if ($_GET["uid"]) {
				// spend resources looking for order
				$userUID = $_GET["uid"];
				
				// authenticate user
				
				
				// Get user
				$authQuery = "
					SELECT * 
					FROM `TekBox-Users` 
					WHERE `OSUUID` = '$osuUID'
					LIMIT 1
				";
				
				
				if ($authResult = mysqli_query($mysqli, $authQuery)) {
					$authCount = mysqli_num_rows($authResult);
					
					if ($authCount > 0) {
						$authRow = mysqli_fetch_row($authResult);
						
						
						
						// found the user, so look for the user's administrated clusters
						// with the passed uid as another user
						$clustersQuery = "
							SELECT * 
							FROM `TekBox-Clusters` 
							WHERE (`Admin1-UUID` = '$osuUID'
							OR `Admin2-UUID` = '$osuUID'
							OR `Admin3-UUID` = '$osuUID'
							OR `Admin4-UUID` = '$osuUID'
							OR `Admin5-UUID` = '$osuUID')
							AND (`Admin1-UUID` = '$userUID'
							OR `Admin2-UUID` = '$userUID'
							OR `Admin3-UUID` = '$userUID'
							OR `Admin4-UUID` = '$userUID'
							OR `Admin5-UUID` = '$userUID'
							OR `Manager1-UUID` = '$userUID'
							OR `Manager2-UUID` = '$userUID'
							OR `Manager3-UUID` = '$userUID'
							OR `Manager4-UUID` = '$userUID'
							OR `Manager5-UUID` = '$userUID'
							OR `Manager6-UUID` = '$userUID'
							OR `Manager7-UUID` = '$userUID'
							OR `Manager8-UUID` = '$userUID'
							OR `Manager9-UUID` = '$userUID'
							OR `Manager10-UUID` = '$userUID')
						";
						
						if ($clustersResult = mysqli_query($mysqli, $clustersQuery)) {
							$clustersCount = mysqli_num_rows($clustersResult);
							
							if ($clustersCount > 0) {
								// the user does have permissions to manage/administrate the requested user.
								
								
								// query user info
								$userQuery = "
									SELECT * 
									FROM `TekBox-Users` 
									WHERE `OSUUID` = '$userUID'
									LIMIT 1
								";
								
								if ($userResult = mysqli_query($mysqli, $userQuery)) {
									$userCount = mysqli_num_rows($userResult);
									
									if ($userCount > 0) {
										// found the user, so can print info
										
										$userRow = mysqli_fetch_row($userResult);
										
										
										$userName = $userRow[0];
										
										
										echo "<h3>$userName</h3>";
										
										
										
										
										echo "<h4>Common Locations</h4>";
										
										
										// store box UID values for activity query
										$boxUIDArray = array();
										$boxUIDArrayIndex = 0;
										
										// keeping track of box names in parallel for printing to page.
										$boxNamesFromUID = array();
										// keeping track of box clusters as well
										$boxClusterUIDfromUID = array();
										
										
										
										// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ store cluster names/UIDs for filtering
										$clusterUIDArray = array();
										$clusterUIDArrayIndex = 0;
										
										$clusterNameFromUID = array();
										
										
										// look for boxes belonging to the clusters
										while ($clusterRow = mysqli_fetch_row($clustersResult)) {
											// store box UID values for activity query
											
											$clusterUID = $clusterRow[1];
											$clusterName = $clusterRow[0];
											
											
											echo "$clusterName" . "<br>";
											
											$clusterUIDArray[$clusterUIDArrayIndex] = $clusterUID;
											$clusterNameFromUID["$clusterUID"] = $clusterName;
											
											$clusterUIDArrayIndex += 1;
											
											$boxesQuery = "
												SELECT * 
												FROM `TekBox-Boxes` 
												WHERE `Cluster-UUID` = '$clusterUID'
											";
											
											if ($boxesResult = mysqli_query($mysqli, $boxesQuery)) {
												// query was successful
												
												$boxesCount = mysqli_num_rows($boxesResult);
												
												if ($boxesCount > 0) {
													// boxes were found for the cluster UID
													
													while ($boxRow = mysqli_fetch_row($boxesResult)) {
														// add the UID to the array
														
														$boxUID = $boxRow[1];
														
														$boxUIDArray[$boxUIDArrayIndex] = $boxUID;
														
														$boxName = $boxRow[0];
														
														$boxNamesFromUID["$boxUID"] = $boxName;
														
														$boxClusterUID = $boxRow[4];
														
														$boxClusterUIDfromUID["$boxUID"] = $boxClusterUID;
														
														$boxUIDArrayIndex += 1;
													}
												} else {
													echo "(No boxes were found)" . "<br>";
												}
											} else {
												echo "Query failed for boxes." . "<br>";
											}
										}
										
										
										
										
										
										// 
										echo "<h4>Recent Activity</h4>";
										
										$boxUIDArrayCount = count($boxUIDArray);
										
										if ($boxUIDArrayCount > 0) {
											// boxes were found in the clusters. Construct query for activity
											
											//echo "Found $boxUIDArrayCount boxes." . "<br>";
											
											$boxUID = $boxUIDArray[0];
											
											$requestsQuery = "
												SELECT * 
												FROM `TekBox-Requests` 
												WHERE `DateTime` IS NOT NULL 
												AND (`Box-UUID` = '$boxUID'
											";
											
											// finish constructing the query with all boxUUID values
											$boxUIDArrayIndex = 1;
											
											while ($boxUIDArrayIndex < $boxUIDArrayCount) {
												
												//echo "adding to query";
												// add the next uuid to the query
												$boxUID = $boxUIDArray[$boxUIDArrayIndex];
												
												$requestsQuery .= " OR `Box-UUID` = '$boxUID'";
												
												$boxUIDArrayIndex += 1;
											}
											
											
											// sort in reverse-chronological order, finishing the query
											
											// filtering to only requests made with the account
											$requestsQuery .= " )
												AND (`Modifier-UUID` = '$userUID')
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
														
														$boxUID = $requestRow[0];
														
														$boxName = $boxNamesFromUID["$boxUID"];
														
														$clusterUID = $boxClusterUIDfromUID["$boxUID"];
														
														$clusterName = $clusterNameFromUID["$clusterUID"];
														
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
																		WHERE `UUID` = '$modifierUID'
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
													echo "No activity found for this user." . "<br>";
												}
											} else {
												echo "Query failed for activity." . "<br>";
											}
											
											
										} else {
											echo "No boxes were found for your account." . "<br>";
										}
										
										
									} else {
										echo "No user found with UID.";
									}
								}
								
								
								
								
								
								
								
							} else {
								echo "No boxes were found for your account." . "<br>";
							}
						}
						
						
						
					} else {
						echo "Account could not be found." . "<br>";
					}
				}
				
				
				
			} else {
				// present error
				echo "No order number in request.";
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