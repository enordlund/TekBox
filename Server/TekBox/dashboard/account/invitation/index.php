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
		<title>TekBox Dashboard - Invitation</title>
		
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
									<a href="../../lockers/"  class="headlink"id="headlink1">
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
									<a href="../../account/" class = "headlink" id="headAccountLinkCurrent">
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
					// user found, so process invitation
					
					$lastUserIP = $userRow[3];
					
					
					// compare IP with last IP
					$ipIsNew = false;
					
					
					if ($ip != $lastUserIP) {
						$ipIsNew = true;
						
						// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ update last IP for box
						
						$updateIPQuery = "
							UPDATE `TekBox-Users`
							SET `Last-IP` = '$ip' 
							WHERE `OSUUID` = '$osuUID'
							LIMIT 1
				        ";
				        
				        if ($updateUnlockResult = mysqli_query($mysqli, $updateIPQuery)) {
					        // query was successful. Check for update
					        //echo "DONE\r\n";
				        } else {
					        echo "ERROR\r\n";
				        }
					}
					
					if ($osuEmail = phpCAS::getAttribute("eduPersonPrincipalName")) {
						if (($invitationUID = $_GET['uid']) && ($accept = $_GET['accept'])) {
							// look for invitation.
							
							$invitationQuery = "
								SELECT *
								FROM `TekBox-Invitations`
								WHERE `Invitee-Email` = '$osuEmail'
								AND `Invitation-UID` = '$invitationUID'
								LIMIT 1
							";
							
							if ($invitationQueryResult = mysqli_query($mysqli, $invitationQuery)) {
								if ($invitationRow = mysqli_fetch_row($invitationQueryResult)) {
									// invitation found
									if ($_GET['accept'] == 1) {

										$locationUID = $invitationRow[1];
										
										// look for location
										
										$locationQuery = "
											SELECT *
											FROM `TekBox-Clusters`
											WHERE `UUID` = '$locationUID'
											LIMIT 1
										";
										
										if ($locationResult = mysqli_query($mysqli, $locationQuery)) {
											if ($locationRow = mysqli_fetch_row($locationResult)) {
												// location found
												// check to make sure there is a spot for the user
												$userShouldBeAdmin = $invitationRow[5];
												
												$locationUID = $locationRow[1];
	// 											echo $locationUID;
												
												if ($userShouldBeAdmin == 0) {
													// not admin
													// check manager slots
													
													$managerIndex = 13;
													$managerAdded = false;
													
													$locationName = $locationRow[0];
													while ($managerIndex < 23) {
														if ($locationRow[$managerIndex] == NULL) {
															// add the user to the open spot
															$managerNumber = $managerIndex - 12;
															$managerColumn = "Manager" . $managerNumber . "-UUID";
															
	// 														echo $managerColumn;
															// update the row
															$addManagerQuery = "
																UPDATE `TekBox-Clusters`
																SET `$managerColumn` = '$osuUID' 
																WHERE `UUID` = '$locationUID'
																LIMIT 1
															";
															
															if ($addManagerResult = mysqli_query($mysqli, $addManagerQuery)) {
																if (mysqli_affected_rows($mysqli) > 0) {
																	// admin updated successfully
																	
																	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ delete the invitation
																	$deleteQuery = "
																		DELETE
																		FROM `TekBox-Invitations`
																		WHERE `Invitee-Email` = '$osuEmail'
																		AND `Invitation-UID` = '$invitationUID'
																	";
																	
																	
																	
																	$managerAdded = true;
																	echo "You have joined the location, \"$locationName\" as a manager!" . "<br>";
																	
																	if ($deleteResult = mysqli_query($mysqli, $deleteQuery)) {
																		if (mysqli_affected_rows($mysqli) < 1) {
																			echo "Failed to delete invitation." . "<br>";
																		}
																	} else {
																		echo "Query failed to delete invitation." . "<br>";
																	}
																	
																	echo "<button class = \"orangeButton\" onclick=\"window.location.href = '../../lockers';\">View Lockers</button>";
																	
																	// log that the user joined
																	$requestUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
																	$requestType = "JOINED";
																	
																	$activityQuery = "
																		INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Location-UID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Response`, `Confirmation`, `New-IP`, `Modifier-UUID`)
																		VALUES ('$locationUID', '$requestUID', '$dateTime', '$requestType', '$ip', 'MANAGER', 'MANAGER', '$ipIsNew', '$osuUID');
																	";
																	
																	if ($activityQuery = mysqli_query($mysqli, $activityQuery)) {
																		if (mysqli_affected_rows($mysqli) < 1) {
																			echo "Failed to update activity." . "<br>";
																		}
																	} else {
																		echo "Query failed to update activity." . "<br>";
																	}
																	
																	break;
																} else {
																	echo "Failed to add user." . "<br>";
																}
															} else {
																echo "Query failed to add user." . "<br>";
															}
														}
														$managerIndex += 1;
													}
													
													if ($managerAdded == false) {
														echo "The location \"$locationName\" cannot have any more managers. Please contact an existing administrator for help with joining the location.";
													}
												} else {
													// is admin
													// check admin slots
													
													$adminIndex = 8;
													$adminAdded = false;
													
													$locationName = $locationRow[0];
													while ($adminIndex < 13) {
														if ($locationRow[$adminIndex] == NULL) {
															// add the user to the open spot
															$adminNumber = $adminIndex - 7;
															$adminColumn = "Admin" . $adminNumber . "-UUID";
															
															
															// update the row
															$addAdminQuery = "
																UPDATE `TekBox-Clusters`
																SET `$adminColumn` = '$osuUID' 
																WHERE `UUID` = '$locationUID'
																LIMIT 1
															";
															
															if ($addAdminResult = mysqli_query($mysqli, $addAdminQuery)) {
																if (mysqli_affected_rows($mysqli) > 0) {
																	// admin updated successfully
																	
																	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ delete the invitation
																	
																	
																	$adminAdded = true;
																	echo "You have joined the location, \"$locationName\" as an administrator!" . "<br>";
																	
																	// delete the invitation
																	
																	$deleteQuery = "
																		DELETE
																		FROM `TekBox-Invitations`
																		WHERE `Invitee-Email` = '$osuEmail'
																		AND `Invitation-UID` = '$invitationUID'
																	";
																	
																	
																	if ($deleteResult = mysqli_query($mysqli, $deleteQuery)) {
																		if (mysqli_affected_rows($mysqli) < 1) {
																			echo "Failed to delete invitation." . "<br>";
																		}
																	} else {
																		echo "Query failed to delete invitation." . "<br>";
																	}
																	
																	// log that the user joined
																	$requestUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
																	$requestType = "JOINED";
																	
																	$activityQuery = "
																		INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Location-UID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Response`, `Confirmation`, `New-IP`, `Modifier-UUID`)
																		VALUES ('$locationUID', '$requestUID', '$dateTime', '$requestType', '$ip', 'ADMIN', 'ADMIN', '$ipIsNew', '$osuUID');
																	";
																	
																	if ($activityQuery = mysqli_query($mysqli, $activityQuery)) {
																		if (mysqli_affected_rows($mysqli) < 1) {
																			echo "Failed to update activity." . "<br>";
																		}
																	} else {
																		echo "Query failed to update activity." . "<br>";
																	}
																	
																	echo "<button class = \"orangeButton\" onclick=\"window.location.href = '../../lockers';\">View Lockers</button>";
																	
																	break;
																} else {
																	echo "Failed to add user." . "<br>";
																}
															} else {
																echo "Query failed to add user." . "<br>";
															}
														}
														$adminIndex += 1;
													}
													
													
													
													if ($adminAdded == false) {
														echo "The location \"$locationName\" cannot have any more administrators. Please contact an existing administrator for help with joining the location.";
													}
												}
											} else {
												"Location not found." . "<br>";
											}
										} else {
											echo "Location query failed." . "<br>";
										}
									} else {
										// user declined invitation
										// delete the invitation
										
										
										$deleteQuery = "
											DELETE
											FROM `TekBox-Invitations`
											WHERE `Invitee-Email` = '$osuEmail'
											AND `Invitation-UID` = '$invitationUID'
										";
										
										
										if ($deleteResult = mysqli_query($mysqli, $deleteQuery)) {
											if (mysqli_affected_rows($mysqli) > 0) {
												echo "Invitation deleted." . "<br>";
											}
										} else {
											echo "Query failed to delete invitation." . "<br>";
										}
										
										echo "<button class = \"orangeButton\" onclick=\"window.location.href = '../';\">View Account</button>";
										
									}
									
								}
							}
						} else {
							echo "Invalid data.";
						}
					} else {
						echo "ONID email not found.";
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