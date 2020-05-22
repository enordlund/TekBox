# TekBox for iOS
## Device Requirements
TekBox for iOS currently supports all iPhone and iPod touch models with iOS 13 or later.
## Xcode Project Configuration
### URL Configuration
The project contains two strings pointing to TekBox Server. Open the project, and modify the following strings to match your server configuration:
* `rootURL` in `NetworkData.swift`
* `authURL` in `ContentManager.swift`
## Developer account guidance
For personal on-device testing, you can visit [Apple Developer](https://developer.apple.com) to accept the Apple Developer Agreement with your Apple ID for free.

## Maintenance
### Dependencies
#### Valet
[Valet](https://github.com/square/Valet) provides a robust, high-stakes library for storing data in the encrypted iOS keychain. It is included in the Xcode project as a Swift package, and it should remain updated to the latest version.
#### BluetoothSerial
The app uses a modified version of [swiftBluetoothSerial](https://github.com/hoiberg/SwiftBluetoothSerial) to assist with Bluetooth communication.