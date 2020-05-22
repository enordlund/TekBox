<?php
	// Installation file paths
	!defined('HOME_DIRECTORY_PATH') && define('HOME_DIRECTORY_PATH', 'HOME PATH HERE');
	
	!defined('TEKBOX_DIRECTORY_PATH') && define('TEKBOX_DIRECTORY_PATH', 'TEKBOX PATH HERE');
	
	!defined('TEKBOX_LIBRARY_FILE_PATH') && define('TEKBOX_LIBRARY_FILE_PATH', 'TEKBOX_LIBRARY PATH HERE');
	
	
	
	// Database information
	!defined('DB_HOST') && define('DB_HOST', 'oniddb.cws.oregonstate.edu');
	
	!defined('DB_NAME') && define('DB_NAME', 'DATABASE NAME HERE');
	
	!defined('DB_USER') && define('DB_USER', 'DATABASE USER HERE');
	
	!defined('DB_PASS') && define('DB_PASS', 'DATABASE PASSWORD HERE');
	
	
	
	// CAS information
	!defined('CAS_HOSTNAME') && define('CAS_HOSTNAME', 'login.oregonstate.edu');
	
	!defined('CAS_CA_CHAIN_FILE_PATH') && define('CAS_CA_CHAIN_FILE_PATH', TEKBOX_DIRECTORY_PATH . 'oregonstate-edu-chain.pem');
	
	
	
	// URLs and email addresses
	!defined('TEKBOX_ROOT_URL') && define('TEKBOX_ROOT_URL', 'https://URL/HERE/');
	
	!defined('TEKBOX_EMAIL_FROM_ADDRESS') && define('TEKBOX_EMAIL_FROM_ADDRESS', 'no-reply@tekbox.cloud');
	
	!defined('TAMPERING_EMAIL_ADDRESS') && define('TAMPERING_EMAIL_ADDRESS', 'admin@tekbox.cloud');
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// NOTE: constants below this line are generally to be left alone.
	
	// TekBox Library files
	!defined('TEKBOX_DATABASE_FILE_PATH') && define('TEKBOX_DATABASE_FILE_PATH', TEKBOX_LIBRARY_FILE_PATH . 'TekBox-Database.php');
	
	!defined('APP_NETWORKING_FILE_PATH') && define('APP_NETWORKING_FILE_PATH', TEKBOX_LIBRARY_FILE_PATH . 'AppNetworking.php');
	
	!defined('TEKBOX_ACCESS_ELEMENTS_FILE_PATH') && define('TEKBOX_ACCESS_ELEMENTS_FILE_PATH', TEKBOX_LIBRARY_FILE_PATH . 'TekBox_Access_Elements.php');
	
	!defined('RANDOM_COMPAT_FILE_PATH') && define('RANDOM_COMPAT_FILE_PATH', TEKBOX_LIBRARY_FILE_PATH . 'random_compat-master/lib/random.php');
	
	!defined('CAS_CONFIG_FILE_PATH') && define('CAS_CONFIG_FILE_PATH', TEKBOX_DIRECTORY_PATH . 'cas_config.php');
	
	!defined('PHPCAS_FILE_PATH') && define('PHPCAS_FILE_PATH', TEKBOX_LIBRARY_FILE_PATH . 'phpCAS/source/CAS.php');
	
	
	
	// URLs
	!defined('TEKBOX_DASHBOARD_URL') && define('TEKBOX_DASHBOARD_URL', TEKBOX_ROOT_URL . 'dashboard/');
	
	!defined('TEKBOX_DASHBOARD_ACCOUNT_URL') && define('TEKBOX_DASHBOARD_ACCOUNT_URL', TEKBOX_DASHBOARD_URL . 'account/');
	
	!defined('TEKBOX_ACCESS_URL') && define('TEKBOX_ACCESS_URL', TEKBOX_ROOT_URL . 'access/');
	
	
	
	// Email messages
	
	// Tampering
	!defined('TEKBOX_EMAIL_TAMPERING_SUBJECT') && define('TEKBOX_EMAIL_TAMPERING_SUBJECT', 'TekBox Detected Tampering!');
	
	!defined('TEKBOX_EMAIL_TAMPERING_BODY') && define('TEKBOX_EMAIL_TAMPERING_BODY', 'TekBox Detected Tampering! Check TekBox Dashboard for more: ' . TEKBOX_DASHBOARD_URL);
	
	// Activation Block
	!defined('TEKBOX_EMAIL_ACTIVATION_BLOCK_SUBJECT') && define('TEKBOX_EMAIL_ACTIVATION_BLOCK_SUBJECT', 'Your TekBox Locker connected with a different account.');
	
	!defined('TEKBOX_EMAIL_ACTIVATION_BLOCK_BODY') && define('TEKBOX_EMAIL_ACTIVATION_BLOCK_BODY', 'Another account tried to register your TekBox Locker. Your Locker will remain registered to your account until you remove it from your account. Please visit TekBox Dashboard if you would like to do so: ' . TEKBOX_DASHBOARD_URL);
	
	// Customer order notification
	!defined('TEKBOX_EMAIL_ORDER_READY_SUBJECT') && define('TEKBOX_EMAIL_ORDER_READY_SUBJECT', 'Your TekBots Store order is ready for pickup!');
	
	!defined('TEKBOX_EMAIL_ORDER_READY_BODY') && define('TEKBOX_EMAIL_ORDER_READY_BODY', 'Your TekBots Store order is ready for pickup from a TekBox Locker at the store. When you arrive, go here to sign in with your ONID ONID account and unlock your TekBox Locker: ' . TEKBOX_ACCESS_URL);
	
	// User invitation
	!defined('TEKBOX_EMAIL_USER_INVITE_SUBJECT') && define('TEKBOX_EMAIL_USER_INVITE_SUBJECT', ' invited you to a TekBox location!');// Starts with inviter's name
	
?>
