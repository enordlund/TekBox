//
//  PhoneSymbolView.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/22/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct DeviceSymbolView: View {
    var model: String
    
    var body: some View {
        Group {
            if model.contains("Mac") {
                ZStack {
                    Image(systemName: "rectangle.fill")
                        .foregroundColor(Color(.systemTeal))
                        .scaleEffect(x: 1.0, y: 0.66, anchor: .top)
                    Image(systemName: "desktopcomputer")
                    
                }.scaleEffect(x: 1.42, y: 1.42, anchor: .center)
                
            } else {
                ZStack {
                    Image(systemName: "rectangle.fill")
                        .foregroundColor(Color(.systemTeal))
                    Image(systemName: "rectangle")
                }
                    .rotationEffect(Angle(degrees: 90))
                .scaleEffect(x: {model.contains("iPad") ? CGFloat(1.4) : CGFloat(1.0)}(), y: 1.42, anchor: .center)
            }
        }
        
    }
}

struct DeviceSymbolView_Previews: PreviewProvider {
    static var previews: some View {
        DeviceSymbolView(model: "iPhone")
    }
}
