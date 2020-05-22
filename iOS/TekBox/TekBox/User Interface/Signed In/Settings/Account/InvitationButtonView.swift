//
//  InvitationButtonView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/22/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct InvitationButtonView: View {
    @EnvironmentObject var contentManager: ContentManager
    
	var inviterName: String
    var locationName: String
	var isAdmin: Bool
	
	
    var body: some View {
		
		VStack {
			Text(self.locationName)
				.font(.headline)
			
			Text("Invited By \(self.inviterName)")
				.multilineTextAlignment(.center)
				.font(.caption)
				.padding([.top])
		}.padding()
		
    }
}

struct InvitationButtonView_Previews: PreviewProvider {
    static var previews: some View {
		InvitationButtonView(inviterName: "Inviter Name", locationName: "Location Name", isAdmin: true)
    }
}
