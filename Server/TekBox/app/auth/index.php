<?php
// This endpoint is for the mobile app to sign in with the ONID CAS system, and receive a token to be used for accessing the other mobile app endpoints.
// The token will be hashed and stored in the database, along with some device info for user convenience on the Account page (not to be creepy).

// It will be loaded in a web view, so that the user can enter their credentials on the login.oregonstate.edu page.

require_once '../../' . 'tekbox_config.php';


require_once CAS_CONFIG_FILE_PATH;
require_once PHPCAS_FILE_PATH;

require_once RANDOM_COMPAT_FILE_PATH;



phpCAS::setDebug();

phpCAS::setVerbose(true);

phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

phpCAS::setNoCasServerValidation();

phpCAS::forceAuthentication();


// At this point, the user should be signed in with ONID in the web view, and the server should generate a token, store its hash in the database, and send it to the app.



class MobileToken {
	public $tokenUID;
	public $token;
}


// make sure the request has all the info needed
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ need to change this to POST for production
if ($_GET["model"] && $_GET["vendorID"]) {
	if ($osuUID = phpCAS::getAttribute("osuuid")) {
		
		// connecting to database
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if ($mysqli->connect_errno) {
		    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
			
			$dateTime = date("Y-m-d H:i:s");
			
			$today = date("F j, Y");
			
			// see if the account is already in the database
			$userQuery = "
				SELECT * 
				FROM `TestBox-Users` 
				WHERE `OSUUID` = '$osuUID'
				LIMIT 1
			";
			
			if ($userResult = mysqli_query($mysqli, $userQuery)) {
				// make sure the account is set up in the database
				$accountIsInDatabase = false;
				if ($userRow = mysqli_fetch_row($userResult)) {
					// account found in the TekBox database
					$accountIsInDatabase = true;
				} else {
					// account not found. Need to add row to the table before continuing.
					
					if ($firstName = phpCAS::getAttribute("firstname")) {
						if ($lastName = phpCAS::getAttribute("lastname")) {
							if ($email = phpCAS::getAttribute("eduPersonPrincipalName")) {
								// got all info needed for making the entry.
								$fullName = $firstName . " " . $lastName;
								$insertUserQuery = "
									INSERT INTO `nordlune-db`.`TestBox-Users` (`Full-Name`, `Email`, `OSUUID`, `Last-IP`, `First-Name`, `Last-Name`)
									VALUES ('$fullName', '$email', '$osuUID', '$ip', '$firstName', '$lastName');
								";
								
								if ($insertResult = mysqli_query($mysqli, $insertUserQuery)) {
									// the query was successful, so now it can move on
									$accountIsInDatabase = true;
								} else {
									echo "Account insert query failed.";
								}
							} else {
								echo "Couldn't find email address.";
							}
						} else {
							echo "Couldn't find last name.";
						}
					} else {
						echo "Couldn't find first name.";
					}
				}
				
				if ($accountIsInDatabase) {
					// generate the token for the mobile device
					$token = bin2hex(random_bytes(32));
					
					// hash it for the database
					$hash = password_hash($token, PASSWORD_DEFAULT);
					
					$tokenUID = substr(sha1(time()), 0, 16);
					
					
					$model = $_GET["model"];
					
					$vendorID = $_GET["vendorID"];
					
					$didUpdate = false;
					
					// update the existing token if the device already has a token
					$updateTokenQuery = "
						UPDATE `TekBox-App-Tokens`
						SET `Token-UID` = '$tokenUID',
						`Token-Hashed` = '$hash',
						`DateTime-Created` = '$dateTime',
						`DateTime-Accessed` = '$dateTime',
						`Last-IP` = '$ip'
						WHERE `Account-UID` = '$osuUID'
						AND `Device-Model` = '$model'
						AND `Vendor-ID` = '$vendorID'
						LIMIT 1
					";
					
					if ($updateTokenResult = mysqli_query($mysqli, $updateTokenQuery)) {
						$updateCount = mysqli_affected_rows($mysqli);
						
						if ($updateCount > 0) {
							// update was successful.
							//echo "updated successfully.";
							$didUpdate = true;
							
							
						} else {
							echo "didn't update a row.";
						}
					} else {
						echo "update query failed.";
					}
					
					
					if ($didUpdate) {
						
						$callbackURL = "tekbox:auth?tokenUID=" . $tokenUID . "&token=" . $token;
						
						echo "<html><head><title>ONID</title><meta http-equiv=\"refresh\" content=\"0; url=" . $callbackURL . "\"/></head><body>Hello</body></html>";
						
					} else {
						// insert the token
						$insertTokenQuery = "
							INSERT INTO `nordlune-db`.`TekBox-App-Tokens` (`Account-UID`, `Token-UID`, `Token-Hashed`, `Device-Model`, `Vendor-ID`, `DateTime-Created`, `DateTime-Accessed`, `Last-IP`)
							VALUES ('$osuUID', '$tokenUID', '$hash', '$model', '$vendorID', '$dateTime', '$dateTime', '$ip');
						";
						
						if ($insertResult = mysqli_query($mysqli, $insertTokenQuery)) {
							// token's saved, so send it to the app
							
							$callbackURL = "tekbox:auth?tokenUID=" . $tokenUID . "&token=" . $token;
							
							echo "<html><head><title>ONID</title><meta http-equiv=\"refresh\" content=\"0; url=" . $callbackURL . "\"/></head><body>Hello</body></html>";
						} else {
							echo "Query failed to insert authentication data.";
						}
						
					}
					
					
				} else {
					echo "Account setup failed.";
				}
			} else {
				echo "Account query failed for " . $osuUID;
			}
			
		}
	} else {
		echo "Failed to get ONID account info.";
	}
} else {
	echo "Did not receive parameters.";
}



$mysqli->close();


?> 