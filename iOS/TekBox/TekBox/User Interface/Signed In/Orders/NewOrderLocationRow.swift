//
//  NewOrderLocationRow.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/27/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct NewOrderLocationRow: View {
    
    let name: String
    
    let openLockers: Int
    
    var body: some View {
        HStack(alignment: .center) {
            Group {
                if (openLockers > 0) {
                    Image(systemName: "circle.fill")
                    .foregroundColor(.green)
                } else {
                    Image(systemName: "circle.fill")
                    .foregroundColor(.red)
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
        var summary = ""
        if openLockers == 1 {
            summary = "\(openLockers) locker available"
        } else {
            summary = "\(openLockers) lockers available"
        }
        
        return summary
    }
}

struct NewOrderLocationRow_Previews: PreviewProvider {
    static var previews: some View {
        NewOrderLocationRow(name: "Preview Location", openLockers: 2)
    }
}
