#include <WiFi.h>
#include <WiFiClientSecure.h>
#include "esp_wpa2.h"
#include "esp_wifi.h"
#include <Wire.h>

#include <BLEDevice.h>
#include <BLEUtils.h>
#include <BLEServer.h>
#include "esp_gatts_api.h"

// SERVER DETAILS
const char* host = "web.engr.oregonstate.edu";
#define httpsPort 443

#define LOCKER_REQUEST_PATH			"/~nordlune/TestBox/locker/request"
#define LOCKER_CONFIRMATION_PATH	"/~nordlune/TestBox/locker/confirm"














#define EAP_TLS  0
#define EAP_PEAP 1
#define EAP_TTLS 2

#define EXAMPLE_WIFI_SSID       "eduroam"             //SSID (network name) for the example to connect to.

#define EXAMPLE_EAP_METHOD      EAP_PEAP                //EAP method (TLS, PEAP or TTLS) for the example to use.
#define EXAMPLE_EAP_ID          "user@oregonstate.edu" //Identity in phase 1 of EAP procedure.
#define EXAMPLE_EAP_USERNAME    "user@oregonstate.edu"             //Username for EAP method (PEAP and TTLS).
#define EXAMPLE_EAP_PASSWORD    "password"                //Password for EAP method (PEAP and TTLS).




#define SERVICE_UUID        "4fafc201-1fb5-459e-8fcc-c5c9c331914b"
#define CHARACTERISTIC_UUID "beb5483e-36e1-4688-b7f5-ea07361b26a8"


#define WIFI_TIMEOUT_SECONDS 60


// BLUETOOTH
BLEServer *pServer;
BLEService *pService;


BLECharacteristic *pCharacteristic;
bool bluetoothConnected = false;







struct wapCredentialsType {
	bool isStored;
	String ssid;
	String identity;
	String password;
	wifi_auth_mode_t authenticationMode;
};

typedef wapCredentialsType WapCredentials;

// TO BE REPLACED WITH PERMANENT STORAGE
WapCredentials storedWapCredentials;




const char* root_ca = \
"-----BEGIN CERTIFICATE-----\n" \
	"MIIF3jCCA8agAwIBAgIQAf1tMPyjylGoG7xkDjUDLTANBgkqhkiG9w0BAQwFADCB\n" \
	"iDELMAkGA1UEBhMCVVMxEzARBgNVBAgTCk5ldyBKZXJzZXkxFDASBgNVBAcTC0pl\n" \
	"cnNleSBDaXR5MR4wHAYDVQQKExVUaGUgVVNFUlRSVVNUIE5ldHdvcmsxLjAsBgNV\n" \
	"BAMTJVVTRVJUcnVzdCBSU0EgQ2VydGlmaWNhdGlvbiBBdXRob3JpdHkwHhcNMTAw\n" \
	"MjAxMDAwMDAwWhcNMzgwMTE4MjM1OTU5WjCBiDELMAkGA1UEBhMCVVMxEzARBgNV\n" \
	"BAgTCk5ldyBKZXJzZXkxFDASBgNVBAcTC0plcnNleSBDaXR5MR4wHAYDVQQKExVU\n" \
	"aGUgVVNFUlRSVVNUIE5ldHdvcmsxLjAsBgNVBAMTJVVTRVJUcnVzdCBSU0EgQ2Vy\n" \
	"dGlmaWNhdGlvbiBBdXRob3JpdHkwggIiMA0GCSqGSIb3DQEBAQUAA4ICDwAwggIK\n" \
	"AoICAQCAEmUXNg7D2wiz0KxXDXbtzSfTTK1Qg2HiqiBNCS1kCdzOiZ/MPans9s/B\n" \
	"3PHTsdZ7NygRK0faOca8Ohm0X6a9fZ2jY0K2dvKpOyuR+OJv0OwWIJAJPuLodMkY\n" \
	"tJHUYmTbf6MG8YgYapAiPLz+E/CHFHv25B+O1ORRxhFnRghRy4YUVD+8M/5+bJz/\n" \
	"Fp0YvVGONaanZshyZ9shZrHUm3gDwFA66Mzw3LyeTP6vBZY1H1dat//O+T23LLb2\n" \
	"VN3I5xI6Ta5MirdcmrS3ID3KfyI0rn47aGYBROcBTkZTmzNg95S+UzeQc0PzMsNT\n" \
	"79uq/nROacdrjGCT3sTHDN/hMq7MkztReJVni+49Vv4M0GkPGw/zJSZrM233bkf6\n" \
	"c0Plfg6lZrEpfDKEY1WJxA3Bk1QwGROs0303p+tdOmw1XNtB1xLaqUkL39iAigmT\n" \
	"Yo61Zs8liM2EuLE/pDkP2QKe6xJMlXzzawWpXhaDzLhn4ugTncxbgtNMs+1b/97l\n" \
	"c6wjOy0AvzVVdAlJ2ElYGn+SNuZRkg7zJn0cTRe8yexDJtC/QV9AqURE9JnnV4ee\n" \
	"UB9XVKg+/XRjL7FQZQnmWEIuQxpMtPAlR1n6BB6T1CZGSlCBst6+eLf8ZxXhyVeE\n" \
	"Hg9j1uliutZfVS7qXMYoCAQlObgOK6nyTJccBz8NUvXt7y+CDwIDAQABo0IwQDAd\n" \
	"BgNVHQ4EFgQUU3m/WqorSs9UgOHYm8Cd8rIDZsswDgYDVR0PAQH/BAQDAgEGMA8G\n" \
	"A1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQEMBQADggIBAFzUfA3P9wF9QZllDHPF\n" \
	"Up/L+M+ZBn8b2kMVn54CVVeWFPFSPCeHlCjtHzoBN6J2/FNQwISbxmtOuowhT6KO\n" \
	"VWKR82kV2LyI48SqC/3vqOlLVSoGIG1VeCkZ7l8wXEskEVX/JJpuXior7gtNn3/3\n" \
	"ATiUFJVDBwn7YKnuHKsSjKCaXqeYalltiz8I+8jRRa8YFWSQEg9zKC7F4iRO/Fjs\n" \
	"8PRF/iKz6y+O0tlFYQXBl2+odnKPi4w2r78NBc5xjeambx9spnFixdjQg3IM8WcR\n" \
	"iQycE0xyNN+81XHfqnHd4blsjDwSXWXavVcStkNr/+XeTWYRUc+ZruwXtuhxkYze\n" \
	"Sf7dNXGiFSeUHM9h4ya7b6NnJSFd5t0dCy5oGzuCr+yDZ4XUmFF0sbmZgIn/f3gZ\n" \
	"XHlKYC6SQK5MNyosycdiyA5d9zZbyuAlJQG03RoHnHcAP9Dc1ew91Pq7P8yF1m9/\n" \
	"qS3fuQL39ZeatTXaw2ewh0qpKJ4jjv9cJ2vhsE/zB+4ALtRZh8tSQZXq9EfX7mRB\n" \
	"VXyNWQKV3WKdwrnuWih0hKWbt5DHDAff9Yk2dDLWKMGwsAvgnEzDHNb842m1R0aB\n" \
	"L6KCq9NjRHDEjf8tM7qtj3u1cIiuPhnPQCjY/MiQu12ZIvVS5ljFH4gxQ+6IHdfG\n" \
	"jjxDah2nGN59PRbxYvnKkKj9\n" \
	"-----END CERTIFICATE-----\n";



String root_ca_cache = "";











void printToBluetooth(String message) {
	Serial.println("Printing to bluetooth: " + message);
	//delay(500);
	pCharacteristic->setValue(message.c_str());

	//std::string value = pCharacteristic->getValue();
	//Serial.println(value.c_str());

	pCharacteristic->notify();

	delay(100);
}


void printJsonToBluetooth(String json) {
	// send substrings ending with commas until end
	int fromIndex = 0;
	int toIndex = json.indexOf("}", fromIndex) + 1;

	while (toIndex > 0) {// greater than 0, because indexOf returns -1 when val isn't found (and starts with +1)
		String segment = json.substring(fromIndex, toIndex);
		printToBluetooth(segment);

		fromIndex = toIndex;
		toIndex = json.indexOf("}", fromIndex) + 1;
	}

	String lastSegment = json.substring(fromIndex);
	printToBluetooth(lastSegment);

	// send end
	printToBluetooth("jsonend.");
}









String getTOTP() {
	return "246802";
}

void setupWiFi() {
	// Set WiFi to station mode and disconnect from an AP if it was previously connected
	WiFi.mode(WIFI_STA);
	WiFi.disconnect();
}


// this struct is from: https://techtutorialsx.com/2017/06/29/esp32-arduino-getting-started-with-wifi/
String translateEncryptionType(wifi_auth_mode_t encryptionType) {
	switch (encryptionType) {
	case (WIFI_AUTH_OPEN):
			return "OPEN";
	case (WIFI_AUTH_WEP):
			return "WEP";
	case (WIFI_AUTH_WPA_PSK):
			return "WPA_PSK";
	case (WIFI_AUTH_WPA2_PSK):
			return "WPA2_PSK";
	case (WIFI_AUTH_WPA_WPA2_PSK):
			return "WPA_WPA2_PSK";
	case (WIFI_AUTH_WPA2_ENTERPRISE):
			return "WPA2_ENTERPRISE";
	}
}

wifi_auth_mode_t translateEncryptionTypeString(String encryptionType) {
	
	if (encryptionType == "OPEN")
		return WIFI_AUTH_OPEN;
	if (encryptionType == "WEP")
		return WIFI_AUTH_WEP;
	if (encryptionType == "WPA_PSK")
		return WIFI_AUTH_WPA_PSK;
	if (encryptionType == "WPA2_PSK")
		return WIFI_AUTH_WPA2_PSK;
	if (encryptionType == "WPA_WPA2_PSK")
		return WIFI_AUTH_WPA_WPA2_PSK;
	if (encryptionType == "WPA2_ENTERPRISE")
		return WIFI_AUTH_WPA2_ENTERPRISE;
	
	return WIFI_AUTH_OPEN;//NULL;
}

void scanWiFi() {
	Serial.println("scan start");

	// WiFi.scanNetworks will return the number of networks found

	WiFi.disconnect();

	int n = WiFi.scanNetworks();
	Serial.println("scan complete.");
	if (n == 0) {
		Serial.println("no networks found");
	} else {
		// create json doc for sending access points
		DynamicJsonDocument doc(3000);//~~~~~~~~~~~~~~~~~~~~~~~~~ calculate size, based on number of networks scanned. There should probably be a cap for stability.

		//JsonArray waps = doc.createNestedArray("waps");


		Serial.print(n);
		Serial.println(" networks found");
		for (int i = 0; i < n; ++i) {
			// check if the wap is already listed
			bool notListed = true;
			for (int j = 0; j < i; ++j) {
				if ((WiFi.SSID(i) == WiFi.SSID(j)) && (WiFi.encryptionType(i) == WiFi.encryptionType(j))) {
					notListed = false;
					break;
				}
			}

			if (notListed == true) {
				// populate json doc
				JsonObject point = doc.createNestedObject();
				point["ssid"] = WiFi.SSID(i);
				point["rssi"] = WiFi.RSSI(i);
				point["encryption"] = translateEncryptionType(WiFi.encryptionType(i));
			}

		}


		// send json to app via bluetooth

		String jsonPoints = "";

		serializeJson(doc, jsonPoints);

		WiFi.disconnect();

		WiFi.scanDelete();

		printJsonToBluetooth(jsonPoints);
	}
}



bool observeWiFiConnectionWithTimeout(int seconds) {
	int timeoutCounter = 0;

	while (WiFi.status() != WL_CONNECTED) {
		if (timeoutCounter > seconds) {
			Serial.println("ERROR: Wi-Fi timeout");
			return false;
		} else {
			delay(1000);
			Serial.print(".");
			timeoutCounter += 1;
		}
	}

	Serial.println("");
	Serial.println("Wi-Fi connected");
	Serial.println("IP address: ");
	Serial.println(WiFi.localIP());

	return true;
}

bool connectToOpenWap(String ssid) {
	Serial.println("connectToOpenWap()");

	Serial.println();
	Serial.println();
	Serial.print("Connecting to ");
	Serial.println(ssid);

	//WiFi.disconnect(true);
	//WiFi.disconnect(false);

	//WiFi.mode(WIFI_STA);
	WiFi.begin(ssid.c_str());

	return observeWiFiConnectionWithTimeout(WIFI_TIMEOUT_SECONDS);
}


bool connectToPasswordWap(String ssid, String password) {
	Serial.println("connectToPasswordWap()");

	Serial.println();
	Serial.println();
	Serial.print("Connecting to ");
	Serial.println(ssid);

	//WiFi.disconnect(true);
	//WiFi.disconnect(false);
	WiFi.disconnect();

	WiFi.mode(WIFI_STA);
	WiFi.begin(ssid.c_str(), password.c_str());

	return observeWiFiConnectionWithTimeout(WIFI_TIMEOUT_SECONDS);
}

bool connectToIdentityWap(String ssid, String username, String password) {
	Serial.println("connectToIdentityWap()");

	Serial.println();
	Serial.println();
	Serial.print("Connecting to ");
	Serial.println(ssid);


//	WiFi.disconnect(true);
//	//WiFi.disconnect(false);
//
//	WiFi.mode(WIFI_STA);
//
//	esp_wifi_sta_wpa2_ent_set_identity((uint8_t *)"anonymous@oregonstate.edu", strlen("anonymous@oregonstate.edu")); //provide identity
////	esp_wifi_sta_wpa2_ent_set_identity((uint8_t *)identity.c_str(), strlen(identity.c_str())); //provide identity
////	esp_wifi_sta_wpa2_ent_set_username((uint8_t *)identity.c_str(), strlen(identity.c_str())); //provide username --> identity and username is same
//	esp_wifi_sta_wpa2_ent_set_username((uint8_t *)username.c_str(), strlen(username.c_str())); //provide username --> identity and username is same
//	esp_wifi_sta_wpa2_ent_set_new_password((uint8_t *)password.c_str(), strlen(password.c_str())); //provide password
//	esp_wpa2_config_t config = WPA2_CONFIG_INIT_DEFAULT(); //set config settings to default
//
//	esp_wifi_sta_wpa2_ent_enable(&config); //set config settings to enable function
//
//	WiFi.begin(ssid.c_str());
	
	WiFi.begin(EXAMPLE_WIFI_SSID, NULL, 0 , NULL, false);
    if( esp_wifi_sta_wpa2_ent_set_ca_cert((uint8_t *)root_ca, strlen(root_ca)) ){
        Serial.println("Failed to set WPA2 CA Certificate");
        return false;
    }
//    if( esp_wifi_sta_wpa2_ent_set_cert_key((uint8_t *)client_crt, strlen(client_crt), (uint8_t *)client_key, strlen(client_key), NULL, 0) ){
//        Serial.println("Failed to set WPA2 Client Certificate and Key");
//        return;
//    }
    if( esp_wifi_sta_wpa2_ent_set_identity((uint8_t *)EXAMPLE_EAP_ID, strlen(EXAMPLE_EAP_ID)) ){
        Serial.println("Failed to set WPA2 Identity");
        return false;
    }
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Disabled until this type of wifi works
    if (EXAMPLE_EAP_METHOD == EAP_PEAP || EXAMPLE_EAP_METHOD == EAP_TTLS) {
		return false;// this will go away once this is supported
//        if( esp_wifi_sta_wpa2_ent_set_username((uint8_t *)EXAMPLE_EAP_USERNAME, strlen(EXAMPLE_EAP_USERNAME)) ){
//            Serial.println("Failed to set WPA2 Username");
//            return false;
//        }
//        if( esp_wifi_sta_wpa2_ent_set_password((uint8_t *)EXAMPLE_EAP_PASSWORD, strlen(EXAMPLE_EAP_PASSWORD)) ){
//            Serial.println("Failed to set WPA2 Password");
//            return false;
//        }
    }
//    if( esp_wifi_sta_wpa2_ent_enable() ){
//        Serial.println("Failed to enable WPA2");
//        return;
//    }
	
	esp_wpa2_config_t config = WPA2_CONFIG_INIT_DEFAULT();
	if( esp_wifi_sta_wpa2_ent_enable(&config) ){
		Serial.println("Failed to enable WPA2");
		return false;
	}
    esp_wifi_connect();

	return observeWiFiConnectionWithTimeout(WIFI_TIMEOUT_SECONDS);
}


WapCredentials newWapCredentials(String ssid, String identity, String password, wifi_auth_mode_t authenticationMode) {
	WapCredentials newCredentials;

	newCredentials.isStored = false;
	newCredentials.ssid = ssid;
	newCredentials.identity = identity;
	newCredentials.password = password;
	newCredentials.authenticationMode = authenticationMode;

	return newCredentials;
}


void storeWapCredentials(WapCredentials credentials) {
	storedWapCredentials.ssid = credentials.ssid;
	storedWapCredentials.identity = credentials.identity;
	storedWapCredentials.password = credentials.password;
	storedWapCredentials.authenticationMode = credentials.authenticationMode;

	storedWapCredentials.isStored = true;
}


bool tryStoredWapCredentials() {
	bool isConnectedToWap = false;

	if (storedWapCredentials.isStored == true) {
		wifi_auth_mode_t encryption = storedWapCredentials.authenticationMode;

		String ssid = storedWapCredentials.ssid;

		if (encryption == WIFI_AUTH_OPEN) {
			// attempt connection with ssid
			isConnectedToWap = connectToOpenWap(ssid);

		} else if (encryption == WIFI_AUTH_WEP) {
			// parse password
			String password = storedWapCredentials.password;

			isConnectedToWap = connectToPasswordWap(ssid, password);

		} else if (encryption == WIFI_AUTH_WPA_PSK) {
			// parse password
			String password = storedWapCredentials.password;

			isConnectedToWap = connectToPasswordWap(ssid, password);

		} else if (encryption == WIFI_AUTH_WPA2_PSK) {
			// parse password
			String password = storedWapCredentials.password;

			isConnectedToWap = connectToPasswordWap(ssid, password);

		} else if (encryption == WIFI_AUTH_WPA_WPA2_PSK) {
			// parse password
			String password = storedWapCredentials.password;

			isConnectedToWap = connectToPasswordWap(ssid, password);

		} else if (encryption == WIFI_AUTH_WPA2_ENTERPRISE) {
			// parse identity, and password
			String identity = storedWapCredentials.identity;
			String password = storedWapCredentials.password;

			isConnectedToWap = connectToIdentityWap(ssid, identity, password);
		} else {
			Serial.println("ERROR: could not determine security type");
		}
	}


	return isConnectedToWap;
}







// SERVER CONNECTION FUNCTIONS


String sendRequest(String request) {
	// Use WiFiClientSecure class to create TCP connections
	WiFiClientSecure client;

	Serial.print("connecting to ");
	Serial.print(host);
	Serial.print(':');
	Serial.println(httpsPort);

	Serial.println("Using certificate");
//	Serial.println(root_ca_cache);
	//client.setFingerprint(fingerprint);
	client.setCACert(root_ca_cache.c_str());

	if (!client.connect(host, httpsPort)) {
		Serial.println("connection failed");
		delay(5000);
		return "ERROR";
	}

	// This will send a string to the server
	if (client.connected()) {
		Serial.println("sending data to server:");

		Serial.println(request);

		client.println(request);

		client.println();
	}

	// wait for data to be available
	unsigned long timeout = millis();
	while (client.available() == 0) {
		if (millis() - timeout > 5000) {
			Serial.println(">>> Client Timeout !");
			client.stop();
			delay(60000);
			return "ERROR";
		}
	}

	// Read all the lines of the reply from server and print them to Serial
	Serial.println("receiving from remote server");
	// not testing 'client.connected()' since we do not need to send data here

	int lineCount = 0;

	String responseString = "";

	while (client.available()) {
		lineCount++;
		/*
		char ch = static_cast<char>(client.read());
		Serial.print(ch);
		*/
		String line = client.readStringUntil('\n');
		Serial.println(line);
		
		if (lineCount == 9) {
			responseString = line;
		}
	}



	Serial.println();
	Serial.println("closing connection");
	client.stop();



	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ parse and return application data
	return responseString;
}






String sendPostRequest(String requestType) {
	String postURL = LOCKER_REQUEST_PATH;

	String uidData = "uid=" + uid;

	String passwordData = "password=" + lockerServerPassword;

	String totpData = "totp=" + getTOTP();

	//String requestType = "request=PRESS";

	String data = uidData + "&" + passwordData + "&" + totpData + "&request=" + requestType;

	String contentLength = String(data.length());

	String requestString = String("POST ") + postURL + " HTTP/1.1\r\n" +
		"Host: " + host + "\r\n" +
		"User-Agent: BuildFailureDetectorESP8266\r\n" +
		"Connection: close\r\n" +
		"Content-Type: application/x-www-form-urlencoded\r\n" +
		"Content-Length: " + contentLength + "\r\n\r\n" +
		data + "\r\n" + "\r\n";

	//Serial.println(requestString);

	String requestUID = "1357";// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~`test UUID
	String confirmationData = "confirmation=";

	String responseData = sendRequest(requestString);

	return responseData;
}


void sendRequestConfirmation(String requestUID, String confirmation) {
	String uidData = "uid=" + uid;
	
	String confirmationData = "confirmation=" + confirmation;

	String passwordData = "password=" + lockerServerPassword;

	String totpData = "totp=" + getTOTP();

	String requestUIDData = "requestUID=" + requestUID;

	String postURL = LOCKER_CONFIRMATION_PATH;

	String data = uidData + "&" + passwordData + "&" + totpData + "&" + requestUIDData + "&" + confirmationData;

	String contentLength = String(data.length());

	String requestString = String("POST ") + postURL + " HTTP/1.1\r\n" +
		"Host: " + host + "\r\n" +
		"User-Agent: BuildFailureDetectorESP8266\r\n" +
		"Connection: close\r\n" +
		"Content-Type: application/x-www-form-urlencoded\r\n" +
		"Content-Length: " + contentLength + "\r\n\r\n" +
		data + "\r\n" +
		"\r\n";

	sendRequest(requestString);
	
	return;
}







bool serverAuthenticationIsGood() {
	
	String requestUID = "";
	String confirmation = "";
	
	String responseData = sendPostRequest("TEST");

	bool authenticationWasSuccessful = false;

	// parse requestUID and command from responseData
	if (responseData.startsWith("test=")) {

		if (responseData.substring(5, 9) == "PASS") {
			// lock the box
			Serial.println("Server authentication passed.");
			confirmation = "PASS";

			requestUID = responseData.substring(15, 31);
			
			if (responseData.length() > 39) {
				// server provided offline key
				String responseOfflineKey = responseData.substring(39, 55);
				Serial.print("Setting offline key from response: ");
				Serial.println(responseOfflineKey);
				
				// store it to flash
				writeOfflineKey(responseOfflineKey);
			}
			
			authenticationWasSuccessful = true;
		} else if (responseData.substring(5, 9) == "FAIL") {
			// unlock the box
			Serial.println("Server authentication failed.");
			confirmation = "FAIL";

			requestUID = responseData.substring(15, 31);
		} else {
			confirmation = "NULL";
			requestUID = responseData.substring(15, 31);
		}
		
	} else if (responseData.startsWith("ERROR")) {
		Serial.println("ERROR: request failed");
		return false;
	}
	
	sendRequestConfirmation(requestUID, confirmation);
	
	return authenticationWasSuccessful;
}







void postPress() {
	String responseData = sendPostRequest("PRESS");


	String requestUID = "1357";// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~`test UUID
	String confirmation = "";

	// parse requestUID and command from responseData
	if (responseData.startsWith("command=")) {

		if (responseData.substring(8, 12) == "LOCK") {
			// lock the box
			Serial.println("LOCKING");
			confirmation = lock();

			requestUID = responseData.substring(18, 34);
		} else if (responseData.substring(8, 14) == "UNLOCK") {
			// unlock the box
			Serial.println("UNLOCKING");
			confirmation = unlock();

			requestUID = responseData.substring(20, 36);
		}
		
	} else if (responseData.startsWith("ERROR")) {
		Serial.println("ERROR: request failed");
		return;
	}
	
	sendRequestConfirmation(requestUID, confirmation);
}




void postTamper() {
	String responseData = sendPostRequest("TAMPER");
}




bool testCertificate(const char * testCert) {
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ works for WiFiClient, but not WiFiClientSecure with root_ca...

	Serial.println("serverConnectionIsGood()");

	//BLEDevice::init("Serial Portal");

	//  deinitBLE();

	// Use WiFiClientSecure class to create TCP connections
	WiFiClientSecure testClient;

	Serial.print("connecting to ");
	Serial.print(host);
	Serial.print(':');
	Serial.println(httpsPort);

	//Serial.printf("Using certificate '%s'\n", storedRootCA);
	//client.setFingerprint(fingerprint);

	testClient.setCACert(testCert);

	if(WiFi.status() == WL_CONNECTED) {
		Serial.println("WiFi connected.");
	} else {
		Serial.println("WiFi not connected.");
	}


	//delay(5000);

	Serial.println("attempting client connection");
	if (!testClient.connect(host, httpsPort)) {
		Serial.println("client connection failed.");
		//delay(500);
		testClient.stop();
		//BLEDevice::init("Serial Portal");
		return false;
	} else {
		Serial.println("client connection successful.");
		testClient.stop();
		//BLEDevice::init("Serial Portal");
		return true;
	}
}






bool cacheRootCertificate() {
	String rootCertString = readFile(SPIFFS, "/rootCert.txt");

	if (rootCertString.length() > 0) {
		root_ca_cache = rootCertString;
		return true;
	}
	return false;
}

bool rootCertificateIsReady() {
	if (cacheRootCertificate() == true) {
		return true;
	} else {
		String tempCert = readFile(SPIFFS, "/tempCert.txt");
		if (tempCert.length() > 0) {
			// test the certificate
			//        tempCert += '\n';
			if (testCertificate(tempCert.c_str()) == true) {
				// store it as the new actual certificate
				if (writeFile(SPIFFS, "/rootCert.txt", tempCert.c_str())) {
					deleteFile(SPIFFS, "/tempCert.txt");
					return cacheRootCertificate();
				}
			} else {
				return false;
			}
			
		} else {
			return false;
		}
	}

}




bool serverIsReady() {
	if (rootCertificateIsReady() && serverAuthenticationIsGood()) {
		return true;
	} else {
		return false;
	}
}







//bool serverReady = false;




bool storeWAPCredentialsOpen(const char * ssid) {
	String wapEncryptionTypeString = translateEncryptionType(WIFI_AUTH_OPEN);

	if (writeFile(SPIFFS, "/wapEncryption.txt", wapEncryptionTypeString.c_str()) == true) {
		// store ssid
		if (writeFile(SPIFFS, "/wapSSID.txt", ssid) == true) {
			return true;
		}
	}
	
	return false;
}

bool storeWAPCredentialsPassword(const char * ssid, const char * password, wifi_auth_mode_t encryption) {
	String wapEncryptionTypeString = translateEncryptionType(encryption);

	Serial.println("Storing credentials");
	Serial.print("wapEncryptionTypeString: ");
	Serial.println(wapEncryptionTypeString);

	if (writeFile(SPIFFS, "/wapEncryption.txt", wapEncryptionTypeString.c_str()) == true) {
		// store ssid
		if (writeFile(SPIFFS, "/wapSSID.txt", ssid) == true) {
			if (writeFile(SPIFFS, "/wapPassword.txt", password) == true) {
				return true;
			}
		}
	}

	return false;
}

bool storeWAPCredentialsIdentity(const char * ssid, const char * password, const char * identity) {
	String wapEncryptionTypeString = translateEncryptionType(WIFI_AUTH_OPEN);

	if (writeFile(SPIFFS, "/wapEncryption.txt", wapEncryptionTypeString.c_str()) == true) {
		// store ssid
		if (writeFile(SPIFFS, "/wapSSID.txt", ssid) == true) {
			if (writeFile(SPIFFS, "/wapPassword.txt", password) == true) {
				if (writeFile(SPIFFS, "/wapIdentity.txt", identity) == true) {
					return true;
				}
			}
		}
	}

	return false;
}

bool cacheWAPCredentials() {
	Serial.println("cacheWAPCredentials()");

	String wapEncryption = readFile(SPIFFS, "/wapEncryption.txt");

	if (wapEncryption.length() > 0) {
		wifi_auth_mode_t encryption = translateEncryptionTypeString(wapEncryption);

		if (encryption == WIFI_AUTH_OPEN) {//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ make this normal
			Serial.println("dectected open access point");
			// cache encryption and ssid
			String ssidString = readFile(SPIFFS, "/wapSSID.txt");
			if (ssidString.length() > 0) {

				WapCredentials newCredentials = newWapCredentials(ssidString, "", "", encryption);

				storeWapCredentials(newCredentials);
				return true;
			}
		} else if (encryption == WIFI_AUTH_WEP) {
			Serial.println("dectected WEP access point");
			// cache encryption, ssid, and password
			String ssidString = readFile(SPIFFS, "/wapSSID.txt");
			if (ssidString.length() > 0) {

				String passwordString = readFile(SPIFFS, "/wapPassword.txt");

				if (passwordString.length() > 0) {
					WapCredentials newCredentials = newWapCredentials(ssidString, "", passwordString, encryption);

					storeWapCredentials(newCredentials);
					return true;
				}
			}
		} else if (encryption == WIFI_AUTH_WPA_PSK) {
			Serial.println("dectected WPA_PSK access point");
			// cache encryption, ssid, and password
			String ssidString = readFile(SPIFFS, "/wapSSID.txt");
			if (ssidString.length() > 0) {

				String passwordString = readFile(SPIFFS, "/wapPassword.txt");

				if (passwordString.length() > 0) {
					WapCredentials newCredentials = newWapCredentials(ssidString, "", passwordString, encryption);

					storeWapCredentials(newCredentials);
					return true;
				}
			}
		} else if (encryption == WIFI_AUTH_WPA2_PSK) {
			Serial.println("dectected WPA2_PSK access point");
			// cache encryption, ssid, and password
			String ssidString = readFile(SPIFFS, "/wapSSID.txt");
			if (ssidString.length() > 0) {

				String passwordString = readFile(SPIFFS, "/wapPassword.txt");

				if (passwordString.length() > 0) {
					WapCredentials newCredentials = newWapCredentials(ssidString, "", passwordString, encryption);

					storeWapCredentials(newCredentials);
					return true;
				}
			}
		} else if (encryption == WIFI_AUTH_WPA_WPA2_PSK) {
			Serial.println("dectected WPA_WPA2_PSK access point");
			// cache encryption, ssid, and password
			String ssidString = readFile(SPIFFS, "/wapSSID.txt");
			if (ssidString.length() > 0) {

				String passwordString = readFile(SPIFFS, "/wapPassword.txt");

				if (passwordString.length() > 0) {
					WapCredentials newCredentials = newWapCredentials(ssidString, "", passwordString, encryption);

					storeWapCredentials(newCredentials);
					return true;
				}
			}
		} else if (encryption == WIFI_AUTH_WPA2_ENTERPRISE) {
			Serial.println("dectected WPA2_ENTERPRISE access point");
			// cache encryption, ssid, identity, password
			String ssidString = readFile(SPIFFS, "/wapSSID.txt");
			if (ssidString.length() > 0) {

				String passwordString = readFile(SPIFFS, "/wapPassword.txt");

				if (passwordString.length() > 0) {

					String identityString = readFile(SPIFFS, "/wapIdentity.txt");

					if (identityString.length() > 0) {
						WapCredentials newCredentials = newWapCredentials(ssidString, "", passwordString, encryption);

						storeWapCredentials(newCredentials);
						return true;
					}

				}
			}
		} else {
			Serial.println("encryption type did not match cases");
		}
	} else {
		Serial.println("wapEncryption length < 1");
	}
	Serial.println("unable to determine wifi security type");
	return false;
}



bool wifiIsReady() {
	if (cacheWAPCredentials() == true) {
		return tryStoredWapCredentials();
	}
}





void eraseCredentials() {
	deleteFile(SPIFFS, "/wapEncryption.txt");
	deleteFile(SPIFFS, "/wapSSID.txt");
	deleteFile(SPIFFS, "/wapPassword.txt");

	deleteFile(SPIFFS, "/tempCert.txt");
	deleteFile(SPIFFS, "/rootCert.txt");
}
























void bluetoothConnectedMode() {
	Serial.println("Bluetooth connected mode.");
	Serial.println("Device connected.");

	pCharacteristic->setValue("Device Connected.");
	pCharacteristic->notify();

	while (bluetoothConnected) {
		if (Serial.available()) {
			String serialString = Serial.readString();
			Serial.println("Sent: " + serialString);
			//std::string btOut = serialString;
			printToBluetooth(serialString);
		}
	}
}

void bluetoothPairingMode() {
	Serial.println("Bluetooth pairing mode.");
	Serial.println("Pairing available");
	while (!bluetoothConnected) {
		digitalWrite(LED_BUILTIN, LOW);
		delay(1000);
		digitalWrite(LED_BUILTIN, HIGH);
		delay(1000);
	}
	// enter connected mode
	bluetoothConnectedMode();
}




void handleConnectionRequestMessage() {
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ eventually, parse the certificate from the message
	Serial.println("handleConnectionRequestMessage()");

	if (offlineKey.length() > 0) {
		// offline key is cached, so return uid
		Serial.println("Found offline key in cache. Sending uid.");
		printToBluetooth("c:" + uid);
	} else {
		Serial.println("Didn't find offline key in cache. Sending connection confirmation.");
		printToBluetooth("c.");
	}
	return;
}




bool handleWapCredentialsMessage(String credentialsMessage) {
	// first shave off the header message for JSON
	credentialsMessage.remove(0, 3);

	// next, parse the JSON to get the encryption type.
	DynamicJsonDocument doc(500);//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ calculate based on assumed maximum size for credentials of one wap

	//char json[] = credentialsMessage;

	DeserializationError error = deserializeJson(doc, credentialsMessage);

	if (error) {
		Serial.print("ERROR");
		Serial.println(error.c_str());
		return false;
	}

	wifi_auth_mode_t encryption = translateEncryptionTypeString(doc["encryption"]);

	String ssid = doc["ssid"];

	bool isConnectedToWap = false;

	if (encryption == WIFI_AUTH_OPEN) {
		// attempt connection with ssid
		isConnectedToWap = connectToOpenWap(ssid);

		if (isConnectedToWap == true) {
			storeWAPCredentialsOpen(ssid.c_str());
		}

	} else if (encryption == WIFI_AUTH_WEP) {
		// parse password
		String password = doc["password"];

		isConnectedToWap = connectToPasswordWap(ssid, password);

		if (isConnectedToWap == true) {
			storeWAPCredentialsPassword(ssid.c_str(), password.c_str(), WIFI_AUTH_WEP);
		}

	} else if (encryption == WIFI_AUTH_WPA_PSK) {
	// parse password
	String password = doc["password"];

	isConnectedToWap = connectToPasswordWap(ssid, password);

	if (isConnectedToWap == true) {
		storeWAPCredentialsPassword(ssid.c_str(), password.c_str(), WIFI_AUTH_WPA_PSK);
	}

	} else if (encryption == WIFI_AUTH_WPA2_PSK) {
	// parse password
	String password = doc["password"];

	isConnectedToWap = connectToPasswordWap(ssid, password);

	if (isConnectedToWap == true) {
		storeWAPCredentialsPassword(ssid.c_str(), password.c_str(), WIFI_AUTH_WPA2_PSK);
	}

	} else if (encryption == WIFI_AUTH_WPA_WPA2_PSK) {
		// parse password
		String password = doc["password"];

		isConnectedToWap = connectToPasswordWap(ssid, password);

		if (isConnectedToWap == true) {
			storeWAPCredentialsPassword(ssid.c_str(), password.c_str(), WIFI_AUTH_WPA_WPA2_PSK);
		}

	} else if (encryption == WIFI_AUTH_WPA2_ENTERPRISE) {
		// parse identity, and password
		String identity = doc["identity"];
		String password = doc["password"];

		isConnectedToWap = connectToIdentityWap(ssid, identity, password);

		if (isConnectedToWap == true) {
			storeWAPCredentialsIdentity(ssid.c_str(), password.c_str(), identity.c_str());
		}
	} else {
		Serial.println("ERROR: could not determine security type");
	}


//	if (writeFile(SPIFFS, "/tempCert.txt", root_ca) == true) {
//		ESP.restart();
//		String receivedString = "rcr:" + uid;
//		printToBluetooth(receivedString);
//		ESP.restart();
//	}



	if (isConnectedToWap == true) {
		// send success to app, to receive root certificate.
		Serial.println("connection successful");

		// save the credentials


		printToBluetooth("wcs.");

		return true;
	} else {
		// send error to app
		printToBluetooth("wcf.");

		return false;
	}
}









void deinitBLE() {
	/*
	// disconnect client from: https://www.esp32.com/viewtopic.php?t=3923
	ESP_LOGD(LOG_TAG, ">> disconnectClient()");
	esp_err_t errRc = ::esp_ble_gatts_close(getGattsIf(), getConnId());
	if (errRc != ESP_OK) {
	ESP_LOGE(LOG_TAG, "esp_ble_gatts_close: rc=%d %s", errRc, GeneralUtils::errorToString(errRc));
	return;
	}
	ESP_LOGD(LOG_TAG, "<< disconnectClient()");
	*/
	Serial.println("deinitBLE()");

	//  BLEDevice::deinit();

	Serial.println(" end of deinitBLE()");
}

void handleRootCertificateMessage(String message) {
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ eventually, parse the certificate from the message
	Serial.println("storing root certificate from code");

//	Serial.println(root_ca);

	// store temporary certificate
	if (writeFile(SPIFFS, "/tempCert.txt", root_ca) == true) {

		// tell app that the certificate was received.
		
		String responseString = "rcr:" + uid;
		
		printToBluetooth(responseString);
		
		// wait for app to receive the message, and tell the locker to restart

		// restart, disconnecting from app, and then finish confirming on reboot
//		ESP.restart();
		
		
//		printToBluetooth("rcs.");
		return;
	}

	// send failure to app
	printToBluetooth("rcf.");
	return;
}



void handleMessage(String message) {
	Serial.print("Handling message: ");
	Serial.println(message);
	if (message == "c?") {
		handleConnectionRequestMessage();
	} else if (message == "ws?") {
		// scan wifi list request
		scanWiFi();
	} else if (message.startsWith("wc:")) {
		// parse following JSON, and attempt connecting to the network.
		handleWapCredentialsMessage(message);
	} else if (message.startsWith("rc:")) {
		// parse root certificate from message
		handleRootCertificateMessage(message);
	} else if (message.startsWith("ul")) {
		if (message.startsWith("ul"+offlineKey)) {
			unlock();
		}
	} else if (message.startsWith("lk")) {
		if (message.startsWith("lk"+offlineKey)) {
			lock();
		}
	} else if (message.startsWith("rs")) {
		Serial.println("Should restart");
		ESP.restart();
	} else {
		Serial.println("ERROR: message type unrecognized.");
	}
}





class MyServerCallbacks: public BLEServerCallbacks {
	void onConnect(BLEServer* pServer) {
		Serial.println("bluetoothConnected = true");
		bluetoothConnected = true;
	}

	void onDisconnect(BLEServer* pServer) {
		Serial.println("bluetoothConnected = false");
		bluetoothConnected = false;
	}
};



class MyCallbacks: public BLECharacteristicCallbacks {
	void onWrite(BLECharacteristic *pCharacteristic) {
		std::string value = pCharacteristic->getValue();
		//Serial.println("onWrite()");

		if (value.length() > 0) {
			//Serial.println("*********");
			Serial.print("Received: ");
			for (int i = 0; i < value.length(); i++)
				Serial.print(value[i]);

			Serial.println();
			//Serial.println("*********");

			// handle requests
			handleMessage(value.c_str());
		}
	}
};






void initBLE() {
	Serial.println("Starting BLE work!");


	BLEDevice::init("TekBox");
	pServer = BLEDevice::createServer();

	pServer->setCallbacks(new MyServerCallbacks());

	pService = pServer->createService(SERVICE_UUID);
	pCharacteristic = pService->createCharacteristic(
													 CHARACTERISTIC_UUID,
													 BLECharacteristic::PROPERTY_READ | BLECharacteristic::PROPERTY_WRITE
													 );
	pCharacteristic->setCallbacks(new MyCallbacks());
	pCharacteristic->setValue("Hello World says Neil");
	pService->start();
	// BLEAdvertising *pAdvertising = pServer->getAdvertising();  // this still is working for backward compatibility
	BLEAdvertising *pAdvertising = BLEDevice::getAdvertising();
	pAdvertising->addServiceUUID(SERVICE_UUID);
	pAdvertising->setScanResponse(true);
	pAdvertising->setMinPreferred(0x06);  // functions that help with iPhone connections issue
	pAdvertising->setMinPreferred(0x12);
	BLEDevice::startAdvertising();
	//pAdvertising->start();
	Serial.println("Characteristic defined! Now you can read it in your phone!");

	return;
}
