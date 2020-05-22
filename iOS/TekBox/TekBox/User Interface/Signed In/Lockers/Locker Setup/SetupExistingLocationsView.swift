//
//  SetupExistingLocationsView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/10/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct SetupExistingLocationsView: View {
	@EnvironmentObject var contentManager: ContentManager
	
	let locations: [Location]
	
	let lockerUID: String
	
    var body: some View {
		List {
			ForEach(self.locations) { location in
				NavigationLink(destination: SetupSelectCoordinatesView(location: location, lockerUID: self.lockerUID)) {
					//self.locationTransport.set(uuid: location.uuid)
					SetupExistingLocationRow(name: location.name)
				}
			}
		}.onAppear() {
			if self.contentManager.networkData != nil {
				self.contentManager.networkData!.resetSetupForExistingLocationVariables()
			}
		}
    }
}

//struct SetupExistingLocationsView_Previews: PreviewProvider {
//    static var previews: some View {
//        SetupExistingLocationsView()
//    }
//}
