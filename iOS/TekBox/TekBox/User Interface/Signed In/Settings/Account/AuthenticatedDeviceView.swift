//
//  AuthenticatedDeviceView.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/24/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI
import UIKit

struct AuthenticatedDeviceView: View {
    @EnvironmentObject var contentManager: ContentManager
    
    @Binding var isPresented: Bool
    
    @Binding var showSignOutAlert: Bool
    
    var device: MobileDevice
    
    @State private var showDeauthenticationAlert = false
    
    var body: some View {
        ScrollView {
            if self.contentManager.networkData!.deauthenticationSuccess == true {
                VStack {
                    Text("Device Removed.")
                    .font(.title)
                    .padding()
                    Text("Existing offline keys are not deleted.")
                    .padding()
                    Button(action: {
                        self.isPresented = false
                    }) {
                        Text("Return to Account")
                    }.buttonStyle(OrangeButtonStyle())
                    .padding()
                }.onDisappear() {
                    self.contentManager.networkData!.deauthenticationSuccess = nil
                }
            } else {
                VStack {
                    DeviceSymbolView(model: self.device.model)
                        .font(.title)
                        .padding()
                    HStack {
                        Text("Last Online")
                        .font(.headline)
                        Spacer()
                    }.padding([.top, .bottom])
                    HStack {
                        Text(formatDate(fromDateTime: self.device.lastAccessed) + " at " + formatTime(fromDateTime: self.device.lastAccessed))
                        Spacer()
                    }.padding([.leading])
                    HStack {
                        Text("Authenticated Since")
                        .font(.headline)
                        Spacer()
                    }.padding([.top, .bottom])
                    HStack {
                        Text(formatDate(fromDateTime: self.device.firstAuthenticated) + " at " + formatTime(fromDateTime: self.device.firstAuthenticated))
                        Spacer()
                    }.padding([.leading, .bottom])
                    
                    Button(action: {
                        self.confirmDeviceDeauthentication()
                    }) {
                        Text("Remove from Account")
                            .foregroundColor(Color.red)
                            .font(.headline)
                    }.padding()
                }.padding()
            }
        }.navigationBarTitle(Text(self.device.model), displayMode: .inline)
        .alert(isPresented: self.$showDeauthenticationAlert, content: {
            Alert(title: Text("Remove \(self.device.model)"),
                  message: Text("Removing a device will sign it out of your account. The device will keep any offline keys that it already has."),
                  primaryButton: .default(Text("Cancel").fontWeight(.bold), action: {self.showDeauthenticationAlert = false}),
                  secondaryButton: .destructive(Text("Remove"),
                  action: {self.deauthenticateDevice()}))
        })
        
    }
    
    func confirmDeviceDeauthentication() {
        print("confirmDeviceDeauthentication()")
        self.showDeauthenticationAlert = true
    }
    
    func deauthenticateDevice() {
        print("deauthenticateDevice()")
        if self.device.vendorID == String(describing: UIDevice.current.identifierForVendor!) {
            // tell server to delete the token, and then sign out (preserve bluetooth keys)
            if self.contentManager.networkData?.tokenData != nil {
                self.contentManager.networkData!.requestSignOut()
                self.isPresented = false
            } else {
                print("ERROR ALERT")
            }
        } else {
            self.contentManager.networkData!.requestDeviceDeauthentication(forDevice: self.device)
            self.showDeauthenticationAlert = false
        }
        
    }
}

/*

@propertyWrapper public struct Binding<Value> {

    /// Creates a binding with an immutable `value`.
    public static func constant(_ value: Value) -> Binding<Value>
}

struct AuthenticatedDeviceView_Previews: PreviewProvider {
    //@State var isPresented = true
    static var previews: some View {
        AuthenticatedDeviceView(isPresented: .constant(true), device: MobileDevice(model: "This iPhone", vendorID: "1234", tokenUID: "2468", lastAccessed: "2020-02-24 15:57:37", firstAuthenticated: "2020-02-14 12:24:37"))
    }
}
*/
