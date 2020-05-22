//
//  OrderDetailView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/27/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct OrderDetailView: View {
    @EnvironmentObject var contentManager: ContentManager
    var orderUID: String
    
    //@ObservedObject var orderDetails = OrderDetails()
    
    @State private var showLockerView = false
    
    var body: some View {
        VStack {
            
            Group {
                if (self.contentManager.networkData!.orderDetailsDataIsLoaded && (self.contentManager.networkData!.orderDetails != nil)) {
                    VStack {
                        
                        
                        HStack {
                            Text("Order " + self.contentManager.networkData!.orderDetails!.orderNumber)
                            .font(.title)
                            
                            Spacer()
                        }
                        
                        HStack {
                            Text(self.contentManager.networkData!.orderDetails!.lockerName + " (" + self.contentManager.networkData!.orderDetails!.locationName + ")")
                                .font(.headline)
                            Spacer()
                        }
                        
                        if self.contentManager.networkData!.orderDetails!.unloadedDateTime !=  nil {
                            // it is inactive
                            VStack {
                                HStack {
                                    Text("Loaded " + formatDate(fromDateTime: self.contentManager.networkData!.orderDetails!.loadedDateTime) + " at " + formatTime(fromDateTime: self.contentManager.networkData!.orderDetails!.loadedDateTime))
                                    
                                    Spacer()
                                }
                                
                                HStack {
                                    Text("Unloaded " + formatDate(fromDateTime: self.contentManager.networkData!.orderDetails!.unloadedDateTime!) + " at " + formatTime(fromDateTime: self.contentManager.networkData!.orderDetails!.unloadedDateTime!))
                                    
                                    Spacer()
                                }
                                
                                
                            }.padding([.leading, .top, .bottom])
                            
                            
                        } else {
                            // it is loaded.
                            HStack {
                                Text("Loaded " + formatDate(fromDateTime: self.contentManager.networkData!.orderDetails!.loadedDateTime) + " at " + formatTime(fromDateTime: self.contentManager.networkData!.orderDetails!.loadedDateTime))
                                    //.padding()
                                
                                Spacer()
                            }.padding([.leading, .top, .bottom])
                        }
                        
                        if self.contentManager.networkData!.orderDetails!.customerName != nil && self.contentManager.networkData!.orderDetails!.customerEmail != nil {
                            VStack {
                                HStack {
                                    Text("Customer Information")
                                        .font(.headline)
                                    
                                    Spacer()
                                }
                                
                                VStack {
                                    HStack {
                                        Text("Name: " + self.contentManager.networkData!.orderDetails!.customerName!)
                                        
                                        Spacer()
                                    }//.padding([.leading])
                                    
                                    HStack {
                                        Text("Email: " + self.contentManager.networkData!.orderDetails!.customerEmail!)
                                        
                                        Spacer()
                                    }//.padding([.leading])
                                }.padding([.leading, .top, .bottom])
                                
                                
                                
                            }
                            
                            
                        }
                        
                        if self.contentManager.networkData!.orderDetails!.unloadedDateTime ==  nil {
                            // locker is loaded. present option to view the locker view.
                            
                            VStack {
                                Spacer()
                                Button(action: {self.showLocker()}) {
                                    Text("Show Locker")
                                }.buttonStyle(OrangeButtonStyle())
                                .sheet(isPresented: self.$showLockerView) {
                                    NavigationView {
                                        NewOrderLockerView(uid: self.contentManager.networkData!.orderDetails!.lockerUID, lockerTitle: self.contentManager.networkData!.orderDetails!.lockerName)
                                            .environmentObject(self.contentManager)
                                        .navigationBarItems(leading: HStack(alignment: .bottom) {
                                            Button(action: {self.showLockerView = false}) {
                                                Text("Cancel")
                                            }.foregroundColor(.red)
                                        })
                                    }
                                    
                                }
                            }
                            
                            
                        }
                        
                        Spacer()
                        
                    }
                    
                } else if self.contentManager.networkData!.orderDetailsError != nil {
                    // error loading account
                    VStack {
                        Text("Error")
                            .font(.headline)
                            .padding([.leading, .trailing])
                        
                        Text(self.contentManager.networkData!.orderDetailsError!)
                            .padding()
                        
                        Button(action: {self.refreshView()}) {
                            Text("Try Again")
                        }.padding()
                        .buttonStyle(OrangeButtonStyle())
                    }
                } else {
                    
                    LoadingView()
                }
            }.padding()
        }.navigationBarTitle("Details", displayMode: .inline)
            .onAppear() {
                if self.contentManager.networkData?.tokenData != nil {
                    self.contentManager.networkData!.requestOrderDetails(forOrderUID: self.orderUID)
                }
                
        }
        
    }
    
    func refreshView() {
        if self.contentManager.networkData?.tokenData != nil {
            self.contentManager.networkData!.requestOrderDetails(forOrderUID: self.orderUID)
        } else {
            print("ERROR ALERT")
        }
    }
    
    func showLocker() {
        print("showLocker()")
        self.showLockerView = true
    }
}

struct OrderDetailView_Previews: PreviewProvider {
    static var previews: some View {
        OrderDetailView(orderUID: "Blah")
    }
}



struct OrderDetail: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var uid: String
    
    var orderNumber: String
    
    var loadedDateTime: String
    
    var unloadedDateTime: String?
    
    var lockerUID: String
    var lockerName: String
    var locationName: String
    
    var customerName: String?
    var customerEmail: String?
    
    var message: String?
}
