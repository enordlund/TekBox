<?php
	// remove this for PHP 7+ (adds compatibility for random_bytes)
	//require_once "../php/libraries/random_compat-master/lib/random.php";
	require_once '../tekbox_config.php';
	
	require_once TEKBOX_DATABASE_FILE_PATH;
	
	
	
	logFirstVisit($ipAddress, $date);
	
	function logFirstVisit($ip, $dateTime) {
		$database = new TekBoxDatabase();
		
		// connecting to database
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if ($mysqli->connect_errno) {
		    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		} else {
			
			// reference values
			$ipAddress = $_SERVER['REMOTE_ADDR'];
			$dateTime = date("Y-m-d H:i:s");
			$lockerUID = $_POST['uid'];
			$requestType = $_POST['request'];
			
			// generate requestUUID
			$requestUUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
			
			$isAuthenticated = false;
			
			$shouldUnlock = false;
			
			//echo $requestUUID;
			
			// find box with uuid
			$boxQuery = "
				SELECT * 
				FROM `TekBox-Boxes` 
				WHERE `UUID` = '$lockerUID'
				LIMIT 1
			";
			
			$lastBoxIP = "";
			$modifierUUID = "";
			
			if ($boxResult = mysqli_query($mysqli, $boxQuery)) {
				$boxCount = mysqli_num_rows($boxResult);
				
				if ($boxCount > 0) {
					// found the box
					$boxRow = mysqli_fetch_row($boxResult);
					
					$lastBoxIP = $boxRow[7];
					
					$modifierUUID = $boxRow[8];
					
					$hash = $boxRow[2];
					
					$requestPassword = $_POST['password'];
					
			
					if (password_verify($requestPassword, $hash)) {
						// password matched, so check totp to continue authentication.
						$requestTOTP = $_POST['totp'];
					
						if ($requestTOTP == "246802") {
							$isAuthenticated = true;
							
							
							$unlockUntil = $boxRow[12];
							
							if ($unlockUntil > $dateTime) {
								$shouldUnlock = true;
							}
							
							
							
						} else {
							echo "TOTP did not match.";
						}
					} else {
						echo "Password did not match.";
					}
				} else {
					echo "Found 0 boxes with uuid.";
				}
			} else {
				echo "Box query failed.";
			}
			
			
			
			if ($isAuthenticated) {
				// the locker is registered with the system, so proceed to handle the request.
				
				$serverResponse = "";
				
				// determine request type
				if ($requestType == "TEST") {
					
					
					// create a setup session
					
					
					// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ for now, the session uid is just the locker uid (to be checked with the app), but ideally the session uid would be ephemeral.
					
					
					$setupSessionQuery = "
						INSERT INTO `" . DB_NAME . "`.`TekBox-Setup-Sessions` (`Session-UID`, `Locker-UID`, `DateTime`)
						VALUES ('$lockerUID', '$lockerUID', '$dateTime');
					";
					
					if ($sessionResult = mysqli_query($mysqli, $setupSessionQuery)) {
						$serverResponse = "PASS";
						
						$secret = null;
						
// 						echo "passed";
						
						// find or create offline key
						if ($offlineKeySecret = $database->getOfflineKeySecret($lockerUID)) {
// 							echo "found offline secret";
							$secret = $offlineKeySecret;
						} else if ($newSecret = $database->generateOfflineKey($lockerUID)) {
// 							echo "generated new offline secret";
							$secret = $newSecret;
						}
						
						if ($secret != null) {
							echo "test=PASS&uuid=$requestUUID&secret=$secret\r\n";
						} else {
							echo "test=PASS&uuid=$requestUUID\r\n";
						}
						
						
					} else {
						$serverResponse = "FAIL";
						echo "test=FAIL&uuid=$requestUUID\r\n";
					}
					
					
				} else if ($requestType == "PRESS") {
					if ($shouldUnlock) {
						$serverResponse = "UNLOCK";
						echo "command=UNLOCK&uuid=$requestUUID\r\n";
					} else {
						// handle other cases
						
						$serverResponse = "LOCK";
						echo "command=LOCK&uuid=$requestUUID\r\n";
					}
				}
				
				
				
				
				
				
				
				// compare IP with last IP
				$ipIsNew = false;
				
				
				if ($ipAddress != $lastBoxIP) {
					$ipIsNew = true;
					
					// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ update last IP for box
					
					$updateIPQuery = "
						UPDATE `TekBox-Boxes`
						SET `Last-IP` = '$ipAddress' 
						WHERE `UUID` = '$lockerUID'
						LIMIT 1
			        ";
			        
			        if ($updateUnlockResult = mysqli_query($mysqli, $updateIPQuery)) {
				        // query was successful. Check for update
				        //echo "DONE\r\n";
			        } else {
				        echo "ERROR\r\n";
			        }
				}
				
				
				// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ echo request number to box for confirmation request
				// // ~~~~~~~~~~~~~~~~ when the confirmation request is sent, check last IP.
				
				
				
				
				
				
				
				// log the request
				$query = "
					INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Box-UUID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Response`, `New-IP`, `Modifier-UUID`)
					VALUES ('$lockerUID', '$requestUUID', '$dateTime', '$requestType', '$ipAddress', '$serverResponse', '$ipIsNew', '$modifierUUID');
					";
				
				if ($requestType == "TAMPER") {
					
					// send email/notification to adminitrators
					$tekBoxDashboardURL = TEKBOX_DASHBOARD_URL;
					
					$administratorEmail = TAMPERING_EMAIL_ADDRESS;
					
					$emailSubject = TEKBOX_EMAIL_TAMPERING_SUBJECT;
					
					$emailBody = TEKBOX_EMAIL_TAMPERING_BODY;
					
					$emailHeaders = "From: " . TEKBOX_EMAIL_FROM_ADDRESS . "\r\n";
					
					if (mail($administratorEmail, $emailSubject, $emailBody, $emailHeaders)) {
						$query = "
						INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Box-UUID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Error`, `Response`, `New-IP`, `Modifier-UUID`)
						VALUES ('$lockerUID', '$requestUUID', '$dateTime', '$requestType', '$ipAddress', 'TAMPER', '$serverResponse', '$ipIsNew', '$modifierUUID');
						";
						
						
						
					} else {
						// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ provide button to retry email
						// // ~~~~~~~~~~ display the order info so the person can verify the info is correct
						
						$query = "
						INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Box-UUID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Error`, `Response`, `New-IP`, `Modifier-UUID`)
						VALUES ('$lockerUID', '$requestUUID', '$dateTime', '$requestType', '$ipAddress', 'TAMPER', '$serverResponse', '$ipIsNew', '$modifierUUID');
						";
						
						
						//echo "Email failed to send. Please try again.";
					}
					
					
				}
				
				
				if($result = mysqli_query($mysqli, $query)) {
					// successfully logged request
				} else {
					echo "query failed\r\n";
				}
			} else {
				echo "Authentication failed.\r\n";
				
				// log the request, with AUTH error
				$query = "
					INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Box-UUID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Error`)
					VALUES ('$lockerUID', '$requestUUID', '$dateTime', '$requestType', '$ipAddress', 'AUTH');
					";
				
				if($result = mysqli_query($mysqli, $query)) {
					// maybe send response?
					$serverResponse = "";
					if ($requestType == "TEST") {
						// is authenticated
						$serverResponse = "FAIL";
						echo "test=FAIL&uuid=$requestUUID\r\n";
					}
				} else {
					echo "query failed\r\n";
				}
			}
			
			
		}
	}
?>