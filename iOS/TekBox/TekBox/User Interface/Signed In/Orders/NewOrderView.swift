//
//  NewOrderView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/27/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI









struct NewOrderViewReturnState {
    let locationUID: String?
    let lockerUID: String?
    
}

var newOrderViewReturnState: NewOrderViewReturnState? = nil

struct NewOrderView: View {
    @EnvironmentObject var contentManager: ContentManager
    
    @Environment(\.presentationMode) var presentationMode
    
    @State var showLocationServicesRequestView = false
    
    //@State private var locationServicesEnabled = locationServicesAreEnabled
    
    @State private var buttonIsActive: Int? = 0
    
    
    @ObservedObject var locationServicesSettings = LocationServicesSettings()
    
    //@ObservedObject var navigationLinkTracker = NavigationLinkTracker()
    
    
    //@ObservedObject var locations = NetworkData(token: nil)
    
    //var locationTransport = LocationViewTransport()
    
    
    
    private var locationUID: String? = nil
    private var lockerUID: String? = nil
    
    var body: some View {
        NavigationView {
            
            
            Group {
                if ((self.contentManager.networkData!.locationsDataIsLoaded == true) && (self.contentManager.networkData!.locations != nil)) {
                    if (self.locationServicesSettings.areAllowed == true) {
                        //Text("Sorting locations by nearest location")
                        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ if only one location, go straight to lockers
                        List {
                                        
                            ForEach(self.contentManager.networkData!.locations!, id: \.id) { location in
                                
                                NavigationLink(destination: LockersView(uid: location.uid, name: location.name)
								.navigationBarTitle(Text(location.name), displayMode: .inline)) {
                                    //self.locationTransport.set(uid: location.uid)
                                    NewOrderLocationRow(name: location.name, openLockers: location.openLockerCount)
                                }
                                
                                
                                
                            }
                        }
                        .navigationBarItems(leading:
                            HStack(alignment: .bottom) {
                                Button(action: {self.dismissViewDestructive()}) {
                                    Text("Cancel")
                                }.foregroundColor(.red)
                            }, trailing:
                            EmptyView()
                        )
                        
                    } else {
                        
                        // start with option to sort by location
                        // // if this is coming up, location services are disabled.
                        // // Therefore, it is okay for this button to automatically present LocationServicesRequestView
                        List {
                                        
                            ForEach(self.contentManager.networkData!.locations!, id: \.id) { location in
                                NavigationLink(destination: LockersView(uid: location.uid, name: location.name)
								.navigationBarTitle(Text(location.name), displayMode: .inline)) {
                                    //self.locationTransport.set(uid: location.uid)
                                    NewOrderLocationRow(name: location.name, openLockers: location.openLockerCount)
                                }
                                
                                /*
                                NavigationLink(destination: NewOrderLocationLockersView()) {
                                    NewOrderLocationRow(name: location.name, openLockers: location.openLockerCount)
                                }
                                */
                            }
                        }
                        .navigationBarItems(leading:
                            HStack(alignment: .bottom) {
                                Button(action: {self.dismissViewDestructive()}) {
                                    Text("Cancel")
                                }.foregroundColor(.red)
                            }, trailing:
                            NavigationLink(destination: LocationServicesRequestView().environmentObject(locationServicesSettings), tag: 1, selection: $buttonIsActive) {
                                Button(action: {self.activateButtonState()}) {
                                    HStack {
                                        locationImage
                                        Text("Sort")
                                    }
                                }
                                
                            }
                        )
                        
                        
                        
                    }
                } else if self.contentManager.networkData!.locationsError != nil {
                    // error loading account
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
                
            }.navigationBarTitle(Text("New Order"), displayMode: .inline)
            
            
            
            
                
        }.accentColor(.orange)
        .onAppear() {
            self.refreshView()
        }
        
        
    }
    
    func refreshView() {
        if self.contentManager.networkData?.tokenData != nil {
			self.contentManager.networkData!.requestLocations(completion: nil)
        } else {
            print("ERROR ALERT")
        }
        
    }
    
    func activateButtonState() {
        //print("\(self.navigationLinkTracker.didReturn)")
        print("activating button")
        
        //self.navigationLinkTracker.returning()
        //self.navigationLinkTracker.reset()
        //self.navigationLinkTracker.flip()
        
        //print("\(self.navigationLinkTracker.didReturn)")
        
        self.buttonIsActive = 1
        return
    }
    
    func offerLocationServices() {
        //newOrderViewReturnState = NewOrderViewReturnState(locationUID: nil, lockerUID: nil)
        self.showLocationServicesRequestView = true
        return
    }
    
    func dismissViewForReturn() {
        // save return state
        //newOrderViewReturnState = NewOrderViewReturnState(locationUID: locationUID, lockerUID: lockerUID)
        
        self.presentationMode.wrappedValue.dismiss()
        return
    }
    
    func dismissViewDestructive() {
        // clear return state
        //newOrderViewReturnState = nil
        
        self.presentationMode.wrappedValue.dismiss()
        return
    }
    
}

struct NewOrderView_Previews: PreviewProvider {
    static var previews: some View {
        NewOrderView()
    }
}








class LocationViewTransport: ObservableObject {
    @Published var locationUID: String? = nil
    @Published var uidDidSet = false
    
    init() {
        locationUID = nil
    }
    
    
    func set(uid: String) {
        locationUID = uid
        uidDidSet = true
    }
}




