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
		<title>TekBox Dashboard - Locker Prep</title>
		
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
											
											
											// check to see if the box was locked since it was disarmed, indicating that the order is inside the box
											
											$boxShouldUnlock = true;
											
											$unlockUntil = $boxRow[12];
											
											if ($unlockUntil <= $dateTime) {
												$boxShouldUnlock = false;
											}
											
											$boxIsLoaded = $boxRow[9];
											
											
											// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ also make sure the box is closed. (handle in else closure)
											
											// check for the form post
											if ($_POST["orderNumber"] && $_POST["customerName"] && $_POST["customerEmail"]) {
												// the form was submitted
												
												$orderNumber = $_POST["orderNumber"];
												$customerName = $_POST["customerName"];
												$customerEmail = $_POST["customerEmail"];
												
												
												
												
												// validate the values
												$formIsValid = true;
												// // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ validate order number
												
												
												
												// // validate customer name
												if (!preg_match("/^[a-zA-Z -]*$/",$customerName)) {
													$formIsValid = false;
													echo "Only letters, hyphens, and white space allowed" . "<br>"; 
												}
												
												// // validate email address
												if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
													$formIsValid = false;
													echo "Invalid email format"; 
												}
												
												// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
												
												
												if ($formIsValid && $boxIsLoaded && !$boxShouldUnlock) {
													
													
													
													echo "Order Number: $orderNumber" . "<br>";
													echo "Customer Name: $customerName" . "<br>";
													echo "Customer Email: $customerEmail" . "<br>";
													
													
													
													// generate order UUID
													$orderUUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
													
													// ~~~~~~~~~~~~~~~~~~~ handle case of duplicate active order number (make sure they really want to add another)
													// // ~~~~~~~~~~~~~~~~~~~~ one important case could be that an order takes up multiple boxes.
													
													// insert order
													$insertOrderQuery = "
														INSERT INTO `" . DB_NAME . "`.`TekBox-Orders` (`UUID`, `Order-Number`, `Customer-Name`, `Customer-Email`, `Is-Active`, `Email-Did-Send`, `DateTime`, `Box-UUID`)
														VALUES ('$orderUUID', '$orderNumber', '$customerName', '$customerEmail', 1, 0, '$dateTime', '$boxUUID');
													";
													
													
													if($insertOrderResult = mysqli_query($mysqli, $insertOrderQuery)) {
														// successfully logged request
														
														$shouldNotUnlockDate = date("Y-m-d H:i:s", time() - 1);
														
														// update box with order uuid, should unlock, and isLoaded
														$updateBoxQuery = "
																UPDATE `TekBox-Boxes`
																SET `Unlock-Until` = '$shouldNotUnlockDate',
																`Order-UUID` = '$orderUUID' 
																WHERE `UUID` = '$boxUUID'
																LIMIT 1
														";
														
														
														if ($boxUpdateResult = mysqli_query($mysqli, $updateBoxQuery)) {
															// successfully updated box
															
															// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ UPDATE ACTIVITY LOG WITH USER LOADING BOX
															
															// send email to customer
															$tekBoxAccessURL = TEKBOX_ACCESS_URL;
															
															
															// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ send email for order
															$emailSubject = TEKBOX_EMAIL_ORDER_READY_SUBJECT;
															
															$emailBody = TEKBOX_EMAIL_ORDER_READY_BODY;
															
															$emailHeaders = "From: " . TEKBOX_EMAIL_FROM_ADDRESS . "\r\n";
															
															if (mail($customerEmail, $emailSubject, $emailBody, $emailHeaders)) {
																// email sent successfully
																
																// update order with email sent outcome
																$updateOrderQuery = "
																	UPDATE `TekBox-Orders`
																	SET `Email-Did-Send` = 1 
																	WHERE `UUID` = '$orderUUID'
																	LIMIT 1
																";
																
																if ($orderUpdateResult = mysqli_query($mysqli, $updateOrderQuery)) {
																	// successfully updated database
																	
																	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ display success message, and link back to Boxes page
																	echo "Success!";
																} else {
																	echo "Failed to update order info, but email did successfully send.";
																}
																
																
																
																
															} else {
																// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ provide button to retry email
																// // ~~~~~~~~~~ display the order info so the person can verify the info is correct
																
																
																
																
																echo "Email failed to send. Please try again.";
															}
															
														} else {
															echo "Failed to update box with order info.";
														}
														
														
														
														
														
													} else {
														echo "order insert query failed\r\n";
													}
													
													
													
													
													exit();
													
												}
												
												
												
												// errors for prep issues
												// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ add one for if the box isn't closed.
												
												
												
												
												if ($boxShouldUnlock) {
													echo "The box is still disarmed. Please check that the order is in the box, and confirm that the box will remain locked by pressing the button." . "<br>";
												} else if (!$boxIsLoaded) {
													echo "The box is not locked. Please confirm that the box is locked by pressing the button (two presses may be required)." . "<br>";
												}
												
												
												
											}
											
											
											// the form post request wasn't completely filled out
											// check to make sure the box Should-Unlock
											
											
											
											
											
											
											
											
											
											
											
											
											
											
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
											
											$boxIsLoaded = $boxRow[9];
											
											$orderUUID = $boxRow[13];
											
											$orderUUIDIsNull = false;
											
											if ($orderUUID == NULL) {
												$orderUUIDIsNull = true;
											}
											
											if ($orderUUIDIsNull) {
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
													
													
													// watch for case where the box is already loaded and should remain armed until user chooses otherwise
													$shouldPrep = 1;
													
													if ($boxIsLoaded) {
														$unlockUntil = date("Y-m-d H:i:s", time() - 1);
														$shouldPrep = 0;
													}
													
													// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ setting Should-Load preemptively for when it is locked again
													$updateUnlockQuery = "
														UPDATE `TekBox-Boxes`
														SET `Unlock-Until` = '$unlockUntil',
														`Should-Prep` = '$shouldPrep'
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
																
																
																
																
																$boxShouldUnlock = true;
																
																$unlockUntil = $boxRow[12];
																
																if ($unlockUntil <= $dateTime) {
																	$boxShouldUnlock = false;
																}
																
																
																
																if ($boxShouldUnlock) {
																	$boxUUID = $boxRow[1];
																	
																	$boxName = $boxRow[0];
																	
																	
																		
																} else if (!$boxIsLoaded) {
																	echo "Failed to disarm box. Please try again.";
																}
														        
														        
														        // generate page for the box
																
																echo "<h3>Prepare Order — $boxName</h3>";
																
																echo "<div id = \"boxPageContent\">";
																
																
																
																if ($boxIsLoaded) {
																	echo "The box is already loaded. You may complete an order with its current contents, or disarm the box for a different order." . "<br>";
																	echo "<button class = \"orangeButton\" onclick=\"window.location.href = '../disarm?clusterUUID=$clusterUUID&boxUUID=$boxUUID';\">Disarm For Different Order</button>";
																	echo "<h3>Or send an invitation</h3>";
																} else {
																	echo "<h3>1. Unlock and load</h3>";
																	
																	echo "[Video/Animation]" . "<br>";
																	
																	$minutesString = "$unlockForMinutes minute";
																	
																	if ($unlockForMinutes != 1) {
																		$minutesString .= "s";
																	}
																	
																	echo "$boxName may be unlocked once within the next $minutesString by pressing its button. Once the order is inside, close the door and press the button again to lock the box.";
																	
																	echo "<h3>2. Send an invitation</h3>";
																}
																
																
																
																
																echo "";
																
																
																
																
																
																echo "<form action = \"$_PHP_SELF\" method = \"POST\">";
																
																echo "Order Number:" . "<br>" . "<input name = \"orderNumber\" class = \"TextInput\" placeholder = \"12345678\">" . "<br>";
																
																echo "Customer Name:" . "<br>" . "<input name = \"customerName\" class = \"TextInput\" placeholder = \"Benny Beaver\">" . "<br>";
																
																echo "Customer Email:" . "<br>" . "<input name = \"customerEmail\" class = \"TextInput\" placeholder = \"beaverb@oregonstate.edu\">" . "<br>";
																
																echo "<button class = \"orangeButton\">Complete</button>";
																
																echo "</form>";
																
																
																
																//echo "<h3>3. Profit</h3>";
																
																
																
																
																
																
																echo "</div>";
																
																
																
															}
												        }
												        
												        
												        
												        
												        
												        
												        
												        
												        
												        
													} else {
												        echo "Query failed to disarm box. Please try again, or contact an administrator.";
											        }
													
												} else {
													echo "request query failed.";
													
													
												}
											} else {
												echo "$boxName is already loaded. Please select a different box.";
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