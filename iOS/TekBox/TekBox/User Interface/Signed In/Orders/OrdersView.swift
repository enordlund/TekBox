//
//  OrdersView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/25/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI


struct OrderData: Decodable {
    var activeThenInactiveOrders: [OrderCategory]
    
}

struct OrderCategory: Decodable {
    let id: UUID? = UUID()
    
    var status: String
    var orders: [OrderEntry]
}

struct OrderEntry: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var uid: String
    
    var orderNumber: String
    
    var loadedDateTime: String
    
    var unloadedDateTime: String?
    
    var lockerName: String
    var locationName: String
    
    var message: String?
}


struct OrdersView: View {
    
    @EnvironmentObject var contentManager: ContentManager
    //@Binding var shortcutSelection: ContentManager.ShortcutSelection?
    
    
    //@ObservedObject private var orders = Orders()
    
    //@State var filterIsActive: Bool = false
    
    @State private var showLocationServicesRequestView = false
    
    
    var body: some View {
        NavigationView {
            Group {
                if (contentManager.networkData!.orderSectionsDataIsLoaded && (self.contentManager.networkData!.orderSections != nil)) {
                    List {
                                    
                        ForEach(contentManager.networkData!.orderSections!, id: \.id) { section in
                            Section(header: Text(section.status)) {
                                ForEach(section.orders) { entry in
                                    //Text("success")
                                    
                                    NavigationLink(destination: OrderDetailView(orderUID: entry.uid)) {
										OrdersViewRow(loadedDateTime: entry.loadedDateTime, unloadedDateTime: entry.unloadedDateTime, orderNumber: entry.orderNumber, lockerName: entry.lockerName, locationName: entry.locationName, message: entry.message)
                                    }
                                    
                                }
                            }
                        }
                    }.onAppear() {
                        if self.contentManager.shortcutSelection.selection == ContentManager.ShortcutSelection.newOrder {
                            self.newOrder()
                        }
                    }
                } else if self.contentManager.networkData!.orderSectionsError != nil {
                    // error loading account
                    VStack {
                        Text("Error")
                            .font(.headline)
                            .padding([.leading, .trailing])
                        
                        Text(self.contentManager.networkData!.orderSectionsError!)
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
            .navigationBarTitle(Text("Orders"), displayMode: .large)
            .navigationBarItems(trailing:
                HStack(alignment: .bottom) {
                    Button(action: {self.newOrder()}) {
                        Image(systemName: "plus.circle.fill")
                            .foregroundColor(.orange)
                            .font(.title)
                    }.sheet(isPresented: $contentManager.showNewOrderView) {
//                        NewOrderView()
						NewOrderView()
                            .environmentObject(self.contentManager)
							.navigationBarTitle(Text("New Order"), displayMode: .inline)
                            .onDisappear() {
                                self.contentManager.resetShortcuts()
                        }
                    }
                }
            )
                
        }.onAppear() {
            self.refreshView()
        }
    }
    
    func refreshView() {
        if self.contentManager.networkData?.tokenData != nil {
			self.contentManager.networkData!.requestOrders(withReset: true)
        } else {
            print("ERROR ALERT")
        }
    }
    
    func cancelOrder() {
        return
    }
    
    func newOrder() {
        self.contentManager.showNewOrderView = true
        return
    }
}

struct OrdersView_Previews: PreviewProvider {
    static var previews: some View {
        OrdersView()
    }
}

