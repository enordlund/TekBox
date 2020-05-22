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
		<title>TekBox Dashboard - Disarm Locker</title>
		
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
			
			$ipAddress = $_SERVER['REMOTE_ADDR'];
			
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
					
					
					$clusterUUID = $_GET["clusterUUID"];
					
					// found the user, so look for the user's clusters
					$clustersQuery = "
						SELECT * 
						FROM `TekBox-Clusters` 
						WHERE (`Admin1-UUID` = '$osuUID'
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
						OR `Manager10-UUID` = '$osuUID')
						AND `UUID` = '$clusterUUID'
						LIMIT 1
					";
					
					if ($clustersResult = mysqli_query($mysqli, $clustersQuery)) {
						$clustersCount = mysqli_num_rows($clustersResult);
						
						if ($clustersResult > 0) {
							// the user does have permissions to manage/administrate the resulting cluster
							
							
							$boxUUID = $_GET["boxUUID"];
							
							// look for boxes belonging to the clusters
							while ($clusterRow = mysqli_fetch_row($clustersResult)) {
								// store box UUID values for activity query
								
								$clusterUUID = $clusterRow[1];
								$clusterName = $clusterRow[0];
								
								
								
						        
						        
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
											
											
											$boxName = $boxRow[0];
											
											
											// the form post request wasn't completely filled out
											// check to make sure the box Should-Unlock
											
											
											
											
											
											
											
											// errors for prep issues
											// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ add one for if the box isn't closed.
											if ($boxShouldUnlock) {
												echo "The box is still disarmed. Please check that the order is in the box, and confirm that the box will remain locked by pressing the button." . "<br>";
											}
											
											
											
											
											
											
											// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ insert disarm action for activity log
											
											$lastUserIP = $userRow[3];
											
											
											// compare IP with last IP
											$ipIsNew = false;
											
											
											if ($ipAddress != $lastUserIP) {
												$ipIsNew = true;
												
												// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ update last IP for box
												
												$updateIPQuery = "
													UPDATE `TekBox-Users`
													SET `Last-IP` = '$ipAddress' 
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
											
											// generate requestUUID
											$requestUUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
											
											$requestType = "DISARM";
											
											$unlockForMinutes = $clusterRow[4];
											
											// log the request
											$insertDisarmActionQuery = "
												INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Box-UUID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Response`, `Confirmation`, `New-IP`, `Modifier-UUID`)
												VALUES ('$boxUUID', '$requestUUID', '$dateTime', '$requestType', '$ipAddress', '$unlockForMinutes', '$unlockForMinutes', '$ipIsNew', '$osuUID');
											";
											
											if($insertResult = mysqli_query($mysqli, $insertDisarmActionQuery)) {
												// successfully logged request
												
												
												// disarm the box
												
												
												$unlockUntil = date("Y-m-d H:i:s", time() + $unlockForMinutes * 60);
												
												
												$updateUnlockQuery = "
													UPDATE `TekBox-Boxes`
													SET `Unlock-Until` = '$unlockUntil',
													`Should-Prep` = 0
													WHERE `UUID` = '$boxUUID'
													AND `Cluster-UUID` = '$clusterUUID'
													LIMIT 1
										        ";
										        
										        if ($updateUnlockResult = mysqli_query($mysqli, $updateUnlockQuery)) {
											        // verify that the box was disarmed
											        
											        
											        $disarmVerificationQuery = "
											        	SELECT * 
														FROM `TekBox-Boxes` 
														WHERE `Cluster-UUID` = '$clusterUUID'
														AND `UUID` = '$boxUUID'
														LIMIT 1
											        ";
											        
											        
											        if ($boxResult = mysqli_query($mysqli, $boxQuery)) {
												        $boxCount = mysqli_num_rows($boxResult);
														
														if ($boxCount > 0) {
															$boxRow = mysqli_fetch_row($boxResult);
															
															$boxShouldUnlock = false;
															
															
															$unlockUntil = $boxRow[12];
															
															if ($unlockUntil > $dateTime) {
																$boxShouldUnlock = true;
															}
															
															
															if ($boxShouldUnlock) {
																$boxUUID = $boxRow[1];
																
																$boxName = $boxRow[0];
																
																
																	
															} else {
																echo "Failed to disarm box. Please try again.";
															}
													        
													        
													        // generate page for the box
															
															echo "<h3>Disarmed — $boxName</h3>";
															
															echo "<div id = \"boxPageContent\">";
															
															
															
															
															//echo "<h3>Box disarmed</h3>";
															
															//echo "[Video/Animation]" . "<br>";
															
															$minutesString = "$unlockForMinutes minute";
															
															if ($unlockForMinutes != 1) {
																$minutesString .= "s";
															}
															
															echo "$boxName may be unlocked once within the next $minutesString by pressing its button. Doing so will void the current order.";
															
															
															
															
															
															
															
															
															echo "</div>";
															
															
															
														}
											        }
											        
											        
											        
											        
											        
											        
											        
											        
											        
											        
												} else {
											        echo "Query failed to disarm box. Please try again, or contact an administrator.";
										        }
												
											} else {
												echo "request query failed.";
												
												
											}
											
											
											
										}
										
										
										
									} else {
										echo "Box could not be found." . "<br>";
									}
								} else {
									echo "Query failed for boxes." . "<br>";
								}
						        
								
								
								
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
								
								echo "<input name = \"customerEmailField\" class = \"TextInput\" placeholder = \"Customer Email\">" . "<br>";
								
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
							echo "You do not have permission to manage this box. Please contact an administrator if you believe this message was received in error." . "<br>";
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