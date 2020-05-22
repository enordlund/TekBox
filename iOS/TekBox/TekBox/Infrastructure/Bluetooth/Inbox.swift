//
//  Inbox.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/16/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import Foundation

class Inbox {
    
    
    
    let incomingMessages = IncomingMessages()
    
    
    
    
    var expectedMessage: IncomingMessage.Type?
    
    
    
    var expectedMessageString: String?
    var message: String?
    
    
    func getExpectedMessage() -> String? {
        return expectedMessageString
    }
    
    func getMessage() -> String? {
        return message
    }
    
    func setExpectedMessage(newExpectedMessage: String?) {
        expectedMessageString = newExpectedMessage
    }
    
    func setMessage(newMessage: String) {
        message = newMessage
    }
    
    
    func messageIsExpected() -> Bool {
        // rewrite this based on messageHandler's state
        
        if messageIsConnectionVerifier() && expectedMessageIsConnectionVerifier() {
            return true
        } else if messageIsRestartVerifier() && expectedMessageIsRestartVerifier() {
            return true
        } else if messageIsConfigurationNameChangeVerifier() && expectedMessageIsConfigurationNameChangeVerifier() {
            return true
        } else {
            debugPrint("ERROR: incoming message unexpected")
            return false
        }
    }
    
    
    
    
    func messageIsConnectionVerifier() -> Bool {
		if message?.hasPrefix(incomingMessages.connectionVerifierPrefix()) ?? false {
            return true
        } else {
            return false
        }
    }
    
    
    
    
    
    
    
    func messageIsJsonSegment() -> Bool {
        if message?.last == "}" {
            return true
        } else {
            return false
        }
    }
    
    func messageIsJsonClosingBracket() -> Bool {
        if message?.last == "]" {
            return true
        } else {
            return false
        }
    }
    
    
    func messageIsWapConfirmation() -> Bool {
        if message == "wapc." {
            return true
        } else {
            return false
        }
    }
    
    func messageIsWapFailure() -> Bool {
        if message == "wapf." {
            return true
        } else {
            return false
        }
    }
    
    func messageIsServerConnectionFailure() -> Bool {
        if message == "serverf." {
            return true
        } else {
            return false
        }
    }
    
    
    func messageIsId() -> Bool {
        if message?.starts(with: "id:") ?? false && message?.last == "." {
            return true
        } else {
            return false
        }
    }
    
    func messageIsIdFailure() -> Bool {
        if message == "idf." {
            return true
        } else {
            return false
        }
    }
    
    
    
    func messageIsPasswordRequest() -> Bool {
        if message == "pass?" {
            return true
        } else {
            return false
        }
    }
    
    func messageIsPasswordFailure() -> Bool {
        if message == "passf." {
            return true
        } else {
            return false
        }
    }
    
    
    
    func messageIsKeySegment() -> Bool {
        if message?.starts(with: "keyseg:") ?? false {
            return true
        } else {
            return false
        }
    }
    
    func messageIsKeyEnd() -> Bool {
        if message == "keyend." {
            return true
        } else {
            return false
        }
    }
    
    func messageIsKeyGenFailure() -> Bool {
        if message == "keygenf." {
            return true
        } else {
            return false
        }
    }
    
    
    
    func messageIsKeyTestSuccess() -> Bool {
        if message == "keytest." {
            return true
        } else {
            return false
        }
    }
    
    func messageIsKeyTestFailure() -> Bool {
        if message == "keytestf." {
            return true
        } else {
            return false
        }
    }
    
    
    
    func messageIsRestartVerifier() -> Bool {
        if message == incomingMessages.restartVerifier() {
            return true
        } else {
            return false
        }
    }
    
    
    
    func messageIsConfigurationNameChangeVerifier() -> Bool {
        if message == incomingMessages.configurationNameChangeVerifier() {
            return true
        } else {
            return false
        }
    }
    
    
    func expectedMessageIsConnectionVerifier() -> Bool {
		if expectedMessageString?.hasPrefix(incomingMessages.connectionVerifierPrefix()) ?? false {
            return true
        } else {
            return false
        }
    }
    
    
    func expectedMessageIsRestartVerifier() -> Bool {
        if expectedMessageString == incomingMessages.restartVerifier() {
            return true
        } else {
            return false
        }
    }
    
    
    func expectedMessageIsConfigurationNameChangeVerifier() -> Bool {
        if expectedMessageString == incomingMessages.configurationNameChangeVerifier() {
            return true
        } else {
            return false
        }
    }
    
    
}
