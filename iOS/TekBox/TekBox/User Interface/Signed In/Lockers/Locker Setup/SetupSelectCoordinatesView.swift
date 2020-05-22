//
//  SetupSelectCoordinatesView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/10/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI





struct SetupSelectCoordinatesView: View {
	@EnvironmentObject var contentManager: ContentManager
	
	@State var showSetupRequestLockerCoordinatesView: Bool = false
	
//	@State private var lockers: [Locker]? = nil
	@State private var lockersError: String? = nil
	
	@State private var lockerGrid: LockerGridTable? = nil
	
//	@State private var setupForExistingLocationSuccess: Bool = false
//	@State private var setupForExistingLocationOfflineKey: String? = nil
//	@State private var setupForExistingLocationError: String? = nil
	
	let location: Location
	
	let lockerUID: String
	
    var body: some View {
		Group {
			if self.lockerGrid == nil {
				if self.lockersError != nil {
					// lockers error
					Text("Error loading lockers")
				} else {
					// need to load the locker grid for an updated selection
					LoadingView()
						.onAppear() {
							self.contentManager.networkData?.requestLockerGrid(fromLocationUID: self.location.uid, rowCount: self.location.rows, columnCount: self.location.columns) { data, error in
								self.lockerGrid = data
								self.lockersError = error
							}
					}
				}
			} else {
				// locker grid is loaded, so determine status
				if self.contentManager.networkData?.setupForExistingLocationSuccess == true {
					// successfully added locker
					VStack {
						Text("Successfully added locker!")
							.padding()
						
						if self.contentManager.networkData!.setupForExistingLocationOfflineKey != nil {
							HStack {
								Spacer()
								
								Button(action: {self.saveOfflineKey()}) {
									Text("Save Offline Key")
								}.buttonStyle(OrangeButtonStyle())
								
								Spacer()
							}.padding()
							
						}
					}
					
				} else if self.contentManager.networkData?.setupForExistingLocationError != nil {
					// present error
					VStack {
						Text("Error")
							.font(.headline)
							.padding([.leading, .trailing])
						
						Text(self.contentManager.networkData!.setupForExistingLocationError!)
							.padding()

						Button(action: {self.refreshView()}) {
							Text("Try Again")
						}.padding()
						.buttonStyle(OrangeButtonStyle())
					}
				} else {
					// lockers are loaded, so construct and present the grid of lockers
					// this might need to be in a horizontal scroll view
					VStack {
						Text("Select Placement")
							.font(.title)
						Spacer()
						
						Text("Tap one of the orange grid cells to select the placement of this locker.")
						
						Spacer()
						
						ForEach (self.lockerGrid!.lockers) { row in
							HStack {
								ForEach (row.row) { column in
									LockerGridElementView(element: column, lockerUID: self.lockerUID, locationUID: self.location.uid)
										.cornerRadius(5.0)
									.padding(2)
									
								}
							}
						}
					}
					
				}
			}
		}.onDisappear() {
			self.refreshView()
		}
		
    }
	
	
//	func requestLockers() {
//		if self.contentManager.networkData != nil {
//			self.contentManager.networkData!.requestLockers(fromLocationUID: self.location.uid, completion: { data, error in
//				self.lockers = data
//				self.lockersError = error
//			})
//		}
//	}
	
	func refreshView() {
		if self.contentManager.networkData != nil {
			self.contentManager.networkData!.resetSetupForExistingLocationVariables()
		}
	}
	
	func saveOfflineKey() {
		print("saveOfflineKey()")
		// pair lockerUID and the offline key from networkData
		if self.contentManager.networkData != nil {
			if let secret = self.contentManager.networkData!.setupForExistingLocationOfflineKey {
				do {
					print("deleting any matching keys")
					try deleteOfflineKey(forLockerUID: self.lockerUID)
					
					print("storing key")
					try storeOfflineKey(offlineKey: OfflineKey(lockerUID: self.lockerUID, secret: secret))
					
					
				} catch {
					print("KEYCHAIN ERROR: \(error)")
				}
			}
			
		}
		
	}
	
}

//struct SetupSelectCoordinatesView_Previews: PreviewProvider {
//    static var previews: some View {
//        SetupSelectCoordinatesView()
//    }
//}

