
// SPIFFS file system support
//#include "FS.h"
#include "SPIFFS.h"

#define FORMAT_SPIFFS_IF_FAILED true




// SPIFFS functions

void listDir(fs::FS &fs, const char * dirname, uint8_t levels){
    Serial.printf("Listing directory: %s\r\n", dirname);

    File root = fs.open(dirname);
    if(!root){
        Serial.println("- failed to open directory");
        return;
    }
    if(!root.isDirectory()){
        Serial.println(" - not a directory");
        return;
    }

    File file = root.openNextFile();
    while(file){
        if(file.isDirectory()){
            Serial.print("  DIR : ");
            Serial.println(file.name());
            if(levels){
                listDir(fs, file.name(), levels -1);
            }
        } else {
            Serial.print("  FILE: ");
            Serial.print(file.name());
            Serial.print("\tSIZE: ");
            Serial.println(file.size());
        }
        file = root.openNextFile();
    }
}

String readFile(fs::FS &fs, const char * path){
    Serial.printf("Reading file: %s\r\n", path);
    
    String fileBuffer = "";
    
    File file = fs.open(path);
    if(!file || file.isDirectory()){
        Serial.println("- failed to open file for reading");
        return fileBuffer;
    }

    Serial.println("- read from file:");

    /*
    while(file.available()){
        fileBuffer += file.read();
        //Serial.write(file.read());
    }
    */

    fileBuffer = file.readString();
    
    Serial.println(fileBuffer);

    return fileBuffer;
}

bool writeFile(fs::FS &fs, const char * path, const char * message){
    Serial.printf("Writing file: %s\r\n", path);

    Serial.println(message);

    File file = fs.open(path, FILE_WRITE);
    if(!file){
        Serial.println("- failed to open file for writing");
        return false;
    }
    if(file.print(message)){
        Serial.println("- file written");
        return true;
    } else {
        Serial.println("- frite failed");
    }

    return false;
}

void appendFile(fs::FS &fs, const char * path, const char * message){
    Serial.printf("Appending to file: %s\r\n", path);

    File file = fs.open(path, FILE_APPEND);
    if(!file){
        Serial.println("- failed to open file for appending");
        return;
    }
    if(file.print(message)){
        Serial.println("- message appended");
    } else {
        Serial.println("- append failed");
    }
}

void renameFile(fs::FS &fs, const char * path1, const char * path2){
    Serial.printf("Renaming file %s to %s\r\n", path1, path2);
    if (fs.rename(path1, path2)) {
        Serial.println("- file renamed");
    } else {
        Serial.println("- rename failed");
    }
}

void deleteFile(fs::FS &fs, const char * path){
    Serial.printf("Deleting file: %s\r\n", path);
    if(fs.remove(path)){
        Serial.println("- file deleted");
    } else {
        Serial.println("- delete failed");
    }
}


void formatSpiffs() {
	bool formatted = SPIFFS.format();

	if (formatted) {
	 Serial.println("Formatted");
	} else {
	 Serial.println("Format failed");
	}
}

bool writeOfflineKey(String key) {
	Serial.print("Writing offline key to flash: ");
	Serial.println(key);
	
	if (writeFile(SPIFFS, "/offlineKey.txt", key.c_str()) == true) {
		Serial.println("Wrote key successfully.");
		return true;
	}
	Serial.println("Failed to write key.");
	return false;
}


void cacheOfflineKey() {
	
	
	String offlineKeyString = readFile(SPIFFS, "/offlineKey.txt");
	
	Serial.print("Caching offline key: ");
	Serial.println(offlineKeyString);
	
	if (offlineKeyString.length() > 0) {
		Serial.println("Cache successful");
		offlineKey = offlineKeyString;
	} else {
		Serial.println("ERROR: Stored key length is < 1");
		offlineKey = "";
	}
}
