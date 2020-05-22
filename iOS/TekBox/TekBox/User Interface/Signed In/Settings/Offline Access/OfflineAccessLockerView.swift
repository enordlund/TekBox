//
//  MessagesView.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/16/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

import CoreBluetooth



struct OfflineAccessLockerView: View {
	
	@EnvironmentObject var contentManager: ContentManager
	
//	@ObservedObject var connectionAttempt = ConnectionVerifierRequest()
    
	let lockerName: String = "Locker Name"
    
	@State private var showRestartAlert = false
    
    var body: some View {
		
		
		VStack {
			if self.contentManager.offlineAccessAttempt != nil {
				if self.contentManager.offlineAccessAttempt!.dataIsLoaded == true {
					if self.contentManager.offlineAccessAttempt!.lockerSecretWasFound == true {
						// offline key was found
						Image(systemName: "lock.shield.fill")
							.font(.largeTitle)
							.foregroundColor(.orange)
							.padding()
						
						Text("Offline Key Available")
							.font(.title)
						
						Text("Unlock or lock the locker with the buttons below.")
							.multilineTextAlignment(.center)
							.padding([.top, .bottom])
													
						Spacer()
						
						HStack {
							Spacer()
							
							Button(action: {self.unlockLocker()}) {
								Text("Unlock")
							}.buttonStyle(OrangeButtonStyle())
							
							Spacer()
						}.padding([.leading, .trailing, .top])
						
//									Spacer()
						HStack {
							Spacer()
							
							Button(action: {self.lockLocker()}) {
								Text("Lock")
							}.buttonStyle(OrangeButtonStyle())
							
							Spacer()
						}.padding()
						
					} else if self.contentManager.offlineAccessAttempt!.lockerUIDWasReceived == true {
						// lockerUID is provided by locker, need to look for offline key
						
						Image(systemName: "lock.shield.fill")
							.font(.largeTitle)
							.foregroundColor(.orange)
							.padding()
						
						Text("No Offline Key")
							.font(.title)
						
						Text("No offline keys were found for this locker on this device.")
							.multilineTextAlignment(.center)
							.padding([.top, .bottom])
													
						Spacer()
						
					} else {
						Image(systemName: "lock.shield.fill")
							.font(.largeTitle)
							.foregroundColor(.orange)
							.padding()
						
						Text("No Offline Key")
							.font(.title)
						
						Text("This locker does not have any offline keys in its storage.")
							.multilineTextAlignment(.center)
							.padding([.top, .bottom])
													
						Spacer()
					}
				} else {
					LoadingView()
					.onAppear() {
						print("lockerUIDWasReceived: \(self.contentManager.offlineAccessAttempt!.lockerUIDWasReceived)")
						print("lockerSecretWasFound: \(self.contentManager.offlineAccessAttempt!.lockerSecretWasFound)")
						print("dataIsLoaded: \(self.contentManager.offlineAccessAttempt!.dataIsLoaded)")
						self.contentManager.offlineAccessAttempt!.requestConnectionVerifier()
					}
				}
			} else {
				Text("Initializing connection delegate")
					.onAppear() {
						self.contentManager.initializeOfflineConnection()
				}
			}
			
			
		}.navigationBarTitle(Text(self.lockerName), displayMode: .inline)
		.navigationBarItems(trailing:
			Button(action: {self.confirmRestart()}) {
				Image(systemName: "power")
					.font(.headline)
					.foregroundColor(.red)
			}.padding()
		)
		.alert(isPresented: self.$showRestartAlert, content: {
			Alert(title: Text("Confirm"),
				  message: Text("Are you sure you would like to restart this locker?"),
				  primaryButton: .destructive(Text("Restart"),
											  action: {self.restart()}),
				  secondaryButton: .cancel({self.showRestartAlert = false}))
		})
		.padding()
		.onDisappear() {
			if self.contentManager.offlineAccessAttempt != nil {
				self.contentManager.offlineAccessAttempt!.restoreVariables()
			}
			btSerial.disconnect()
		}
        
//
        
        
            
    }
	
	func unlockLocker() {
		messageHandler.sendUnlockRequest()
	}
	
	func lockLocker() {
		messageHandler.sendLockRequest()
	}
	
	func confirmRestart() {
		self.showRestartAlert = true
	}
	
	func restart() {
		messageHandler.sendRestartCommand()
	}
    
}

struct OfflineAccessLockerView_Previews: PreviewProvider {
    static var previews: some View {
        OfflineAccessLockerView()
    }
}
