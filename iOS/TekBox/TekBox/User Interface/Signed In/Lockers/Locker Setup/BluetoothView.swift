//
//  BluetoothView.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/16/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

import CoreBluetooth


struct BluetoothPeripheral: Identifiable {
    var id = UUID()
    
    var peripheral: CBPeripheral
    var rssi: Float?
}


struct BluetoothView: View, MessageHandlerDelegate {
	@EnvironmentObject var contentManager: ContentManager
    
    @Environment(\.presentationMode) var presentationMode
    
    @State private var discoveredPeripherals = [BluetoothPeripheral]()
    
    @State private var peripheralConnected: Int? = 0
    
    @State private var connecting = false
    
    func statusDidRefresh() {
        print("BluetoothView() statusDidRefresh")
    }
    
    func deviceNameDidChange(_ name: String) {
        print("BluetoothView() deviceNameDidChange")
    }
    
    func messageWasReceived(_ message: String) {
        print("BluetoothView() messageWasReceived")
    }
    
    func messageWasSent(_ message: String) {
        print("BluetoothView() messageWasSent")
    }
    
    func peripheralDidDisconnect(_ poweredOn: Bool) {
        print("BluetoothView() peripheralDidDisconnect")
    }
    
    func connectionDidChangeState(_ poweredOn: Bool) {
        print("BluetoothView() connectionDidChangeState. poweredOn = ", poweredOn)
        
        if poweredOn {
            self.scanBluetooth()
        }
    }
    
    func peripheralDiscovered(_ isDuplicate: Bool, _ peripherals: [(peripheral: CBPeripheral, RSSI: Float)]) {
        
        print("BluetoothView() peripheralDiscovered")
		
		var tempDiscoveredPeripherals = [BluetoothPeripheral]()
        
        var i = 0
        while (i < peripherals.count) {
            let indexPath = IndexPath(row: i, section: 0)
            
            let peripheral = peripherals[(indexPath as IndexPath).row].peripheral
            
			tempDiscoveredPeripherals.append(BluetoothPeripheral(peripheral: peripheral))
            
            
            
            
            i = i + 1
        }
		
		discoveredPeripherals = tempDiscoveredPeripherals
    }
    
    func peripheralConnectionSuccessful() {
        print("BluetoothView() peripheralConnectionSuccessful")
        
    }
    
    func peripheralConnectionFailed() {
        print("BluetoothView() peripheralConnectionFailed")
    }
    
    func peripheralConnectionReady(_ peripheral: CBPeripheral) {
        print("BluetoothView() peripheralConnectionReady")
        self.peripheralConnected = 1
    }
    
    var body: some View {
        NavigationView {
            Group {
                List (discoveredPeripherals) { discoveredPeripheral in
                    
                    Button(action: {self.connect(peripheral: discoveredPeripheral.peripheral)}) {
						NavigationLink(destination: BoxesNewBoxWiFiView().environmentObject(self.contentManager), tag: 1, selection: self.$peripheralConnected) {
                            //self.clusterTransport.set(uuid: cluster.uuid)
                            //NewOrderClusterRow(name: cluster.name, openBoxes: cluster.openBoxCount)
                            
                            //EmptyView()
                            
                            
                            HStack {
                                Text(discoveredPeripheral.peripheral.name!)
                                
                                Spacer()
                                
                                Image(systemName: "slowmo")
                                    .foregroundColor(Color(.systemGray))
                                    .rotationEffect(self.connecting ? .degrees(0) : .degrees(-4320))
                                    .animation(.linear(duration: 16.0))
                                    .opacity(self.connecting ? 1 : 0)
                                    .animation(.easeIn(duration: 0.2))
                                
                                
                            }
                        }
                    }
                    
                }.navigationBarItems(leading:
                HStack(alignment: .bottom) {
                    Button(action: {self.dismissViewDestructive()}) {
                        Text("Cancel")
                    }.foregroundColor(.red)
                }, trailing:
                    HStack(alignment: .bottom) {
                        Button(action: {self.scanBluetooth()}) {
                            Image(systemName: "arrow.clockwise")
                                .foregroundColor(.orange)
                                //.font(.title)
                        }
                    }
                )
                
            }.navigationBarTitle("Select A Locker", displayMode: .inline)
            .onAppear() {
                if messageHandler != nil {
                    messageHandler.setDelegate(delegate: self)
                } else {
                    messageHandler = MessageHandler(delegate: self)
                }
//                if btSerial != nil {
//					if btSerial.isPoweredOn {
//						if self.discoveredPeripherals.count < 1 {
//							self.scanBluetooth()
//						}
//						
//					}
//				}
            }.onDisappear() {
                self.connecting = false
            }
        }.accentColor(.orange)
        .onDisappear() {
            btSerial.disconnect()
			self.contentManager.networkData?.resetSetupSession()
        }
        
        
    }
    
    
    func scanBluetooth() {
        self.discoveredPeripherals = [BluetoothPeripheral]()
        //messageHandler = MessageHandler(delegate: self)
        print("BluetoothView() scanning bluetooth")
        btSerial.startScan()
        Timer.scheduledTimer(withTimeInterval: 10, repeats: false, block: {_ in
            messageHandler.scanTimeOut()
        })
    }
    
    func connect(peripheral: CBPeripheral) {
        print("BluetoothView() connect()")
        
        self.connecting = true
        
        btSerial.connectToPeripheral(peripheral)
    }
    
    
    func dismissViewDestructive() {
        // clear return state
        //newOrderViewReturnState = nil
        
        self.presentationMode.wrappedValue.dismiss()
        return
    }
}

struct BluetoothView_Previews: PreviewProvider {
    static var previews: some View {
        BluetoothView()
    }
}


/*
class BluetoothManager: ObservableObject {
    @Published var peripherals
}
*/
