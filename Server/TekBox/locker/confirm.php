<?php
	require_once '../tekbox_config.php';
	
	require_once TEKBOX_DATABASE_FILE_PATH;
	
	
	logFirstVisit($ipAddress, $date);
	
	function logFirstVisit($ip, $dateTime) {
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
			
			
			
			$isAuthenticated = false;
			
			$shouldUnlock = false;
			
			
			
			// find locker with UID
			$lockerQuery = "
				SELECT * 
				FROM `TekBox-Boxes` 
				WHERE `UUID` = '$lockerUID'
				LIMIT 1
			";
			
			$lastLockerIP = "";
			
			if ($lockerResult = mysqli_query($mysqli, $lockerQuery)) {
				$lockerCount = mysqli_num_rows($lockerResult);
				
				if ($lockerCount > 0) {
					// found the locker
					$lockerRow = mysqli_fetch_row($lockerResult);
					
					$lastLockerIP = $lockerRow[7];
					
					
					
					$hash = $lockerRow[2];
					
					$requestPassword = $_POST['password'];
					
			
					if (password_verify($requestPassword, $hash)) {
						// password matched, so check totp to continue authentication.
						$requestTOTP = $_POST['totp'];
					
						if ($requestTOTP == "246802") {
							$isAuthenticated = true;
							
							
							$unlockUntil = $lockerRow[12];
							
							if ($unlockUntil > $dateTime) {
								$shouldUnlock = true;
							}
							
							
							
							// continue
							
							
							if ($isAuthenticated) {
								// compare IP with last IP
								$ipIsNew = false;
								
								if (true == false) {
								// NOTE: this is causing issues, because cellular switches IP every time.
								// -- -- -- it is being bypassed for now by the above line.
								// // // // could bring this back by keeping connection open rather than closing.
								//if ($ipAddress != $lastLockerIP) {
									$ipIsNew = true;
									
									// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ mark error since IP changed between command and confirmation
									
									echo "ERROR: New IP\r\n";
								} else {
									// confirm response with UID
									$requestUID = $_POST['requestUID'];
									
									$requestConfirmation = $_POST['confirmation'];
									
									// find latest request with UID
									
									if ($requestConfirmation && $requestUID) {
										$requestUIDQuery = "
											UPDATE `TekBox-Requests`
											SET `Confirmation` = '$requestConfirmation' 
											WHERE `Request-UUID` = '$requestUID'
											AND `Confirmation`IS NULL
											ORDER BY  `DateTime` DESC
											LIMIT 1
								        ";
										
										if ($uidResult = mysqli_query($mysqli, $requestUIDQuery)) {
											$resultsCount = mysqli_affected_rows($mysqli);
											
											if ($resultsCount > 0) {
												// select first result
												
												
												
												
												// if locker was unlocked, set "should unlock" to false again.
												
												$shouldNotUnlockDate = date("Y-m-d H:i:s", time() - 1);
												
												$shouldPrep = $lockerRow[6];
												
												if ($shouldUnlock) {
													
													// update the locker info so that it won't unlock next time.
													
													
													
													$updateUnlockQuery = "
														UPDATE `TekBox-Boxes`
														SET `Unlock-Until` = '$shouldNotUnlockDate', 
														`Is-Loaded` = 0,
														`Order-UUID` = NULL
														WHERE `UUID` = '$lockerUID'
														LIMIT 1
											        ";
											        
											        if ($updateUnlockResult = mysqli_query($mysqli, $updateUnlockQuery)) {
												        // query was successful. Check for update
												        
												        
												        
												        // if the locker is holding an order, deactivate the order
												        $lockerOrderUID = $lockerRow[13];
												        if ($lockerOrderUID != NULL) {
													        $deactivateOrderQuery = "
													        	UPDATE `TekBox-Orders`
																SET `Is-Active` = 0,
																`Void-DateTime` = '$dateTime'
																WHERE `UUID` = '$lockerOrderUID'
																LIMIT 1
													        ";
													        
													        if ($deactivateOrderResult = mysqli_query($mysqli, $deactivateOrderQuery)) {
														        echo "DONE\r\n";
													        } else {
														        echo "ERROR\r\n";
													        }
												        } else {
													        echo "DONE\r\n";
												        }
												        
												        
											        } else {
												        echo "ERROR\r\n";
											        }
												} else if ($requestConfirmation == "UNLOCK") {
													// if the confirmation was "UNLOCK," but the locker wasn't supposed to unlock, this is an error.
													echo "ERROR\r\n";
												} else if ($shouldPrep){
													
													// confirming LOCK after loading the locker
													
													$updateLoadedQuery = "
														UPDATE `TekBox-Boxes`
														SET `Is-Loaded` = 1,
														`Should-Prep` = 0
														WHERE `UUID` = '$lockerUID'
														LIMIT 1
											        ";
											        
											        if ($updateLoadedResult = mysqli_query($mysqli, $updateLoadedQuery)) {
												        // query was successful. check for update
												        
												        $rowCount = mysqli_affected_rows($mysqli);
												        
												        if ($rowCount > 0) {
													        echo "DONE\r\n";
												        } else {
													        echo "ERROR\r\n";
												        }
											        }
													
													
													
													
												} else {
													// locker is supposed to unlock.
													echo "DONE\r\n";
												}
												
												
												
												
												
												
												
											} else {
												echo "ERROR: $requestUID\r\n";
											}
										} else {
											echo "ERROR: query failed.\r\n";
										}
									} else {
										echo "ERROR: Missing response UID or request confirmation.\r\n";
									}
									
									
								}
								
							} else {
								echo "ERROR: Authentication failed.\r\n";
								
								// log the request, with AUTH error
								$query = "
									INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Box-UUID`, `DateTime`, `Request`, `IP-Address`, `Error`)
									VALUES ('$lockerUID', '$dateTime', '$requestType', '$ipAddress', 'AUTH');
									";
								
								if($result = mysqli_query($mysqli, $query)) {
									// maybe send response?
									echo "You win\r\n";
								} else {
									echo "ERROR: query failed\r\n";
								}
							}
							
							
							
							
							
							
							
							
							
						} else {
							echo "ERROR: TOTP did not match.";
						}
					} else {
						echo "ERROR: Password did not match.";
					}
				} else {
					echo "ERROR: Found 0 lockers with UID.";
				}
			} else {
				echo "ERROR: Locker query failed.";
			}
			
			
			
			
			
			
			
			
			
		}
	}
?>