//
//  RootCertificateSentView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/10/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct RootCertificateView: View {
	@EnvironmentObject var contentManager: ContentManager
    
	@ObservedObject var certificateAttempt = RootCertificateAttempt()
	
	@State private var showSetupSessionView: Int? = 0
	
    var body: some View {
        
		Group {
			if self.certificateAttempt.dataIsLoaded != true {
				// attempting certificate
				VStack {
					Text("Wi-Fi connection successful!")
						.padding()
					Spacer()
					
					if self.certificateAttempt.dataIsLoaded == nil {
						// certificate has not been attempted.
					} else if self.certificateAttempt.dataIsLoaded == false {
						// certificate is being attempted.
						SpinnerView(subtext: "Testing server connection...")
					}
					
					Spacer()
					
				}.onAppear() {
					self.sendCertificate()
				}
			} else {
				// certificate was attempted
				if self.certificateAttempt.wasSuccessful {
					// in this case, user should wait until the locker has established a session.
					VStack {
						Text("Your locker is now attempting to connect to TekBox Cloud. Wait for the locker to stop blinking before continuing.")
						NavigationLink(destination: SetupSessionView(sessionUID: self.certificateAttempt.sessionUID).environmentObject(self.contentManager), tag: 1, selection: $showSetupSessionView) {
							EmptyView()
						}
						
						Button(action: {self.showSetupSessionView = 1}) {
							Text("Continue")
						}.buttonStyle(OrangeButtonStyle())
						.padding()
					}
				} else {
					VStack {
						Text("Server connection failed.")
						Button(action: {self.tryCertificateAgain()}) {
							Text("Try Again")
						}.buttonStyle(OrangeButtonStyle())
						.padding()
					}
					
				}
			}
		}
    }
	
	func sendCertificate() {
		self.certificateAttempt.objectWillChange.send()
        self.certificateAttempt.sendCertificate()
    }
	
	func tryCertificateAgain() {
//        self.connectingServer = false
		self.certificateAttempt.objectWillChange.send()
        self.certificateAttempt.restoreVariables()
    }
	
	
}

//struct RootCertificateView_Previews: PreviewProvider {
//    static var previews: some View {
//        RootCertificateView()
//    }
//}
