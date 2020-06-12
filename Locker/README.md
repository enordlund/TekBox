# TekBox Locker
TekBox Locker provides an Arduino sketch and supporting files for upload to TekBox Locker hardware with the Arduino IDE.

## Dependencies
Be sure to install the Arduino IDE first for best results.

### Arduino IDE
###### (Tested with 1.8.12)
Download and install the Arduino IDE from [Arduino](https://www.arduino.cc).

### arduino-esp32
Follow current instructions to install [arduino-esp32](https://github.com/espressif/arduino-esp32) for the Arduino IDE.

### ArduinoJson
###### (Tested with 6.14.0)
In the Arduino IDE Library Manager, search for ArduinoJson and install the latest version.


## Installation
After cloning the repository and installing dependencies, copy `/TekBox-Locker/` to your `Arduino/libraries/` directory. `TekBox.ino` can be copied to your preferred location.

## Configuration
### TekBoxLockerWireless.hpp
The value of `host` should be set to the host name of the server hosting TekBox Server, without the transfer protocol or forward slashes. For example, `"web.engr.oregonstate.edu"`.

Define `LOCKER_REQUEST_PATH` with the path to the `locker/request.php` endpoint, with a leading forward slash beginning where `host` ends. For example, `"/TekBox/locker/request"`.

Define `LOCKER_CONFIRMATION_PATH` with the path to the `locker/confirm.php` endpoint, with a leading forward slash beginning where `host` ends. For example, `"/TekBox/locker/confirm"`.

When combined, `host` and each `LOCKER_*_PATH` should form their respective complete URLs without a leading transfer protocol. For example, `web.engr.oregonstate.edu/TekBox/locker/request`.

The value of `root_ca` should match the public root certificate of the server hosting TekBox Server, strictly following the format of the example included in the file.

### TekBox.ino and Database
The TekBox system depends on assigning a unique identifier and password to each locker. Passwords are to be stored to the database in a hashed format using PHP's `password_hash($password, PASSWORD_DEFAULT)`.

Go to the `hashword` endpoint of TekBox Server to generate a random UID/password/hash set. Copy the UID and password to the `TekBox.ino` Arduino sketch (assigned to `uid` and `lockerServerPassword` as strings), and copy the UID and hash to a new row in the `TekBox-Lockers` table of the database.

#### Note
It is recommended to maintain a secure list of UID/password pairs, because re-uploading should be assumed to be necessary in the future (see Maintenance below).

### Setup without the mobile app
Adding a locker to an existing location is currently supported automatically with the mobile app. Without the mobile app, a locker can be added (without an offline key) by modifying TekBox.ino, and the database.

#### Setting default Wi-Fi credentials
TekBox.ino includes a commented line in `setup()`:

`setDefaultWiFiCredentials("name", "identity", "password", "AUTH_MODE");`

This function establishes default Wi-Fi credentials that will be tried by a locker before entering Offline Mode. Each string should be replaced with the desired values. Changing `"identity"` or `"password"` may not be necessary depending on the selected authentication mode:

- `"OPEN"`: Name required
- `"WEP"`: Name and password required
- `"WPA_PSK"`: Name and password required
- `"WPA2_PSK"`: Name and password required
- `"WPA_WPA2_PSK"`: Name and password required
- `"WPA2_ENTERPRISE"`: Name, identity and password required

#### Adding a locker to a location
The following columns should be populated in a locker's database row (in TekBox-Boxes) to add a locker to a location:

- `Cluster-UUID`: The UID of the desired location
- `Row`: The row value for the locker's position in the location
- `Column`: The column value for the locker's position in the location

Row/column coordinates reference a bottom-left origin, starting at (0, 0).

The location row (in TekBox-Clusters) should be modified to match the new number of rows and columns. For info on creating a new location, see the TekBox Database README.

## Uploading to a New Locker

### Sketch Upload
After configuring the sketch and database with a new UID and password, it's time to upload the sketch. The current hardware revision requires the following procedure for a successful upload:
1. Plug in the locker unit via USB.
2. Select the corresponding port in the Arduino IDE.
3. Match the following board settings:
	/board-configuration.png
4. After initiating the upload, the Arduino IDE will compile and begin connecting to the locker unit. At this point, hold down SW2 on the locker unit motherboard while pressing and releasing SW1. Continue holding SW2 until the Arduino IDE indicates that the upload is making progress.

After the upload completes, the locker unit will enter offline mode to be configured with the mobile app via Bluetooth.


## Maintenance
The current design verifies the identity of the server with `root_ca`, which is never updated after a sketch is uploaded to a locker unit. It is important to anticipate the expiration date of the root certificate to prevent unexpected downtime. Remember to set `uid` and `lockerServerPassword` values accordingly (see Sketch and Database Configuration above).