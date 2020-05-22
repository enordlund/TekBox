//
//  IncomingMessage.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/16/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import Foundation

enum IncomingMessage {
    case connectionVerifier
    case wapScanVerifier
    case jsonSegment
    case jsonClosingBracket
}

class IncomingMessages {
    
    
    enum IncomingMessage {
        case connectionVerifier
        case wapScanVerifier
        case jsonSegment
        case jsonClosingBracket
    }
    
    
    func connectionVerifierPrefix() -> String {
        return "c"
    }
    
    func restartVerifier() -> String {
        return "r."
    }
    
    // Configuration messages
    func configurationNameChangeVerifier() -> String {
        return "atname."//-------------------------------- This isn't really an option right now.
    }
    
}
