//
//  NewOrderLockerView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/28/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct NewOrderLockerRow: View {
    
    let name: String
    
    let isLoaded: Bool
    
    
    let summary: String?
    
    
    
    var body: some View {
        HStack(alignment: .center) {
            Group {
                if (isLoaded) {
                    Image(systemName: "circle.fill")
                    .foregroundColor(.red)
                } else {
                    Image(systemName: "circle.fill")
                    .foregroundColor(.green)
                }
            }
            
            
            VStack(alignment: .leading) {
                Text("\(name)")
                    .font(.headline)
                
                //Spacer()
                
                Text(getSummary())
                
                
                
                //Text("Test message")
            }
        }
    }
    
    
    func getSummary() -> String {
        if (summary != nil) {
            return summary!
        } else if (isLoaded) {
            return "Loaded"
        } else if (!isLoaded) {
            return "Available"
        } else {
            return "No Summary"
        }
    }
}

struct NewOrderLockerRow_Previews: PreviewProvider {
    static var previews: some View {
        NewOrderLockerRow(name: "Preview Locker", isLoaded: false, summary: nil)
    }
}
