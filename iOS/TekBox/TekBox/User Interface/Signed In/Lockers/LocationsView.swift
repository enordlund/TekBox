//
//  LocationsView.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/20/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct LocationsView: View {
    @EnvironmentObject var contentManager: ContentManager
	
	@State private var locations: [Location]? = nil
	@State private var locationsError: String? = nil
    
	@State private var showNewLockerView = false
	
    @State var locationServicesSettingsAreAllowed: Bool = false
    
    var body: some View {
		
		NavigationView {
			Group {
				if self.locations != nil {
					// locations are loaded
					if self.locations!.count > 1 {
						// user has more than one location, so display list
						Group {
							if (self.locationServicesSettingsAreAllowed == true) {
								//Text("Sorting locations by nearest location")
								
								List {
												
									ForEach(self.locations!, id: \.id) { location in
										
										NavigationLink(destination: LockersView(uid: location.uid, name: location.name)
											/*.navigationBarTitle(Text(location.name),
																displayMode: .large)*/) {
											//self.locationTransport.set(uuid: location.uuid)
											NewOrderLocationRow(name: location.name, openLockers: location.openLockerCount)
										}
										
									}
								}
								
							} else {
								
								// start with option to sort by location
								// // if this is coming up, location services are disabled.
								// // Therefore, it is okay for this button to automatically present LocationServicesRequestView
								List {
												
									ForEach(self.locations!, id: \.id) { location in
										
										NavigationLink(destination: LockersView(uid: location.uid, name: location.name)
											/*.navigationBarTitle(Text(location.name), displayMode: .large)*/) {
											//self.locationTransport.set(uuid: location.uuid)
											NewOrderLocationRow(name: location.name, openLockers: location.openLockerCount)
										}
										
									}
								}
								
							}
						}.navigationBarTitle(Text("Locations"), displayMode: .large)
					} else if self.locations!.count == 1 {
						// user only has one location, so go straight to list of lockers
						LockersView(uid: self.locations!.first!.uid, name: self.locations!.first!.name)
							.navigationBarTitle(Text("Lockers"), displayMode: .large)
					} else {
						// user has no locations
						VStack {
							Text("No Lockers Found")
								.font(.title)
								.padding()
							
							Text("Tap the plus button above to set up a locker.")
							
							Button(action: {self.refreshView()}) {
								Text("Refresh")
							}.padding()
							
						}.navigationBarTitle(Text("Lockers"), displayMode: .large)
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
					// data didn't load, or no locations were found.
					SpinnerView(subtext: "Loading Locations...")
				}
			}
			.navigationBarItems(trailing:
				HStack(alignment: .bottom) {
					Button(action: {self.showNewLockerView = true}) {
						Image(systemName: "plus.circle.fill")
							.foregroundColor(.orange)
							.font(.title)
					}.sheet(isPresented: self.$showNewLockerView) {
						BluetoothView()//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ this will change to an earlier view in the flow
							.environmentObject(self.contentManager)
							.onDisappear() {
								self.refreshView()
						}
					}
				}
			)
		}.onAppear() {
			self.refreshView()
		}
		
		/*
        Group {
            if ((self.contentManager.networkData!.locationsDataIsLoaded == true) && (self.contentManager.networkData!.locations != nil)) {
                
                if (self.contentManager.networkData!.locations!.count > 1) {
                    Group {
                        if (self.locationServicesSettingsAreAllowed == true) {
                            //Text("Sorting locations by nearest location")
                            // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ if only one location, go straight to lockers
                            List {
                                            
                                ForEach(self.contentManager.networkData!.locations!, id: \.id) { location in
                                    
                                    NavigationLink(destination: NewOrderLocationLockersView(uid: location.uid, name: location.name)
										.navigationBarTitle(Text(location.name), displayMode: .large)) {
                                        //self.locationTransport.set(uuid: location.uuid)
                                        NewOrderLocationRow(name: location.name, openLockers: location.openLockerCount)
                                    }
                                    
                                    
                                    
                                }
                            }
                            
                        } else {
                            
                            // start with option to sort by location
                            // // if this is coming up, location services are disabled.
                            // // Therefore, it is okay for this button to automatically present LocationServicesRequestView
                            List {
                                            
                                ForEach(self.contentManager.networkData!.locations!, id: \.id) { location in
                                    NavigationLink(destination: NewOrderLocationLockersView(uid: location.uid, name: location.name)
										.navigationBarTitle(Text(location.name), displayMode: .large)) {
                                        //self.locationTransport.set(uuid: location.uuid)
                                        NewOrderLocationRow(name: location.name, openLockers: location.openLockerCount)
                                    }
                                    
                                    /*
                                    NavigationLink(destination: NewOrderLocationLockersView()) {
                                        NewOrderLocationRow(name: location.name, openLockers: location.openLockerCount)
                                    }
                                    */
                                }
                            }
                            
                            
                            
                        }
                    }.navigationBarTitle(Text("Locations"))
                    
                } else if (self.contentManager.networkData!.locations!.count == 1) {
                    // go straight to the list of lockers for the one location.
                    NewOrderLocationLockersView(uid: self.contentManager.networkData!.locations!.first!.uid, name: self.contentManager.networkData!.locations!.first!.name)
                    .navigationBarTitle(Text("Lockers"))
                } else {
                    // no locations...
                    VStack {
                        Text("No lockers found.")
                        Text("Tap the plus button above to set up a locker.")
                    }.navigationBarTitle(Text("Lockers"))
                    
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
                // data didn't load, or no locations were found.
                LoadingView()
            }
            
        }*/
    }
    
    func refreshView() {
        if self.contentManager.networkData?.tokenData != nil {
			self.contentManager.networkData!.requestLocations() { data, error in
				self.locations = data
				self.locationsError = error
			}
			self.contentManager.networkData?.resetSetupSession()
        } else {
            print("ERROR ALERT")
        }
    }
}

struct LocationsView_Previews: PreviewProvider {
    static var previews: some View {
        LocationsView(locationServicesSettingsAreAllowed: true)
    }
}



