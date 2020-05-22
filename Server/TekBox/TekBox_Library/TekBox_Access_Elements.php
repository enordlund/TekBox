<?php
	//echo "hi from AN";
	// remove this for PHP 7+ (adds compatibility for random_bytes)
	//echo "hi1";
	
	//echo "hi2";
	
	require_once dirname(__FILE__).'/TekBox-Database.php';
	
	
/*
	class TaskReport {
		public $didComplete;
		public $asset;
		public $errorMessage;
	}
*/
	
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
	
	
	
	
	
	class TekBoxAccessElements {
		private $ip;
		private $dateTime;
		
		private $database;
		
		
		
		function __construct() {
			$this->ip = $_SERVER['REMOTE_ADDR'];
			$this->dateTime = date("Y-m-d H:i:s");
			
			$this->database = new TekBoxDatabase();
		}
		
		
		
		public function ordersListSorted($forEmail) {
			if ($ordersResult = $this->database->getOrdersResultSorted($forEmail)) {
				// at least one order was found
				
				// determine if the first order is active
				
				
				$wasActive == null;
				
				
				// look for additional orders
				while ($orderRow = mysqli_fetch_row($ordersResult)) {
					// display order number, date of order, location of order
					$isActive = $orderRow[4];
					$orderUID = $orderRow[0];
					$orderNumber = $orderRow[1];
					
					if ($wasActive == null) {
						// need to set first header
						if ($isActive) {
							echo "<h3>Active Orders</h3>";
						} else {
							echo "<h3>Previous Orders</h3>";
						}
					} else if ($wasActive != $isActive) {
						// no longer active orders
						echo "<h3>Previous Orders</h3>";
					}
					$wasActive = $isActive;
					
					
					// finish listing details of first order
					echo "<div class = \"activityRow\">";
					
					if ($isActive) {
						echo "<h3>Order #$orderNumber</h3>";
						echo "<div class = \"activityRow\">";
						echo "<button class = \"orangeButton\" onclick=\"window.location.href = './order?uid=$orderUID';\">Pickup Now</button>" . "<br>";
						echo "</div>";
					} else {
						echo "<a href = \"./order?uid=$orderUID\">";
						echo "<h3>Order #$orderNumber</h3>";
						echo "</a>";
					}
					
					echo "</div>";
					
				}
			} else {
				// no orders found for the account
				echo "No orders for \"$forEmail\"." . "<br>";
			}
		}
		
		
		public function ordersListActive($forEmail) {
			if ($ordersResult = $this->database->getOrdersResultActive($forEmail)) {
				// at least one order was found
				
				echo "<h3>Active Orders</h3>";
				
				// look for additional orders
				while ($orderRow = mysqli_fetch_row($ordersResult)) {
					// display order number, date of order, location of order
					$isActive = $orderRow[4];
					$orderUID = $orderRow[0];
					$orderNumber = $orderRow[1];
					
					// finish listing details of first order
					
					echo "<h3>Order #$orderNumber</h3>";
					echo "<button class = \"orangeButton\" onclick=\"window.location.href = './orders/order?uid=$orderUID';\">Pickup Now</button>" . "<br>";
					
				}
			} else {
				// no orders found for the account
				echo "No active orders for \"$forEmail\"." . "<br>";
			}
		}
		
		
		public function ordersListActiveOld($forEmail) {
			if ($ordersResult = $this->database->getOrdersResultActive($forEmail)) {
				// at least one order was found
				
				echo "<h3>Active Orders</h3>";
				
				// look for additional orders
				while ($orderRow = mysqli_fetch_row($ordersResult)) {
					// display order number, date of order, location of order
					$isActive = $orderRow[4];
					$orderUID = $orderRow[0];
					$orderNumber = $orderRow[1];
					
					// finish listing details of first order
					echo "<div class = \"activityRow\">";
					
					echo "<h3>Order #$orderNumber</h3>";
					echo "<div class = \"activityRow\">";
					echo "<button class = \"orangeButton\" onclick=\"window.location.href = './orders/order?uid=$orderUID';\">Pickup Now</button>" . "<br>";
					echo "</div>";
					
					echo "</div>";
					
				}
			} else {
				// no orders found for the account
				echo "No active orders for \"$forEmail\"." . "<br>";
			}
		}
		
		public function orderContent($forOrderUID, $withEmail) {
			// first, see if the order exists
			if ($orderRow = $this->database->getOrderRowWithEmail($forOrderUID, $withEmail)) {
				// the order exists, so determine if the order is active or not.
				$isActive = $orderRow[4];
				
				$orderNumber = $orderRow[1];
				
				if ($isActive) {
					echo "<h3>Order #$orderNumber</h3>";
					
					// customer should be able to pick up the order by disarming the locker
					// check that locker is loaded
					$lockerUID = $orderRow[7];
					
					if ($lockerRow = $this->database->getLockerRow($lockerUID)) {
						// make sure it's loaded
						$isLoaded = $lockerRow[9];
						
						if ($isLoaded) {
							// display pickup interface
							$lockerName = $lockerRow[0];
							echo "<h3>$lockerName</h3>";
							echo "<div class = \"activityRow\">";
							echo "Your order is ready for pickup in $lockerName. Use the button below to disarm the locker." . "<br>";
							echo "<button class = \"orangeButton\" onclick=\"window.location.href = './disarm?uid=$forOrderUID';\">Disarm $lockerName</button>" . "<br>";
							echo "</div>";
						}
					} else {
						echo "System error: Locker not found." . "<br>";
					}
				} else {
					// the order is inactive, so it should just show some details about the order.
					echo "<h3>Order Details</h3>";
					// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ TO BE COMPLETED
				}
			} else {
				echo "This order was not found for \"$withEmail\".";
			}
		}
		
		public function disarmContent($forOrderUID, $withEmail) {
			// get the locker name
			
			if ($orderRow = $this->database->getOrderRow($forOrderUID)) {
				$lockerUID = $orderRow[7];
				
				if ($lockerRow = $this->database->getLockerRow($lockerUID)) {
					$lockerName = $lockerRow[0];
					
					if ($unlockForMinutes = $this->database->disarmLockerForAccess($forOrderUID, $withEmail, $this->dateTime, $this->ip)) {
						echo "$lockerName is disarmed for $unlockForMinutes minute";
						if ($unlockForMinutes == 1) {
							echo ". ";
						} else {
							echo "s. ";
						}
						echo "Press the button on the front of $lockerName to unlock the locker and retrieve your order." . "<br>";
					} else {
						"Failed to disarm $lockerName." . "<br>";
					}
				}
				
			}
			
		}
		
		
		public function helpContent() {
			echo "Good luck!";
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
										
										
										$emailSubject = TEKBOX_EMAIL_ACTIVATION_BLOCK_SUBJECT;
										
										$emailBody = "$ownerName,\r\n" . TEKBOX_EMAIL_ACTIVATION_BLOCK_BODY;
										
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
					
					
				} else if (($lockerUID = $this->jsonObject->{"lockerUID"}) && ($locationUID = $this->jsonObject->{"locationUID"}) && ($row = $this->jsonObject->{"row"}) && ($column = $this->jsonObject->{"column"})) {
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