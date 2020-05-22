//
//  MessagesView.swift
//  TekBox
//
//  Created by Erik Nordlund on 1/16/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

import CoreBluetooth

enum MessageSender {
    case app
    case esp
}

enum MessageDataType {
    case string
}

enum MessageStatus {
    case sent
    case delivered
    case failed
    case initialized
}

struct BluetoothMessage: Identifiable {
    var id = UUID()
    
    var sender: MessageSender
    var dataType: MessageDataType = .string
    
    var status: MessageStatus = .initialized
    
    var body = String()
}





struct MessagesView: View, MessageHandlerDelegate {
    
    @State private var textFieldMessage: String = ""
    
    
    @State private var messages = [BluetoothMessage]()
    
    
    
    func statusDidRefresh() {
        print("statusDidRefresh")
    }
    
    func deviceNameDidChange(_ name: String) {
        print("deviceNameDidChange")
    }
    
    func messageWasReceived(_ message: String) {
        print("messageWasReceived")
        
        let reply = BluetoothMessage(sender: .esp, body: message)
        self.messages.append(reply)
    }
    
    func messageWasSent(_ message: String) {
        print("messageWasSent: ", message)
        
        let newMessage = BluetoothMessage(sender: .app, body: message)
        self.messages.append(newMessage)
    }
    
    func peripheralDidDisconnect(_ poweredOn: Bool) {
        print("peripheralDidDisconnect")
    }
    
    func connectionDidChangeState(_ poweredOn: Bool) {
        print("connectionDidChangeState. poweredOn = ", poweredOn)
    }
    
    func peripheralDiscovered(_ isDuplicate: Bool, _ peripherals: [(peripheral: CBPeripheral, RSSI: Float)]) {
        
        print("peripheralDiscovered")
    }
    
    func peripheralConnectionSuccessful() {
        print("peripheralConnectionSuccessful")
    }
    
    func peripheralConnectionFailed() {
        print("peripheralConnectionFailed")
    }
    
    func peripheralConnectionReady(_ peripheral: CBPeripheral) {
        print("peripheralConnectionReady")
    }
    
    
    var body: some View {
        
        VStack {
            VStack (spacing: 0) {
                
                ScrollView {
                    
                    Group {
                        if (self.messages.count > 0) {
                            ForEach (self.messages) { message in
                                if message.sender == .app {
                                    VStack (alignment: .trailing) {
                                        Spacer()
                                        HStack {
                                            Spacer()
                                            Text(message.body)
                                                .padding([.leading, .trailing], 14.0)
                                                .padding([.top, .bottom], 7.0)
                                                .background(Color(.systemBlue))
                                                .clipShape(Capsule())
                                                .foregroundColor(Color.white)
                                        }
                                    }.padding([.trailing])
                                    
                                } else {
                                    VStack (alignment: .leading) {
                                        Spacer()
                                        HStack {
                                            Text(message.body)
                                                .padding([.leading, .trailing], 14.0)
                                                .padding([.top, .bottom], 7.0)
                                                .background(Color(.systemGray5))
                                                .clipShape(Capsule())
                                            Spacer()
                                        }
                                        //Spacer()
                                    }.padding([.leading])
                                }
                                //self.messageBubble(fromMessage: message.body)
                                //Text(message.body)
                            }
                        } else {
                            VStack {
                                Spacer()
                                HStack {
                                    Spacer()
                                    Text("No Messages")
                                    Spacer()
                                }
                            }
                            
                            //.padding()
                        }
                    }
                    
                    
                    
                    
                }
                
                
                
                HStack(alignment: .center) {
                    TextField("Message", text: self.$textFieldMessage)
                        //.textFieldStyle(RoundedBorderTextFieldStyle())
                        .background(Color(.systemBackground))
                        //.clipShape(Capsule())
                        .padding([.leading, .trailing], 14.0)
                        .padding([.top, .bottom], 7.0)
                        
                    Button(action: {self.sendMessage(fromString: self.textFieldMessage)}) {
                        Image(systemName: "arrow.up.circle.fill")
                            //.background(Color(.white))
                            .foregroundColor(.orange)
                            .font(.title)
                        
                    }.padding([.leading, .trailing], 7.0)
                    .padding([.top, .bottom], 7.0)
                }.background(Color(.systemBackground))
                    //.border(Color.purple, width: 5, cornerRadius: 20)
                .clipShape(Capsule())
                .overlay(
                    Capsule()
                        .stroke(Color(.systemGray), lineWidth: 1.0)
                )
                .padding()
                .background(Color(.secondarySystemBackground))
                
                
                //Spacer()
                
                
                
                
                
            }.navigationBarTitle("Messages", displayMode: .inline)
            .onAppear() {
                messageHandler.delegate = self
            }
            .onDisappear() {
                btSerial.disconnect()
            }
            .frame(width: nil, height: 390, alignment: .top)
            
            Spacer()
        }
        
        
            
    }
    
    func messageBubble(fromMessage: BluetoothMessage) -> AnyView {
        switch fromMessage.sender {
        case .app:
            return AnyView(
                Text(fromMessage.body)
                    .background(Color(.systemBlue))
            )
        default:
            return AnyView(
                Text(fromMessage.body)
                    .background(Color(.systemGray))
            )
        }
    }
    
    func sendMessage(fromString: String){
        var successful = false
        
        //let newMessage = BluetoothMessage(sender: .app, body: fromString)
        
        // send message with Bluetooth
        messageHandler.sendOutgoingMessage(message: fromString)
        successful = true
        
        if successful {
            print("Message sent: ", fromString)
            //self.messages.append(newMessage)
            
            //simulating reply
            //let simulatedReply = BluetoothMessage(sender: .esp, body: "Hi")
            //self.messages.append(simulatedReply)
            
            self.textFieldMessage = ""
        } else {
            print("failed to send: ", fromString)
        }
        
    }
}

struct MessagesView_Previews: PreviewProvider {
    static var previews: some View {
        MessagesView()
    }
}
