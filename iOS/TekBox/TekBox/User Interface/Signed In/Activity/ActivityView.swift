//
//  ActivityView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/21/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI
//import Combine





struct ActivityData: Decodable {
    var activityDays: [ActivityDay]
    
    /*
    init(json: [String: Any]) {
        activityDays = json["days"] as? [ActivityDay] ?? [ActivityDay]()
    }*/
}

struct ActivityEntry: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var uid: String
    
    var dateTime: String
    
    var type: String?
    var lockerName: String?
    var locationName: String
    
    var summary: String?
    
    var alerts: [String]?
    
    var notices: [String]?
    
    /*
    init(json: [String: Any]) {
        id = UUID()
        uuid = json["uuid"] as? String ?? ""
        dateTime = json["dateTime"] as? String ?? ""
        type = json["type"] as? String ?? nil
        boxName = json["boxName"] as? String ?? ""
        clusterName = json["clusterName"] as? String ?? ""
        summary = json["summary"] as? String ?? ""
        alerts = json["alerts"] as? [String] ?? nil
        notices = json["notices"] as? [String] ?? nil
    }*/
}


let today = Date()

let earlier = Date(timeInterval: -10000, since: today)

let yesterday = Date(timeInterval: -86400, since: today)

let slightlyEarlier = Date(timeInterval: -199960, since: today)

let wayEarlier = Date(timeInterval: -200000, since: today)

let wayWayEarlier = Date(timeInterval: -250000, since: today)

let evenEarlier = Date(timeInterval: -250150, since: today)

let evenWayEarlier = Date(timeInterval: -250200, since: today)

let lastYear = Date(timeInterval: -31557000, since: today)


/*
 Activity entries are sorted in reverse chronological order by the query on the server, and parsed into this array for local use.
*/

        




struct ActivityDay: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var dateTime: String
    
    var entries: [ActivityEntry]
    
}




struct ActivityView: View {
    @EnvironmentObject var contentManager: ContentManager
    
    //let activityDays = getActivityDays()//?.activityDays
    //@ObservedObject var activityDays = ActivityDays()
    
    @State var filterIsActive: Bool = false
    
    var body: some View {
        
        
        NavigationView {
            
            Group {
                if (contentManager.networkData!.activityByDayDataIsLoaded && (contentManager.networkData!.activityByDay != nil)) {
                    List {
                                    
                        ForEach(contentManager.networkData!.activityByDay!, id: \.dateTime) { day in
                            Section(header: Text(formatDate(fromDateTime: day.dateTime))) {
                                ForEach(day.entries) { entry in
                                    //Text("success")
                                    if (entry.alerts != nil) {
                                        
                                        if (entry.notices != nil) {
                                            NavigationLink(destination: ActivityDetailView(entry: entry)) {
												ActivityViewRow(date: getDate(fromDateTime: entry.dateTime), locationName: entry.locationName, boxName: entry.lockerName, message: entry.summary, isNotice: true, isAlert: true)
                                            }
                                        } else {
                                            NavigationLink(destination: ActivityDetailView(entry: entry)) {
                                                ActivityViewRow(date: getDate(fromDateTime: entry.dateTime), locationName: entry.locationName, boxName: entry.lockerName, message: entry.summary, isNotice: false, isAlert: true)
                                            }
                                        }
                                        
                                         
                                        //Text("success")
                                    } else if (entry.notices != nil) {
                                        NavigationLink(destination: ActivityDetailView(entry: entry)) {
                                            ActivityViewRow(date: getDate(fromDateTime: entry.dateTime), locationName: entry.locationName, boxName: entry.lockerName, message: entry.summary, isNotice: true, isAlert: false)
                                        }
                                    } else {
                                        ActivityViewRow(date: getDate(fromDateTime: entry.dateTime), locationName: entry.locationName, boxName: entry.lockerName, message: entry.summary, isNotice: false, isAlert: false)
                                    }
                                    
                                }
                            }
                        }
                    }
                } else if self.contentManager.networkData!.activityByDayError != nil {
                    // error loading account
                    VStack {
                        Text("Error")
                            .font(.headline)
                            .padding([.leading, .trailing])
                        
                        Text(self.contentManager.networkData!.activityByDayError!)
                            .padding()
                        
                        Button(action: {self.refreshView()}) {
                            Text("Try Again")
                        }.padding()
                        .buttonStyle(OrangeButtonStyle())
                    }
                } else {
                    LoadingView()
                }
            }
            
            
           
            
            
            .navigationBarTitle(Text("Activity"), displayMode: .large)
            .navigationBarItems(trailing:
                Button(action: {self.toggleFilter()}) {
                    if (self.filterIsActive == true) {
                        HStack {
                            Text("Active")
                            
                            Image(systemName: "line.horizontal.3.decrease.circle.fill")// change to *.fill if a box/cluster is selected.
                                .foregroundColor(.orange)
                                .font(.title)
                        }
                        
                    } else {
                        HStack {
                            // adding a Spacer for this one to make up for difference in size
                            Spacer()// The button is left justified by default, and this is the simplest way to address the issue.
                            
                            Text("Filter")
                            
                            Image(systemName: "line.horizontal.3.decrease.circle")
                                .foregroundColor(.orange)
                                .font(.title)
                        }
                    }
                }
            )
            //.navigationBarHidden(false)
            //.navigationBarItems()
                
        }.onAppear() {
            self.refreshView()
        }
    }
    
    func refreshView() {
        if self.contentManager.networkData?.tokenData != nil {
            self.contentManager.networkData!.requestActivityByDay(withReset: true)
        } else {
            print("ERROR ALERT")
        }
    }
    
    func toggleFilter() {
        if (filterIsActive) {
            filterIsActive = false
        } else {
            filterIsActive = true
        }
    }
}

struct ActivityView_Previews: PreviewProvider {
    static var previews: some View {
        ActivityView()
    }
}

