//
//  BoxesNewBoxWiFiView.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/18/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

import CoreBluetooth

struct BoxesNewBoxWiFiView: View {
	@EnvironmentObject var contentManager: ContentManager
    
    @Environment(\.presentationMode) var presentationMode
    
    
    @ObservedObject var wapScan = WapScanRequest()
    
    @State private var animateWifiIcon = false
    
    
    var body: some View {
        Group {
            if ((self.wapScan.dataIsLoaded == true) && (self.wapScan.waps != nil)) {
                // wap scan is done, so list them in the UI.
                List {
                                
                    ForEach(self.wapScan.waps!, id: \.id) { point in
                        
                        NavigationLink(destination: WiFiCredentialsView(ssid: point.ssid, encryption: point.encryption)) {
                            HStack {
                                Text(point.ssid)
                                //Spacer()
                                if point.encryption != "OPEN" {
                                    Spacer()
                                    Image(systemName: "lock.fill")
                                        .padding([.leading, .trailing])
                                }
                                
                            }
                        }
                        
                    }
                }.navigationBarItems(trailing:
                    HStack(alignment: .bottom) {
                        Button(action: {
                            self.tryAgain()
                        }) {
                            Image(systemName: "arrow.clockwise")
                                .foregroundColor(.orange)
                                //.font(.title)
                        }
                    }
                )
                
            } else if self.wapScan.scanDidFail {
                Text("Scan failed.")
                    .padding()
                Button(action: {self.tryAgain()}) {
                    Text("Try Again")
                }.buttonStyle(OrangeButtonStyle())
            } else {
                // data hasn't loaded, or no clusters were found.
                VStack {
                    Image(systemName: "wifi")
                        .font(.title)
                        .foregroundColor(.orange)
                        .rotation3DEffect(self.animateWifiIcon ? .degrees(0) : .degrees(1440), axis: (x: 0, y: 1.0, z: 0))
                        .animation(.easeInOut(duration: 10.0))
                        .opacity(self.animateWifiIcon ? 1 : 0)
                        .animation(.easeIn(duration: 2.0))
                    Text("Scanning for Wi-Fi networks...")
                }.onAppear() {
                    print("else appeared")
                    self.printState()
                    self.animateWifiImage()
                    self.wapScan.requestScan()
                    print("scan started")
                    self.printState()
                }.onDisappear() {
                    self.animateWifiIcon = false
                }
                
            }
            
        }
        .navigationBarTitle(Text("Select A Network"), displayMode: .inline)
    }
    
    func animateWifiImage() {
        self.animateWifiIcon = true
    }
    
    func tryAgain() {
        self.animateWifiIcon = false
        self.wapScan.requestScan()
    }
    
    func dismissViewDestructive() {
        // clear return state
        //newOrderViewReturnState = nil
        
        self.presentationMode.wrappedValue.dismiss()
        return
    }
    
    func printState() {
        print("wapScan.dataIsLoaded = ", self.wapScan.dataIsLoaded)
        print("wapScan.waps = ", self.wapScan.waps ?? "(nil)")
        print("wapScan.isConnected = ", self.wapScan.isConnected)
        print("wapScan.isScanning = ", self.wapScan.isScanning)
        print("wapScan.scanDidFail = ", self.wapScan.scanDidFail)
    }
}

struct BoxesNewBoxWiFiView_Previews: PreviewProvider {
    static var previews: some View {
        BoxesNewBoxWiFiView()
    }
}
