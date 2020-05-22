//
//  SheetXButtonView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/22/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct SheetXButtonView: View {
    var body: some View {
        ZStack {
			Image(systemName: "circle.fill")
			.foregroundColor(Color(.systemGray))
			
			Image(systemName: "xmark.circle.fill")
				.foregroundColor(Color(.systemGray4))
			
			
			
		}.font(.title)
    }
}

struct SheetXButtonView_Previews: PreviewProvider {
    static var previews: some View {
        SheetXButtonView()
    }
}
