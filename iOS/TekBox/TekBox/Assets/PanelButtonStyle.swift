//
//  PanelButtonStyle.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/23/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct PanelButtonStyle: ButtonStyle {
    func makeBody(configuration: Self.Configuration) -> some View {
        configuration.label
        .padding()
        .foregroundColor(Color(.label))
        .background(Color(.secondarySystemFill))
        .cornerRadius(CGFloat(8))
        .font(.headline)
        .opacity(configuration.isPressed ? 0.8 : 1.0)
        .scaleEffect(configuration.isPressed ? 0.9 : 1.0, anchor: .center)
    }
}

struct PanelButtonStyle_Previews: PreviewProvider {
    static var previews: some View {
        Button(action: {}) {
            VStack {
                DeviceSymbolView(model: "iPhone")
                    .font(.title)
                    .padding([.top])
                Text("This iPhone")
                    .padding([.top])
            }
        }.buttonStyle(PanelButtonStyle())
    }
}
