//
//  SignedOutNoKeysView.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/20/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import SwiftUI
import UIKit

import AuthenticationServices

struct TokenData {
    var tokenUID: String
    var token: String
}

//var tokenData: TokenData?


final class AuthProviderView: NSObject, View {
    //@EnvironmentObject var contentManager: ContentManager
    
    private var session: ASWebAuthenticationSession?
    private let presentingWindow: UIWindow
    
    var body: some View {
        /*
        VStack {
            Text("TekBox")
            .font(.largeTitle)
            
            Spacer()
            
            Button(action: {/*self.signIn()*/}) {
                Text("Sign In")
                    .padding()
                    .foregroundColor(.white)
                    .background(Color.orange)
                    .cornerRadius(CGFloat(5))
                    .font(.headline)
            }.padding()
        }
        */
        
        Text("Hey guys, it's Austin!")
    }
    
    init(window: UIWindow) {
        presentingWindow = window
    }
    
    func saveToken(tokenUID: String, token: String) {
        //tokenData = TokenData(tokenUID: tokenUID, token: token)
    }
    
    func signIn() {
        if session != nil {
            session?.cancel()
            session = nil
        }
        
        let device = UIDevice.current.model
        
        guard let authURL = URL(string: "https://web.engr.oregonstate.edu/~nordlune/TestBox/app/iOS-13/auth?type=\(device)") else {return}
        let scheme = "tekbox"
        
        session = ASWebAuthenticationSession(url: authURL, callbackURLScheme: scheme, completionHandler: {
            callbackURL, error in
            print(callbackURL as Any)
            print(error as Any)
            
            if (callbackURL != nil) && (error == nil) {
                guard let components = URLComponents(url: callbackURL!, resolvingAgainstBaseURL: true) else {
                        print("Invalid URL or missing token path")
                    return
                }
                let tokenPath = components.path
                let parameters = components.queryItems
                
                if let tokenUID = parameters?.first(where: {$0.name == "tokenUID"}) {
                    if let token = parameters?.first(where: {$0.name == "token"}) {
                        self.saveToken(tokenUID: "\(tokenUID)", token: "\(token)")
                    }
                }
            }
            
        })
        
        session?.presentationContextProvider = self
        session?.start()
    }
}

extension AuthProviderView: ASWebAuthenticationPresentationContextProviding {
    func presentationAnchor(for session: ASWebAuthenticationSession) -> ASPresentationAnchor {
        return presentingWindow
    }
}


/*
struct SignedOutNoKeysView_Previews: PreviewProvider {
    
    static var previews: some View {
        SignedOutNoKeysView()
    }
}

 */
