//
//  LocationServicesRequestView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/27/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI


var locationServicesAreEnabled = false

var shouldOfferLocationServices = true


enum NextView {
    case newOrder
    case newBox
}


let locationImage = Image(systemName: "location.fill")

struct LocationServicesRequestView: View {
    
    @Environment(\.presentationMode) private var presentationMode
    
    var nextView: NextView?
    
    @State private var showNewOrderView = false
    
    
    @EnvironmentObject var locationServicesSettings: LocationServicesSettings
    
    //@EnvironmentObject var navigationLinkTracker: NavigationLinkTracker
    
    var body: some View {
        Group {
            if (self.locationServicesSettings.shouldOffer) {
                VStack {
                    Spacer()
                    
                    locationImage
                        .foregroundColor(.orange)
                        .font(.title)
                    
                    
                    //Spacer()
                    
                    VStack/*(alignment: .leading)*/ {
                        Text("Approve Location Services?")
                            .font(.title)
                        Text("Your location will only leave your device when you add it to a location.")
                            .multilineTextAlignment(.center)
                            //.padding()
                        //Text("• Adding a location to a new cluster")
                        //Text("• Sorting clusters by nearest location")
                        
                        //Text("Your location will not be stored in association with your account.")
                            //.padding()
                        
                    }.padding()
                    
                    
                    Spacer()
                    
                    Button(action: {self.enableLocationServices()}) {
                        Text("Approve Location Services")
                    }.buttonStyle(OrangeButtonStyle())
                    
                    //Spacer()
                    
                    Button(action: {self.declineLocationServices()}) {
                        Text("Not Now")
                            .padding()
                    }
                }
            } else {
                Text("Please enable Location Services in Settings.")
            }
        }.onAppear() {
            print("Location Services View appeared")
            //self.navigationLinkTracker.flip()
        }.onDisappear() {
            
            //self.navigationLinkTracker.flip()
            print("Location Services View disappeared")
        }
        
        
        
        
    }
    
    func enableLocationServices() {
        locationServicesAreEnabled = true
        locationServicesSettings.refreshSettings()
        self.dismissView(nextView: nil)
    }
    
    func declineLocationServices() {
        shouldOfferLocationServices = false
        locationServicesSettings.refreshSettings()
        self.dismissView(nextView: nil)
    }
    
    func dismissView(nextView: NextView?) {
        print("Location Services View dismissed")
        self.presentationMode.wrappedValue.dismiss()
        
        if (nextView == NextView.newOrder) {
            //super.showLocationServicesRequestView = false
        } else if (nextView == NextView.newBox) {
            
        }
        
        return
    }
}

struct LocationServicesRequestView_Previews: PreviewProvider {
    static var previews: some View {
        LocationServicesRequestView()
    }
}




class LocationServicesSettings: ObservableObject {
    @Published var areAllowed = false
    @Published var shouldOffer = true
    
    init() {
        refreshSettings()
    }
    
    func refreshSettings() {
        areAllowed = locationServicesAreEnabled
        shouldOffer = true
    }
}
