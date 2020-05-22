//
//  KeysView.swift
//  TekBox
//
//  Created by Erik Nordlund on 3/11/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct KeysView: View {
	
	@State var keys: [OfflineKey]? = nil
	
    var body: some View {
		Group {
			if self.keys != nil {
				if self.keys!.count > 0 {
					List {
						ForEach (self.keys!) { key in
							Text("\(key.lockerUID)")
						}
					}
				} else {
					Text("No Offline Keys Found")
				}
				
			} else {
				Text("No Offline Keys")
					.onAppear() {
						self.getKeys()
				}
			}
		}.navigationBarTitle("Keys")
		
    }
	
	func getKeys() {
		
		self.keys = getOfflineKeys()
		
	}
}

struct KeysView_Previews: PreviewProvider {
    static var previews: some View {
        KeysView()
    }
}
