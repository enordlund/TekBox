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
		<title>TekBox Dashboard - Order</title>
		
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
									<a href="../../lockers"  class="headlink"id="headlink1">
									Lockers
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../orders/"  class="headlink" id="headlinkcurrent">
									Orders
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../users/"  class="headlink" id="headlink2">
									Users
									</a>
								</div>
								<div class="headlinkframe">
									<a href="../../activity/"  class="headlink" id="headlink4">
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
			
			$ip = $_SERVER['REMOTE_ADDR'];
			
			$dateTime = date("Y-m-d H:i:s");
			
			$today = date("F j, Y");
			
			
			//print "<h3>Today</h3>";
			
			//$hash = password_hash("thisISaPassw0rd", PASSWORD_DEFAULT);
			
			//print "Hash: $hash" . "<br>";
			
			if ($_GET["orderUUID"]) {
				// spend resources looking for order
				$orderUUID = $_GET["orderUUID"];
				
				// authenticate user
				
				
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
						
						// found the user, so look for the user's clusters
						$clustersQuery = "
							SELECT * 
							FROM `TekBox-Clusters` 
							WHERE `Admin1-UUID` = '$osuUID'
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
							OR `Manager10-UUID` = '$osuUID'
						";
						
						if ($clustersResult = mysqli_query($mysqli, $clustersQuery)) {
							$clustersCount = mysqli_num_rows($clustersResult);
							
							if ($clustersResult > 0) {
								// the user does have permissions to manage/administrate the resulting cluter(s)
								
								// store box UUID values for activity query
								$boxUUIDArray = array();
								$boxUUIDArrayIndex = 0;
								
								// keeping track of box names in parallel for printing to page.
								$boxNamesFromUUID = array();
								// keeping track of box clusters as well
								$boxClusterUUIDfromUUID = array();
								
								
								
								// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ store cluster names/UUIDs for filtering
								$clusterUUIDArray = array();
								$clusterUUIDArrayIndex = 0;
								
								$clusterNameFromUUID = array();
								
								// look for boxes belonging to the clusters
								while ($clusterRow = mysqli_fetch_row($clustersResult)) {
									// store box UUID values for activity query
									
									$clusterUUID = $clusterRow[1];
									$clusterName = $clusterRow[0];
									
									$clusterUUIDArray[$clusterUUIDArrayIndex] = $clusterUUID;
									$clusterNameFromUUID["$clusterUUID"] = $clusterName;
									
									$clusterUUIDArrayIndex += 1;
									
									$boxesQuery = "
										SELECT * 
										FROM `TekBox-Boxes` 
										WHERE `Cluster-UUID` = '$clusterUUID'
									";
									
									if ($boxesResult = mysqli_query($mysqli, $boxesQuery)) {
										// query was successful
										
										$boxesCount = mysqli_num_rows($boxesResult);
										
										if ($boxesCount > 0) {
											// boxes were found for the cluster UUID
											
											while ($boxRow = mysqli_fetch_row($boxesResult)) {
												// add the UUID to the array
												
												$boxUUID = $boxRow[1];
												
												$boxUUIDArray[$boxUUIDArrayIndex] = $boxUUID;
												
												$boxName = $boxRow[0];
												
												$boxNamesFromUUID["$boxUUID"] = $boxName;
												
												$boxClusterUUID = $boxRow[4];
												
												$boxClusterUUIDfromUUID["$boxUUID"] = $boxClusterUUID;
												
												
												$boxUUIDArrayIndex += 1;
											}
										} else {
											echo "No boxes were found." . "<br>";
										}
									} else {
										echo "Query failed for boxes." . "<br>";
									}
								}
								
								$boxUUIDArrayCount = count($boxUUIDArray);
								
								if ($boxUUIDArrayCount > 0) {
									// boxes were found in the clusters. Construct query for activity
									
									//echo "Found $boxUUIDArrayCount boxes." . "<br>";
									
									$boxUUID = $boxUUIDArray[0];
									
									//echo "$boxUUID" . "<br>";
									
									$orderQuery = "
										SELECT * 
										FROM `TekBox-Orders` 
										WHERE `DateTime` IS NOT NULL 
										AND `UUID` = '$orderUUID'
										AND (`Box-UUID` = '$boxUUID'
									";
									
									// finish constructing the query with all boxUUID values
									$boxUUIDArrayIndex = 1;
									
									while ($boxUUIDArrayIndex < $boxUUIDArrayCount) {
										//echo "$boxUUID" . "<br>";
										
										//echo "adding to query";
										// add the next uuid to the query
										$boxUUID = $boxUUIDArray[$boxUUIDArrayIndex];
										
										$orderQuery .= " OR `Box-UUID` = '$boxUUID'";
										
										$boxUUIDArrayIndex += 1;
									}
									
									
									// sort in reverse-chronological order, finishing the query
									$orderQuery .= ")
										ORDER BY `DateTime` DESC
										LIMIT 1
									";
									
									//echo "Query: $orderQuery" . "<br>";
									
									// query for activity
									if ($orderResult = mysqli_query($mysqli, $orderQuery)) {
										// query was successful
										$orderCount = mysqli_num_rows($orderResult);
										
										if ($orderCount > 0) {
											// orders were found
											
											// ~~~~~~~~~~~~~~~~~~~~~~~ time to print the activity to the page.
											
											$dateProgress = date("Y");
									
											$thisYear = date("Y");
											
											$yesterday = date("F j, Y", time() - 60 * 60 * 24);
											
											$isFirstRow = true;
											
											$wasActive = true;
											
											
											while ($orderRow = mysqli_fetch_row($orderResult)) {
												// printing row to page
												
												$rowDateTime = $orderRow[6];
												
												$dt = new DateTime($rowDateTime);
												
												$rowDate = $dt->format('F j, Y');
												
												$rowYear = $dt->format('Y');
												
												$rowTime = $dt->format('g:i a');
												
												$orderIsActive = $orderRow[4];
												
												$orderNumber = $orderRow[1];
												
												
												$boxUUID = $orderRow[7];
												
												$boxName = $boxNamesFromUUID["$boxUUID"];
												
												$clusterUUID = $boxClusterUUIDfromUUID["$boxUUID"];
												
												$clusterName = $clusterNameFromUUID["$clusterUUID"];
												
												
												$statusString = "Inactive";
												
												if ($orderIsActive) {
													$statusString = "Active";
												}
												
												
												echo "<h3>Order #$orderNumber — $statusString</h3>";
												
												echo "$boxName ($clusterName)" . "<br>";
												
												echo "Loaded $rowDateTime" . "<br>";
												
												if (!$orderIsActive) {
													$unloadTime = $orderRow[8];
													echo "Unloaded $unloadTime" . "<br>";
												}
												
												echo "<h3>Customer Info</h3>";
												
												$customerName = $orderRow[2];
												
												$customerEmail = $orderRow[3];
												
												echo "<p>Name: $customerName</p>";
												
												echo "<p>Email: $customerEmail</p>" . "<br>";
												
												echo "</div>";
											}
											
											
											
										} else {
											echo "No order found for this order UUID: $orderUUID." . "<br>";
										}
									} else {
										echo "Query failed for activity." . "<br>";
									}
									
									
								} else {
									echo "No boxes were found for your account." . "<br>";
								}
							} else {
								echo "No boxes were found for your account." . "<br>";
							}
						}
						
						
						
					} else {
						echo "Account could not be found." . "<br>";
					}
				}
				
				
				
			} else {
				// present error
				echo "No order number in request.";
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