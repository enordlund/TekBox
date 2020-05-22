//
//  NewOrderLockerView.swift
//  TekBox
//
//  Created by Erik Nordlund on 12/7/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct NewOrderLockerView: View {
    @EnvironmentObject var contentManager: ContentManager
    
    var uid: String
    
    var lockerTitle: String
	
	@State private var locker: Locker? = nil
	@State private var lockerError: String? = nil
	
	@State private var showUpdateOrderForm: Bool = false
    
    @State private var orderNumber: String = ""
    @State private var customerName: String = ""
    @State private var customerEmail: String = ""
    
    
    var body: some View {
		
		Group {
			if self.locker != nil {
				// data is loaded
				
				// check for disarm period
				if self.locker!.disarmPeriod != nil {
					// locker is disarmed
					VStack {
						
						if self.locker!.disarmPeriod! == "1" {
							Text("Disarmed for " + self.locker!.disarmPeriod! + " minute.")
							.padding()
						} else {
							Text("Disarmed for " + self.locker!.disarmPeriod! + " minutes.")
							.padding()
						}
						
						
						Button(action: {self.refreshView()}) {
							Text("Continue")
						}.padding()
						.buttonStyle(OrangeButtonStyle())
					}
				} else if self.locker!.isLoaded == "0" {
					// locker is not loaded, and not disarmed
					VStack {
						Text("Unlock and Load")
							.font(.title)
							.padding()
						
						if (self.locker!.prepMessage != nil) {
							Text("\(self.locker!.prepMessage!)")
						} else {
							Text("\(self.locker!.name) is ready for order prep. Once the order is inside, close the door and press the button again to lock the locker.")
						}
						
						Spacer()
						
						Button(action: {self.disarmLockerForPrep()}) {
							Text("Continue")
						}.padding()
						.buttonStyle(OrangeButtonStyle())
					}
				} else if self.locker!.isLoaded == "1" {
					// locker is loaded
					// check for order
					// locker is loaded, so check if it has order info
					if self.locker!.orderUID == nil || self.showUpdateOrderForm {
						// loaded without order info
						ScrollView {
							Spacer()
							
							Text("Send An Invitation")
								.font(.title)
								.padding()
							
							// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ pop text fields above keyboard on tap
							VStack(alignment: .leading) {

								// provide fields to enter order info
								Text("Order Number:")
								TextField("12345678", text: self.$orderNumber)
									.textFieldStyle(RoundedBorderTextFieldStyle())
									.padding([.bottom])
								Text("Customer Name:")
								TextField("Benny Beaver", text: self.$customerName)
									.textFieldStyle(RoundedBorderTextFieldStyle())
									.padding([.bottom])
								Text("Customer Email:")
								TextField("beaverb@oregonstate.edu", text: self.$customerEmail)
									.textFieldStyle(RoundedBorderTextFieldStyle())
									.padding([.bottom])
							}.padding()
							
							
							Spacer()
							
							
							Button(action: {self.sendOrderInfo()}) {
								Text("Submit Order")
							}.padding()
							.buttonStyle(OrangeButtonStyle())
							
							Button(action: {self.disarmLocker()}) {
								Text("Disarm")
									.foregroundColor(.red)
									.font(.headline)
							}.padding()
						}
						
						
					} else {
						// loaded with order info
						VStack {
                            Text("Loaded with order")
                                .font(.title)
                                .padding()
                            
                            if (self.locker!.prepMessage != nil) {
                                Text("\(self.locker!.prepMessage!)")
                            } else {
                                Text("\(self.locker!.name) is loaded with an order. You may update the info, or disarm the locker to unload it and cancel the order.")
                            }
                            
                            Spacer()
                            
                            Button(action: {self.showUpdateOrderForm = true}) {
                                Text("Update Order Info")
                            }.padding()
                            .buttonStyle(OrangeButtonStyle())
                            
                            Button(action: {self.disarmLocker()}) {
                                Text("Disarm")
                                    .foregroundColor(.red)
                                    .font(.headline)
                            }.padding()
                        }
					}
				}
				
			} else if self.lockerError != nil {
			   // error loading locker
			   VStack {
				   Text("Error")
					   .font(.headline)
					   .padding([.leading, .trailing])
				   
				   Text(self.lockerError!)
					   .padding()
				   
				   Button(action: {self.refreshView()}) {
					   Text("Try Again")
				   }.padding()
				   .buttonStyle(OrangeButtonStyle())
			   }
		   } else {
			   // data isn't loaded
			   LoadingView()
//					.onAppear() {
//						self.refreshView()
//				}
		   }
		}.navigationBarTitle(Text(self.lockerTitle), displayMode: .inline)
		.onAppear() {
			self.refreshView()
		}
		
    }
	
	
    
    func refreshView() {
        if self.contentManager.networkData?.tokenData != nil {
			self.contentManager.networkData!.requestLocker(withUID: self.uid) { data, error in
				self.locker = data
				self.lockerError = error
				self.showUpdateOrderForm = false
			}
        } else {
            print("ERROR ALERT")
        }
    }
    
    func sendOrderInfo() {
        if ((self.orderNumber.count > 0) && (self.customerName.count > 0) && (self.customerEmail.count > 0)) {
            // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ check for proper format (especially email account)
            // all fields contain stuff (assuming it's fine for now)
            if self.contentManager.networkData?.tokenData != nil {
				self.contentManager.networkData!.sendOrderInfo(forLockerUID: self.uid, orderNumber: self.orderNumber, customerName: self.customerName, customerEmail: self.customerEmail) { data, error in
					self.locker = data
					self.lockerError = error
					self.showUpdateOrderForm = false
				}
            } else {
                print("ERROR ALERT")
            }
            
        }
    }
    
    func disarmLocker() {
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ present alert confirming destructive action before sending request
        if self.contentManager.networkData?.tokenData != nil {
			self.contentManager.networkData!.disarmLocker(withUID: self.uid, forPrep: false) { data, error in
				self.locker = data
				self.lockerError = error
				self.showUpdateOrderForm = false
			}
        } else {
            print("ERROR ALERT")
        }
        
    }
    
    func disarmLockerForPrep() {
        if self.contentManager.networkData?.tokenData != nil {
            self.contentManager.networkData!.disarmLocker(withUID: self.uid, forPrep: true) { data, error in
				self.locker = data
				self.lockerError = error
				self.showUpdateOrderForm = false
			}
        } else {
            print("ERROR ALERT")
        }
        
    }
}

struct NewOrderLockerView_Previews: PreviewProvider {
    static var previews: some View {
        NewOrderLockerView(uid: "1234", lockerTitle: "Locker Name")
    }
}

