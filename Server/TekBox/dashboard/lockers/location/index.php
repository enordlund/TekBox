<?php
	require_once '../../../' . 'tekbox_config.php';
	
	
	require_once CAS_CONFIG_FILE_PATH;
	require_once PHPCAS_FILE_PATH;
/*
require_once '../../../cas_config.php';
require_once '../../../../../' . $phpcas_path . 'CAS.php';
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
		<title>TekBox Dashboard - Locker Loader</title>
		
		<link type = "text/css" rel = "stylesheet" href = "../../../style.css">
		
		<script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		
	</head> 
	<body> 
		<div id = "centerFrame">
			<div id="head">
				<div id="headcontcage">
					<div id="headconttable">
						<div id="headcontframe">
							<!--
							<a href="../" class="cleanlink" id="homelink">
								
								<div id="homelinkcage">
	
									<div id="profpicframe">
	
										<img src="../images/scenicheadshot.jpg" id="profpic">
	
									</div>
	
									<div id="nameframe">
	
										<h3 id="name">TekBox </h3> <span id="bluecontact">Dashboard</span>
	
									</div>
	
								</div>
	
							</a>
							-->
							
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
									<a href="../"  class="headlink"id="headlinkcurrent">
									Lockers
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../orders/"  class="headlink" id="headlink2">
									Orders
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../users/"  class="headlink" id="headlink2">
									Users
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../activity/"  class="headlink" id="headlink3">
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
	
				 <!-- <div id="headslate">
				</div> -->
	
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
					
					
					
					if ($_GET["locationUID"]) {
						$clusterUUID = $_GET["locationUID"];
						
						// found the user, so look for the user's clusters (only admin for editing a location)
						$clustersQuery = "
							SELECT * 
							FROM `TekBox-Clusters` 
							WHERE (`Admin1-UUID` = '$osuUID'
							OR `Admin2-UUID` = '$osuUID'
							OR `Admin3-UUID` = '$osuUID'
							OR `Admin4-UUID` = '$osuUID'
							OR `Admin5-UUID` = '$osuUID')
							AND `UUID` = '$clusterUUID'
							LIMIT 1
						";
						
						if ($clustersResult = mysqli_query($mysqli, $clustersQuery)) {
							$clustersCount = mysqli_num_rows($clustersResult);
							
							if ($clustersCount > 0) {
								// the user does have permissions to manage/administrate the resulting cluster
								
								
								$boxUUID = $_GET["lockerUID"];
								
								
								
								$clusterRow = mysqli_fetch_row($clustersResult);
								
								// store box UUID values for activity query
								
								$clusterUUID = $clusterRow[1];
								$clusterName = $clusterRow[0];
								
								$locationLatitude = $clusterRow[6];
								$locationLongitude = $clusterRow[7];
								
								
								echo "<h2>$clusterName</h2>";
								
								
								echo "[change name]<br>";
								
								echo "Latitude: $locationLatitude<br>";
								
								echo "Longitude: $locationLongitude<br>";
								
								echo "[change coordinates]<br>";
								
								
								echo "<h3>Administrators</h3>";
								
								
								
								
								
								echo "<h3>Managers</h3>";
								
								
								
								
								
								
								echo "<h3>Lockers</h3>";
								
								
								
								
								echo "[delete location]<br>";// WARNING: This will remove all boxes from the location, and erase all data associated with the location.
								
								
								
								
								
								$boxQuery = "
									SELECT * 
									FROM `TekBox-Boxes` 
									WHERE `Cluster-UUID` = '$clusterUUID'
									AND `UUID` = '$boxUUID'
									LIMIT 1
								";
								
								// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ sort by position in cluster
								
								if ($boxResult = mysqli_query($mysqli, $boxQuery)) {
									// query was successful
									
									$boxCount = mysqli_num_rows($boxResult);
									
									if ($boxCount > 0) {
										// boxes were found for the cluster UUID
										
										while ($boxRow = mysqli_fetch_row($boxResult)) {
											// add the UUID to the array
											
											$boxUUID = $boxRow[1];
											
											$boxName = $boxRow[0];
											
											$boxIsLoaded = $boxRow[9];
											
											$boxStatusString = "<span class = \"openBoxText\">Available</span>";
											
											$boxOrderIsNull = true;
											
											$boxOrderUUID = $boxRow[13];
											
											if ($boxOrderUUID == NULL) {
												$boxOrderIsNull = true;
											} else {
												$boxOrderIsNull = false;
											}
											
											if ($boxIsLoaded) {
												if ($boxOrderIsNull) {
													$boxStatusString = "<span class = \"loadedBoxText\">Loaded Without Order</span>";
												} else {
													$boxStatusString = "<span class = \"loadedBoxText\">Waiting for Pickup</span>";
												}
												
											}
											
											
											// generate page for the box
											
											echo "<h3>$boxName — $boxStatusString</h3>";
											
											echo "<div id = \"boxPageContent\">";
											
											//echo "<h3>Status</h3>";
											
											
											if (!$boxIsLoaded) {
												
												echo "<button class = \"orangeButton\" onclick=\"window.location.href = '../prep?clusterUUID=$clusterUUID&boxUUID=$boxUUID';\">Prepare for Pickup</button>";
												
												//echo "<a href = \"../prep?clusterUUID=$clusterUUID&boxUUID=$boxUUID\">Prepare for Pickup</a>";
											} else {
												if ($boxIsLoaded) {
													echo "The box is already loaded. You may complete an order with its current contents, or disarm the box for a different order." . "<br>";
													echo "<button class = \"orangeButton\" onclick=\"window.location.href = '../prep?clusterUUID=$clusterUUID&boxUUID=$boxUUID';\">Prepare Contents for Pickup</button>" . "<br>";
												}
												echo "<button class = \"orangeButton\" onclick=\"window.location.href = '../disarm?clusterUUID=$clusterUUID&boxUUID=$boxUUID';\">Disarm</button>";
											}
											
											
											echo "<h3>Activity</h3>";
											
											$requestsQuery = "
												SELECT * 
												FROM `TekBox-Requests` 
												WHERE `DateTime` IS NOT NULL 
												AND `Box-UUID` = '$boxUUID'
												ORDER BY  `DateTime` DESC
											";
											
											print "<div id = \"boxActivityContent\">";
											
											// query for activity
											if ($requestsResult = mysqli_query($mysqli, $requestsQuery)) {
												// query was successful
												$requestsCount = mysqli_num_rows($requestsResult);
												
												if ($requestsCount > 0) {
													// requests were found
													
													// ~~~~~~~~~~~~~~~~~~~~~~~ time to print the activity to the page.
													
													$dateProgress = date("Y");
											
													$thisYear = date("Y");
													
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
														
														
														print "<div class = \"activityRow\">$rowTime —";
														
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
																	print " Locked";
																} else if ($rowConfirmation == "UNLOCK") {
																	print " Unlocked";
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
																	
																	print " Disarmed for $minutesString";
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
													echo "No activity found for $boxName." . "<br>";
												}
											} else {
												echo "Query failed for activity." . "<br>";
											}
											
											print "</div>";
											
											echo "</div>";
										}
									} else {
										echo "Box could not be found." . "<br>";
									}
								} else {
									echo "Query failed for boxes." . "<br>";
								}
								
								
								
								
								
								$boxUUIDArrayCount = count($boxUUIDArray);
								
								if ($boxUUIDArrayCount > 0) {
									// boxes were found in the clusters. Construct query for activity
									
									//echo "Found $boxUUIDArrayCount boxes." . "<br>";
									
									
									
									
									
									$testName = $_GET["name"];
									
									echo "$testName<br>";
									
									
									$boxUUID = $boxUUIDArray[0];
									
									echo "<h4>Available Boxes: </h4>";
									
									
									
									
									echo "<select>
											<optgroup label = \"Cluster 1\">
										  <option value=\"volvo\">Volvo</option>
										  <option value=\"saab\">Saab</option>
										  	</optgroup>
										  	<optgroup label = \"Cluster 2\">
										  <option value=\"mercedes\">Mercedes</option>
										  <option value=\"audi\">Audi</option>
										  	</optgroup>
										</select>" . "<br>";
									
									
									
									echo "<input name = \"orderNumberField\" class = \"TextInput\" placeholder = \"Order Number\">" . "<br>";
									
									echo "<input name = \"customerEmailField\" class = \"TextInput\" placeholder = \"Customer ONID Email\">" . "<br>";
									
									echo "<button>Unlock and Load</button>";
									
									
									
									
									
									
									
									// finish constructing the query with all boxUUID values
									$boxUUIDArrayIndex = 1;
									
									while ($boxUUIDArrayIndex < $boxUUIDArrayCount) {
										
										//echo "adding to query";
										// add the next uuid to the query
										$boxUUID = $boxUUIDArray[$boxUUIDArrayIndex];
										
										$requestsQuery .= " OR `Box-UUID` = '$boxUUID'";
										
										$boxUUIDArrayIndex += 1;
									}
									
									
									// sort in reverse-chronological order, finishing the query
									$requestsQuery .= ")
										
									";
									
									//echo "Query: $requestsQuery" . "<br>";
									
									
									
									
								} else {
									"No boxes were found for your account." . "<br>";
								}
							} else {
								echo "You do not have permission to edit this location. Please contact an administrator if you believe this message was received in error." . "<br>";
							}
						}
					} else {
						echo "Incomplete URL";
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