//
//  Keychain.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/11/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import Foundation
import Valet

struct TokenData: Codable {
    var tokenUID: String
    var token: String
}

struct OfflineKey: Identifiable {
	let id: UUID? = UUID()
	
	var lockerUID: String
	var secret: String
}

enum KeychainError: Error {
    case noPassword
    case unexpectedPasswordData
    case unhandledError(status: OSStatus)
}


func getTokenSecret(forTokenUID: String) -> String? {
	print("getTokenSecret()")
	
	let myValet = Valet.valet(with: Identifier(nonEmpty: "TekBoxToken")!, accessibility: .whenUnlocked)
	
	return myValet.string(forKey: forTokenUID)
}


func deleteToken(withTokenUID: String) -> Bool {
	print("deleteToken(withTokenUID: \(withTokenUID))")
	
	let myValet = Valet.valet(with: Identifier(nonEmpty: "TekBoxToken")!, accessibility: .whenUnlocked)
	
	return myValet.removeObject(forKey: withTokenUID)
}

func deleteTokenSet(tokenSet: Set<String>) -> Bool {
	print("deleteTokenSet()")
	for tokenUID in tokenSet {
		if deleteToken(withTokenUID: tokenUID) == false {
			return false
		}
	}
	return true
}

func deleteAllTokens() -> Bool {
	print("deleteAllTokens()")
	let myValet = Valet.valet(with: Identifier(nonEmpty: "TekBoxToken")!, accessibility: .whenUnlocked)
	
	let tokenSet = myValet.allKeys()
	
	return deleteTokenSet(tokenSet: tokenSet)
}

func storeToken(token: TokenData) -> Bool {
	print("storeToken()")
	
	let myValet = Valet.valet(with: Identifier(nonEmpty: "TekBoxToken")!, accessibility: .whenUnlocked)
	
	// if another token already exists, replace it by deleting the existing token(s) after confirming new token was stored.
	
	let tokenSet = myValet.allKeys()
	
	
	// store the new key
	if myValet.set(string: token.token, forKey: token.tokenUID) == false {
		return false
	}
	
	return deleteTokenSet(tokenSet: tokenSet)
	
	
}

func getToken() -> TokenData? {
	print("getToken()")
	let myValet = Valet.valet(with: Identifier(nonEmpty: "TekBoxToken")!, accessibility: .whenUnlocked)
	
	let tokenSet = myValet.allKeys()
	
	if tokenSet.count != 1 {
		return nil
	}
	
	if let tokenUID = tokenSet.first {
		if let secret = getTokenSecret(forTokenUID: tokenUID) {
			let tokenData = TokenData(tokenUID: tokenUID, token: secret)
			return tokenData
		}
	}
	
	return nil
	
}


func storeOfflineKey(offlineKey: OfflineKey) -> Bool {
	print("storeOfflineKey()")
	
	let myValet = Valet.valet(with: Identifier(nonEmpty: "TekBoxOfflineKey")!, accessibility: .whenUnlocked)
	
	return myValet.set(string: offlineKey.secret, forKey: offlineKey.lockerUID)
}

func deleteOfflineKey(forLockerUID: String) -> Bool {
	
	let myValet = Valet.valet(with: Identifier(nonEmpty: "TekBoxOfflineKey")!, accessibility: .whenUnlocked)
	
	return myValet.removeObject(forKey: forLockerUID)
}

func getOfflineKeySecret(forLockerUID: String) -> String? {
	
	let myValet = Valet.valet(with: Identifier(nonEmpty: "TekBoxOfflineKey")!, accessibility: .whenUnlocked)
	
	return myValet.string(forKey: forLockerUID)
}



func getOfflineKeys() -> [OfflineKey] {
	print("getOfflineKeys()")
	let myValet = Valet.valet(with: Identifier(nonEmpty: "TekBoxOfflineKey")!, accessibility: .whenUnlocked)
	
	let lockerUIDSet = myValet.allKeys()
	
	// iterate through the set
	var offlineKeys = [OfflineKey]()
	
	for lockerUID in lockerUIDSet {
		if let secret = getOfflineKeySecret(forLockerUID: lockerUID) {
			let offlineKey = OfflineKey(lockerUID: lockerUID, secret: secret)
			offlineKeys.append(offlineKey)
		}
	}
	
	print(offlineKeys)
	
	return offlineKeys
}
