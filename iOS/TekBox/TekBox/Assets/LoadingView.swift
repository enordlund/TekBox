//
//  LoadingView.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/24/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct LoadingView: View {
	var label: String = "Loading..."
    var spinnerAnimationDuration: Double?
    
    var body: some View {
        Group {
            if spinnerAnimationDuration != nil {
				SpinnerView(subtext: self.label, animationDuration: self.spinnerAnimationDuration!)
            } else {
				SpinnerView(subtext: self.label)
            }
        }
        
        
    }
}

struct LoadingView_Previews: PreviewProvider {
    static var previews: some View {
        LoadingView()
    }
}
