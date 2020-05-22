//
//  ContentView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/20/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI



struct ContentView: View {
    
    @EnvironmentObject var contentManager: ContentManager
    
    //@State var tabSelection: ContentManager.TabSelection = .lockers
    
    //@State var shortcutSelection: ContentManager.ShortcutSelection? = nil
	
	@State private var accountInfoWasLoading = false
    
    
    // assets
    let dashboardImage = Image(systemName: "rectangle.3.offgrid")
    
    let boxesImage = Image(systemName: "square.grid.3x2")
    
    let ordersImage = Image(systemName: "doc.plaintext")
    
    let activityImage = Image(systemName: "list.dash")
    
    let gearImage = Image(systemName: "gear")
    
    let ellipsisImage = Image(systemName: "ellipsis")
    
    
    
    
    var body: some View {
        
        Group {
            if contentManager.networkData?.tokenData != nil {
				// user is signed in
				if contentManager.networkData?.launchDataDidLoad == false {
					// locations haven't loaded, so load the locations first
					SpinnerView(subtext: "Loading Account Info...")
						
				} else if contentManager.networkData?.locations?.count == 0 {
					// user has no locations, so offer setup or invitation acceptance
					VStack {
						Text("No Locations Found")
							.font(.title)
						.padding()
						
						Text("No lockers were found for your account. You may add a locker to your account, or view any available location invitations.")
							.padding()
						
						Spacer()
						
						
						Button(action: {}) {
							Text("Add Locker")
						}.buttonStyle(OrangeButtonStyle())
						
						
						// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ if user has any invitations
						Button(action: {}) {
							Text("View Invitations")
						}.buttonStyle(OrangeButtonStyle())
						.padding()
					}
					
				} else {
					// this is the normal scenario once the user has at least one location, so present the tabs
					TabView(selection: self.$contentManager.tabSelection){
						LocationsView()
							.tabItem {
								VStack {
									//Image("first")
									boxesImage
									
									Group {
										if contentManager.networkData?.locations?.count == 1 {
											Text("Lockers")
										} else {
											Text("Locations")
										}
									}
									
								}
							}
						.tag(ContentManager.TabSelection.lockers)
						
						OrdersView()
							//.environmentObject(self.contentManager)
							.tabItem {
								VStack {
									//Image("second")
									ordersImage
									
									Text("Orders")
								}
							}
						.tag(ContentManager.TabSelection.orders)
						
						ActivityView()
							.tabItem {
								VStack {
									//Image("first")
									activityImage
									
									Text("Activity")
								}
							}
						.tag(ContentManager.TabSelection.activity)
						
						SettingsView()
							.tabItem {
								VStack {
									//Image("second")
									gearImage
									
									Text("Settings")
								}
							}
						.tag(ContentManager.TabSelection.settings)
					}
					.modifier(NavigationViewFixBefore134())
					//.edgesIgnoringSafeArea(.top)
					.accentColor(.orange)
					
						
				}
                
            } else {
                SignedOutView()
                .environmentObject(contentManager)
            }
        }.onAppear() {
			print("ContentView requestLaunchData()")
			self.contentManager.networkData?.requestLaunchData()
		}
        
            
        
    }
    
}

struct ContentView_Previews: PreviewProvider {
    static var previews: some View {
        ContentView()
    }
}





struct NavigationViewFixBefore134: ViewModifier {
	
	/// Before iOS 13.4, a TabView containing a NavigationView did not go well.
	func body(content: Content) -> some View {
		if #available(iOS 13.4, *) {
			return AnyView(content)
		} else {
			return AnyView(content.edgesIgnoringSafeArea(.top))
		}
	}
}


