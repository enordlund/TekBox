//
//  SetupRequestLockerCoordinatesView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/10/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//
/*
import SwiftUI

struct SetupRequestLockerCoordinatesView: View {
	@EnvironmentObject var contentManager: ContentManager
	
	let lockerUID: String
	let locationUID: String
	let element: LockerGridElement
	
	@State private var setupForExistingLocationSuccess: Bool = false
	@State private var setupForExistingLocationOfflineKey: String? = nil
	@State private var setupForExistingLocationError: String? = nil
	
    var body: some View {
		Group {
			if self.contentManager.networkData != nil {
				if self.contentManager.networkData!.setupForExistingLocationDataIsLoaded && self.contentManager.networkData!.setupForExistingLocationSuccess == true && self.contentManager.networkData!.setupForExistingLocationError == nil {
					// successfully added locker
					Text("Successfully added locker!")
				} else if self.contentManager.networkData!.setupForExistingLocationError != nil {
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
					// need to load
					LoadingView()
						.onAppear() {
								self.refreshView()
						}
				}
			}
		}
		
    }
	
	
	func refreshView() {
		print("SetupRequestLockerCoordinatesView.refreshView()")
		print("lockerUID: \(self.lockerUID)")
		print("locationUID: \(self.locationUID)")
		print("Row: \(self.element.row)")
		print("Column: \(self.element.column)")
		self.contentManager.networkData!.requestLockerSetupExistingLocation(forLockerUID: self.lockerUID, locationUID: self.locationUID, row: self.element.row, column: self.element.column) { success, offlineKey, error in
			self.setupForExistingLocationSuccess = success
			self.setupForExistingLocationOfflineKey = offlineKey
			self.setupForExistingLocationError = error
		}
	}
}

//struct SetupRequestLockerCoordinatesView_Previews: PreviewProvider {
//    static var previews: some View {
//        SetupRequestLockerCoordinatesView()
//    }
//}
*/
