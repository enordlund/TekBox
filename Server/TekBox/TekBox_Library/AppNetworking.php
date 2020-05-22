<?php
	//echo "hi from AN";
	// remove this for PHP 7+ (adds compatibility for random_bytes)
	//echo "hi1";
	
	//echo "hi2";
	
	require_once dirname(__FILE__).'/TekBox-Database.php';
	
	
	
	
	class TokenError {
		public $tokenError;
	}
	
	
	// setup data
	class SetupStatus {
		// lockerUID is included if setup should continue
		public $lockerUID;
		public $otherOwner;
		public $noSession;
	}
	
	
	
	// account data
	class Account {
		public $name;
		public $email;
		public $invitations;
		public $mobileDevices;
		public $locations;
		public $lockers;
	}
	
	
	class MobileDevice {
		public $model;
		public $vendorID;
		public $tokenUID;
		public $lastAuthenticated;
	}
	
	
	
	
	// Locations
	
	
	class Location {
		public $uid;
		public $name;
		public $rows;
		public $columns;
		public $openLockerCount;
		public $loadedLockerCount;
		public $latitude;
		public $longitude;
	}
	
	class LocationsData {
		public $locations;
	}
	
	
	
	// Lockers
	class Locker {
		public $uid;
		public $name;
		public $isLoaded;
		public $row;
		public $column;
		public $summary;
		public $prepMessage;
		public $orderUID;
		public $disarmPeriod;
	}
	
	class LockersData {
		public $lockers;
	}
	
	
	
	
	
	// Orders
	class OrderEntry {
		
		public $uid;
		public $orderNumber;
		public $loadedDateTime;
		public $unloadedDateTime;
		public $lockerName;
		public $locationName;
		//public $message;
		
	}
	
	
	/**
	 * Stores activity information for a given day
	 */
	class OrderCategory {
		public $status;
		public $orders;
	}
	
	class OrderData {
		public $activeThenInactiveOrders;
	}
	
	class OrderDetail {
		public $uid;
		public $orderNumber;
		public $loadedDateTime;
		public $unloadedDateTime;
		public $lockerUID;
		public $lockerName;
		public $locationName;
		public $customerName;
		public $customerEmail;
	}
	
	
	
	// activity data
		/**
	* Stores activity entry details
	*/
	class ActivityEntry {
		
		public $uid;
		public $type;
		public $dateTime;
		public $lockerName;
		public $locationName;
		public $summary;
		//public $details;
		public $notices;
		public $alerts;
	
	}
	
	
	/**
	 * Stores activity information for a given day
	 */
	class ActivityDay {
		public $dateTime;
		public $entries;
	}
	
	class ActivityData {
		public $activityDays;
	}
	
	
	
	
	
	class AppData {
		private $ip;
		private $dateTime;
		
		private $database;
		
		private $jsonObject;
		
		private $jsonInputString;
		
		private function getJSONFromContents() {
			if ($content = file_get_contents('php://input')) {
				$this->jsonInputString = $content;
				if ($object = json_decode($content)) {
					return $object;
				}
			}
			return null;
		}
		
		
		
		function __construct() {
			$this->ip = $_SERVER['REMOTE_ADDR'];
			$this->dateTime = date("Y-m-d H:i:s");
			
			$this->database = new TekBoxDatabase();
			
			$this->jsonObject = $this->getJSONFromContents();
		}
		
		
		
		private function tokenGatekeeper() {
			if ($this->jsonObject != null) {
				if (($tokenUID = $this->jsonObject->tokenUID) && ($token = $this->jsonObject->token)) {
					
					if ($tokenRow = $this->database->verifyToken($tokenUID, $token)) {
						//echo "good token";
						return $tokenRow;
					} else {
						//echo "didn't verify token?";
					}
				} else {
					//echo "no token data?";
				}
			}
			
			
			
			// if it gets to this point, token failed.
			$tokenError = new TokenError();
			$tokenError->tokenError = true;
			$jsonString = json_encode($tokenError);
			echo $jsonString;
			
			return null;
		}
		
		
		
		
		
		// Endpoints
		
		public function deauthEndpoint() {
			
			if ($tokenRow = $this->tokenGatekeeper()) {
				// valid token
				
				$taskReport = new TaskReport();
				$tokenUID = $tokenRow[1];
				$osuUID = $tokenRow[0];
				
				// checking to see if the token is for another device, or this device
				if ($deauthenticatedTokenUID = $this->jsonObject->deauthenticatedTokenUID) {
					$tokenUID = $deauthenticatedTokenUID;
				}
				
				
				if ($this->database->deleteToken($tokenUID)) {
					// successfully deleted token
					$taskReport->didComplete = true;
				} else {
					$taskReport->didComplete = false;
				}
				
				$jsonString = json_encode($taskReport);
				echo $jsonString;
				
				// log the IP
				$this->database->updateUserIP($osuUID, $this->ip);
			}
			
			// token failure is handled by tokenProxy().
			$this->database->close();
		}
		
		
		
		public function accountEndpoint() {
			
			if ($tokenRow = $this->tokenGatekeeper()) {
				// valid token
				$osuUID = $tokenRow[0];
				
				if ($userRow = $this->database->getUserRow($osuUID)){
					
					if ($devices = $this->database->getAuthenticatedDevices($osuUID)) {
						
						$userName = $userRow[0];
						//echo "Name: $userName" . "<br>";
						
						$userEmail = $userRow[1];
						//echo "Email: $userEmail" . "<br>";
						
						$account = new Account();
						
						$account->name = $userName;
						$account->email = $userEmail;
						$account->mobileDevices = $devices;
						
						
						if ($invitations = $this->database->getInvitations($userEmail)) {
							$account->invitations = $invitations;
						}
						
						
						
						$jsonString = json_encode($account);
						
						echo $jsonString;
						//return;
					}
				}
				
				// log the IP
				$this->database->updateUserIP($osuUID, $this->ip);
				
				$this->database->updateTokenAccess($tokenRow[1], $this->dateTime, $this->ip);
			}
			
			$this->database->close();
		}
		
		
		public function rsvpEndpoint() {
			if ($tokenRow = $this->tokenGatekeeper()) {
				// valid token
				$osuUID = $tokenRow[0];
				
				$report = new TaskReport();
				$report->didComplete = false;
				
				if (($this->jsonObject->invitationUID !== NULL) && ($this->jsonObject->accepted !== NULL)) {
					// sufficient parameters to continue
					
					$invitationUID = $this->jsonObject->invitationUID;
					$invitationAccepted = $this->jsonObject->accepted;
					
					
					if ($databaseReport = $this->database->rsvpInvitation($invitationUID, $invitationAccepted, $osuUID)) {
						// echo the TaskReport object as json
						$report = $databaseReport;
						$jsonString = json_encode($report);
						echo $jsonString;
						
						return;
					} else {
						$report->errorMessage = "Unknown database error.";
					}
				} else {
					$report->errorMessage = "Incomplete request parameters: " . $this->jsonInputString;
				}
				
				// if it's at this point, need to return unknown error
				

				
				$jsonString = json_encode($report);
				echo $jsonString;
				return;
			}
		}
		
		
		
		
		
		
		public function locationsEndpoint() {
			if ($tokenRow = $this->tokenGatekeeper()) {
				
				$osuUID = $tokenRow[0];
				if ($locationsData = $this->database->getLocationsData($osuUID)) {
					
					$jsonString = json_encode($locationsData);
					
					echo $jsonString;
				}
				
				// log the IP
				$this->database->updateUserIP($osuUID, $this->ip);
				
				$this->database->updateTokenAccess($tokenRow[1], $this->dateTime, $this->ip);
			}
			
			$this->database->close();
		}
		
		
		
		
		public function lockersEndpoint() {
			if ($tokenRow = $this->tokenGatekeeper()) {
				$osuUID = $tokenRow[0];
				if ($locationUID = $this->jsonObject->locationUID) {
					
					if ($lockersData = $this->database->getAuthenticatedLocationLockersData($locationUID, $osuUID)) {
						
						$jsonString = json_encode($lockersData);
						
						echo $jsonString;
					}
				}
				
				// log the IP
				$this->database->updateUserIP($osuUID, $this->ip);
				
				$this->database->updateTokenAccess($tokenRow[1], $this->dateTime, $this->ip);
			}
			
			$this->database->close();
		}
		
		
		
		public function lockerEndpoint() {
			if ($tokenRow = $this->tokenGatekeeper()) {
				$osuUID = $tokenRow[0];
				
				if ($lockerUID = $this->jsonObject->lockerUID) {
					
					if ($lockerData = $this->database->getAuthenticatedLockerData($lockerUID, $osuUID)) {
						// check if it's adding order info, etc.
						
						if (($orderNumber = $this->jsonObject->orderNumber) && ($customerName = $this->jsonObject->customerName) && ($customerEmail = $this->jsonObject->customerEmail)) {
							
							if ($postOrderLockerData = $this->database->addOrder($orderNumber, $customerName, $customerEmail, $lockerUID, $this->dateTime)) {
								$lockerData = $postOrderLockerData;
							}
							
							
						} else if ($this->jsonObject->disarm == true) {
							
							
							if ($userRow = $this->database->getUserRow($osuUID)){
								
								$lockerIsLoaded = $lockerData->isLoaded;
								
								$forPrep = $this->jsonObject->forPrep;
								
								if ($disarmedLockerData = $this->database->disarmLocker($lockerUID, $userRow, $forPrep, $this->dateTime, $this->ip)) {
									$lockerData = $disarmedLockerData;
								}
							}
						}
						
						$jsonString = json_encode($lockerData);
						
						echo $jsonString;
						
					}
				}
				
				// log the IP
				$this->database->updateUserIP($osuUID, $this->ip);
				
				$this->database->updateTokenAccess($tokenRow[1], $this->dateTime, $this->ip);
			}
			
			$this->database->close();
		}
		
		
		
		
		public function ordersEndpoint() {
			if ($tokenRow = $this->tokenGatekeeper()) {
				$osuUID = $tokenRow[0];
				if ($ordersData = $this->database->getOrdersData($osuUID)) {
					$jsonString = json_encode($ordersData);
						
					echo $jsonString;
				}
				
				// log the IP
				$this->database->updateUserIP($osuUID, $this->ip);
				
				$this->database->updateTokenAccess($tokenRow[1], $this->dateTime, $this->ip);
			}
			
			$this->database->close();
		}
		
		
		
		
		public function orderEndpoint() {
			
			if ($tokenRow = $this->tokenGatekeeper()) {
				$osuUID = $tokenRow[0];
				
				if ($orderUID = $this->jsonObject->orderUID) {
					
					if ($orderDetail = $this->database->getOrderDetail($orderUID, $osuUID)) {
						
						$jsonString = json_encode($orderDetail);
							
						echo $jsonString;
					}
				}
				
				// log the IP
				$this->database->updateUserIP($osuUID, $this->ip);
				
				$this->database->updateTokenAccess($tokenRow[1], $this->dateTime, $this->ip);
			}
			
			$this->database->close();
		}
		
		
		
		
		
		
		
		public function activityEndpoint() {
			//echo "activityEndpoint()";
			if ($tokenRow = $this->tokenGatekeeper()) {
				// valid token
				//echo "valid token";
				$osuUID = $tokenRow[0];
				
				if ($activityData = $this->database->getActivityData($osuUID)) {
					//echo "got activity data";
					$jsonString = json_encode($activityData);
					
					echo $jsonString;
				}
				
				// log the IP
				$this->database->updateUserIP($osuUID, $this->ip);
				
				$this->database->updateTokenAccess($tokenRow[1], $this->dateTime, $this->ip);
				
			}
			
			// token failure is handled by tokenProxy().
			$this->database->close();
		}
		
		
		public function lockerSetupEndpoint() {
			if ($tokenRow = $this->tokenGatekeeper()) {
				// valid token
				//echo "valid token";
				$osuUID = $tokenRow[0];
				
				
				// 
				if ($sessionUID = $this->jsonObject->sessionUID) {
					// look for the session to find the locker uid
					if ($sessionRow = $this->database->getSetupSessionRow($sessionUID)) {
						
						$lockerUID = $sessionRow[1];
						
						// look for the locker
						if ($lockerRow = $this->database->getLockerRow($lockerUID)) {
							// check the locker's Admin1 entry
							$ownerUID = $lockerRow[5];
							
							$setupShouldContinue = false;
							
							$otherOwner = false;
							
							if ($ownerUID != null) {
								if ($osuUID == $ownerUID) {
									// if the entry is populated with the user's UID, user is authenticated to proceed.
									$setupShouldContinue = true;
								} else {
									// if the entry is populated with a different user's UID, echo error.
									$setupShouldContinue = false;
									$otherOwner = true;
									
									
									// send email to owner
									if ($ownerRow = $this->database->getUserRow($ownerUID)) {
										$ownerName = $ownerRow[0];
										$ownerEmail = $ownerRow[1];
										
										// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ send email for order
										$emailSubject = TEKBOX_EMAIL_ACTIVATION_BLOCK_SUBJECT;
										
										$emailBody = "$ownerName,\n" . TEKBOX_EMAIL_ACTIVATION_BLOCK_BODY;
										
										$emailHeaders = "From: " . TEKBOX_EMAIL_FROM_ADDRESS . "\r\n";
										
										
										
										if (mail($ownerEmail, $emailSubject, $emailBody, $emailHeaders)) {
											// email successful.
										} else {
											// try one more time
											mail($ownerEmail, $emailSubject, $emailBody, $emailHeaders);
										}
										
									}
								}
							} else {
								// if the entry is NULL, update the locker with the user's UID. user is authenticated to proceed.
								if ($this->database->assignOwnerToLocker($osuUID, $lockerUID)) {
									$setupShouldContinue = true;
								} else {
									// error setting owner in database
									$setupShouldContinue = false;
								}
							}
							
							$setupStatus = new SetupStatus();
							
							if ($setupShouldContinue) {
								$setupStatus->lockerUID = $lockerUID;
							}
							
							$setupStatus->noSession = false;
							
							$setupStatus->otherOwner = $otherOwner;
							
							$jsonString = json_encode($setupStatus);
								
							echo $jsonString;
						}
						
						// not echoing json in an else closure, because it's not an informed error (it's just a query error, not based on database information)
						
					} else {
						$setupStatus = new SetupStatus();
						
						$setupStatus->noSession = true;
						
						$jsonString = json_encode($setupStatus);
							
						echo $jsonString;
					}
					
					
				} else if (($lockerUID = $this->jsonObject->{"lockerUID"}) && ($locationUID = $this->jsonObject->{"locationUID"}) && ($this->jsonObject->{"row"} != null) && ($this->jsonObject->{"column"} != null)) {
					$row = $this->jsonObject->{"row"};
					$column = $this->jsonObject->{"column"};
					// user opted for existing location
					
					// make sure locker is owned by the user (check the admin1 entry)
					if ($lockerRow = $this->database->getLockerRow($lockerUID)) {
						$ownerUID = $lockerRow[5];
						
						if ($osuUID == $ownerUID) {
							
							// make sure location is administrated by the user
							if ($locationRow = $this->database->getAuthenticatedLocationRow($locationUID, $osuUID)) {
								
								// make sure the locker coordinates will work with the location
								// by checking that they're not used by other lockers
								if ($this->database->addLockerToLocation($lockerUID, $locationUID, $row, $column) == true) {
									// locker was added to the location! We're almost done
									
									
									$report = new TaskReport();
									$report->didComplete = true;
									
									
									
									if ($secret = $this->database->getOfflineKeySecret($lockerUID)) {
										$report->asset = $secret;
									}
									// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ eventually, generate a key if not found, and have the locker load it somehow.
									
									
									
									$jsonString = json_encode($report);
										
									echo $jsonString;
									
								} else {
									// otherwise, echo an error
									$report = new TaskReport();
									$report->didComplete = false;
									$report->errorMessage = "Failed to add locker to location.";
									
									$jsonString = json_encode($report);
										
									echo $jsonString;
								}
							} else {
								// otherwise, echo an error
								$report = new TaskReport();
								$report->didComplete = false;
								$report->errorMessage = "Could not find selected location.";
								
								$jsonString = json_encode($report);
									
								echo $jsonString;
							}
						} else {
							// otherwise, echo an error
							$report = new TaskReport();
							$report->didComplete = false;
							
							$report->errorMessage = "Locker owned by another account.";
							
							$jsonString = json_encode($report);
								
							echo $jsonString;
						}
						
					} else {
						// otherwise, echo an error
						$report = new TaskReport();
						$report->didComplete = false;
						
						$report->errorMessage = "Failed to find locker information.";
						
						$jsonString = json_encode($report);
							
						echo $jsonString;
					}
					
					
					
				} else if (($lockerUID = $this->jsonObject->lockerUID) && ($locationName = $this->jsonObject->locationName) && ($locationLatitude = $this->jsonObject->locationLatitude) && ($locationLongitude = $this->jsonObject->locationLongitude)) {
					// user opted for new location
					
					// make sure the locker is owned by the user
					if ($lockerRow = $this->database->getLockerRow($withLockerUID)) {
						$ownerUID = $lockerRow[5];
						
						if ($osuUID == $ownerUID) {
							// create a new location with user as administrator
							if ($newUID = $this->database->createNewLocation($locationName, $locationLatitude, $locationLongitude, $osuUID)) {
								// register the location uid and coordinates (0,0) to the locker
								if ($this->database->addLockerToLocation($lockerUID, $newUID, 0, 0) == true) {
									// locker was added to the location! We're done
									$report = new TaskReport();
									$report->didComplete = true;
									
									$jsonString = json_encode($report);
										
									echo $jsonString;
									
								} else {
									// otherwise, echo an error
									$report = new TaskReport();
									$report->didComplete = false;
									
									$jsonString = json_encode($report);
										
									echo $jsonString;
								}
							} else {
								// otherwise, echo an error
								$report = new TaskReport();
								$report->didComplete = false;
								
								$jsonString = json_encode($report);
									
								echo $jsonString;
							}
							
						} else {
							// otherwise, echo an error
							$report = new TaskReport();
							$report->didComplete = false;
							
							$jsonString = json_encode($report);
								
							echo $jsonString;
						}
					} else {
						// otherwise, echo an error
						$report = new TaskReport();
						$report->didComplete = false;
						
						$jsonString = json_encode($report);
							
						echo $jsonString;
					}
					
					
				} else {
					// otherwise, echo an error
					$report = new TaskReport();
					$report->didComplete = false;
					
					$report->errorMessage = "Incomplete parameters: " . $this->jsonInputString;
					
					$jsonString = json_encode($report);
						
					echo $jsonString;
				}
				
				// log the IP
				$this->database->updateUserIP($osuUID, $this->ip);
				
				$this->database->updateTokenAccess($tokenRow[1], $this->dateTime, $this->ip);
				
			}
			
			// token failure is handled by tokenProxy().
			$this->database->close();
		}
		
	}
	
	
	
	
	
	
?>