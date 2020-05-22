//
//  ActivityDetailView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/23/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI

extension String: Identifiable {
    public var id: String {
        return self
    }
}

struct ActivityDetailView: View {
    
    var entry: ActivityEntry
    
    var body: some View {
        VStack {
            
            
            HStack {
                Text(formatDate(fromDateTime: self.entry.dateTime))
                    .font(.headline)
                
                Spacer()
            }
            
            
            HStack {
                Text(formatTime(from: getDate(fromDateTime: self.entry.dateTime)))
                    .font(.title)
                
                Spacer()
            }
            
            
            
            
            if self.entry.summary != nil {
                HStack {
					Text("\(self.entry.lockerName ?? "")\(self.entry.summary!)")
                    
                    Spacer()
                }.padding()
                
            }
            
            if self.entry.alerts != nil {
                VStack {
                    HStack {
                        Text("Alerts")
                            .font(.headline)
                        
                        Spacer()
                    }
                    
                    
                    ForEach(self.entry.alerts!, id: \.self) { alert in
                        HStack {
                            Image(systemName: "exclamationmark.circle.fill")
                                .foregroundColor(.red)
                            
                            Text(alert)
                            
                            Spacer()
                        }.padding([.leading])
                    }
                }
                
            }
            
            if self.entry.notices != nil {
                VStack {
                    HStack {
                        Text("Notices")
                            .font(.headline)
                        
                        Spacer()
                    }
                    
                    
                    ForEach(self.entry.notices!, id: \.self) { notice in
                        HStack {
                            Image(systemName: "asterisk.circle.fill")
                                .foregroundColor(.gray)
                            
                            Text(notice)
                            
                            Spacer()
                        }.padding([.leading])
                    }
                }
                
            }
            
            
            Spacer()
            
            
            
		}.padding()
		.navigationBarTitle("Details", displayMode: .inline)
        
	}
    
    
    
}

struct ActivityDetailView_Previews: PreviewProvider {
    static var previews: some View {
        ActivityDetailView(entry: ActivityEntry(uid: "1234", dateTime: "1234", type: nil, lockerName: "Locker Name", locationName: "Location Name", summary: "Summary...", alerts: ["Alert 1", "Alert 2"], notices: ["Notice 1", "Notice 2"]))
    }
}





