//
//  NetworkDataErrorView.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/26/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI

struct NetworkDataErrorView: View {
    
    let errorString: String
    
    var body: some View {
        VStack {
            Text("Error")
                .font(.headline)
                .padding([.leading, .trailing])
            
            Text(self.errorString)
                .padding()
            
            Button(action: {self.refreshView()}) {
                Text("Try Again")
            }.padding()
            .buttonStyle(OrangeButtonStyle())
        }
    }
    
    func refreshView() {
        print("errorString")
        // really, this needs to refresh the parent view.
    }
}

struct NetworkDataErrorView_Previews: PreviewProvider {
    static var previews: some View {
        NetworkDataErrorView(errorString: "There was a problem loading this data.")
    }
}
