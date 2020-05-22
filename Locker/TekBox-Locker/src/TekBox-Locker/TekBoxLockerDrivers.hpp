
#define LED_BUILTIN 13

#define SOLENOID 16

#define DISPLAY_POWER 12

// box status variables
bool isLocked = false;
bool shouldSetup = false;

// button stuff
const int buttonPin = 4;
bool wasHigh = false;
bool wasPushed = false;





String unlock() {
  digitalWrite(SOLENOID, HIGH);   // Turn the LED on (Note that HIGH is the voltage level
  // but actually the LED is on; this is because
  // it is active low on the ESP-01)

  // "unlocking the box"
  isLocked = false;

  // send confirmation
  if (!isLocked) {
    return "UNLOCK";
  } else {
    return "FAILED";
  }
}


String lock() {
  digitalWrite(SOLENOID, LOW);   // Turn the LED on (Note that HIGH is the voltage level
  // but actually the LED is on; this is because
  // it is active low on the ESP-01)

  // "locking the box"
  isLocked = true;
  
  // send confirmation
  if (isLocked) {
    return "LOCK";
  } else {
    return "FAILED";
  }
}




void unlockLED() {
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
}


void lockLED() {
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
}
