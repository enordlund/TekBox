//
//  ActivityViewCell.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/23/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct ActivityViewRow: View {
    
    var date: Date
	
	var locationName: String
    
    var boxName: String?
    
    //var clusterName: String
    
    var message: String?
    
    var isNotice: Bool
    
    var isAlert: Bool
    
    var body: some View {
        
        return HStack(alignment: .center) {
            VStack(alignment: .leading) {
				
				Text(self.locationName)
				.font(.subheadline)
				
				
                Text(formatTime(from: date))
                .font(.headline)
                
                //Spacer()
                
				
				
                Text(getSummary())
                
                
                
                //Text("Test message")
            }
            
            Spacer()
            
            HStack(alignment: .center) {
                
                if (isNotice) {
                    Image(systemName: "asterisk.circle.fill")
                    .foregroundColor(.gray)
                    //.font(.title)
                    
                }
                
                if (isAlert) {
                    Image(systemName: "exclamationmark.circle.fill")
                    .foregroundColor(.red)
                    //.font(.title)
                    
                }
                
                
            }
            
        }
    }
    
    func getSummary() -> String {
        if (message != nil) {
			if boxName != nil {
				let summary = boxName! + message!
				return summary
			} else {
				let summary = message!
				return summary
			}
            
        } else {
            return "No summary."
        }
    }
}

struct ActivityViewRow_Previews: PreviewProvider {
    static var previews: some View {
		ActivityViewRow(date: Date(), locationName: "Location Name", boxName: "Preview Box", message: "Message", isNotice: true, isAlert: true)
    }
}
