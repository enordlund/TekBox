//
//  NewOrderClusterView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/27/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI







struct LockersView: View {
    
    @EnvironmentObject var contentManager: ContentManager
    
    var uid: String
    
    var name: String
	
	@State var edit: Bool = false
	
	@State private var lockers: [Locker]? = nil
	@State private var lockersError: String? = nil
    
    var body: some View {
		
		Group {
			if self.lockers != nil {
				// lockers are loaded to view, so diplay them
				if self.lockers?.count == 0 {
					// no lockers to show
					VStack {
						Text("No lockers were found for this location.")
							.padding()
						
						Button(action: {self.refreshView()}) {
							Text("Try Again")
						}.padding()
						.buttonStyle(OrangeButtonStyle())
					}.navigationBarTitle(Text(self.name), displayMode: .large)
				} else if self.lockers?.count == 1 {
					// should go straight to the locker view
					NewOrderLockerView(uid: lockers!.first!.uid, lockerTitle: lockers!.first!.name)
						.navigationBarTitle(Text(self.lockers!.first!.name), displayMode: .inline)
				} else {
					// multiple lockers
					List {
									
						ForEach(self.lockers!, id: \.id) { locker in
							
							NavigationLink(destination: NewOrderLockerView(uid: locker.uid, lockerTitle: locker.name)
								.navigationBarTitle(Text(locker.name), displayMode: .inline)) {
								NewOrderLockerRow(name: locker.name, isLoaded: (locker.isLoaded == "1"), summary: locker.summary)
							}
							
						}
					}.navigationBarTitle(Text(self.name), displayMode: .large)
				}
			} else if self.lockersError != nil {
				// error loading lockers
				VStack {
                    Text("Error")
                        .font(.headline)
                        .padding([.leading, .trailing])
                    
                    Text(self.lockersError!)
                        .padding()
                    
                    Button(action: {self.refreshView()}) {
                        Text("Try Again")
                    }.padding()
                    .buttonStyle(OrangeButtonStyle())
                }.navigationBarTitle(Text(self.name), displayMode: .large)
			} else {
				// loading lockers
				LoadingView()
				.navigationBarTitle(Text(self.name), displayMode: .large)
//				.onAppear() {
//					self.refreshView()
//				}
			}
		}.onAppear() {
			self.refreshView()
		}
        
    }
    
    func refreshView() {
        if self.contentManager.networkData?.tokenData != nil {
			self.contentManager.networkData!.requestLockers(fromLocationUID: self.uid, completion: { data, error in
				self.lockers = data
				self.lockersError = error
			})
        } else {
            print("ERROR ALERT")
        }
    }
    
	func toggleEdit() {
		self.edit.toggle()
	}
    
    
}

struct NewOrderLocationLockersView_Previews: PreviewProvider {
    static var previews: some View {
        LockersView(uid: "2468013579", name: "Location Name")
    }
}


