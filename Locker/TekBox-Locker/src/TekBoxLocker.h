#include <ArduinoJson.h>
#include "TekBox-Locker/TekBoxLockerDrivers.hpp"
#include "TekBox-Locker/TekBoxLockerFileSystem.hpp"
#include "TekBox-Locker/TekBoxLockerWireless.hpp"




void eraseNetworkCredentials() {
	// first cache offlineKey
	
	cacheOfflineKey();
	
	formatSpiffs();
	
	// write the key back to the file system
	
	if (offlineKey.length() > 0) {
		writeOfflineKey(offlineKey);
	}
	
}

void credentialsFailedOperation() {
	eraseNetworkCredentials();
	ESP.restart();
}

void normalOperation() {
	Serial.println("Normal operation mode.");
	lock();

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ change this to interrupt, turn on wifi first when button is pressed
	while (WiFi.status() == WL_CONNECTED) {
		
		int buttonState = digitalRead(buttonPin);

		if (buttonState == HIGH) {
			wasHigh = true;
		}
		if (buttonState == LOW && wasHigh) {
			postPress();
			wasHigh = false;
		}
	}
	
	credentialsFailedOperation();
}


