//
//  MessageHandler.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/16/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//
//  TekBox includes the following open-source components:
//      • swiftBluetoothSerial: https://github.com/hoiberg/SwiftBluetoothSerial


import Foundation
import CoreBluetooth

/// Global handler variable, initialize with .init(delegate: ). delegate should be self in view controller.
var messageHandler: MessageHandler!

protocol MessageHandlerDelegate {
    func statusDidRefresh()
    func deviceNameDidChange(_ name: String)
    func messageWasReceived(_ message: String)
    func messageWasSent(_ message: String)
    
    func peripheralDidDisconnect(_ poweredOn: Bool)
    func connectionDidChangeState(_ poweredOn: Bool)
    func peripheralDiscovered(_ isDuplicate: Bool,_ peripherals: [(peripheral: CBPeripheral, RSSI: Float)])
    func peripheralConnectionSuccessful()
    func peripheralConnectionFailed()
    func peripheralConnectionReady(_ peripheral: CBPeripheral)
}

protocol WapScanDelegate {
    func jsonDidLoad()
}


/*
struct Status {
    var x: Double?
    var y: Double?
    var z: Int?
}
*/
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ remember to set unique timeout for various requests (for example, wifi scanning takes longer than a status request)

enum MessageState {
    // general
    case connectionRequestSent// expected responses: "c." (confirmation), ["?" (repeat request), or no response after some timeout] handle "?" before assessing type
    
    // setup
	case unlockRequestSent
	case lockRequestSent
	
    case wapScanRequestSent// expected responses: json segment, json closing bracket, ["?" or none after timeout]
    case wapCredentialsSent// expected: "wapc." (confirmation), "wapf." (wifi failure), ["?" or none]
    case rootCertificateSent// expected: "servers." (success), "serverf." (failure), ["?" or none]
    // at this point in the process, the box and app can both talk to the server for setup (stays paired to bluetooth for later stages of setup).
    case boxIdRequested//expected "id:xxxxxxx." (box is confirmed with server), "idf." (box failed to get approval from server to continue), (id followed by "."), "" (), ["?" or none]
    
    // (box then tells the server that it just connected to wifi again, and the app sends the id to the server to make sure it's okay to proceed.)
    
    // after box is established as new, or belonging to the user's account, request a key from the box, using a temporary password with the request that was provided by the server to the locker and app for confirmation
    case boxKeyRequested// expected: "pass?", "passf" (locker failed to load password from server), ["?", or none]
    case boxKeyPasswordSent// expected: "keyseg:xxxxxxxxx", "keyend." ["?", or none] THIS WILL HAVE TO COME IN SEGMENTS, BECAUSE IT WILL BE HUGE.
    case boxKeyTestSent//
}




class MessageHandler: ObservableObject {
    var delegate: MessageHandlerDelegate!
	
	var connectionVerifierDelegate: ConnectionVerifierDelegate!
    
    var wapScanDelegate: WapScanDelegate!
    
    var wapCredentialsDelegate: WapCredentialsDelegate!
    
    var rootCertificateDelegate: RootCertificateDelegate!
    
    init(delegate: MessageHandlerDelegate) {
        //super.init()
        
        print("MessageHandler() setting delegate in init()")
        
        self.delegate = delegate
        
        
        
        // Initialize serial
        btSerial = BluetoothSerial(delegate: self)
        
        
        // start scanning and schedule the time out
        /*
        btSerial.startScan()
        Timer.scheduledTimer(withTimeInterval: 10, repeats: false, block: {_ in
            self.scanTimeOut()
        })
        */
    }
    
    func setDelegate(delegate: MessageHandlerDelegate) {
        print("MessageHandler() setting delegate")
        self.delegate = delegate
    }
	
	func setConnectionVerifierDelegate(delegate: ConnectionVerifierDelegate) {
		print("MessageHandler() setting connectionVerifierDelegate")
		self.connectionVerifierDelegate = delegate
	}
    
    func setWapScanDelegate(delegate: WapScanDelegate) {
        print("MessageHandler() setting wapScanDelegate")
        self.wapScanDelegate = delegate
    }
    
    
    var bluetoothIsConnected: Bool {
        get {
            if btSerial.connectedPeripheral != nil {
                return true
            } else {
                return false
            }
        }
    }
    
    
    
    private let outbox = Outbox()
    
    private let inbox = Inbox()
	
	var lockerUID = ""
    
    //var status = Status()
    
    var state: MessageState?
    
    let outgoingMessages = OutgoingMessages()
    let incomingMessages = IncomingMessages()
    
    private func setOutboxMessage(_ message: String?) {
        outbox.message = message
        
    }
    
    private func setExpectedInboxMessage(message: String) {
        inbox.setExpectedMessage(newExpectedMessage: message)
    }
    
    
    private func getOutboxMessage() -> String? {
        return outbox.message
    }
    
    private func getExpectedInboxMessage() -> String? {
        return inbox.getExpectedMessage()
    }
    
    private func setInboxMessage(_ message: String) {
        inbox.setMessage(newMessage: message)
    }
    
    func sendOutgoingMessage(message: String?) {
        setOutboxMessage(message)
        
        updateExpectedInboxMessage()
        
        if outbox.message != nil {
            if bluetoothIsConnected {
                btSerial.sendMessageToDevice(message!)
                delegate.messageWasSent(message!)
            } else {
                debugPrint("ERROR: no devices connected.")
            }
            
        } else {
            print("ERROR: Outbox empty. Send failed.")
        }
    }
    
    
    private func updateExpectedInboxMessage() {
        if outbox.isConnectionRequest() {
            inbox.setExpectedMessage(newExpectedMessage: inbox.incomingMessages.connectionVerifierPrefix())
		} else if outbox.isUnlockRequest() {
			inbox.setExpectedMessage(newExpectedMessage: nil)
		} else if outbox.isLockRequest() {
			inbox.setExpectedMessage(newExpectedMessage: nil)
		} else if outbox.isStatusRequest() {
            //---------------------------------- This is where an accurate expectedMessage will be constructed
			#warning("This should be completed")
        } else if outbox.isRestartCommand() {
            inbox.setExpectedMessage(newExpectedMessage: inbox.incomingMessages.restartVerifier())
        } else if outbox.isConfigurationNameCommand() {
            inbox.setExpectedMessage(newExpectedMessage: inbox.incomingMessages.configurationNameChangeVerifier())
        } else {
            inbox.setExpectedMessage(newExpectedMessage: nil)
        }
        // ------------------------------------------------------ should also handle case of repeat request
    }
    
    
    
    private var incomingBuffer: String?
	
//	@Published var lockerOfflineAccessSecretDidSet = false
    
    @Published var wapScan: [Wap]? = nil
    @Published var wapScanDidSet = false
    
	private var offlineKeySecret: String? = nil
	
    private func handleIncomingMessage(message: String) {
        delegate.messageWasReceived(message)
        
        setInboxMessage(message)
        
        
        
        
        switch messageHandler.state {
        case .connectionRequestSent:
			if message == "c." {
				// continue, queue next task
                print("received connection verifier.")
				connectionVerifierDelegate.lockerUID(wasReceived: false, andOfflineKey: false)
			} else if message.starts(with: "c:") {
				// store uid for key lookup
				let messageLockerUID = message.split(separator: ":")[1]
				print("received lockerUID: \(lockerUID)")
				
				lockerUID = String(messageLockerUID)
				
				// look for offlineKey
				var offlineKeyFound = false
				if let offlineSecret = getOfflineKeySecret(forLockerUID: messageHandler.lockerUID) {
					offlineKeyFound = true
					offlineKeySecret = offlineSecret
				} else {
					offlineKeySecret = nil
				}
				connectionVerifierDelegate.lockerUID(wasReceived: true, andOfflineKey: offlineKeyFound)
			} else {
				// handle unrecognized message
                print("received unrecognized message: ", message)
			}
//		case .unlockRequestSent:
//
//		case .lockRequestSent:
			
        case .wapScanRequestSent:
            //
            if messageIsJsonSegment(message: message) {
                // expecting more JSON segments
                print("received json segment: ", message)
                // append segment to a buffer
                if incomingBuffer != nil {
                    incomingBuffer! += message
                } else {
                    incomingBuffer = message
                }
            } else if messageIsJsonEnd(message: message) {
                // handle parsing the complete JSON
                print("received json end")
                // decode the json from the buffer
                // clear the buffer
                if incomingBuffer != nil {
					print("buffer: \(incomingBuffer ?? "DEFAULT_VALUE")")
                    wapScanDelegate.jsonDidLoad()
                }
            } else {
                // handle unrecognized message
                print("received unrecognized message: ", message)
            }
        case .wapCredentialsSent:
            //
			
			if messageIsWapConfirmation(message: message) {
                // proceed to next stage of setup. box is connected to internet/server.
                print("received box wifi confirmation.")
                self.wapCredentialsDelegate.wapCredentials(wereSuccessful: true)
            } else if messageIsWapFailure(message: message) {
                // credentials didn't work for some reason.
                print("received box wifi failure.")
                self.wapCredentialsDelegate.wapCredentials(wereSuccessful: false)
            } else {
                // handle unrecognized message
                print("received unrecognized message: ", message)
            }
        case .rootCertificateSent:
            if messageIsCertificateReceived(message: message) {
                // box connected to wifi, but failed to connect to the server.
                print("received certificate success.")
				let sessionUID = String(message.split(separator: ":", maxSplits: 1, omittingEmptySubsequences: true)[1])
				
				print("sessionUID: \(sessionUID)")
				
				// tell the locker to restart
				messageHandler.sendRestartCommand()
				
				self.rootCertificateDelegate.rootCertificate(wasSuccessful: true, sessionUID: sessionUID)
            } else if messageIsServerConnectionFailure(message: message) {
                // box connected to wifi, but failed to connect to the server.
                print("received certificate failure.")
				self.rootCertificateDelegate.rootCertificate(wasSuccessful: false, sessionUID: nil)
            } else if messageIsServerAuthenticationFailure(message: message) {
                print("received box server authentication failure.")
                self.rootCertificateDelegate.serverAuthentication(wasSuccessful: false)
            } else {
                // handle unrecognized message
                print("received unrecognized message: ", message)
            }
        case .boxIdRequested:
            //
            if messageIsId(message: message) {
                // use id to communicate with server
                print("received id from box: ", message)
            } else if messageIsIdFailure(message: message) {
                // handle failure
                print("received id failure from box")
            } else {
                // handle unrecognized message
                print("received unrecognized message: ", message)
            }
        case .boxKeyRequested:
            //
            if messageIsPasswordRequest(message: message) {
                // send password to locker
                print("received password request")
            } else if messageIsPasswordFailure(message: message) {
                // handle failure (box is going to have to try again, or maybe the server can tell the app what happened in some cases.)
                print("received password failure")
            } else {
                // handle unrecognized message
                print("received unrecognized message: ", message)
            }
        case .boxKeyPasswordSent:
            //
            if messageIsKeySegment(message: message) {
                // add to buffer, retain state
                print("received key segment: ", message)
            } else if messageIsKeyEnd(message: message) {
                // test key with box
                print("received key end")
            } else if messageIsKeyGenFailure(message: message) {
                print("received key gen failure")
            } else {
                // handle unrecognized message
                print("received unrecognized message: ", message)
            }
        case .boxKeyTestSent:
            //
            if messageIsKeyTestSuccess(message: message) {
                // store key and move on
                print("received key test success")
            } else if messageIsKeyTestFailure(message: message) {
                // handle failure
                print("received key test failure")
            } else {
                // handle unrecognized message
                print("received unrecognized message: ", message)
            }
        default:
            // state is nil, so the box may be initiating some communication...
            print("received message with nil state: ", message)
        }
        
        
    }
    
    
    
    
    
    private func messageIsConnectionVerifier(message: String) -> Bool {
        if message == "c." {
            return true
        } else {
            return false
        }
    }
    
    
    
    
    
    
    
    private func messageIsJsonSegment(message: String) -> Bool {
        if message.last == "}" || message.last == "]" {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsJsonEnd(message: String) -> Bool {
        if message == "jsonend." {
            return true
        } else {
            return false
        }
    }
    
    
    private func messageIsWapConfirmation(message: String) -> Bool {
        if message == "wcs." {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsWapFailure(message: String) -> Bool {
        if message == "wcf." {
            return true
        } else {
            return false
        }
    }
	
	private func messageIsCertificateReceived(message: String) -> Bool {
		if message.starts(with: "rcr:") {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsServerConnectionSuccess(message: String) -> Bool {
        if message == "rcs." {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsServerConnectionFailure(message: String) -> Bool {
        if message == "rcf." {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsServerAuthenticationSuccess(message: String) -> Bool {
        if message == "sauths." {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsServerAuthenticationFailure(message: String) -> Bool {
        if message == "sauthf." {
            return true
        } else {
            return false
        }
    }
    
    
    private func messageIsId(message: String) -> Bool {
        if message.starts(with: "id:") {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsIdFailure(message: String) -> Bool {
        if message == "idf." {
            return true
        } else {
            return false
        }
    }
    
    
    
    private func messageIsPasswordRequest(message: String) -> Bool {
        if message == "pass?" {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsPasswordFailure(message: String) -> Bool {
        if message == "passf." {
            return true
        } else {
            return false
        }
    }
    
    
    
    private func messageIsKeySegment(message: String) -> Bool {
        if message.starts(with: "keyseg:") {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsKeyEnd(message: String) -> Bool {
        if message == "keyend." {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsKeyGenFailure(message: String) -> Bool {
        if message == "keygenf." {
            return true
        } else {
            return false
        }
    }
    
    
    
    private func messageIsKeyTestSuccess(message: String) -> Bool {
        if message == "keytest." {
            return true
        } else {
            return false
        }
    }
    
    private func messageIsKeyTestFailure(message: String) -> Bool {
        if message == "keytestf." {
            return true
        } else {
            return false
        }
    }
    
    
	func requestConnectionVerifier(withDelegate: ConnectionVerifierDelegate) {
		print("MessageHandler requestConnectionVerifier()")
		
		self.connectionVerifierDelegate = withDelegate
		
		state = .connectionRequestSent
		
		btSerial.sendMessageToDevice(outgoingMessages.connectionRequest())
	}
    
    
    func requestWapScan(withDelegate: WapScanDelegate) {
        print("MessageHandler() requestWapScan()")
        self.wapScanDelegate = withDelegate
        
        // update state for incoming message handling
        state = .wapScanRequestSent
        
        // send scan request
        btSerial.sendMessageToDevice(outgoingMessages.wapScanRequest())
    }
    
    
    func getWapScanData() -> Data? {
        print("MessageHandler() getWapScanData()")
        if let outString = incomingBuffer {
            incomingBuffer = nil
            state = nil
            return outString.data(using: .utf8)
        } else {
            return nil
        }
    }
    
    
    
    
    func sendWapCredentials(credentials: WapCredentials, withDelegate: WapCredentialsDelegate) {
		print("sendWapCredentials()")
        // update context
        self.state = .wapCredentialsSent
        
        self.wapCredentialsDelegate = withDelegate
        
        // convert to JSON
        do {
            let json = try JSONEncoder().encode(credentials)
            //print(String(bytes: json, encoding: .utf8))
            
            
            if let jsonString = String(bytes: json, encoding: .utf8) {
                print("Sending JSON: ", jsonString)
                // send the message
                let message = "wc:" + jsonString
                
                btSerial.sendMessageToDevice(message)
            } else {
                print("ERROR: json could not convert to String...")
            }
            
            
        } catch let jsonError {
            print("ERROR: JSON encoding failed... ", jsonError)
        }
    }
    
    
    func sendRootCertificate(withDelegate: RootCertificateDelegate) {
		print("sendRootCertificate()")
        self.state = .rootCertificateSent
        
        self.rootCertificateDelegate = withDelegate
        
        
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ get certificate from server
        let certificate = "abcd"
        
        // convert to JSON
        do {
            let json = try JSONEncoder().encode(certificate)
            //print(String(bytes: json, encoding: .utf8))
            
            
            if let jsonString = String(bytes: json, encoding: .utf8) {
                print("Sending JSON: ", jsonString)
                // send the message
                let message = "rc:" + jsonString
                
                btSerial.sendMessageToDevice(message)
            } else {
                print("ERROR: json could not convert to String...")
            }
            
            
        } catch let jsonError {
            print("ERROR: JSON encoding failed... ", jsonError)
        }
    }
    
    
    
    
    
    
    
    /// BluetoothSerialDelegate stuff
    
    /// The peripherals that have been discovered (no duplicates and sorted by asc RSSI)
    @Published var peripherals: [(peripheral: CBPeripheral, RSSI: Float)] = []
    
    
    /// Should be called 10s after we've begun scanning
    func scanTimeOut() {
        // timeout has occurred, stop scanning and give the user the option to try again
        btSerial.stopScan()
    }
    
    /// Should be called 10s after we've begun connecting
    func connectionFailed() {
        
        // don't if we've already connected
        if let _ = btSerial.connectedPeripheral {
            return
        }
        
        delegate.peripheralConnectionFailed()
    }
    
    
    
    
    // added functionality
    func connectToPeripheral(peripheral: CBPeripheral) {
        btSerial.stopScan()//-------------------------------- this duplicates the completion block for the peripheralActionSheet in the ViewController
        btSerial.connectToPeripheral(peripheral)
        
    }
    
	
	func sendUnlockRequest() {
		if offlineKeySecret != nil {
			sendOutgoingMessage(message: outbox.outgoingMessages.unlockRequest(withSecret: offlineKeySecret!))
		}
	}
	
	func sendLockRequest() {
		if offlineKeySecret != nil {
			sendOutgoingMessage(message: outbox.outgoingMessages.lockRequest(withSecret: offlineKeySecret!))
		}
	}
    
    func requestStatusUpdate() {
        sendOutgoingMessage(message: outbox.outgoingMessages.statusRequest())
    }
    
    func sendRestartCommand() {
        sendOutgoingMessage(message: outbox.outgoingMessages.restartCommand())
    }
    
    func changePeripheralName(newName: String) {
        sendOutgoingMessage(message: outbox.outgoingMessages.changeDeviceNameCommand(newName: newName))
    }
    
}



extension MessageHandler: BluetoothSerialDelegate {
    func serialDidReceiveString(_ message: String) {
        handleIncomingMessage(message: message)
    }
    
    func serialDidSendString(_ message: String) {
        //delegate.messageWasSent(message)
    }
    
    
    func serialDidDisconnect(_ peripheral: CBPeripheral, error: NSError?, _ poweredOn: Bool) {
        delegate.peripheralDidDisconnect(poweredOn)
    }
    
    func serialDidChangeState() {
        let bluetoothPowerState: Bool = (btSerial.centralManager.state == .poweredOn)
        delegate.connectionDidChangeState(bluetoothPowerState)
    }
    
    func serialDidDiscoverPeripheral(_ peripheral: CBPeripheral, RSSI: NSNumber?) {
        print("MessageHandler() serialDidDiscoverPeripheral")
        
        var isDuplicate = false
        
        for existing in peripherals {
            if existing.peripheral.identifier == peripheral.identifier {
                isDuplicate = true
            }
        }
        
        if !isDuplicate {
            let RSSI = RSSI?.floatValue ?? 0.0
            peripherals.append((peripheral: peripheral, RSSI: RSSI))
            peripherals.sort { $0.RSSI < $1.RSSI }
        }
        
        delegate.peripheralDiscovered(false, peripherals)
    }
    
    
    func serialDidConnect(_ peripheral: CBPeripheral) {
        delegate.peripheralConnectionSuccessful()
    }
    
    func serialDidFailToConnect(_ peripheral: CBPeripheral, error: NSError?) {
        self.connectionFailed()
    }
    
    func serialIsReady(_ peripheral: CBPeripheral) {
        // Notify Arduino of complete connection
        sendOutgoingMessage(message: outbox.outgoingMessages.connectionRequest())
        
        
        delegate.peripheralConnectionReady(peripheral)
    }
}



protocol ConnectionVerifierDelegate {
	func lockerUID(wasReceived: Bool, andOfflineKey: Bool)
}

class ConnectionVerifierRequest: ObservableObject, ConnectionVerifierDelegate {
	@Published var dataIsLoaded: Bool? = nil
    @Published var lockerUIDWasReceived: Bool = false
	@Published var lockerSecretWasFound: Bool = false
	
	
	init() {
        print("connectionVerifierRequest() initializing ConnectionVerifierRequest")
        //self.requestScan()
        messageHandler.setConnectionVerifierDelegate(delegate: self)
    }
	
	func restoreVariables() {
		self.dataIsLoaded = nil
		self.lockerUIDWasReceived = false
		self.lockerSecretWasFound = false
	}
	
	func requestConnectionVerifier() {
		self.restoreVariables()
		messageHandler.requestConnectionVerifier(withDelegate: self)
	}
	
	func lockerUID(wasReceived: Bool, andOfflineKey: Bool) {
		print("lockerUID(wasReceived: \(wasReceived), andOfflineKey: \(andOfflineKey))")
		self.lockerUIDWasReceived = wasReceived
		
		self.lockerSecretWasFound = andOfflineKey
		
		
		self.dataIsLoaded = true
		
	}
	
	
}





struct Wap: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var ssid: String
    var rssi: Int
    var encryption: String
}

struct WapScanData: Decodable {
    let id: UUID? = UUID()
    
    var waps: [Wap]
}

// this is only called within an instance of MessageHandler.
class WapScanRequest: ObservableObject, WapScanDelegate {
    
    
    @Published var dataIsLoaded = false
    @Published var waps: [Wap]? = nil
    @Published var isConnected = true
    @Published var isScanning = false
    @Published var scanDidFail = false
    
    
    init() {
        print("WapScanRequest() initializing WapScanRequest")
        //self.requestScan()
        messageHandler.setWapScanDelegate(delegate: self)
    }
    
    
    func restoreVariables() {
        dataIsLoaded = false
        waps = nil
        isConnected = true
        isScanning = false
        scanDidFail = false
    }
    
    func requestScan() {
        self.restoreVariables()
        messageHandler.requestWapScan(withDelegate: self)
        isScanning = true
    }
    
    
    // delegate function
    func jsonDidLoad() {
        print("WapScanRequest() jsonDidLoad()")
        if let wapScanData = messageHandler.getWapScanData() {
            //print("data: ", wapScanData)
            do {
                let wapsData = try JSONDecoder().decode([Wap].self, from: wapScanData)// JSONDecoder().decode(WapScanData.self, from: wapScanData)
                
                
                DispatchQueue.main.async {
                    self.waps = wapsData
                    
                    self.dataIsLoaded = true
                    
                    print("waps count: ", self.waps?.count)
                    print("dataIsLoaded: ", self.dataIsLoaded)
                }
            } catch let jsonError {
                print("Error decoding JSON: ", jsonError)
                scanDidFail = true
            }
        }
    }
    
    
}


protocol WapCredentialsDelegate {
    func wapCredentials(wereSuccessful: Bool)
}

class WapCredentialsAttempt: ObservableObject, WapCredentialsDelegate {
    
    
    @Published var dataIsLoaded: Bool? = nil
    @Published var wasSuccessful: Bool = false
    
    
    
    init() {
        print("WapCredentialsAttempt() initializing")
        
    }
    
    
    func restoreVariables() {
        dataIsLoaded = nil
        wasSuccessful = false
        messageHandler.state = .wapScanRequestSent// ignore any incoming success/failure if attempt is restored.
    }
		
    
    func attempt(credentials: WapCredentials) {
		print("attempt(WapCredentials)")
        dataIsLoaded = false
        messageHandler.sendWapCredentials(credentials: credentials, withDelegate: self)
    }
    
    
    func wapCredentials(wereSuccessful: Bool) {
        self.dataIsLoaded = true
        self.wasSuccessful = wereSuccessful
        
        /*
        if self.dataIsLoaded == false {
            
        } else {
            // variables were restored, so ignore the outcome of an incoming message.
        }
        */
    }
}




protocol RootCertificateDelegate {
	func rootCertificate(wasSuccessful: Bool, sessionUID: String?)
    func serverAuthentication(wasSuccessful: Bool)
}


class RootCertificateAttempt: ObservableObject, RootCertificateDelegate {
    
    
    @Published var dataIsLoaded: Bool?
    @Published var wasSuccessful: Bool
	@Published var sessionUID: String?
    
    
    init() {
        print("RootCertificateAttempt() initializing")
		dataIsLoaded = nil
		wasSuccessful = false
		sessionUID = nil
    }
    
    
    func restoreVariables() {
		self.dataIsLoaded = nil
		
		self.wasSuccessful = false
        messageHandler.state = .rootCertificateSent// ignore any incoming success/failure if attempt is restored.
    }
    
    func sendCertificate() {
		
		self.dataIsLoaded = false
		
		self.wasSuccessful = false
        messageHandler.sendRootCertificate(withDelegate: self)
    }
    
    
	func rootCertificate(wasSuccessful: Bool, sessionUID: String?) {
		print("rootCertificate(wasSuccessful: _)")
		
		self.sessionUID = sessionUID
		
        self.dataIsLoaded = true
		
        self.wasSuccessful = wasSuccessful
        /*
        if self.dataIsLoaded == false {
            
        } else {
            // variables were restored, so ignore the outcome of an incoming message.
        }
        */
    }
    
    func serverAuthentication(wasSuccessful: Bool) {
		
        self.dataIsLoaded = true
		
        self.wasSuccessful = wasSuccessful
    }
}
