//
//  ContentManager.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/22/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import Foundation
import SwiftUI
import Combine

import AuthenticationServices

#if targetEnvironment(macCatalyst)
import AppKit
#endif





final class ContentManager: NSObject, View, ObservableObject {
    var body: some View {
        Text("hi")
    }
    
    enum TabSelection {
        case lockers
        case orders
        case activity
        case settings
    }
    
    
    enum ShortcutSelection {
        case newOrder
    }
    
    class ShortcutSelectionObject: ObservableObject {
        @Published var selection: ShortcutSelection? = nil
    }
    
    @Published var tabSelection: TabSelection = .lockers
    
    @Published var shortcutSelection = ShortcutSelectionObject()
    
    @Published var globalPresentationAnchor: ASPresentationAnchor?
    
    
    //@Published var mobileDevicesRowCount: Int = 0
    
    
    //@Published var tokenData: TokenData?
    
    private var session: ASWebAuthenticationSession?
    @Published var presentingWindow: UIWindow?
    
    @Published var networkData: NetworkData?
	
	@Published var offlineAccessAttempt: ConnectionVerifierRequest?
	
	@Published var offlineKeysAvailable: Bool = false
    
    
    init(window: UIWindow?) {
        print("ContentManager")
        presentingWindow = window
        //super.init()
        
		// look for keys using Valet
		if getOfflineKeys().count > 0 {
			self.offlineKeysAvailable = true
		}
		
		super.init()
		
		if let tokenData = getToken() {
			
			cacheToken(tokenUID: tokenData.tokenUID, token: tokenData.token)
		}
    }
    
    @Published var showNewOrderView = false
    func newOrderShortcut() {
        print("newOrderShortcut()")
        
        shortcutSelection.objectWillChange.receive(subscriber: Subscribers.Sink(receiveCompletion: { _ in }, receiveValue: {
            self.objectWillChange.send()
        }))
        
        tabSelection = .orders
        shortcutSelection.selection = .newOrder
        showNewOrderView = true
    }
    
    func resetShortcuts() {
        print("resetShortcuts")
        shortcutSelection.objectWillChange.receive(subscriber: Subscribers.Sink(receiveCompletion: { _ in }, receiveValue: {
            self.objectWillChange.send()
        }))
        shortcutSelection.selection = nil
        showNewOrderView = false
    }
    
	func initializeOfflineConnection() {
		offlineAccessAttempt = ConnectionVerifierRequest()
		
		offlineAccessAttempt!.objectWillChange.receive(subscriber: Subscribers.Sink(receiveCompletion: { _ in }, receiveValue: {
            self.objectWillChange.send()
        }))
	}
	
	
	func cacheToken(tokenUID: String, token: String) {
		let tokenData = TokenData(tokenUID: tokenUID, token: token)
        
        networkData = NetworkData(token: tokenData)
        
        // credit to Benedict: https://stackoverflow.com/questions/59274736/why-is-an-observableobject-embedded-in-an-environmentobject-not-adding-new-items
        networkData!.objectWillChange.receive(subscriber: Subscribers.Sink(receiveCompletion: { _ in }, receiveValue: {
            self.objectWillChange.send()
        }))
	}
    
    
    func saveToken(tokenUID: String, token: String) {
		
		// save the token with Valet
		
		
		
        let tokenData = TokenData(tokenUID: tokenUID, token: token)
		
		if storeToken(token: tokenData) == false {
			print("ERROR: didn't store token with Valet")
		}
        
        networkData = NetworkData(token: tokenData)
        
        // credit to Benedict: https://stackoverflow.com/questions/59274736/why-is-an-observableobject-embedded-in-an-environmentobject-not-adding-new-items
        networkData!.objectWillChange.receive(subscriber: Subscribers.Sink(receiveCompletion: { _ in }, receiveValue: {
            self.objectWillChange.send()
        }))
        
    }
    
    func signIn() {
        if session != nil {
            session?.cancel()
            session = nil
        }
        
#if !targetEnvironment(macCatalyst)
        let device = UIDevice.current.model
#endif
        
#if targetEnvironment(macCatalyst)
        let device = "Mac"
#endif
        
        let vendorID = String(describing: UIDevice.current.identifierForVendor!)
        
        guard let authURL = URL(string: "https://web.engr.oregonstate.edu/~nordlune/TekBox/app/auth?model=\(device)&vendorID=\(vendorID)") else {return}
        let scheme = "tekbox"
        print(authURL)
        session = ASWebAuthenticationSession(url: authURL, callbackURLScheme: scheme, completionHandler: {
            callbackURL, error in
            print(callbackURL as Any)
            print(error as Any)
            
            if (callbackURL != nil) && (error == nil) {
                guard let components = URLComponents(url: callbackURL!, resolvingAgainstBaseURL: true) else {
                        print("Invalid URL or missing token path")
                    return
                }
                let tokenPath = components.path
                let parameters = components.queryItems
                
                if let tokenUID = parameters?.first(where: {$0.name == "tokenUID"})?.value {
                    if let token = parameters?.first(where: {$0.name == "token"})?.value {
                        self.saveToken(tokenUID: "\(tokenUID)", token: "\(token)")
                    }
                }
            }
            
        })
        
        session?.presentationContextProvider = self
        
        session?.start()
    }
    
    
}

extension ContentManager: ASWebAuthenticationPresentationContextProviding {
    func presentationAnchor(for session: ASWebAuthenticationSession) -> ASPresentationAnchor {
        return presentingWindow!
    }
}
