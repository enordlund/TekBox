//
//  LockerGridElementView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/10/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct LockerGridElementView: View {
	@EnvironmentObject var contentManager: ContentManager
	
	@State var element: LockerGridElement
	
	let lockerUID: String
	let locationUID: String
	
    var body: some View {
		Group {
			if self.element.state == .open {
				Button(action:{self.requestCoordinates()}) {
					VStack {
						Text("Row: \(self.element.row)")
						Text("Column: \(self.element.column)")
					}
				}
			} else if self.element.state == .locker {
				VStack {
					Text("Row: \(self.element.row)")
					Text("Column: \(self.element.column)")
				}
			} else {
				VStack {
					Text("Row: \(self.element.row)")
					Text("Column: \(self.element.column)")
				}
			}
		}
//		VStack {
//			Text("Row: \(self.element.row)")
//			Text("Column: \(self.element.column)")
//		}
		
    }
	
	func requestCoordinates() {
		let row = element.row
		let column = element.column
		
		if self.contentManager.networkData != nil {
			self.contentManager.networkData!.requestLockerSetupExistingLocation(forLockerUID: self.lockerUID, locationUID: self.locationUID, row: row, column: column, completion: nil)
		}
	}
}

//struct LockerGridElementView_Previews: PreviewProvider {
//    static var previews: some View {
//        LockerGridElementView()
//    }
//}
