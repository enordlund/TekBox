<?php
// 	require_once dirname(__FILE__).'../tekbox_config.php';// not required, because the endpoints load this file before this file is loaded.
	
	require_once dirname(__FILE__).'/random_compat-master/lib/random.php';
	
	class DatabaseStatus {
		public $databaseError;
	}
	
	class TaskReport {
		public $didComplete;
		public $asset;
		public $errorMessage;
	}
	
	class Invitation{
		public $invitationUID;
		public $inviterName;
		public $inviterEmail;
		public $locationName;
		public $locationUID;
		public $isAdmin;
	}
	
	class TekBoxDatabase {
		// connecting to database
		
		private $mysqli = null;
		
		private $error = null;
				
		public function open() {
			$this->error = null;
			
			
			$this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
			
			if ($this->mysqli->connect_errno) {
			    $this->error = "Failed to connect to MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
			    return false;
			} else {
				return true;
			}
		}
		
		
		function __construct() {
			$this->open();
		}
		
		
		public function close() {
			$this->mysqli->close();
		}
		
		
		function __destruct() {
			$this->close();
		}
		
		
		public function verifyToken($withTokenUID, $token) {
			
			if ($this->open() ) {
				
				$tokenQuery = "
					SELECT * 
					FROM `TekBox-App-Tokens` 
					WHERE `Token-UID` = '$withTokenUID'
					LIMIT 1
				";
				
				if ($tokenResult = mysqli_query($this->mysqli, $tokenQuery)) {
					if ($verifiedTokenRow = mysqli_fetch_row($tokenResult)) {
						
						// make sure the token matches the hash
						$tokenHash = $verifiedTokenRow[2];
						
						if(password_verify($token, $tokenHash)) {
							// token verified
							
							return $verifiedTokenRow;
						} else {
							//echo "bad hash";
						}
					} else {
						//echo "token not found";
					}
				} else {
					//echo "query failed";
				}
				
				
				$this->close();
			} else {
				//echo "failed to open database";
				//echo $this->$error;
			}
			
			
			return false;
		}
		
		public function deleteToken($withTokenUID) {
			
			$deleteTokenQuery = "
				DELETE
				FROM `TekBox-App-Tokens`
				WHERE `Token-UID` = '$withTokenUID'
				LIMIT 1
			";
			
			if ($deleteTokenResult = mysqli_query($this->mysqli, $deleteTokenQuery)) {
				// successfully deleted token
				return true;
			} else {
				// unsuccessful.
				return false;
			}

		}
		
		
		
		
		public function getUserRow($withUID) {
			// Get user
			$userQuery = "
				SELECT * 
				FROM `TekBox-Users` 
				WHERE `OSUUID` = '$withUID'
				LIMIT 1
			";
			
			if ($userResult = mysqli_query($this->mysqli, $userQuery)) {
				if ($userRow = mysqli_fetch_row($userResult)) {
					return $userRow;
				}
			}
			
			return null;
		}
		
		
		public function getInvitations($forEmail) {
			
			$invitationsQuery = "
				SELECT *
				FROM `TekBox-Invitations`
				WHERE `Invitee-Email` = '$forEmail'
			";
			
			if ($invitationsResult = mysqli_query($this->mysqli, $invitationsQuery)) {
				$invitationsCount = mysqli_num_rows($invitationsResult);
				
				if ($invitationsCount > 0) {
					// construct invitations
					
					$invitationsData = array();
					
					while ($invitationRow = mysqli_fetch_row($invitationsResult)) {
						$invitationLocationUID = $invitationRow[1];
						
						// get location name
						$locationQuery = "
							SELECT * 
							FROM `TekBox-Clusters` 
							WHERE `UUID` = '$invitationLocationUID'
							LIMIT 1
						";
						
						if ($locationResult = mysqli_query($this->mysqli, $locationQuery)) {
							
							
							if ($locationRow = mysqli_fetch_row($locationResult)) {
								$locationName = $locationRow[0];
								
								$invitationUID = $invitationRow[0];
								$inviterUID = $invitationRow[2];
								
								
								// find inviter name
								
								$inviterQuery = "
									SELECT *
									FROM `TekBox-Users`
									WHERE `OSUUID` = '$inviterUID'
									LIMIT 1
								";
								
								if ($inviterResult = mysqli_query($this->mysqli, $inviterQuery)) {
									if ($inviterRow = mysqli_fetch_row($inviterResult)) {
										
										// append the invitation to the array
										$invitationData = new Invitation();
										$invitationData->invitationUID = $invitationUID;
										$invitationData->inviterName = $inviterRow[0];
										$invitationData->inviterEmail = $inviterRow[1];
										$invitationData->locationName = $locationName;
										$invitationData->locationUID = $invitationLocationUID;
										$invitationData->isAdmin = (bool)$invitationRow[5];
										
										$invitationsData[] = $invitationData;
									}
								}
								
								
							}
						}
					}
					
					return $invitationsData;
					
				}
			}
			
			return null;
		}
		
		
		
		public function rsvpInvitation($invitationUID, $accepted, $osuUID) {
			// look for invitation.
			
			$report = new TaskReport();
			$report->didComplete = false;
			$report->errorMessage = "Unknown RSVP error.";
			
			
			if ($userRow = $this->getUserRow($osuUID)) {
				// found user
				
				
				
				$lastUserIP = $userRow[3];
				
				
				// compare IP with last IP
				$ip = $_SERVER['REMOTE_ADDR'];
				
				$dateTime = date("Y-m-d H:i:s");
				
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
			        
			        if ($updateUnlockResult = mysqli_query($this->mysqli, $updateIPQuery)) {
				        // query was successful. Check for update
				        //echo "DONE\r\n";
			        } else {
// 				        echo "ERROR\r\n";
			        }
				}
				
				
				
				
				
				
				$osuEmail = $userRow[1];
				
				$invitationQuery = "
					SELECT *
					FROM `TekBox-Invitations`
					WHERE `Invitee-Email` = '$osuEmail'
					AND `Invitation-UID` = '$invitationUID'
					LIMIT 1
				";
				
				if ($invitationQueryResult = mysqli_query($this->mysqli, $invitationQuery)) {
					if ($invitationRow = mysqli_fetch_row($invitationQueryResult)) {
						// invitation found
						if ($accepted == true) {
		
							$locationUID = $invitationRow[1];
							
							// look for location
							if ($locationRow = $this->getLocationRow($locationUID)) {
								// found location
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
									
									
									$report = new TaskReport();
									
									while (($managerIndex < 23) && ($managerAdded == false)) {
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
											
											if ($addManagerResult = mysqli_query($this->mysqli, $addManagerQuery)) {
												if (mysqli_affected_rows($this->mysqli) > 0) {
													// admin updated successfully
													
													// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ delete the invitation
													$deleteQuery = "
														DELETE
														FROM `TekBox-Invitations`
														WHERE `Invitee-Email` = '$osuEmail'
														AND `Invitation-UID` = '$invitationUID'
													";
													
													
													
													$managerAdded = true;
													
	// 												return true;
													
													
													
													$report->asset = "You have joined the location, \"$locationName\" as a manager!";
													
													if ($deleteResult = mysqli_query($this->mysqli, $deleteQuery)) {
														if (mysqli_affected_rows($this->mysqli) < 1) {
															$report->errorMessage = "Failed to delete invitation.";
														}
													} else {
														$report->errorMessage = "Query failed to delete invitation.";
													}
													
													// log that the user joined
													$requestUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
													$requestType = "JOINED";
													
													$activityQuery = "
														INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Location-UID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Response`, `Confirmation`, `New-IP`, `Modifier-UUID`)
														VALUES ('$locationUID', '$requestUID', '$dateTime', '$requestType', '$ip', 'MANAGER', 'MANAGER', '$ipIsNew', '$osuUID');
													";
													
													if ($activityQuery = mysqli_query($this->mysqli, $activityQuery)) {
														if (mysqli_affected_rows($this->mysqli) < 1) {
															$report->errorMessage = "Failed to update activity.";
														}
													} else {
														$report->errorMessage = "Query failed to update activity.";
													}
													
													break;
												} else {
													$report->errorMessage = "Failed to add user.";
												}
											} else {
												$report->errorMessage = "Query failed to add user.";
											}
										}
										$managerIndex += 1;
									}
									
									
									
									
									$report->didComplete = $managerAdded;
									
									
									if ($managerAdded == false) {
										$report->errorMessage = "The location \"$locationName\" cannot have any more managers. Please contact an existing administrator for help with joining the location.";
									}
									
									
									return $report;
									
								} else {
									// is admin
									// check admin slots
									
									$adminIndex = 8;
									$adminAdded = false;
									
									$locationName = $locationRow[0];
									
									$report = new TaskReport();
									
									while (($adminIndex < 13) && ($adminAdded == false)) {
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
											
											if ($addAdminResult = mysqli_query($this->mysqli, $addAdminQuery)) {
												if (mysqli_affected_rows($this->mysqli) > 0) {
													// admin updated successfully
													
													// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ delete the invitation
													
													
													$adminAdded = true;
													
													$report->asset = "You have joined the location, \"$locationName\" as an administrator!";
													
													// delete the invitation
													
													$deleteQuery = "
														DELETE
														FROM `TekBox-Invitations`
														WHERE `Invitee-Email` = '$osuEmail'
														AND `Invitation-UID` = '$invitationUID'
													";
													
													
													if ($deleteResult = mysqli_query($this->mysqli, $deleteQuery)) {
														if (mysqli_affected_rows($this->mysqli) < 1) {
															$report->errorMessage = "Failed to delete invitation.";
														}
													} else {
														$report->errorMessage = "Query failed to delete invitation.";
													}
													
													// log that the user joined
													$requestUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
													$requestType = "JOINED";
													
													$activityQuery = "
														INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Location-UID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Response`, `Confirmation`, `New-IP`, `Modifier-UUID`)
														VALUES ('$locationUID', '$requestUID', '$dateTime', '$requestType', '$ip', 'ADMIN', 'ADMIN', '$ipIsNew', '$osuUID');
													";
													
													if ($activityQuery = mysqli_query($this->mysqli, $activityQuery)) {
														if (mysqli_affected_rows($this->mysqli) < 1) {
															$report->errorMessage = "Failed to update activity.";
														}
													} else {
														$report->errorMessage = "Query failed to update activity.";
													}
													
													
													break;
												} else {
													$report->errorMessage = "Failed to add user.";
												}
											} else {
												$report->errorMessage = "Query failed to add user.";
											}
										}
										$adminIndex += 1;
									}
									
									$report = new TaskReport();
									$report->didComplete = $adminAdded;
									
									
									if ($adminAdded == false) {
										$report->errorMessage = "The location \"$locationName\" cannot have any more administrators. Please contact an existing administrator for help with joining the location.";
									}
									
									
									return $report;
								}
							} else {
								$report->errorMessage = "Location not found.";
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
							
							$report = new TaskReport();
							$report->didComplete = false;
							
							if ($deleteResult = mysqli_query($this->mysqli, $deleteQuery)) {
								if (mysqli_affected_rows($this->mysqli) > 0) {
									// invitation deleted
									
									$report->didComplete = true;
								} else {
									$report->errorMessage = "Invitation not found for deletion.";
								}
							} else {
								$report->errorMessage = "Query failed to delete invitation.";
							}
							
							return $report;
							
						}
						
					} else {
						$report->errorMessage = "Invitation not found: " . "$invitationUID, " . "$osuEmail";
					}
				} else {
					$report->errorMessage = "Invitation query error.";
				}
			} else {
				$report->errorMessage = "Account not found.";
			}
			
			
			
			return $report;
		}
		
		
		
		public function getAuthenticatedDevices($forAccountUID) {
			
			// look for device tokens
			$authenticatedDevicesQuery = "
				SELECT *
				FROM `TekBox-App-Tokens`
				WHERE `Account-UID` = '$forAccountUID'
				ORDER BY  `DateTime-Accessed` DESC
			";
			
			if ($authenticatedDevicesResult = mysqli_query($this->mysqli, $authenticatedDevicesQuery)) {
				
				$authenticatedDevices = array();
				$authenticatedDeviceIndex = 0;
				
				while ($authenticatedDeviceRow = mysqli_fetch_row($authenticatedDevicesResult)) {
					
					$device = new MobileDevice();
					$device->model = $authenticatedDeviceRow[3];
					$device->vendorID = $authenticatedDeviceRow[4];
					$device->tokenUID = $authenticatedDeviceRow[1];
					$device->lastAccessed = $authenticatedDeviceRow[6];
					$device->firstAuthenticated = $authenticatedDeviceRow[5];
					
					$authenticatedDevices[$authenticatedDeviceIndex] = $device;
					$authenticatedDeviceIndex += 1;
				}
				
				return $authenticatedDevices;
				
			}
			
			return null;
			
			
		}
		
		
		public function getSetupSessionRow($withSessionUID) {
			$sessionQuery = "
				SELECT * 
				FROM `TekBox-Setup-Sessions` 
				WHERE `Session-UID` = '$withSessionUID'
			";
			
			if ($result = mysqli_query($this->mysqli, $sessionQuery)) {
				if ($sessionRow = mysqli_fetch_row($result)) {
					return $sessionRow;
				} else {
					return null;
				}
				
			} else {
				return null;
			}
		}
		
		public function assignOwnerToLocker($withOwnerUID, $withLockerUID) {
			// update the locker row wih the owner uid
			$updateOwnerQuery = "
				UPDATE `TekBox-Boxes`
				SET `Admin1-UUID` = '$withOwnerUID'
				WHERE `UUID` = '$withLockerUID'
				LIMIT 1
	        ";
	        
	        if ($result = mysqli_query($this->mysqli, $updateOwnerQuery)) {
		        return true;
	        } else {
		        return false;
	        }
		}
		
		
		private function getLocationsResult($forAccountUID) {
			$locationsQuery = "
				SELECT * 
				FROM `TekBox-Clusters` 
				WHERE `Admin1-UUID` = '$forAccountUID'
				OR `Admin2-UUID` = '$forAccountUID'
				OR `Admin3-UUID` = '$forAccountUID'
				OR `Admin4-UUID` = '$forAccountUID'
				OR `Admin5-UUID` = '$forAccountUID'
				OR `Manager1-UUID` = '$forAccountUID'
				OR `Manager2-UUID` = '$forAccountUID'
				OR `Manager3-UUID` = '$forAccountUID'
				OR `Manager4-UUID` = '$forAccountUID'
				OR `Manager5-UUID` = '$forAccountUID'
				OR `Manager6-UUID` = '$forAccountUID'
				OR `Manager7-UUID` = '$forAccountUID'
				OR `Manager8-UUID` = '$forAccountUID'
				OR `Manager9-UUID` = '$forAccountUID'
				OR `Manager10-UUID` = '$forAccountUID'
			";
			
			if ($locationsResult = mysqli_query($this->mysqli, $locationsQuery)) {
				return $locationsResult;
			} else {
				return null;
			}
		}
		
		public function getAuthenticatedLocationRow($withLocationUID, $forAccountUID) {
			$locationQuery = "
				SELECT * 
				FROM `TekBox-Clusters` 
				WHERE `UUID` = '$withLocationUID'
				AND (`Admin1-UUID` = '$forAccountUID'
				OR `Admin2-UUID` = '$forAccountUID'
				OR `Admin3-UUID` = '$forAccountUID'
				OR `Admin4-UUID` = '$forAccountUID'
				OR `Admin5-UUID` = '$forAccountUID'
				OR `Manager1-UUID` = '$forAccountUID'
				OR `Manager2-UUID` = '$forAccountUID'
				OR `Manager3-UUID` = '$forAccountUID'
				OR `Manager4-UUID` = '$forAccountUID'
				OR `Manager5-UUID` = '$forAccountUID'
				OR `Manager6-UUID` = '$forAccountUID'
				OR `Manager7-UUID` = '$forAccountUID'
				OR `Manager8-UUID` = '$forAccountUID'
				OR `Manager9-UUID` = '$forAccountUID'
				OR `Manager10-UUID` = '$forAccountUID')
				LIMIT 1
			";
			if ($locationResult = mysqli_query($this->mysqli, $locationQuery)) {
				if ($locationRow = mysqli_fetch_row($locationResult)) {
					return $locationRow;
				}
			}
			
			return null;
		}
		
		private function getLockerLocationRow($forLockerUID) {
			
			$lockerQuery = "
				SELECT * 
				FROM `TekBox-Boxes` 
				WHERE `UUID` = '$forLockerUID'
				LIMIT 1
			";
			
			if ($lockerResult = mysqli_query($this->mysqli, $lockerQuery)) {
				if ($lockerRow = mysqli_fetch_row($lockerResult)) {
					
					$locationUID = $lockerRow[4];
					
					$locationQuery = "
						SELECT *
						FROM `TekBox-Clusters`
						WHERE `UUID` = '$locationUID'
						LIMIT 1
					";
					
					if ($locationResult = mysqli_query($this->mysqli, $locationQuery)) {
						
						if ($locationRow = mysqli_fetch_row($locationResult)) {
							
							return $locationRow;
						}
					}
				}
				
				
				
			}
			
			return null;
		}
		
		
		private function getLocationLockersResult($forLocationUID) {
			$lockersQuery = "
				SELECT * 
				FROM `TekBox-Boxes` 
				WHERE `Cluster-UUID` = '$forLocationUID'
				ORDER BY  `Row` ASC,
				`Column` ASC
			";
			
			if ($lockersResult = mysqli_query($this->mysqli, $lockersQuery)) {
				return $lockersResult;
			} else {
				return null;
			}
		}
		
		public function getAuthenticatedLocationLockersData($forLocationUID, $forAccountUID) {
			if ($locationRow = $this->getAuthenticatedLocationRow($forLocationUID, $forAccountUID)) {
				// user has permission
				// find lockers
				if ($lockersResult = $this->getLocationLockersResult($forLocationUID)) {
					$lockers = array();
					
					while ($lockerRow = mysqli_fetch_row($lockersResult)) {
						// add the UID to the array
						
						
						$locker = new Locker();
						$locker->uid = $lockerRow[1];
						$locker->name = $lockerRow[0];
						$locker->isLoaded = $lockerRow[9];
						$locker->row = (int)$lockerRow[10];
						$locker->column = (int)$lockerRow[11];
						$locker->summary = NULL;
						
						$lockers[] = $locker;
						
						
					}
					
					//echo "$lockersCount";
					//echo "$openLockerCount";
															
					// end of location
					$lockersData = new LockersData();
					$lockersData->lockers = $lockers;
					
					
					return $lockersData;
				}
			}
		}
		
		
		public function getLockerRow($withLockerUID) {
			
			$lockerQuery = "
				SELECT * 
				FROM `TekBox-Boxes` 
				WHERE `UUID` = '$withLockerUID'
				LIMIT 1
			";
			if ($lockerResult = mysqli_query($this->mysqli, $lockerQuery)) {
				
				if ($lockerRow = mysqli_fetch_row($lockerResult)) {
					
					return $lockerRow;
				}
			}
			
			return null;
		}
		
		
		
		public function getAuthenticatedLockerData($forLockerUID, $forAccountUID) {
			
			if ($lockerRow = $this->getLockerRow($forLockerUID)) {
				
				$locationUID = $lockerRow[4];
				
				if ($locationRow = $this->getAuthenticatedLocationRow($locationUID, $forAccountUID)) {
					// user is authenticated
					$locker = new Locker();
					$locker->uid = $lockerRow[1];
					$locker->name = $lockerRow[0];
					$locker->isLoaded = $lockerRow[9];
					$locker->row = (int)$lockerRow[10];
					$locker->column = (int)$lockerRow[11];
					$locker->summary = NULL;
					$locker->prepMessage = "Test message";
					$locker->orderUID = $lockerRow[13];
					
					return $locker;
				}
			}
			
			return null;
		}
		
		
		
		private function locationCoordinatesAreOpen($forLocationUID, $withRow, $withColumn) {
			// query for lockers with matching locationuid, and coordinates
			// if no matches, it's open -> return true
			$coordinatesQuery = "
				SELECT *
				FROM `TekBox-Boxes`
				WHERE `Cluster-UUID` = '$forLocationUID'
				AND `Row` = $withRow
				AND `Column` = $withColumn
			";
			
			if ($coordinatesResult = mysqli_query($this->mysqli, $coordinatesQuery)) {
				// see if it's an open spot
				if (mysqli_num_rows($coordinatesResult) == 0) {
					
					return true;
				}
			}
			
			return false;
		}
		
		
		private function getLocationRow($withLocationUID) {
			$locationQuery = "
				SELECT *
				FROM `TekBox-Clusters`
				WHERE `UUID` = '$withLocationUID'
				LIMIT 1
			";
			
			if ($locationResult = mysqli_query($this->mysqli, $locationQuery)) {
				if ($locationRow = mysqli_fetch_row($locationResult)) {
					return $locationRow;
				}
			}
			
			return null;
		}
		
		
		private function moveLockersForNewColumn($forLocationUID, $forLockerUID) {
			// update all lockers by incrementing their column values by one.
			$moveQuery = "
				UPDATE `TekBox-Boxes`
				SET `Column` = `Column` + 1 
				WHERE `Cluster-UUID` = '$forLocationUID'
			";
			
			if ($moveResult = mysqli_query($this->mysqli, $moveQuery)) {
				return true;
			}
			
			return false;
		}
		
		
		private function moveLockersForNewRow($forLocationUID, $forLockerUID) {
			// update all lockers by incrementing their column values by one.
			$moveQuery = "
				UPDATE `TekBox-Boxes`
				SET `Row` = `Row` + 1 
				WHERE `Cluster-UUID` = '$forLocationUID'
			";
			
			if ($moveResult = mysqli_query($this->mysqli, $moveQuery)) {
				return true;
			}
			
			return false;
		}
		
		
		private function incrementLocationDimensions($locationUID, $rowDelta, $columnDelta) {
			$dimensionsQuery = "
				UPDATE `TekBox-Clusters`
				SET `Rows` = `Rows` + $rowDelta,
				`Columns` = `Columns` + $columnDelta
				WHERE `UUID` = '$locationUID'
				LIMIT 1
			";
			
			if ($dimensionsResult = mysqli_query($this->mysqli, $dimensionsQuery)) {
				return true;
			}
			
			return false;
		}
		
		
		
		private function updateLocationRowsAndColumns($forLocationUID, $newLockerRow, $newLockerColumn) {
			
			// get the current number of rows and columns
			if ($locationRow = $this->getLocationRow($forLocationUID)) {
				
				$rowCount = $locationRow[2];
				$columnCount = $locationRow[3];
				
				
				$rowDelta = 0;
				$columnDelta = 0;
				
				$shouldContinue = true;
				
				if ($newLockerColumn == -1) {
					// need to push lockers over one row, and increase the location's columns by one
					$columnDelta = 1;
					
					// update all lockers in the location
					if ($this->moveLockersForNewColumn($forLocationUID)) {
						// moved locker columns
						$shouldContinue = true;
					} else {
						$shouldContinue = false;
					}
					
				} else if ($newLockerColumn >= $columnCount) {
					// make room on the right for the new locker
					$columnDelta = 1;
					
					
				}
				
				if ($newLockerRow == -1) {
					$rowDelta = 1;
					
					// move lockers for new row
					if ($this->moveLockersForNewRow($forLocationUID)) {
						$shouldContinue = true;
					} else {
						$shouldContinue = false;
					}
				}
				
				
				
				if ($shouldContinue == true) {
					// update the location's rows and columns
					return $this->incrementLocationDimensions($forLocationUID, $rowDelta, $columnDelta);
					
				}
				
			}
			
			
			return false;
			
			
		}
		
		
		public function createNewLocation($withName, $withLatitude, $withLongitude, $withAdminUID) {
			// create a new uid for the location
			$newUID = substr(sha1(time()), 0, 16);
			
			$insertNewLocationQuery = "
				INSERT INTO `" . DB_NAME . "`.`TekBox-Clusters` (`Name`, `UUID`, `Latitude`, `Longitude`, `Admin1-UUID`)
				VALUES ('$withName', '$newUID', '$withLatitude', '$withLongitude', '$withAdminUID');
			";
			
			// do the query
			if ($insertResult = mysqli_query($this->mysqli, $insertNewLocationQuery)) {
				// successful query
				return $newUID;
			}
			return null;
		}
		
		
		public function addLockerToLocation($forLockerUID, $forLocationUID, $withRow, $withColumn) {
			// make sure the coordinates are open
			if ($this->locationCoordinatesAreOpen($forLocationUID, $withRow, $withColumn) == true) {
				// update the locker row with the coordinates and location uid
				$realRow = $withRow + 1;
				$realColumn = $withColumn + 1;
				$updateLockerQuery = "
					UPDATE `TekBox-Boxes`
					SET `Cluster-UUID` = '$forLocationUID',
					`Row` = $withRow,
					`Column` = $withColumn
					WHERE `UUID` = '$forLockerUID'
					LIMIT 1
				";
				
				if ($updateResult = mysqli_query($this->mysqli, $updateLockerQuery)) {
					if (mysqli_affected_rows($this->mysqli) > 0) {
						// locker updated, so update the location rows and columns counts
						if ($this->updateLocationRowsAndColumns($forLocationUID, $withRow, $withColumn) == true) {
							// successfully updated location and its existing lockers
							return true;
						}
					}
				}
			}
			
			return false;
		}
		
		public function getOfflineKeySecret($forLockerUID) {
// 			echo "getOfflineKeySecret()";
			$offlineKeyQuery = "
	        	SELECT * 
				FROM `TekBox-Offline-Keys` 
				WHERE `Locker-UID` = '$forLockerUID'
				LIMIT 1
	        ";
	        
	        if ($offlineKeyResult = mysqli_query($this->mysqli, $offlineKeyQuery)) {
		        if ($offlineKeyRow = mysqli_fetch_row($offlineKeyResult)) {
			        $secret = $offlineKeyRow[1];
			        return $secret;
		        }
	        }
	        
	        return null;
		}
		
		
		public function generateOfflineKey($forLockerUID) {
// 			echo "generateOfflineKey()";
			$secret = bin2hex(random_bytes(8));
			
			$expires = null;
			
			// add it to the database
			$insertOfflineKeyQuery = "
				INSERT INTO `" . DB_NAME . "`.`TekBox-Offline-Keys` (`Locker-UID`, `Secret`, `Expires`)
				VALUES ('$forLockerUID', '$secret', '$expires');
			";
			
			if ($offlineKeyResult = mysqli_query($this->mysqli, $insertOfflineKeyQuery)) {
				return $secret;
			}
			
			return null;
		}
		
		
		
		
		public function disarmLockerForAccess($forOrderUID, $withEmail, $dateTime, $ip) {
			// first, make sure the order is associated with the email
			if ($orderRow = $this->getOrderRowWithEmail($forOrderUID, $withEmail)) {
				// it's the right email address, so find the locker and disarm it.
				$lockerUID = $orderRow[7];
				
				if ($lockerRow = $this->getLockerRow($lockerUID)) {
					// found the locker, so no weird errors.
					// Log the request
					
					// generate requestUUID
					$requestUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
					
					$requestType = "DISARM_PICKUP";
					
					$unlockForMinutes = 1;
					
					if ($locationRow = $this->getLockerLocationRow($lockerUID)) {
						
						$unlockForMinutes = $locationRow[4];
						
						
						$locationUID = $locationRow[1];
						
						
						// log the request
						$insertDisarmActionQuery = "
							INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Box-UUID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Response`, `Confirmation`, `New-IP`, `Modifier-UUID`)
							VALUES ('$lockerUID', '$requestUID', '$dateTime', '$requestType', '$ip', '$unlockForMinutes', '$unlockForMinutes', 0, '$withEmail');
						";
						
						if($insertResult = mysqli_query($this->mysqli, $insertDisarmActionQuery)) {
							// successfully logged request
							
							
							// disarm the box
							
							
							$unlockUntil = date("Y-m-d H:i:s", time() + $unlockForMinutes * 60);
							
							$shouldPrep = 0;
							
							if ($forPrep == true) {
								// watch for case where the box is already loaded and should remain armed until user chooses otherwise
								$shouldPrep = 1;
								
								if ($lockerIsLoaded) {
									$unlockUntil = date("Y-m-d H:i:s", time() - 1);
									$shouldPrep = 0;
								}
							}
							
							
							
							
							$updateUnlockQuery = "
								UPDATE `TekBox-Boxes`
								SET `Unlock-Until` = '$unlockUntil',
								`Should-Prep` = '$shouldPrep'
								WHERE `UUID` = '$lockerUID'
								AND `Cluster-UUID` = '$locationUID'
								LIMIT 1
					        ";
					        
					        if ($updateUnlockResult = mysqli_query($this->mysqli, $updateUnlockQuery)) {
						        // verify that the box was disarmed
						        
						        
						        $disarmVerificationQuery = "
						        	SELECT * 
									FROM `TekBox-Boxes` 
									WHERE `Cluster-UUID` = '$locationUID'
									AND `UUID` = '$lockerUID'
									LIMIT 1
						        ";
						        
						        
						        if ($lockerResult = mysqli_query($this->mysqli, $disarmVerificationQuery)) {
							        
							        $lockerCount = mysqli_num_rows($lockerResult);
									
									if ($lockerCount > 0) {
										
										$lockerRow = mysqli_fetch_row($lockerResult);
										
										$lockerShouldUnlock = false;
										
										
										$unlockUntil = $lockerRow[12];
										
										if ($unlockUntil > $dateTime) {
											$lockerShouldUnlock = true;
										}
										
										
										if ($lockerShouldUnlock) {
											$lockerUID = $lockerRow[1];
											
											$lockerName = $lockerRow[0];
											
											return $unlockForMinutes;
												
										} else {
											//echo "Failed to disarm box. Please try again.";
										}
										
									}
						        }
						        
						        
							} else {
						        echo "Query failed to disarm box. Please try again, or contact an administrator.";
					        }
							
						} else {
							echo "request query failed.";
							
							
						}
						
						
						
						
					}
					
				}
			}
			return null;
		}
		
		public function disarmLocker($withLockerUID, $userRow, $forPrep, $dateTime, $ip) {
			
			// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ insert disarm action for activity log
			$osuUID = $userRow[2];
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
		        
		        if ($updateUnlockResult = mysqli_query($this->mysqli, $updateIPQuery)) {
			        // query was successful. Check for update
			        //echo "DONE\r\n";
		        } else {
			        //echo "ERROR\r\n";
		        }
			}
			
			
			// generate requestUUID
			$requestUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
			
			$requestType = "DISARM";
			
			$unlockForMinutes = 1;
			
			if ($locationRow = $this->getLockerLocationRow($withLockerUID)) {
				
				$unlockForMinutes = $locationRow[4];
				
				
				$locationUID = $locationRow[1];
				
				
				
				// log the request
				$insertDisarmActionQuery = "
					INSERT INTO `" . DB_NAME . "`.`TekBox-Requests` (`Box-UUID`, `Request-UUID`, `DateTime`, `Request`, `IP-Address`, `Response`, `Confirmation`, `New-IP`, `Modifier-UUID`)
					VALUES ('$withLockerUID', '$requestUID', '$dateTime', '$requestType', '$ip', '$unlockForMinutes', '$unlockForMinutes', '$ipIsNew', '$osuUID');
				";
				
				if($insertResult = mysqli_query($this->mysqli, $insertDisarmActionQuery)) {
					// successfully logged request
					
					
					// disarm the box
					
					
					$unlockUntil = date("Y-m-d H:i:s", time() + $unlockForMinutes * 60);
					
					$shouldPrep = 0;
					
					if ($forPrep == true) {
						// watch for case where the box is already loaded and should remain armed until user chooses otherwise
						$shouldPrep = 1;
						
						if ($lockerIsLoaded) {
							$unlockUntil = date("Y-m-d H:i:s", time() - 1);
							$shouldPrep = 0;
						}
					}
					
					
					
					
					$updateUnlockQuery = "
						UPDATE `TekBox-Boxes`
						SET `Unlock-Until` = '$unlockUntil',
						`Should-Prep` = '$shouldPrep'
						WHERE `UUID` = '$withLockerUID'
						AND `Cluster-UUID` = '$locationUID'
						LIMIT 1
			        ";
			        
			        if ($updateUnlockResult = mysqli_query($this->mysqli, $updateUnlockQuery)) {
				        // verify that the box was disarmed
				        
				        
				        $disarmVerificationQuery = "
				        	SELECT * 
							FROM `TekBox-Boxes` 
							WHERE `Cluster-UUID` = '$locationUID'
							AND `UUID` = '$withLockerUID'
							LIMIT 1
				        ";
				        
				        
				        if ($lockerResult = mysqli_query($this->mysqli, $disarmVerificationQuery)) {
					        
					        $lockerCount = mysqli_num_rows($lockerResult);
							
							if ($lockerCount > 0) {
								
								$lockerRow = mysqli_fetch_row($lockerResult);
								
								$lockerShouldUnlock = false;
								
								
								$unlockUntil = $lockerRow[12];
								
								if ($unlockUntil > $dateTime) {
									$lockerShouldUnlock = true;
								}
								
								
								if ($lockerShouldUnlock) {
									$lockerUID = $lockerRow[1];
									
									$lockerName = $lockerRow[0];
									
									
										
								} else {
									//echo "Failed to disarm box. Please try again.";
								}
								
								
								
								$locker->uid = $lockerRow[1];
								$locker->name = $lockerRow[0];
								$locker->isLoaded = $lockerRow[9];
								$locker->row = (int)$lockerRow[10];
								$locker->column = (int)$lockerRow[11];
								$locker->summary = NULL;
								
								$locker->prepMessage = "Test message";
								$locker->orderUID = $lockerRow[13];
								
								$locker->disarmPeriod = $unlockForMinutes;
								
								//echo "right place";
								
								return $locker;
								
							}
				        }
				        
				        
					} else {
				        echo "Query failed to disarm box. Please try again, or contact an administrator.";
			        }
					
				} else {
					echo "request query failed.";
					
					
				}
				
				
				
				
			}
			
			
		}
		
		
		
		
		
		public function getLocationsData($forAccountUID) {
			
			if ($locationsResult = $this->getLocationsResult($forAccountUID)) {
				// JSON stuff
				$locations = array();
				//$unlocatedLocations = array();
				
				
				// look for lockers belonging to the locations
				while ($locationRow = mysqli_fetch_row($locationsResult)) {
					// store locker UUID values for activity query
					
					$locationUID = $locationRow[1];
					
					// make new location
					$location = new Location();
					$location->uid = $locationRow[1];
					$location->name = $locationRow[0];
					$location->rows = (int)$locationRow[2];
					$location->columns = (int)$locationRow[3];
					$location->latitude = $locationRow[6];
					$location->longitude = $locationRow[7];
					
					
					
					$locationUIDArray[$locationUIDArrayIndex] = $locationUID;
					$locationNameFromUUID["$locationUID"] = $locationName;
					
					$locationUIDArrayIndex += 1;
					
					// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ sort by position in location
					
					
					if ($lockersResult = $this->getLocationLockersResult($locationUID)) {
						// query was successful
						
						$lockersCount = mysqli_num_rows($lockersResult);
						
						if ($lockersCount >= 0) {
							
							$openLockerCount = 0;
							$loadedLockerCount = 0;
							
							while ($lockerRow = mysqli_fetch_row($lockersResult)) {
								// add the UUID to the array
								
								
								$lockerIsLoaded = $lockerRow[9];
								if ($lockerIsLoaded == 1) {
									$loadedLockerCount = $loadedLockerCount + 1;
								} else {
									$openLockerCount = $openLockerCount + 1;
								}
								
								
							}
							
							//echo "$lockersCount";
							//echo "$openLockerCount";
																	
							// end of location
							$location->openLockerCount = $openLockerCount;
							$location->loadedLockerCount = $loadedLockerCount;
							
							
							// add location to array
							$locations[] = $location;
							
						} else {
							//return null;
							//echo "No lockers were found." . "<br>";
						}
					} else {
						//echo "Query failed for lockers." . "<br>";
					}
				}
				
				
				// got through all locations
				$locationsData = new LocationsData();
				$locationsData->locations = $locations;
				
				return $locationsData;
			}
			
			return null;
		}
		
		
		public function getOrderRow($withOrderUID) {
			$orderQuery = "
				SELECT *
				FROM `TekBox-Orders`
				WHERE `UUID` = '$withOrderUID'
				LIMIT 1
			";
			
			if ($orderResult = mysqli_query($this->mysqli, $orderQuery)) {
				if ($orderRow = mysqli_fetch_row($orderResult)) {
					return $orderRow;
				}
			}
			
			return null;
		}
		
		public function getOrdersResultSorted($forEmail) {
			$ordersQuery = "
				SELECT *
				FROM `TekBox-Orders`
				WHERE `Customer-Email` = '$forEmail'
				ORDER BY  `Is-Active` DESC,
				`DateTime` DESC
			";
			
			if ($ordersResult = mysqli_query($this->mysqli, $ordersQuery)) {
				// check that orders exist
				if (mysqli_num_rows($ordersResult) > 0) {
					return $ordersResult;
				}
			}
			
			return null;
		}
		
		public function getOrdersResultActive($forEmail) {
			$ordersQuery = "
				SELECT *
				FROM `TekBox-Orders`
				WHERE `Customer-Email` = '$forEmail'
				AND `Is-Active` = 1
				ORDER BY  `DateTime` DESC
			";
			
			if ($ordersResult = mysqli_query($this->mysqli, $ordersQuery)) {
				// check that orders exist
				if (mysqli_num_rows($ordersResult) > 0) {
					return $ordersResult;
				}
			}
			
			return null;
		}
		
		public function getOrderRowWithEmail($forOrderUID, $withEmail) {
			$orderQuery = "
				SELECT *
				FROM `TekBox-Orders`
				WHERE `Customer-Email` = '$withEmail'
				AND `UUID` = '$forOrderUID'
				LIMIT 1
			";
			
			if ($orderResult = mysqli_query($this->mysqli, $orderQuery)) {
				// check that order exists
				if ($orderRow = mysqli_fetch_row($orderResult)) {
						return $orderRow;
					}
			}
			
			return null;
		}
		
		
		
		
		public function getOrderDetail($forOrderUID, $withAccountUID) {
			
			if ($orderRow = $this->getOrderRow($forOrderUID)) {
				
				$lockerUID = $orderRow[7];
				if ($lockerRow = $this->getLockerRow($lockerUID)) {
					
					$locationUID = $lockerRow[4];
					if ($locationRow = $this->getAuthenticatedLocationRow($locationUID, $withAccountUID)) {
						// user is authenticated for the locker, so return the order info
						
						$order = new OrderDetail();
						$order->uid = $orderRow[0];
						$order->orderNumber = $orderRow[1];
						$order->loadedDateTime = $orderRow[6];
						
						
						$orderIsActive = $orderRow[4];
						
						if (!$orderIsActive) {
							$order->unloadedDateTime = $orderRow[8];
						}
						
						$order->lockerUID = $lockerUID;
						$order->lockerName = $lockerRow[0];
						$order->locationName = $locationRow[0];
						$order->customerName = $orderRow[2];
						$order->customerEmail = $orderRow[3];
						
						return $order;
						
					}
				}
			}
			
			return null;
		}
		
		
		public function getOrdersData($forAccountUID) {
			
			if ($locationsResult = $this->getLocationsResult($forAccountUID)) {
				if (mysqli_num_rows($locationsResult) > 0) {
				
					// store locker UID values for activity query
					$lockerUIDArray = array();
					$lockerUIDArrayIndex = 0;
					
					// keeping track of locker names in parallel for printing to page.
					$lockerNamesFromUID = array();
					// keeping track of locker locations as well
					$lockerLocationUIDfromUID = array();
					
					
					
					// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ store location names/UIDs for filtering
					$locationUIDArray = array();
					$locationUIDArrayIndex = 0;
					
					$locationNameFromUID = array();
					
					// look for lockers belonging to the locations
					while ($locationRow = mysqli_fetch_row($locationsResult)) {
						// store locker UID values for activity query
						
						$locationUID = $locationRow[1];
						$locationName = $locationRow[0];
						
						$locationUIDArray[$locationUIDArrayIndex] = $locationUID;
						$locationNameFromUID["$locationUID"] = $locationName;
						
						$locationUIDArrayIndex += 1;
						
						$lockersQuery = "
							SELECT * 
							FROM `TekBox-Boxes` 
							WHERE `Cluster-UUID` = '$locationUID'
						";
						
						if ($lockersResult = mysqli_query($this->mysqli, $lockersQuery)) {
							// query was successful
							
							$lockersCount = mysqli_num_rows($lockersResult);
							
							if ($lockersCount > 0) {
								// lockers were found for the location UID
								
								while ($lockerRow = mysqli_fetch_row($lockersResult)) {
									// add the UID to the array
									
									$lockerUID = $lockerRow[1];
									
									$lockerUIDArray[$lockerUIDArrayIndex] = $lockerUID;
									
									$lockerName = $lockerRow[0];
									
									$lockerNamesFromUID["$lockerUID"] = $lockerName;
									
									$lockerLocationUID = $lockerRow[4];
									
									$lockerLocationUIDfromUID["$lockerUID"] = $lockerLocationUID;
									
									
									$lockerUIDArrayIndex += 1;
								}
							} else {
								// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ include this in the JSON somewhere instead.
								//echo "No lockers were found." . "<br>";
							}
						} else {
							echo "Query failed for lockers." . "<br>";
						}
					}
					
					$lockerUIDArrayCount = count($lockerUIDArray);
					
					if ($lockerUIDArrayCount > 0) {
						// lockers were found in the locations. Construct query for activity
						
						//echo "Found $lockerUIDArrayCount lockers." . "<br>";
						
						$lockerUID = $lockerUIDArray[0];
						
						$ordersQuery = "
							SELECT * 
							FROM `TekBox-Orders` 
							WHERE `DateTime` IS NOT NULL 
							AND (`Box-UUID` = '$lockerUID'
						";
						
						// finish constructing the query with all lockerUID values
						$lockerUIDArrayIndex = 1;
						
						while ($lockerUIDArrayIndex < $lockerUIDArrayCount) {
							
							//echo "adding to query";
							// add the next uid to the query
							$lockerUID = $lockerUIDArray[$lockerUIDArrayIndex];
							
							$ordersQuery .= " OR `Box-UUID` = '$lockerUID'";
							
							$lockerUIDArrayIndex += 1;
						}
						
						
						// sort in reverse-chronological order, finishing the query
						$ordersQuery .= ")
							ORDER BY  `Is-Active` DESC,
							`DateTime` DESC
						";
						
						//echo "Query: $requestsQuery" . "<br>";
						
						// query for activity
						if ($ordersResult = mysqli_query($this->mysqli, $ordersQuery)) {
							
							
							// query was successful
							$ordersCount = mysqli_num_rows($ordersResult);
							
							if ($ordersCount > 0) {
								// orders were found
								
								// ~~~~~~~~~~~~~~~~~~~~~~~ time to print the activity to the page.
								
								$dateProgress = date("Y");
						
								$thisYear = date("Y");
								
								$yesterday = date("F j, Y", time() - 60 * 60 * 24);
								
								$isFirstRow = true;
								
								$wasActive = true;
								
								
								// JSON stuff
								
								$activeThenInactiveOrders = array();
								
								
								$orderCategory = new OrderCategory();
								$orderCategory->status = "Active";
								$orderCategory->orders = array();
								
								
								while ($orderRow = mysqli_fetch_row($ordersResult)) {
									// printing row to page
									
									$rowDateTime = $orderRow[6];
									
									$dt = new DateTime($rowDateTime);
									
									$rowDate = $dt->format('F j, Y');
									
									$rowYear = $dt->format('Y');
									
									$rowTime = $dt->format('g:i a');
									
									$rowIsActive = $orderRow[4];
									
									
									
									
									if (!$rowIsActive && $wasActive) {
										// first non-active row
										//echo "<h3>Previous Orders $rowDateTime</h3>";
										if (!$isFirstRow) {
											$activeThenInactiveOrders[] = $orderCategory;
											
											$orderCategory = new OrderCategory();
										}
										
										$orderCategory->status = "Inactive";
										$wasActive = false;
									}
									
									
									if ($isFirstRow) {
										if ($rowIsActive) {
											//print "<h3>Active Orders</h3>";
											$orderCategory->status = "Active";
											$wasActive = true;
										}
										$isFirstRow = false;
									}
									
									
									/*
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
									*/
									$orderUID = $orderRow[0];
									$orderNumber = $orderRow[1];
									
									$lockerUID = $orderRow[7];
									
									$lockerName = $lockerNamesFromUID["$lockerUID"];
									
									$locationUID = $lockerLocationUIDfromUID["$lockerUID"];
									
									$locationName = $locationNameFromUID["$locationUID"];
									
									//print "<div class = \"activityRow\"><a href = \"./order?orderUID=$orderUID\">Order #$orderNumber</a> — $lockerName ($locationName) — Loaded $rowDateTime";
									
									
									// JSON stuff
									$orderEntry = new OrderEntry();
									$orderEntry->uid = $orderUID;
									$orderEntry->orderNumber = $orderNumber;
									$orderEntry->loadedDateTime = $rowDateTime;
									$orderEntry->lockerName = $lockerName;
									$orderEntry->locationName = $locationName;
									
									
									
									if (!$rowIsActive) {
										$unloadTime = $orderRow[8];
										//print" — Unloaded $unloadTime";
										$orderEntry->unloadedDateTime = $unloadTime;
									}
									
									$orderCategory->orders[] = $orderEntry;
									
									
									
									
								}
								
								//$orderCategory->orders = $categoryOrders;
								$activeThenInactiveOrders[] = $orderCategory;
								
								$orderData = new OrderData();
								$orderData->activeThenInactiveOrders = $activeThenInactiveOrders;
								
								
								return $orderData;
								
								
								
								//echo "hi";
								
							} else {
								//echo "No activity found for your lockers." . "<br>";
							}
							
							
							
						} else {
							echo "Query failed for activity." . "<br>";
						}
						
						
					} else {
						echo "No lockers were found for your account." . "<br>";
					}
				}
			}
			
			
			
			return null;
		}
		
		
		
		
		
		public function addOrder($orderNumber, $customerName, $customerEmail, $lockerUID, $dateTime) {
			
			if ($lockerRow = $this->getLockerRow($lockerUID)) {
				//echo "insert";
				$lockerIsLoaded = $lockerRow[9];
				// validate the values
				$formIsValid = true;
				
				
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
				
				if ($formIsValid && $lockerIsLoaded && !$lockerShouldUnlock) {
					
					
					// generate order UUID
					$orderUID = substr(sha1(time()), 0, 16);//bin2hex(random_bytes(8));
					
					// ~~~~~~~~~~~~~~~~~~~ handle case of duplicate active order number (make sure they really want to add another)
					// // ~~~~~~~~~~~~~~~~~~~~ one important case could be that an order takes up multiple lockers.
					
					// insert order
					$insertOrderQuery = "
						INSERT INTO `" . DB_NAME . "`.`TekBox-Orders` (`UUID`, `Order-Number`, `Customer-Name`, `Customer-Email`, `Is-Active`, `Email-Did-Send`, `DateTime`, `Box-UUID`)
						VALUES ('$orderUID', '$orderNumber', '$customerName', '$customerEmail', 1, 0, '$dateTime', '$lockerUID');
					";
					
					if($insertOrderResult = mysqli_query($this->mysqli, $insertOrderQuery)) {
						// successfully logged request
						
						
						$shouldNotUnlockDate = date("Y-m-d H:i:s", time() - 1);
						
						// update box with order uid, should unlock, and isLoaded
						$updateLockerQuery = "
								UPDATE `TekBox-Boxes`
								SET `Unlock-Until` = '$shouldNotUnlockDate',
								`Order-UUID` = '$orderUID' 
								WHERE `UUID` = '$lockerUID'
								LIMIT 1
						";
						
						if ($lockerUpdateResult = mysqli_query($this->mysqli, $updateLockerQuery)) {
							// successfully updated box
							
							// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ UPDATE ACTIVITY LOG WITH USER LOADING BOX
							
							// send email to customer
							$emailSubject = TEKBOX_EMAIL_ORDER_READY_SUBJECT;
							
							$emailBody = TEKBOX_EMAIL_ORDER_READY_BODY;
							
							$emailHeaders = "From: " . TEKBOX_EMAIL_FROM_ADDRESS . "\r\n";
							
							
							
							if (mail($customerEmail, $emailSubject, $emailBody, $emailHeaders)) {
								
								
								// update order with email sent outcome
								$updateOrderQuery = "
									UPDATE `TekBox-Orders`
									SET `Email-Did-Send` = 1 
									WHERE `UUID` = '$orderUID'
									LIMIT 1
								";
		
								
								if ($orderUpdateResult = mysqli_query($this->mysqli, $updateOrderQuery)) {
									// re-read box row to update info for response
									
									$lockerQuery = "
										SELECT * 
										FROM `TekBox-Boxes` 
										WHERE `UUID` = '$lockerUID'
										LIMIT 1
									";
									
									if ($lockerResult = mysqli_query($this->mysqli, $lockerQuery)) {
										// query was successful
										
										$lockerCount = mysqli_num_rows($lockerResult);
										
										if ($lockerCount > 0) {
											$lockerRow = mysqli_fetch_row($lockerResult);
											
											// update the box info
											$locker->uid = $lockerRow[1];
											$locker->name = $lockerRow[0];
											$locker->isLoaded = $lockerRow[9];
											$locker->row = (int)$lockerRow[10];
											$locker->column = (int)$lockerRow[11];
											$locker->summary = NULL;
											
											$locker->prepMessage = "Test message";
											$locker->orderUID = $lockerRow[13];
											
											
											return $locker;
											
											$jsonString = json_encode($locker);
											
											echo $jsonString;
										}
									}
									
									
									
									
								}
								
								
								
								
							}
							
							
							
							
						}
						
						
						
					}
					
				}

			}
			return null;
		}
		
		
		
		
		
		
		public function getActivityData($withAccountUID) {
			//echo "getActivityData()";
			if ($locationsResult = $this->getLocationsResult($withAccountUID)) {
				if (mysqli_num_rows($locationsResult) > 0) {
					// store locker UID values for activity query
					$lockerUIDArray = array();
					$lockerUIDArrayIndex = 0;
					
					// keeping track of locker names in parallel for printing to page.
					$lockerNamesFromUID = array();
					// keeping track of locker locations as well
					$lockerLocationUIDfromUID = array();
					
					
					
					// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ store location names/UIDs for filtering
					$locationUIDArray = array();
					$locationUIDArrayIndex = 0;
					
					$locationNameFromUID = array();
					
					
					// look for lockers belonging to the locations
					while ($locationRow = mysqli_fetch_row($locationsResult)) {
						// store locker UID values for activity query
						
						$locationUID = $locationRow[1];
						$locationName = $locationRow[0];
						
						$locationUIDArray[$locationUIDArrayIndex] = $locationUID;
						$locationNameFromUID["$locationUID"] = $locationName;
						
						$locationUIDArrayIndex += 1;
						
						$lockersQuery = "
							SELECT * 
							FROM `TekBox-Boxes` 
							WHERE `Cluster-UUID` = '$locationUID'
						";
						
						
						
						if ($lockersResult = mysqli_query($this->mysqli, $lockersQuery)) {
							// query was successful
							
							$lockersCount = mysqli_num_rows($lockersResult);
							
							if ($lockersCount > 0) {
								// lockers were found for the location UID
								
								while ($lockerRow = mysqli_fetch_row($lockersResult)) {
									// add the UID to the array
									
									$lockerUID = $lockerRow[1];
									
									$lockerUIDArray[$lockerUIDArrayIndex] = $lockerUID;
									
									$lockerName = $lockerRow[0];
									
									$lockerNamesFromUID["$lockerUID"] = $lockerName;
									
									$lockerLocationUID = $lockerRow[4];
									
									$lockerLocationUIDfromUID["$lockerUID"] = $lockerLocationUID;
									
									$lockerUIDArrayIndex += 1;
								}
							} else {
								// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ include this in the JSON somewhere instead.
								//echo "ERROR: No lockers were found.";
							}
						} else {
							echo "ERROR: Query failed for lockers." . "<br>";
						}
					}
					
					
					
					
					$lockerUIDArrayCount = count($lockerUIDArray);
					
					if ($lockerUIDArrayCount > 0) {
						// lockers were found in the locations. Construct query for activity
						
						//echo "Found $lockerUIDArrayCount lockers." . "<br>";
						
						$lockerUID = $lockerUIDArray[0];
						
						$requestsQuery = "
							SELECT * 
							FROM `TekBox-Requests` 
							WHERE `DateTime` IS NOT NULL 
							AND ((`Box-UUID` = '$lockerUID'
						";
						
						// finish constructing the query with all lockerUID values
						$lockerUIDArrayIndex = 1;
						
						while ($lockerUIDArrayIndex < $lockerUIDArrayCount) {
							
							//echo "adding to query";
							// add the next uuid to the query
							$lockerUID = $lockerUIDArray[$lockerUIDArrayIndex];
							
							$requestsQuery .= " OR `Box-UUID` = '$lockerUID'";
							
							$lockerUIDArrayIndex += 1;
						}
						
						
						
						
						
						$locationUID = $locationUIDArray[0];
						// sort in reverse-chronological order, finishing the query
						$requestsQuery .= ")
							OR (`Location-UID` = '$locationUID'
						";
						
						// finish constructing the query with all boxUUID values
						$locationUIDArrayIndex = 1;
						$locationUIDArrayCount = count($locationUIDArray);
						
						while ($locationUIDArrayIndex < $locationUIDArrayCount) {
							
							//echo "adding to query";
							// add the next uuid to the query
							$locationUID = $locationUIDArray[$locationUIDArrayIndex];
							
							$requestsQuery .= " OR `Location-UID` = '$locationUID'";
							
							$locationUIDArrayIndex += 1;
						}
						
						
						
						
						
						// sort in reverse-chronological order, finishing the query
						$requestsQuery .= "))
							ORDER BY  `DateTime` DESC
						";
						
						//echo "Query: $requestsQuery" . "<br>";
						
						// query for activity
						if ($requestsResult = mysqli_query($this->mysqli, $requestsQuery)) {
							// query was successful
							$requestsCount = mysqli_num_rows($requestsResult);
							
							if ($requestsCount > 0) {
								// requests were found
								
								// ~~~~~~~~~~~~~~~~~~~~~~~ time to print the activity to the page.
								
								$dateProgress = date("Y");
						
								$thisYear = date("Y");
								
								$yesterday = date("F j, Y", time() - 60 * 60 * 24);
								
								
								
								
								// stuff to create JSON
								$activityDays = array();
								
								$activityDay = new ActivityDay();
								
								$isFirst = true;
								
								
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
											//print "<br>";
										}
										
										
										if ($rowDate == $today) {
											//print "<h3>Today</h3>";
										} else if ($rowDate == $yesterday) {
											//print "<h3>Yesterday</h3>";
										} else if ($rowYear == $thisYear) {
											$currentDate = $dt->format('F j');
											//print "<h3>$currentDate</h3>";
										} else {
											//print "<h3>$rowDate</h3>";
											
										}
										
										if ($isFirst) {
											$isFirst = false;
										} else {
											// adding the previous day
											$activityDays[] = $activityDay;
										}
										
										
										// Initializing new day for JSON stuff
										$activityDay = new ActivityDay();
										$activityDay->dateTime = $rowDateTime; // ~~~~~~~~~~~~~~~~~~~~~~~ make sure this is GMT at some point
										$activityDay->entries = array();
										
										
										
										
										$dateProgress = $rowDate;
									}
									
									$lockerUID = $requestRow[0];
									
									$lockerName = $lockerNamesFromUID["$lockerUID"];
									
									$locationUID = $lockerLocationUIDfromUID["$lockerUID"];
									
									if ($requestRow[10] != NULL) {
										$locationUID = $requestRow[10];
									}
									
									$locationName = $locationNameFromUID["$locationUID"];
									
									
									
									//print "<div class = \"activityRow\">$rowTime — $lockerName ($locationName)";
									
									
									// Creating entry for row for JSON
									$activityEntry = new ActivityEntry();
									$activityEntry->uid = $requestRow[1];
									$activityEntry->dateTime = $rowDateTime;
									$activityEntry->lockerName = $lockerName;
									$activityEntry->locationName = $locationName;
									
									// test stuff to see if it's working
									//$activityEntry->summary = "This is the summary.";
									//$activityEntry->details = "These are details.";
									
									
									
									
									
									// look for notices
									if ($requestRow[8]) {
										// New IP
										//echo "<span class=\"StatusBoxGray\">New IP Address</span>";
										
										// for JSON
										$activityEntry->type = "NOTICE";
										$activityEntry->notices = array();
										$activityEntry->notices[] = "New IP Address";
									}
									
									
									
									$serverResponse = $requestRow[5];
									$rowConfirmation = $requestRow[6];
									
									
									// look for errors
									if ($requestRow[7] || ($serverResponse != $rowConfirmation)) {
										
										$rowError = $requestRow[7];
										
										$rowErrorString = "Error: $rowError";
										
										
										// override any existing type with priority type
										$activityEntry->type = "ALERT";
										
										$activityEntry->alerts = array();
										
										
										if ($rowError == "AUTH") {
											$rowErrorString = "Authentication Error";
											$activityEntry->alerts[] = $rowErrorString;
										} else if ($rowError == "CONF") {
											$rowErrorString = "Confirmation Error";
											$activityEntry->alerts[] = $rowErrorString;
										} else if ($rowError == "LOCKED_OPEN") {
											$rowErrorString = "Locked Open Error";
											$activityEntry->alerts[] = $rowErrorString;
										} else if ($serverResponse != $rowConfirmation) {
											// no/unexpected confirmation is also a confirmation error
											//echo "<span class=\"StatusBoxRed\">Confirmation Error</span>";
											$rowErrorString = "Confirmation Error";
											$activityEntry->alerts[] = $rowErrorString;
											// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~this wasn't previously caught, so add the error to the server
										} else if ($rowError == "TAMPER") {
											$rowErrorString = "Tampering Detected";
											$activityEntry->alerts[] = $rowErrorString;
											
											
											// still needs a summary, because this error is for the purpose of displaying the alert.
											
											// for JSON
											$entrySummary = " detected tampering.";
											$activityEntry->summary = $entrySummary;
										} else {
											// handle missing errors
											$activityEntry->alerts[] = $rowErrorString;
										}
										
										//echo "<span class=\"StatusBoxRed\">$rowErrorString</span>";
									} else {
										// print the result of the successful request
										
										$requestType = $requestRow[3];
										
										
										//$activityEntry->type = $requestType;
										
										
										
										// for JSON
										$entrySummary = "";
										
										if ($requestType == "PRESS") {
											
											if ($rowConfirmation == "LOCK") {
												$entrySummary .= " was locked.";
											} else if ($rowConfirmation == "UNLOCK") {
												$entrySummary .= " was unlocked.";
											} else {
												// errors?
												
											}
											
											
											
											
										} else if ($requestType == "DISARM") {
											$minutesString = "$serverResponse";
											
											if ($minutesString != "1") {
												$minutesString .= " minutes";
											} else {
												$minutesString .= " minute";
											}
											
											$entrySummary .= " was disarmed for $minutesString";
											// print the name on the account which approved the request
											$modifierUID = $requestRow[9];
											
											
											$modifierQuery = "
												SELECT * 
												FROM `TekBox-Users` 
												WHERE `OSUUID` = '$modifierUID'
												LIMIT 1
											";
											
											if ($modifierResult = mysqli_query($this->mysqli, $modifierQuery)) {
												$modifierCount = mysqli_num_rows($modifierResult);
												
												if ($modifierCount > 0) {
													// found the modifier
													$modifierRow = mysqli_fetch_row($modifierResult);
													
													$modifierName = $modifierRow[0];
													
													$entrySummary .= " by $modifierName.";
												} else {
													$entrySummary .= " by an unknown user... [User ID Not Found]";
												}
											} else {
												$entrySummary .= " by an unknown user... [query failed]";
											}
											
											
										} else if ($requestType == "DISARM_PICKUP") {
											$minutesString = "$serverResponse";
											
											if ($minutesString != "1") {
												$minutesString .= " minutes";
											} else {
												$minutesString .= " minute";
											}
											
											$entrySummary .= " was disarmed for $minutesString";
											// print the name on the account which approved the request
											$modifierUID = $requestRow[9];
											
											
											$modifierQuery = "
												SELECT * 
												FROM `TekBox-Orders` 
												WHERE `Customer-Email` = '$modifierUID'
												LIMIT 1
											";
											
											if ($modifierResult = mysqli_query($this->mysqli, $modifierQuery)) {
												$modifierCount = mysqli_num_rows($modifierResult);
												
												if ($modifierCount > 0) {
													// found the modifier
													$modifierRow = mysqli_fetch_row($modifierResult);
													
													$modifierName = $modifierRow[2];
													
													$orderNumber = $modifierRow[1];
													
													$entrySummary .= " by customer, $modifierName, for Order #$orderNumber.";
												} else {
													$entrySummary .= " by an unknown customer... [Order History Not Found]";
												}
											} else {
												$entrySummary .= " by an unknown customer... [query failed]";
											}
											
											
										} else if ($requestType == "JOINED") {
											$modifierUID = $requestRow[9];
											
											
											$modifierQuery = "
												SELECT * 
												FROM `TekBox-Users` 
												WHERE `OSUUID` = '$modifierUID'
												LIMIT 1
											";
											
											if ($modifierResult = mysqli_query($this->mysqli, $modifierQuery)) {
												$modifierCount = mysqli_num_rows($modifierResult);
												
												if ($modifierCount > 0) {
													// found the modifier
													$modifierRow = mysqli_fetch_row($modifierResult);
													
													$modifierName = $modifierRow[0];
													
													$entrySummary .= "$modifierName joined as a";
													
													if ($serverResponse == "ADMIN") {
														$entrySummary .= "n administrator.";
													} else if ($serverResponse == "MANAGER") {
														$entrySummary .= " manager.";
													} else {
														$entrySummary .= "n unknown type.";
													}
												} else {
													$entrySummary .= " by an unknown user... [User ID Not Found]";
												}
											} else {
												$entrySummary .= " by an unknown user... [query failed]";
											}
											
										} else if ($requestType == "TAMPER") {
											
											$entrySummary .= " detected tampering.";
											
											
											
											
										}  else if ($requestType == "TEST") {
											// locker connected to wifi
											if ($serverResponse != $rowConfirmation) {
												// this is basically impossible
												$entrySummary .= "Failed to connect to Wi-Fi. [Confirmation Error]";
											} else {
												
												$entrySummary .= " connected to Wi-Fi.";
												
											}
											
										} else {
											$entrySummary .= " Unrecognized Request";
										}
										
										$activityEntry->summary = $entrySummary;
										
									}
									
									
									
									
									
									
									
									// Adding entry to day for JSON
									$activityDay->entries[] = $activityEntry;
									
									
									//echo "</div>";
								}
								
								
								// testing JSON arrays
								$activityDays[] = $activityDay;
								
								// printing array contents
								$daysCount = count($activityDays);
								//echo "$daysCount";
								
								
								$activityData = new ActivityData();
								$activityData->activityDays = $activityDays;
								
								
								return $activityData;
								
								
								
								
							} else {
								echo "ERROR: No activity found for your lockers.";
							}
						} else {
							echo "ERROR: Query failed for activity.";
						}
						
						
					} else {
						echo "ERROR: No lockers were found for your account.";
					}
					
					
					
					
					
					
					
					
				}
			}
		}
		
		
		
		public function updateUserIP($forAccountUID, $newIP) {
			$updateIPQuery = "
				UPDATE `TekBox-Users`
				SET `Last-IP` = '$newIP' 
				WHERE `OSUUID` = '$forAccountUID'
				LIMIT 1
	        ";
	        
	        if ($updateUnlockResult = mysqli_query($this->mysqli, $updateIPQuery)) {
		        // query was successful.
		        return true;
	        } else {
		        return false;
	        }
		}
		
		
		public function updateTokenAccess($tokenUID, $dateTime, $lastIP) {
			$updateTokenAccessQuery = "
				UPDATE `TekBox-App-Tokens`
				SET `DateTime-Accessed` = '$dateTime',
				`Last-IP` = '$lastIP'
				WHERE `Token-UID` = '$tokenUID'
				LIMIT 1
	        ";
	        
	        if ($updateTokenAccessResult = mysqli_query($this->mysqli, $updateTokenAccessQuery)) {
		        //echo $tokenUID;
		        return true;
	        } else {
		        return false;
	        }
		}
	}
	
	
?>