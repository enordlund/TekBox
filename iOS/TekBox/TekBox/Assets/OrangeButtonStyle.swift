//
//  OrangeButton.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/19/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct OrangeButtonStyle: ButtonStyle {
    func makeBody(configuration: Self.Configuration) -> some View {
        configuration.label
			.font(.headline)
            .padding()
            .foregroundColor(.white)
            .background(Color.orange)
            .cornerRadius(CGFloat(8))
            .opacity(configuration.isPressed ? 0.8 : 1.0)
            .scaleEffect(configuration.isPressed ? 0.9 : 1.0, anchor: .center)
        
    }
}

struct OrangeButtonStyle_Previews: PreviewProvider {
    static var previews: some View {
        Button(action: {}) {
            Text("Button")
        }.buttonStyle(OrangeButtonStyle())
    }
}
