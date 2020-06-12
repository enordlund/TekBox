/*
	Based on Neil Kolban example for IDF: https://github.com/nkolban/esp32-snippets/blob/master/cpp_utils/tests/BLE%20Tests/SampleServer.cpp
	Ported to Arduino ESP32 by Evandro Copercini
	updates by chegewara
*/


// BOARD DETAILS
String uid = "92345678";
String lockerServerPassword = "thisISaPassw0rd";
String offlineKey = "";// server will provide this

#include <TekBoxLocker.h>


// See the following for generating UUIDs:
// https://www.uuidgenerator.net/




// TOTP stuff
// shared secret
uint8_t hmacKey[] = {0x34, 0x67, 0x78, 0x71, 0x40, 0x6d, 0x5a, 0x3b, 0x7e, 0x4e};
//TOTP totp = TOTP(hmacKey, 10);


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ END OF PERMANENT STORAGE STUFF





void setup() {
	Serial.begin(115200);

	// button setup
	pinMode(buttonPin, INPUT_PULLUP);

	pinMode(SOLENOID, OUTPUT);

	pinMode(DISPLAY_POWER, OUTPUT);
	digitalWrite(DISPLAY_POWER, HIGH);

	if(!SPIFFS.begin(FORMAT_SPIFFS_IF_FAILED, "/spiffs", 20)){
		Serial.println("SPIFFS Mount Failed");
		return;
	}

	// Uncomment the following line to include default Wi-Fi credentials. See Locker README for more.
//	setDefaultWiFiCredentials("name", "identity", "password", "AUTH_MODE");

 



	if (wifiIsReady() == true) {
		if (rootCertificateIsReady() == true) {
			if (serverAuthenticationIsGood() == true) {
				// the wifi credentials, root certificate, and server authentication work
				// continue to the loop
       cacheOfflineKey();
				return;
			}
		}
	}

	// the wifi credentials or root certificate don't work, so go into setup/offline mode.

  
	Serial.println("Network credentials did not pass. Starting Offline Mode.");
    
    eraseNetworkCredentials();
    
	initBLE();
}

void loop() {
	// put your main code here, to run repeatedly:
	//
	// see if wifi & server are connected
	
	// figure out state of ESP
	if ((WiFi.status() == WL_CONNECTED) || (wifiIsReady())) {
		
		Serial.println("Wi-Fi connected.");
		
		// check connection with server
		Serial.println("Testing connection to server...");
		if (serverIsReady()) {
			// act normally
			normalOperation();
		} else {
			bluetoothPairingMode();
		}
	} else {
		bluetoothPairingMode();
	}
}
