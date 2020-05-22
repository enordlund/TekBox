//
//  SettingsView.swift
//  TekBox
//
//  Created by Erik Nordlund on 11/20/19.
//  Copyright © 2019 Erik Nordlund. All rights reserved.
//

import SwiftUI


// assets
let accountImage = Image(systemName: "person.crop.circle.fill")


let managersImage = Image(systemName: "person.3.fill")

let notificationsImage = Image(systemName: "bell.fill")

let keysImage = Image(systemName: "lock.rotation.open")

let helpImage = Image(systemName: "questionmark")


let messageImage = Image(systemName: "message.fill")

let bluetoothImage = Image(systemName: "dot.radiowaves.left.and.right")

let offlineAccessImage = Image(systemName: "lock.shield.fill")



enum SettingsRow {
    case account
    case users
    case notifications
    case keys
    case help
    case offlineAccess
}

struct Row: Identifiable {
    var id = UUID()
    
    var row: SettingsRow
}


struct Option: Identifiable {
    var id = UUID()
    
    var title = String()
    var icon: Image
    
    var row: SettingsRow
    
    //var view: AnyView
}






let options: [Option] = [Option(title: "Account", icon: accountImage, row: .account),
                         Option(title: "Users", icon: managersImage, row: .users),
                         Option(title: "Notifications", icon: notificationsImage, row: .notifications),
                         Option(title: "Keys", icon: keysImage, row: .keys),
                         Option(title: "Help", icon: helpImage, row: .help),
                         Option(title: "Offline Access", icon: offlineAccessImage, row: .offlineAccess)]


let settingsRows: [Row] = [Row(row: .account),
                                         Row(row: .users),
                                         Row(row: .notifications),
                                         Row(row: .keys),
                                         Row(row: .help),
                                         Row(row: .offlineAccess)]


struct SettingsView: View {
	@EnvironmentObject var contentManager: ContentManager
	
    var body: some View {
        NavigationView {
            
            List (options) { option in
                
                NavigationLink(destination: self.rowView(fromRow: option.row)) {
                    HStack(alignment: .center) {
                        option.icon
                            .foregroundColor(.orange)
                        Text(option.title)
                    }
                }
            }
            .navigationBarTitle(Text("Settings"), displayMode: .large)
            
            //.navigationBarHidden(false)
            //.navigationBarItems()
                
        }
        .accentColor(.orange)
    }
    
    
    func rowView(fromRow: SettingsRow) -> AnyView {
        switch fromRow {
        case .account:
            return AnyView(AccountView())
        case .users:
            return AnyView(Text("Users"))
        case .notifications:
            return AnyView(Text("Notifications"))
        case .keys:
            return AnyView(KeysView())
        case .help:
            return AnyView(Text("Help"))
        case .offlineAccess:
			return AnyView(OfflineAccessView())
        }
    }
    
    
}

struct SettingsView_Previews: PreviewProvider {
    static var previews: some View {
        SettingsView()
    }
}
