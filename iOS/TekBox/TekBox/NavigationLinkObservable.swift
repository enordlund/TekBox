//
//  NavigationLinkObservable.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/27/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import Foundation


class NavigationLinkTracker: ObservableObject {
    @Published var didReturn = false
    
    private var flipFlag = true
    
    init() {
        print("tracker initializing")
        didReturn = false
        print(didReturn)
    }
    
    func reset() {
        print("tracker resetting")
        didReturn = false
        print(didReturn)
    }
    
    func returning() {
        print("tracker returning")
        didReturn = true
        print(didReturn)
        //didReturn = !didReturn
    }
    
    func flip() {
        print("flip()")
        if (true) {
            print("tracker flipping")
            didReturn = !didReturn
            flipFlag = !flipFlag
        } else {
            print("tracker not flipping")
            print("resetting flipFlag")
            flipFlag = !flipFlag
        }
        
        print(didReturn)
    }
    
    func resetFlipFlag() {
        
    }
}
