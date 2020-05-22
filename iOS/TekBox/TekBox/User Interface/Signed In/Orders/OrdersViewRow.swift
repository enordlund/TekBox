//
//  OrdersViewRow.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/27/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct OrdersViewRow: View {
    var loadedDateTime: String?
    var unloadedDateTime: String?
    
    var orderNumber: String
    
    var lockerName: String
	
	var locationName: String
    
    //var clusterName: String
    
    var message: String?
    
    var body: some View {
        
        return HStack(alignment: .center) {
            VStack(alignment: .leading) {
				
				Text(self.locationName)
					.font(.subheadline)
				
                Text("Order \(orderNumber)")
                    .font(.headline)
                
                //Spacer()
                
                Text(getSummary())
                
                
                
                //Text("Test message")
            }
            
        }
    }
    
    func getSummary() -> String {
        
        if (message != nil) {
            return message!
        } else if (unloadedDateTime != nil) {
            // giving preference to unloaded time
            let dateString = formatDateLowercase(fromDateTime: unloadedDateTime!)
            let timeString = formatTime(fromDateTime: unloadedDateTime!)
            return "Unloaded \(dateString) at \(timeString)"
        } else if (loadedDateTime != nil) {
            let dateString = formatDateLowercase(fromDateTime: loadedDateTime!)
            let timeString = formatTime(fromDateTime: loadedDateTime!)
            return "Loaded \(dateString) at \(timeString)"
        } else {
            return ""
        }
        /*
        if (message != nil) {
            let summary = boxName + message!
            return summary
        } else {
            return "No summary."
        }
        */
    }
}

struct OrdersViewRow_Previews: PreviewProvider {
    static var previews: some View {
		OrdersViewRow(orderNumber: "1234", lockerName: "Box Preview", locationName: "Location Name")
    }
}
