//
//  SignedOutView.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/20/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct SignedOutView: View {
    @EnvironmentObject var contentManager: ContentManager
		
		@State var showOfflineAccessView: Bool = false
		
		var body: some View {
			VStack {
				Spacer()
				
				Text("TekBox")
				.font(.largeTitle)
				
				Spacer()
				
				Button(action: {
					self.contentManager.signIn()
				}) {
					HStack {
						Spacer()
						Text("Sign in with ONID")
						Spacer()
					}
					
					
				}.padding()
				.buttonStyle(OrangeButtonStyle())
				
				if self.contentManager.offlineKeysAvailable {
					Button(action: {self.showOfflineAccess()}) {
						Text("Offline Access")
					}.buttonStyle(OrangeButtonStyle())
					.sheet(isPresented: self.$showOfflineAccessView) {
						NavigationView {
							OfflineAccessView()
								.environmentObject(self.contentManager)
							
						}.onDisappear() {
							if self.contentManager.offlineAccessAttempt != nil {
								self.contentManager.offlineAccessAttempt!.restoreVariables()
							}
							btSerial.disconnect()
						}
						
					}
				}
				
			}
		}
		
		func showOfflineAccess() {
			self.showOfflineAccessView = true
		}
}

struct SignedOutView_Previews: PreviewProvider {
    static var previews: some View {
        SignedOutView()
    }
}
