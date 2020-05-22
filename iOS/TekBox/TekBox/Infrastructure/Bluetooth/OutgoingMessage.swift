//
//  OutgoingMessage.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/16/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import Foundation

enum OutgoingMessage {
    case connectionRequest
    case repeatRequest
    case statusRequest
    case wapScanRequest
}

class OutgoingMessages {
    
    
    
    func connectionRequest() -> String {
        return "c?"
    }
    
    func repeatRequest() -> String {
        return "?"
    }
    
    func statusRequest() -> String {
        return "s?"
    }
    
    func wapScanRequest() -> String {
        return "ws?"
    }
    
	func unlockRequest(withSecret: String) -> String {
		return "ul" + withSecret
	}
	
	func lockRequest(withSecret: String) -> String {
		return "lk" + withSecret
	}
    
    func restartCommand() -> String {
        return "rs!"
    }
    
    func changeDeviceNameCommand(newName: String) -> String {
        return "config:name:\(newName)!"
    }
}
