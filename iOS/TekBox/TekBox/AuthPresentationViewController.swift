//
//  AuthPresentationViewController.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/20/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import UIKit
import AuthenticationServices

class AuthPresentationViewController: UIViewController, ASWebAuthenticationPresentationContextProviding {
    func presentationAnchor(for session: ASWebAuthenticationSession) -> ASPresentationAnchor {
        return globalPresentationAnchor ?? ASPresentationAnchor()
    }
    

    override func viewDidLoad() {
        super.viewDidLoad()

        // Do any additional setup after loading the view.
    }
    

    /*
    // MARK: - Navigation

    // In a storyboard-based application, you will often want to do a little preparation before navigation
    override func prepare(for segue: UIStoryboardSegue, sender: Any?) {
        // Get the new view controller using segue.destination.
        // Pass the selected object to the new view controller.
    }
    */

}
