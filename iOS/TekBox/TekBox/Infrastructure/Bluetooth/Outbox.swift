//
//  Outbox.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/16/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//


import Foundation


class Outbox {
    let outgoingMessages = OutgoingMessages()
    
    
    var message: String?
    
    func getMessage() -> String? {
        return message
    }
    
    func setMessage(newMessage: String) {
        message = newMessage
    }
    
    func clearMessage() {
        message = nil
    }
    
    
    
    func isConnectionRequest() -> Bool {
		if message == outgoingMessages.connectionRequest() {
            return true
        } else {
            return false
        }
    }
    
    func isStatusRequest() -> Bool {
        if message == outgoingMessages.statusRequest() {
            return true
        } else {
            return false
        }
    }
	
	
	
	func isUnlockRequest() -> Bool {
		if message?.hasPrefix("ul") ?? false {
			return true
		} else {
			return false
		}
	}
	
	func isLockRequest() -> Bool {
		if message?.hasPrefix("lk") ?? false {
			return true
		} else {
			return false
		}
	}
	
	
	
    
    func isRestartCommand() -> Bool {
        if message == outgoingMessages.restartCommand() {
            return true
        } else {
            return false
        }
    }
    
    // configuration commands
    func isConfigurationCommand() -> Bool {
        let end: Character = "!"
        
        if (message!.prefix(7) == "config:") && (message![message!.endIndex] == end) {
            return true
        } else {
            return false
        }
    }
    
    func isConfigurationNameCommand() -> Bool {
        let end: Character = "!"
        
        if (message!.prefix(12) == "config:name:") && (message![message!.endIndex] == end) {
            return true
        } else {
            return false
        }
    }
}
