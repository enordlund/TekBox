//
//  LockersView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/21/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//
/*
import SwiftUI

// assets
//let accountImage = Image(systemName: "person.crop.circle.fill")


//let managersImage = Image(systemName: "person.3.fill")

/*
struct Option: Identifiable {
    var id = UUID()
    
    var title = String()
    var icon: Image
}
*/

//let options: [Option] = [Option(title: "Account", icon: accountImage), Option(title: "Managers", icon: managersImage)]



struct LockersView: View {
	@EnvironmentObject var contentManager: ContentManager
	
    @State private var showNewLockerView = false
	
	let uid: String
	let name: String
    
    var body: some View {
        NavigationView {
			Group {
				if self.contentManager.networkData?.locationsDataIsLoaded == true {
					// displaying all locations, or going straight to the specific location's lockers if user only has one location
					if self.contentManager.networkData?.locations?.count == 1 {
						NewOrderLocationLockersView(uid: self.contentManager.networkData!.locations!.first!.uid, name: self.contentManager.networkData!.locations!.first!.name)
							.navigationBarTitle(Text("Lockers"), displayMode: .large)
					} else if (self.contentManager.networkData?.locations?.count) ?? 0 > 1 {
						LocationsView(locationServicesSettingsAreAllowed: false)
							.navigationBarTitle(Text("Locations"), displayMode: .large)
					} else if self.contentManager.networkData?.locations?.count == 0 {
						// Locations were successfully loaded, but no locations are on the account.
						// Offer shortcut to setup a locker
						
						// this is handled by the con
					} else {
						// no locations, due to an error
						VStack {
							Text("No Locations Found")
								.font(.title)
								.padding()
							
							Spacer()
							
							Button(action: {self.refreshView()}) {
								Text("Try Again")
							}.buttonStyle(OrangeButtonStyle())
							.padding()
						}.navigationBarTitle(Text("Locations"), displayMode: .large)
						
					}
				} else if self.contentManager.networkData?.locationsError != nil {
					// no locations, due to an error
					VStack {
						Text("No Locations Found")
							.font(.title)
							.padding()
						
						Spacer()
						
						Button(action: {self.refreshView()}) {
							Text("Try Again")
						}.buttonStyle(OrangeButtonStyle())
						.padding()
					}.navigationBarTitle(Text("Locations"), displayMode: .large)
				} else {
					SpinnerView(subtext: "Loading Locations...")
				}
				
			}
//            .navigationBarTitle(Text("Lockers"), displayMode: .large)
            .navigationBarItems(trailing:
                HStack(alignment: .bottom) {
                    Button(action: {self.addLocker()}) {
                        Image(systemName: "plus.circle.fill")
                            .foregroundColor(.orange)
                            .font(.title)
                    }.sheet(isPresented: self.$showNewLockerView) {
                        BluetoothView()//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ this will change to an earlier view in the flow
							.environmentObject(self.contentManager)
                    }
                }
            )
			
            
            //.navigationBarHidden(false)
            //.navigationBarItems()
                
        }.onAppear() {
			self.refreshView()
		}
    }
	
	func refreshView() {
		self.contentManager.networkData?.requestLocations(withReset: true, completion: nil)
	}
    
    func addLocker() {
        self.showNewLockerView = true
        return
    }
}

struct BoxesView_Previews: PreviewProvider {
    static var previews: some View {
        LockersView()
    }
}

*/

