//
//  LoadingView.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/24/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct SpinnerView: View {
    var subtext: String?
    
    var animationDuration: Double = 30
    
    @State private var animate = false
    
    var body: some View {
        VStack {
            Image(systemName: "slowmo")
            .foregroundColor(Color(.systemGray))
            .font(.title)
                .rotationEffect(self.animate ? .degrees(0) : .degrees(-144 * self.animationDuration))
                .animation(.linear(duration: self.animationDuration))
            
            .onAppear() {
                self.animate = true
            }
            
            if self.subtext != nil {
                Text(self.subtext!)
            }
            
        }.opacity(self.animate ? 1 : 0)
        .animation(.easeIn(duration: 0.2))
        
    }
}

struct SpinnerView_Previews: PreviewProvider {
    static var previews: some View {
        SpinnerView(subtext: "Loading...")
    }
}
