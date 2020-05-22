//
//  MobileDeviceButtonView.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/23/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct MobileDeviceButtonView: View {
    @EnvironmentObject var contentManager: ContentManager
    
    var model: String
    var body: some View {
        HStack {
            Spacer()
            VStack {
                //Spacer()
                DeviceSymbolView(model: self.model)
                    .font(.title)
                    .padding()
                Text(self.model)
                    .padding([.leading, .trailing])
                    .multilineTextAlignment(.center)
                .allowsTightening(true)
            }
            
            Spacer()
        }
    }
}

struct MobileDeviceButtonView_Previews: PreviewProvider {
    static var previews: some View {
        MobileDeviceButtonView(model: "This iPhone")
    }
}
