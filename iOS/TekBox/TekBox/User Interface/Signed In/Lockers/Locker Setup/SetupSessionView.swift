//
//  SetupSessionView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/10/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct SetupSessionView: View {
	@EnvironmentObject var contentManager: ContentManager
	
	@Environment(\.presentationMode) var presentationMode
	
	let sessionUID: String?
	
	@State private var setupSession: SetupSession? = nil
	@State private var setupSessionError: String? = nil
	
	@State private var locations: [Location]? = nil
	@State private var locationsError: String? = nil
	
	@State private var showSetupExistingLocationsView: Int? = 0
	
	@State private var showSetupNewLocationView: Int? = 0
	
    var body: some View {
		
		Group {
			if self.setupSession != nil {
				// setup session loaded
				if self.setupSession!.lockerUID != nil {
					// setup is allowed to continue
					// let user choose between creating a new location, or adding to an existing location.
					
					// first, see if the user already has an existing location.
					// request the locations data
					Group {
						if self.locations != nil {
							// locations are loaded
							
							VStack {
								Text("Add Locker to Location")
									.font(.title)
								
								Group {
									if self.locations!.count > 0 {
										VStack {
											// user can choose an existing location
											Spacer()
											
											NavigationLink(destination: SetupExistingLocationsView(locations: self.locations!, lockerUID: self.setupSession!.lockerUID!).environmentObject(self.contentManager), tag: 1, selection: $showSetupExistingLocationsView) {
												EmptyView()
											}
											Button(action: {self.showSetupExistingLocationsView = 1}) {
												Text("Add to Existing Location")
											}.buttonStyle(OrangeButtonStyle())
											.padding()
										}
										
									}
								}
								
								
//									Spacer()
//
//									// user can create a new location
//									NavigationLink(destination: SetupNewLocationView(), tag: 1, selection: $showSetupNewLocationView) {
//										EmptyView()
//									}
//									Button(action: {self.showSetupNewLocationView = 1}) {
//										Text("Add to new Location")
//									}.buttonStyle(OrangeButtonStyle())
//									.padding()
								
								Spacer()
							}
							
						} else if self.locationsError != nil {
							// error loading locations
							VStack {
								Text("Error")
									.font(.headline)
									.padding([.leading, .trailing])
								
								Text(self.locationsError!)
									.padding()
								
								Button(action: {self.refreshView()}) {
									Text("Try Again")
								}.padding()
								.buttonStyle(OrangeButtonStyle())
							}
						} else {
							LoadingView()
							.onAppear() {
								self.requestLocations()
							}
						}
					}
				} else if self.setupSession!.otherOwner == true {
					// user is not the registered owner of the locker
					
					VStack {
						Text("Authorization Error")
							.font(.title)
						
						Spacer()
						
						Text("This locker is already registered to another TekBox account. The registered account must remove the locker from their account before setup can continue. An email will be sent to the registered account.")
						
						Spacer()
					
					}.padding()
						.navigationBarItems(trailing: Button(action: {
							self.presentationMode.wrappedValue.dismiss()
						}) {
							Text("Done")
						})
				} else if self.setupSession!.noSession == true {
					VStack {
						Text("Session Not Found")
							.font(.title)
						
						Spacer()
						
						Text("No session was found for the locker. This can happen if the locker hasn't finished setting up a session. Please wait for the locker to stop blinking, and try again.")
						
						Spacer()
						
						Button(action: {self.refreshView()}) {
							Text("Try Again")
						}.padding()
							.buttonStyle(OrangeButtonStyle())
					}.padding()
					
				}
			} else if self.setupSessionError != nil {
				// error loading setup session
				// error loading account
				VStack {
				   Text("Error")
					   .font(.headline)
					   .padding([.leading, .trailing])
				   
				   Text(self.setupSessionError!)
					   .padding()
				   
				   Button(action: {self.refreshView()}) {
					   Text("Try Again")
				   }.padding()
				   .buttonStyle(OrangeButtonStyle())
			   }
		   } else {
			   // loading setup session
			   // data not loaded
			   VStack {
				   Spacer()
				   
				   if self.contentManager.networkData!.setupSessionDataIsLoaded == false {
					   // certificate is being attempted.
					   SpinnerView(subtext: "Looking for locker setup session...")
				   }
				   
				   Spacer()
				   
			   }.onAppear() {
				   self.requestSetupSessionData()
			   }
		   }
		}
		/*
		Group {
			if self.contentManager.networkData != nil {
				if contentManager.networkData!.setupSessionDataIsLoaded && contentManager.networkData!.setupSessionData != nil {
					// setup session data was successfully loaded
					// need to check to see if the data indicates that setup can continue (check errors in data)
					if contentManager.networkData!.setupSessionData!.lockerUID != nil {
						// setup is allowed to continue
						// let user choose between creating a new location, or adding to an existing location.
						
						// first, see if the user already has an existing location.
						// request the locations data
						
						Group {
							if contentManager.networkData!.locationsDataIsLoaded && contentManager.networkData!.locations != nil {
								// locations are loaded
								
								VStack {
									Text("Add Locker to Location")
										.font(.title)
									
									Group {
										if contentManager.networkData!.locations!.count > 0 {
											VStack {
												// user can choose an existing location
												Spacer()
												
												NavigationLink(destination: SetupExistingLocationsView(locations: self.contentManager.networkData!.locations!, lockerUID: self.contentManager.networkData!.setupSessionData!.lockerUID!), tag: 1, selection: $showSetupExistingLocationsView) {
													EmptyView()
												}
												Button(action: {self.showSetupExistingLocationsView = 1}) {
													Text("Add to Existing Location")
												}.buttonStyle(OrangeButtonStyle())
												.padding()
											}
											
										}
									}
									
									
//									Spacer()
//									
//									// user can create a new location
//									NavigationLink(destination: SetupNewLocationView(), tag: 1, selection: $showSetupNewLocationView) {
//										EmptyView()
//									}
//									Button(action: {self.showSetupNewLocationView = 1}) {
//										Text("Add to new Location")
//									}.buttonStyle(OrangeButtonStyle())
//									.padding()
									
									Spacer()
								}
								
							} else if self.contentManager.networkData!.locationsError != nil {
								// error loading locations
								VStack {
									Text("Error")
										.font(.headline)
										.padding([.leading, .trailing])
									
									Text(self.contentManager.networkData!.locationsError!)
										.padding()
									
									Button(action: {self.refreshView()}) {
										Text("Try Again")
									}.padding()
									.buttonStyle(OrangeButtonStyle())
								}
							} else {
								LoadingView()
								.onAppear() {
									self.contentManager.networkData!.requestLocations(completion: nil)
								}
							}
						}
						
						
					} else if contentManager.networkData!.setupSessionData!.otherOwner == true {
					   // user is not the registered owner of the locker
					   
					   VStack {
						   Text("Authorization Error")
							   .font(.title)
						   
						   Spacer()
						   
						   Text("This locker is already registered to another TekBox account. The registered account must remove the locker from their account before setup can continue. An email will be sent to the registered account.")
						   
						   Spacer()
						   
						   
					   }.padding()
						   .navigationBarItems(trailing: Button(action: {
							   self.presentationMode.wrappedValue.dismiss()
						   }) {
							   Text("Done")
						   })
				   } else if contentManager.networkData!.setupSessionData!.noSession == true {
						VStack {
							Text("Session Not Found")
							.font(.title)
							
							Spacer()
							
							Text("No session was found for the locker. This can happen if the locker hasn't finished setting up a session. Please wait for the locker to stop blinking, and try again.")
							
							Spacer()
							
							Button(action: {self.refreshView()}) {
								Text("Try Again")
							}.padding()
							.buttonStyle(OrangeButtonStyle())
						}.padding()
						
						
					}
				} else if contentManager.networkData!.setupSessionError != nil {
					// error loading setup session
					// error loading account
                    VStack {
                        Text("Error")
                            .font(.headline)
                            .padding([.leading, .trailing])
                        
                        Text(self.contentManager.networkData!.setupSessionError!)
                            .padding()
                        
                        Button(action: {self.refreshView()}) {
                            Text("Try Again")
                        }.padding()
                        .buttonStyle(OrangeButtonStyle())
                    }
				} else {
					// loading setup session
					// data not loaded
					VStack {
						Spacer()
						
						if self.contentManager.networkData!.setupSessionDataIsLoaded == false {
							// certificate is being attempted.
							SpinnerView(subtext: "Looking for locker setup session...")
						}
						
						Spacer()
						
					}.onAppear() {
						self.requestSetupSessionData()
					}
				}
			}
		}*/
    }
	
	
	func requestLocations() {
		self.contentManager.networkData!.requestLocations() { data, error in
			self.locations = data
			self.locationsError = error
		}
	}
	
	func requestSetupSessionData() {
		print("lookForSession()")
		print(self.sessionUID)
		if self.contentManager.networkData != nil && self.sessionUID != nil {
			self.contentManager.networkData!.requestSetupSession(withSessionUID: self.sessionUID!) { data, error in
				self.setupSession = data
				self.setupSessionError = error
			}
		}
		
	}
	
	func refreshView() {
		self.requestSetupSessionData()
	}
}

struct SetupSessionView_Previews: PreviewProvider {
    static var previews: some View {
		SetupSessionView(sessionUID: "1234")
    }
}
