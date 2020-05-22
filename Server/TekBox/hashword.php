<html> 
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>TekBox Hashword</title>
		
		<link type = "text/css" rel = "stylesheet" href = "../../style.css">
		
		<script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		
	</head> 
	<body> 

<?php
	
	require_once "./tekbox_config.php";
	require_once RANDOM_COMPAT_FILE_PATH;
	
	// generate a random password, and a corresponding hash value.
	$password = bin2hex(random_bytes(32));
	
	$hash = password_hash($password, PASSWORD_DEFAULT);
	
	$uid = bin2hex(random_bytes(16));
	
	echo "UID: " . $uid;
	
	echo "<br>";
	
	echo "Password: " . $password;
	
	echo "<br>";
	
	echo "Hash: " . $hash;
	
	
	
?>
	</body>
</html>