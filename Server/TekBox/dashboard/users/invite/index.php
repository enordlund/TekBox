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
		<title>TekBox Dashboard - Invite New User</title>
		
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
									<a href="../"  class="headlink"id="headlink1">
									Lockers
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../orders/"  class="headlink" id="headlink2">
									Orders
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../users/"  class="headlink" id="headlinkcurrent">
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
					
					
					$locationUID = $_GET["locationUID"];
					
					// found the user, so look for the user's locations
					$locationQuery = "
						SELECT * 
						FROM `TekBox-Clusters` 
						WHERE (`Admin1-UUID` = '$osuUID'
						OR `Admin2-UUID` = '$osuUID'
						OR `Admin3-UUID` = '$osuUID'
						OR `Admin4-UUID` = '$osuUID'
						OR `Admin5-UUID` = '$osuUID')
						AND `UUID` = '$locationUID'
						LIMIT 1
					";
					
					if ($locationResult = mysqli_query($mysqli, $locationQuery)) {
						$locationCount = mysqli_num_rows($locationResult);
						
						if ($locationResult > 0) {
							// the user does have permissions to manage/administrate the resulting location
							$locationRow = mysqli_fetch_row($locationResult);
							$locationName = $locationRow[0];
							
							$formIsValid = false;
							
							// Provide the invitation form
							if ($_POST["name"] && $_POST["email"] && $_POST["role"]) {
								
								$name = $_POST["name"];
								$email = $_POST["email"];
								$role = $_POST["role"];
								
								$formIsValid = true;
								
								// // validate customer name
								if (!preg_match("/^[a-zA-Z -]*$/",$name)) {
									$formIsValid = false;
									echo "Only letters, hyphens, and white space are allowed for a name." . "<br>"; 
								}
								
								// // validate email address
								if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
									$formIsValid = false;
									echo "Invalid email format."; 
								}
								
								
								if ($formIsValid) {
									
									// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ this is only successful if there is a spot for an administrator or manager
									// check for available space
									
									
									
									
									
									
									// remove any duplicate invitations (same locationUID and email)
									$deleteInvitationsQuery = "
										DELETE FROM `TekBox-Invitations`
										WHERE `Location-UID` = '$locationUID'
										AND `Invitee-Email` = '$email'
									";
									
									if ($deleteResult = mysqli_query($mysqli, $deleteInvitationsQuery)) {
										// query successful
										
										// insert the invitation into the database
										
										// // create a uid for the invitation (for accurate removal after search when new user signs in)
										$invitationUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
										
										$isAdmin = 0;
										
										$roleString = "manage";
										
										if ($role == "Administrator") {
											$isAdmin = 1;
											$roleString = "administrate";
										}
										
										$insertInvitationQuery = "
											INSERT INTO `" . DB_NAME . "`.`TekBox-Invitations` (`Invitation-UID`, `Location-UID`, `Inviter-UID`, `Invitee-Name`, `Invitee-Email`, `Invitee-Is-Admin`, `Date-Time`)
											VALUES ('$invitationUID', '$locationUID', '$osuUID', '$name', '$email', $isAdmin, '$dateTime');
										";
										
										if ($insertInvitationResult = mysqli_query($mysqli, $insertInvitationQuery)) {
											// after inserting the invitation in the database, send the email
											
											$inviterName = $userRow[0];
											
											$emailSubject = $inviterName . TEKBOX_EMAIL_USER_INVITE_SUBJECT;
											
											$emailBody = $name . ",\r\n\r\n" . $inviterName . " invited you to ". $roleString . " a TekBox location! You may accept your invitation here: " . TEKBOX_DASHBOARD_ACCOUNT_URL;
											
											$emailHeaders = "From: " . TEKBOX_EMAIL_FROM_ADDRESS . "\r\n";
											
											if (mail($email, $emailSubject, $emailBody, $emailHeaders)) {
												// email sent successfully
												echo "Your invitation is sent!" . "<br>";
												
												// display the invitation info
												echo "Name: " . $name . "<br>";
												echo "Email: " . $email . "<br>";
												echo "Role: " . $role . "<br>";
												
												echo "The account owner will be added to " . $locationName . " when they accept their invitation.";
												
												
												
												
												//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ add invite info to requests table for activity log
												
											} else {
												// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ provide button to retry email
												// // ~~~~~~~~~~ display the order info so the person can verify the info is correct
												
												
												
												
												echo "Email failed to send. Please try again.";
											}
											
											
										} else {
											$formIsValid = false;
											echo "Query failed to insert the invitation. Please try again." . "<br>";
										}
										
									} else {
										echo "Failed to access database." . "<br>";
										$formIsValid = false;
									}
									
									
									
									
									
									
									
									
								}
								
							}
							
							if ($formIsValid == false) {
								echo "<h3>Add an operator to " . $locationName . "</h3>";
								
								echo "<form action = \"$_PHP_SELF\" method = \"POST\">";
								
								echo "Name:" . "<br>" . "<input name = \"name\" class = \"TextInput\" placeholder = \"Benny Beaver\">" . "<br>";
								
								echo "Email:" . "<br>" . "<input name = \"email\" class = \"TextInput\" placeholder = \"beaverb@oregonstate.edu\">" . "<br>";
								
								echo "Role:" . "<br>" . "<select name = \"role\" class = \"TextInput\"> <option value = \"Manager\">Manager</option> <option value = \"Administrator\">Administrator</option> </select>" . "<br>";
								
								echo "<button class = \"orangeButton\">Send Invitation</button>";
								
								echo "</form>";
							}
							
							
							
						} else {
							echo "You do not have permission to manage this box. Please contact an administrator if you believe this message was received in error." . "<br>";
						}
					}
					
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