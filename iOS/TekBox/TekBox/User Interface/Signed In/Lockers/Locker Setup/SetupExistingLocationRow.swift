//
//  SetupExistingLocationRow.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/10/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct SetupExistingLocationRow: View {
	let name: String
	
    var body: some View {
		VStack (alignment: .leading) {
			Text("\(name)")
				.font(.headline)
		}
    }
}

struct SetupExistingLocationRow_Previews: PreviewProvider {
    static var previews: some View {
		SetupExistingLocationRow(name: "Location Name")
    }
}
