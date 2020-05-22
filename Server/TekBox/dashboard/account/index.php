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
		<title>TekBox Dashboard - Account</title>
		
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
									<a href="../account/" class = "headlink" id="headAccountLinkCurrent">
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
					
					echo "<h3>Personal Information</h3>";
					
					$userName = $userRow[0];
					echo "Name: $userName" . "<br>";
					
					$userEmail = $userRow[1];
					echo "Email: $userEmail" . "<br>";
					
					
					echo "<h3>Authenticated Devices</h3>";
					
					
					if ($osuEmail = phpCAS::getAttribute("eduPersonPrincipalName")) {
						$invitationsQuery = "
							SELECT *
							FROM `TekBox-Invitations`
							WHERE `Invitee-Email` = '$osuEmail'
						";
						
						if ($invitationsResult = mysqli_query($mysqli, $invitationsQuery)) {
							$invitationsCount = mysqli_num_rows($invitationsResult);
							
							if ($invitationsCount > 0) {
								echo "<h3>Invitations</h3>";
								
								
								while ($invitationRow = mysqli_fetch_row($invitationsResult)) {
									$invitationLocationUID = $invitationRow[1];
									
									// get location name
									$locationQuery = "
										SELECT * 
										FROM `TekBox-Clusters` 
										WHERE `UUID` = '$invitationLocationUID'
										LIMIT 1
									";
									
									if ($locationResult = mysqli_query($mysqli, $locationQuery)) {
										
										
										if ($locationRow = mysqli_fetch_row($locationResult)) {
											$locationName = $locationRow[0];
											$invitationUID = $invitationRow[0];
											
											// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Make some pretty invitations (cards) here
// 											echo $locationName;
											
											echo "<div class = \"AccountCardRow\"><div class = \"AccountCard\"><div class = \"BoxContent\"><h4>$locationName</h4><a href = \"./invitation?uid=" . $invitationUID . "&accept=1\">Accept</a><br><a href = \"./invitation?uid=" . $invitationUID . "&accept=2\">Decline</a></div></div></div>";
											
										} else {
											echo "Location not found." . "<br>";
										}
									} else {
										echo "Location query failed." . "<br>";
									}
									
									//echo $invitationLocationUID;
								}
								
							} else {
								//echo "no invitations";
							}
						} else {
							echo "Invitations query failed." . "<br>";
						}
					} else {
						echo "Failed to get osuprimarymail ONID attribute." . "<br>";
					}
					
					
					
					
					
					
					
					/* 
					
					if (phpCAS::getAttributes()) {
						echo "<h3>ONID Attributes</h3>";
						
						foreach (phpCAS::getAttributes() as $key => $value) {
							if (is_array($value)) {
							echo '<li>', $key, ':<ol>';
							foreach($value as $item) {
							      echo '<li><strong>', $item, '</strong></li>';
							    }
							echo '</ol></li>';
							} else {
							    echo '<li>', $key, ': <strong>', $value, '</strong></li>';
							  }
							}
					}
					
					 */
					
					
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
							
							
							echo "<h3>Locations</h3>";
							
							
							// look for boxes belonging to the clusters
							while ($clusterRow = mysqli_fetch_row($clustersResult)) {
								// store box UUID values for activity query
								
								$clusterUUID = $clusterRow[1];
								$clusterName = $clusterRow[0];
								
								
								echo "$clusterName" . "<br>";
								
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
										echo "No boxes were found." . "<br>";
									}
								} else {
									echo "Query failed for boxes." . "<br>";
								}
							}
							
							
							echo "<h3>Account Activity</h3>";
							
							
							$boxUUIDArrayCount = count($boxUUIDArray);
							
							if ($boxUUIDArrayCount > 0) {
								// boxes were found in the clusters. Construct query for activity
								
								//echo "Found $boxUUIDArrayCount boxes." . "<br>";
								
								$boxUUID = $boxUUIDArray[0];
								
								$requestsQuery = "
									SELECT * 
									FROM `TekBox-Requests` 
									WHERE `DateTime` IS NOT NULL 
									AND (`Box-UUID` = '$boxUUID'
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
								
								
								// sort in reverse-chronological order, finishing the query
								
								// filtering to only requests made with the account
								$requestsQuery .= " )
									AND (`Modifier-UUID` = '$osuUID')
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
							echo "No boxes were found for your account." . "<br>";
						}
					}
					
					
					
				} else {
					echo "Account could not be found." . "<br>";
					
					// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ first, attempt to create an account with the onid attributes.
					
					if ($osuUID = phpCAS::getAttribute("osuuid")) {
						if ($osuEmail = phpCAS::getAttribute("eduPersonPrincipalName")) {
							if ($firstName = phpCAS::getAttribute("firstname")) {
								if ($lastName = phpCAS::getAttribute("lastname")) {
									$fullName = $firstName . " " . $lastName;
									$createAccountQuery = "
										INSERT INTO `" . DB_NAME . "`.`TekBox-Users` (`Full-Name`, `Email`, `OSUUID`, `Last-IP`, `First-Name`, `Last-Name`)
										VALUES ('$fullName', '$osuEmail', '$osuUID', '$ip', '$firstName', '$lastName');
									";
									
									if ($accountResult = mysqli_query($mysqli, $createAccountQuery)) {
										echo "New account created! Refresh to see account details." . "<br>";
										
									} else {
										echo "Failed to create new account." . "<br>";
									}
								} else {
									echo "Couldn't find ONID last name." . "<br>";
								}
							} else {
								echo "Couldn't find ONID first name." . "<br>";
							}
						} else {
							echo "Couldn't find ONID email." . "<br>";
						}
					} else {
						echo "Couldn't find ONID UID." . "<br>";
					}
					
					
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