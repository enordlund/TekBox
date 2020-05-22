//
//  InvitationView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/22/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI
import UIKit


struct InvitationView: View {
	@EnvironmentObject var contentManager: ContentManager
	
	@Binding var isPresented: Bool
	
	var invitation: LocationInvitation
	
	@State private var showJoinAlert = false
	@State private var showDeclineAlert = false
	
	@State private var showJoinSuccess = false
	
	@State private var requestError: String? = nil
	
	
    var body: some View {
		Group {
			if self.showJoinSuccess == true {
				VStack {
					Text("Welcome! ")
					.font(Font.custom("Snell Roundhand", size: UIFont.preferredFont(forTextStyle: UIFont.TextStyle.largeTitle).pointSize))
					
					Group {
						if self.invitation.isAdmin {
							Text("You have successfully joined the location, \"\(self.invitation.locationName)\" as an Administrator.")
						} else {
							Text("You have successfully joined the location, \"\(self.invitation.locationName)\" as a Manager.")
						}
					}
					
					Button(action: {self.dismissSelf()}) {
						Text("Dismiss")
					}.buttonStyle(OrangeButtonStyle())
				}
				
			} else if self.requestError != nil {
				VStack {
					Text("Error")
						.font(.headline)
					
					Text(self.requestError!)
				}
				
			} else {
				ScrollView {
					VStack {
						Group {
							Text("You're Invited ")
								.font(Font.custom("Snell Roundhand", size: UIFont.preferredFont(forTextStyle: UIFont.TextStyle.largeTitle).pointSize))
							
							Group {
								if self.invitation.isAdmin {
									Text("To be an Administrator of the location, \"\(self.invitation.locationName).\"")
								} else {
									Text("To be a Manager of the location, \"\(self.invitation.locationName).\"")
								}
							}
							
							
							VStack (alignment: .leading) {
								Text("From,")
									.font(Font.custom("Snell Roundhand", size: UIFont.preferredFont(forTextStyle: UIFont.TextStyle.title2).pointSize))
								
								Text("\(self.invitation.inviterName)")
									.padding(.leading)
								Text("\(self.invitation.inviterEmail)")
									.padding(.leading)
							}
							
		//					Text("Invited By \(self.invitation.inviterName)")
		//					Text("(\(self.invitation.inviterEmail))")
						}.padding()
						
						
						Button(action: {self.presentJoinAlert()}) {
							Text("Join Location")
						}.buttonStyle(OrangeButtonStyle())
							.padding()
						.alert(isPresented: self.$showJoinAlert, content: {
							
							Alert(title: Text("Join \(self.invitation.locationName)"),
								  message: Text("This location will be available to your account after joining."),
								  primaryButton: .default(Text("Join").fontWeight(.bold), action: {self.joinLocation()}),
								  secondaryButton: .cancel({self.showJoinAlert = false}))
						})
						
						Button(action: {self.presentDeclineAlert()}) {
							Text("Decline Invitation")
						}.buttonStyle(OrangeButtonStyle())
						.padding()
					}.padding()
				}
				
				.alert(isPresented: self.$showDeclineAlert, content: {
					
					Alert(title: Text("Decline Invitation"),
						  message: Text("The invitation will be removed from your account."),
						  primaryButton: .default(Text("Cancel").fontWeight(.bold), action: {self.showDeclineAlert = false}),
						  secondaryButton: .destructive(Text("Decline"),
						  action: {self.declineInvitation()}))
				})
			}
		}
		
		
		
		
    }
	
	func dismissSelf() {
		self.isPresented = false
	}
	
	func presentJoinAlert() {
		self.showJoinAlert = true
	}
	
	func presentDeclineAlert() {
		self.showDeclineAlert = true
	}
	
	func joinLocation() {
		
		self.contentManager.networkData!.requestInvitationRSVP(forInvitationUID: self.invitation.invitationUID, accepted: true) { didComplete, error in
			if didComplete {
				// invitation was accepted, so go away
				
				
				self.isPresented = false
			} else if error != nil {
				// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ present error
				print("JOIN ERROR")
				self.requestError = error
//				self.isPresented = false
			} else {
				// didn't complete and no error?
				print("Unknown join error")
			}
		}
	}
	
	func declineInvitation() {
		self.contentManager.networkData!.requestInvitationRSVP(forInvitationUID: self.invitation.invitationUID, accepted: false) { didComplete, error in
			if didComplete {
				// invitation was successfully declined, so go away
				self.isPresented = false
			} else if error != nil {
				// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ present error
				print("DECLINE ERROR")
				self.requestError = error
//				self.isPresented = false
			} else {
				// didn't complete and no error?
				print("Unknown decline error")
			}
		}
	}
}

//struct InvitationView_Previews: PreviewProvider {
//    static var previews: some View {
//        InvitationView()
//    }
//}
