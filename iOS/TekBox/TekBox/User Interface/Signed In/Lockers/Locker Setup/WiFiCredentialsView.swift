//
//  WiFiCredentialsView.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/19/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI


// extension from rraphael: https://stackoverflow.com/questions/56491386/how-to-hide-keyboard-when-using-swiftui
extension UIApplication {
    func endEditing() {
        sendAction(#selector(UIResponder.resignFirstResponder), to: nil, from: nil, for: nil)
    }
}


// inspired by: https://stackoverflow.com/questions/56491386/how-to-hide-keyboard-when-using-swiftui
struct Background<Content: View>: View {
    private var content: Content
    
    var width: CGFloat
    var height: CGFloat
    

    init(width: CGFloat, height: CGFloat, @ViewBuilder content: @escaping () -> Content) {
        self.width = width
        self.height = height
        self.content = content()
    }

    var body: some View {
        Color(.systemBackground)
        .frame(width: width, height: height)
        .overlay(content)
    }
}




struct WapCredentials: Encodable {
    var ssid: String
    var identity: String?
    var password: String?
    var encryption: String
}




struct WiFiCredentialsView: View {
	@EnvironmentObject var contentManager: ContentManager
    
    @ObservedObject var credentialsAttempt = WapCredentialsAttempt()
	
//	@ObservedObject var certificateAttempt = RootCertificateAttempt()
    
    @State private var identityTextField = ""
    
    @State private var passwordTextField = ""
    
    @State private var secureText = true
    
    @State private var connectingWiFi = false
    
    @State private var connectingServer = false
	
	@State private var showCertificateView: Int? = 0
    
    var ssid: String
    
    var encryption: String
    
    var body: some View {
        
        Group {
            if self.credentialsAttempt.dataIsLoaded != true {
                // credentials have not yet been attempted.
                GeometryReader { geometry in
                    Background (width: geometry.size.width, height: geometry.size.height) {
                        VStack {
                            Text(self.ssid)
                                .font(.title)
                                .padding([.leading, .trailing, .top])
                            
                            
                            HStack (alignment: .center) {
                                if self.encryption == "OPEN" {
                                    Image(systemName: "lock.open.fill")
                                    Text("Unsecured Network")
                                } else {
                                    Image(systemName: "lock.fill")
                                    Text(self.encryption)
                                }
                                
                            }.padding([.leading, .trailing])
                            
                            if self.credentialsAttempt.dataIsLoaded == nil {
                                // credentials have not yet been attempted.
                                Group {
                                    if (self.encryption == "WEP") || (self.encryption == "WPA_PSK") || (self.encryption == "WPA2_PSK") || (self.encryption == "WPA_WPA2_PSK") || (self.encryption == "WPA2_ENTERPRISE"){
                                        Text("Enter network credentials to continue.")
                                            .padding()
                                        if (self.encryption == "WPA2_ENTERPRISE") {
                                            TextField("Identity", text: self.$identityTextField)
                                                .textFieldStyle(RoundedBorderTextFieldStyle())
                                                .padding()
                                        }
                                        
                                        if self.secureText {
                                            HStack {
                                                SecureField("Password", text: self.$passwordTextField)
                                                    .textFieldStyle(RoundedBorderTextFieldStyle())
                                                
                                                Button(action: {self.toggleSecurity()}) {
                                                    Image(systemName: "eye")
                                                }
                                                
                                            }.padding([.leading, .trailing])
                                        } else {
                                            HStack {
                                                TextField("Password", text: self.$passwordTextField)
                                                    .textFieldStyle(RoundedBorderTextFieldStyle())
                                                    
                                                Button(action: {self.toggleSecurity()}) {
                                                    Image(systemName: "eye.slash")
                                                }
                                            }.padding([.leading, .trailing])
                                        }
                                        Button(action: {self.savePassword()}) {
                                            Text("Save")
                                        }.padding()
                                        .buttonStyle(OrangeButtonStyle())
                                    } else if self.encryption == "OPEN" {
                                        VStack {
                                            Text("Press \"Save\" to confirm your selection.")
                                                .padding()
                                            
                                            Button(action: {self.savePassword()}) {
                                                Text("Save")
                                            }.padding()
                                            .buttonStyle(OrangeButtonStyle())
                                        }
                                        
                                    }
                                }
                            } else if self.credentialsAttempt.dataIsLoaded == false {
                                // credentials are being attempted.
                                VStack {
                                    Spacer()
                                    VStack {
                                        
                                        Image(systemName: "slowmo")
                                            .foregroundColor(Color(.systemGray))
                                            .font(.title)
                                            .rotationEffect(self.connectingWiFi ? .degrees(0) : .degrees(-4320))
                                            .animation(.linear(duration: 30.0))
                                            .opacity(self.connectingWiFi ? 1 : 0)
                                            .animation(.easeIn(duration: 0.2))
                                            .onAppear() {
                                                self.connectingWiFi = true
                                            }
                                        Text("Connecting...")
                                            //.padding()
                                        
                                        
                                        
                                    }
                                    Spacer()
                                    Button(action: {self.tryAgain()}) {
                                        Text("Cancel")
                                            .foregroundColor(.red)
                                    }.padding()
                                }
                                
                            } else {
                                Text("Connection error.")
                            }
                            
                            
                            
                            
                            Spacer()
                        }
                    }.onTapGesture {
                        UIApplication.shared.endEditing()
                    }
                }
            } else {
                // data is loaded. handle cases of success and failure.
                Group {
                    if self.credentialsAttempt.wasSuccessful {
                        // the locker is connected to wifi.
                        // attempting certificate
						VStack {
							Text("Wi-Fi connection successful!")
								.padding()
							Spacer()
							
							NavigationLink(destination: RootCertificateView().environmentObject(self.contentManager), tag: 1, selection: $showCertificateView) {
								EmptyView()
							}
							
							Button(action: {self.showCertificateView = 1}) {
								Text("Continue")
							}.buttonStyle(OrangeButtonStyle())
							.padding()
						}
                    } else {
                        // locker failed to connect
                        VStack {
                            Text("Wi-Fi connection failed.")
                                .padding()
                            Button(action: {self.tryAgain()}) {
                                Text("Try Again")
                            }.buttonStyle(OrangeButtonStyle())
							.padding()
                        }
                        
                        
                    }
                }.onAppear() {
                    self.connectingWiFi = false
                    self.connectingServer = false
				}.padding()
                
            }
        }.navigationBarTitle(Text("Network Info"), displayMode: .inline)
            
        
    }
    
    func toggleSecurity() {
        UIApplication.shared.endEditing()
        self.secureText.toggle()
    }
    
    func restoreStateNonDestructive() {
        self.secureText = true
        
        self.connectingWiFi = false
        
        self.connectingServer = false
    }
    
    func tryAgain() {
        // restore state for user to enter credentials again.
        self.restoreStateNonDestructive()
        self.credentialsAttempt.restoreVariables()
    }
    
    func savePassword() {
        // dismiss keyboard to prevent crash
        UIApplication.shared.endEditing()
        
        
        // send ssid and passwordTextField to locker
        
        // consider which information to send, depending on security type.
        
        let credentials = WapCredentials(ssid: self.ssid, identity: (self.identityTextField.count > 0) ? self.identityTextField : nil, password: (self.passwordTextField.count > 0) ? self.passwordTextField : nil, encryption: self.encryption)
        
        credentialsAttempt.attempt(credentials: credentials)
        // update state
        
    }
    
    
	
//
//	func sendCertificate() {
//		self.certificateAttempt.objectWillChange.send()
//		self.certificateAttempt.sendCertificate()
//	}
//
//	func tryCertificateAgain() {
////        self.connectingServer = false
//		self.certificateAttempt.objectWillChange.send()
//		self.certificateAttempt.restoreVariables()
//	}
//
//	func requestSetupSessionData() {
//		print("lookForSession()")
//		print(self.credentialsAttempt.dataIsLoaded as Any)
////		self.credentialsAttempt.resetAttempt()
//		if self.contentManager.networkData != nil {
//			if self.certificateAttempt.sessionUID != nil {
//				self.contentManager.networkData!.requestSetupSession(withSessionUID: self.certificateAttempt.sessionUID!)
//				print(self.credentialsAttempt.dataIsLoaded as Any)
//			}
//		}
//
//	}
    
    
	
	
}

struct WiFiCredentialsView_Previews: PreviewProvider {
    static var previews: some View {
        WiFiCredentialsView(ssid: "SSID", encryption: "OPEN")
    }
}
