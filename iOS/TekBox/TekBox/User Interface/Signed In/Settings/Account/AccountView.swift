//
//  AccountView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/23/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI
import UIKit


enum SheetView {
    case device
	case invitation
}

struct AccountView: View {
    @EnvironmentObject var contentManager: ContentManager
    
    @State private var showSignOutAlert = false
    
//    @State private var showAuthenticatedDevice = false
    
    @State private var deviceToPresent: MobileDevice?
	
	@State private var showSheet = false
	
	@State private var sheetView: SheetView?
	
	@State private var invitationToPresent: LocationInvitation?
    
    private let vendorID = String(describing: UIDevice.current.identifierForVendor!)
    
    var body: some View {
        Group {
            if self.contentManager.networkData!.accountDataIsLoaded && self.contentManager.networkData!.signOutSuccess == true {
                Text("Signed Out")
                    .onAppear() {
                        //self.contentManager.tokenData = nil
                        self.contentManager.networkData?.tokenData = nil
                }
            } else if self.contentManager.networkData!.accountDataIsLoaded && self.contentManager.networkData!.account != nil {
                // account info is loaded
                GeometryReader { geometry in
                    ScrollView {
                        VStack {
                            HStack {
                                Text("Personal Information")
                                .font(.headline)
                                
                                Spacer()
                            }
                            
                            
                            VStack {
                                HStack {
                                    Text("Name: " + self.contentManager.networkData!.account!.name)
                                    
                                    Spacer()
                                }
                                
                                HStack {
                                    Text("Email: " + self.contentManager.networkData!.account!.email)
                                    
                                    Spacer()
                                }
                                
                                
                                
                            }.padding()
							
							Group {
								if (self.contentManager.networkData!.account!.invitations != nil) {
									HStack {
										Text("Invitations")
											.font(.headline)
										Spacer()
									}.padding([.top])
									
									VStack {
										ForEach (self.contentManager.networkData!.account!.invitations!) { invitation in
											
											Button(action: {self.showInvitationView(forInvitation: invitation)}) {
												InvitationButtonView(inviterName: invitation.inviterName, locationName: invitation.locationName, isAdmin: invitation.isAdmin)
											}.buttonStyle(PanelButtonStyle())
                                            .padding()
											
										}
									}
								} else {
									EmptyView()
								}
							}
                            
                            HStack {
                                Text("Authenticated Devices")
                                    .font(.headline)
                                Spacer()
                            }.padding([.top])
                            
                            VStack {
                                ForEach (self.contentManager.networkData!.mobileDeviceTable!.devices) { row in
                                    HStack {
                                        ForEach (row.row) { column in
                                            Button(action: {self.showDeviceView(forDevice: column)}) {
                                                VStack {
                                                    Spacer()
                                                    MobileDeviceButtonView(model: column.model)
                                                    Spacer()
                                                }
                                            }.buttonStyle(PanelButtonStyle())
                                            .padding([.leading, .trailing])
                                                
                                            
                                        }
                                    }.padding([.top, .bottom])
                                        .layoutPriority(row.layoutPriority)
                                    
                                }
                            }
                            
                            
                            Spacer()
                            
                            
                            
                        }.padding()
                        
                    }
                }
                .navigationBarItems(trailing:
                    Button(action: {self.signOutButton()}) {
                        Text("Sign Out")
                            .foregroundColor(.red)
                    }
                )
                
                
                
                
            } else if self.contentManager.networkData!.accountDataIsLoaded {
                // no account provided
                
            } else if self.contentManager.networkData!.accountError != nil {
                // error loading account
                VStack {
                    Text("Error")
                        .font(.headline)
                        .padding([.leading, .trailing])
                    
                    Text(self.contentManager.networkData!.accountError!)
                        .padding()
                    
                    Button(action: {self.refreshView()}) {
                        Text("Try Again")
                    }.padding()
                    .buttonStyle(OrangeButtonStyle())
                }
            } else {
                LoadingView()
                    .onAppear() {
                        self.refreshView()
                }
            }
        }.navigationBarTitle("Account", displayMode: .inline)
            .onAppear() {
                self.refreshView()
        }.alert(isPresented: self.$showSignOutAlert, content: {
            Alert(title: Text("Confirm"),
                  message: Text("Are you sure you would like to sign out of TekBox?"),
                  primaryButton: .destructive(Text("Sign Out"),
                                              action: {self.signOut()}),
                  secondaryButton: .cancel({self.showSignOutAlert = false}))
        })
//        .sheet(isPresented: self.$showAuthenticatedDevice) {
//                NavigationView {
//                    AuthenticatedDeviceView(isPresented: self.$showAuthenticatedDevice, showSignOutAlert: self.$showSignOutAlert, device: self.deviceToPresent!)
//                        .environmentObject(self.contentManager)
//                    .navigationBarItems(trailing: HStack(alignment: .bottom) {
//                        Button(action: {
//                            self.dismissDeviceView()
//                        }) {
//                            Text("Done")
//                                .font(.headline)
//                                .foregroundColor(.orange)
//                        }
//                    })
//                }
//        }
		.sheet(isPresented: self.$showSheet) {
			NavigationView {
				Group {
					if self.sheetView == SheetView.invitation {
						// show invitation view
						InvitationView(isPresented: self.$showSheet, invitation: self.invitationToPresent!)
							.environmentObject(self.contentManager)
					} else if self.sheetView == SheetView.device {
						// show device view
						AuthenticatedDeviceView(isPresented: self.$showSheet, showSignOutAlert: self.$showSignOutAlert, device: self.deviceToPresent!)
                        .environmentObject(self.contentManager)
					} else {
						EmptyView()
					}
				}
				.navigationBarItems(trailing:
					HStack {
						Button(action: {
							self.dismissSheet()
						}) {
							SheetXButtonView()
						}
				})
			}
		}
        
            
    }
    
    
    func refreshView() {
        if self.contentManager.networkData?.tokenData != nil {
            self.contentManager.networkData!.requestAccount()
        } else {
            print("ERROR ALERT")
        }
        
    }
    
    func showDeviceView(forDevice: MobileDevice) {
        print("showDevice()")
        self.deviceToPresent = forDevice
		self.sheetView = SheetView.device
		self.showSheet = true
        
    }
    
    func dismissDeviceView() {
        self.showSheet = false
        self.deviceToPresent = nil
    }
	
	
	func showInvitationView(forInvitation: LocationInvitation) {
		print("showInvitationView()")
		self.invitationToPresent = forInvitation
		self.sheetView = SheetView.invitation
		self.showSheet = true
	}
	
	func dismissSheet() {
		self.showSheet = false
		self.invitationToPresent = nil
	}
    
    func signOutButton() {
        print("AccountView signOutButton()")
        
        // present confirmation alert
        self.showSignOutAlert = true
    }
    
    func signOut() {
        print("AccountView signOut()")
        
        // tell server to delete the token, and then sign out (preserve bluetooth keys)
        if contentManager.networkData?.tokenData != nil {
            self.contentManager.networkData!.requestSignOut()
        } else {
            print("ERROR ALERT")
        }
        
    }
}

struct AccountView_Previews: PreviewProvider {
    static var previews: some View {
        AccountView()
    }
}




struct MobileDevice: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var model: String
    var vendorID: String
    var tokenUID: String
    var lastAccessed: String
    var firstAuthenticated: String
}

struct LocationInvitation: Identifiable, Decodable {
	let id: UUID? = UUID()
	
	var invitationUID: String
	var inviterName: String
	var inviterEmail: String
	var locationName: String
	var locationUID: String
	var isAdmin: Bool
}

struct Account: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var name: String
    var email: String
	var invitations: [LocationInvitation]?
    var mobileDevices: [MobileDevice]
    var locations: String?
    var lockers: String?
}

struct TaskReport: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var didComplete: Bool
	var asset: String?
	var errorMessage: String?
}


