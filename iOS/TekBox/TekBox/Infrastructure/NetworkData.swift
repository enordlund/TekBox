//
//  NetworkRequest.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/22/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import Foundation
import UIKit

import Combine



struct LocationsData: Decodable {
    let id: UUID? = UUID()
    
    var locations: [Location]
}

struct Location: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var uid: String
    var name: String
    var rows: Int
    var columns: Int
    var openLockerCount: Int
    var loadedLockerCount: Int
    var latitude: String?//Double
    var longitude: String?//Double
}


enum LockerGridState {
	case empty
	case locker
	case open
}

struct LockerGridElement: Identifiable {
	let id: UUID? = UUID()
	
	var state: LockerGridState
	
	let row: Int
	let column: Int
}

struct LockerGridRow: Identifiable {
	let id: UUID = UUID()
	
	var row: [LockerGridElement]
}

struct LockerGridTable: Identifiable {
	let id: UUID = UUID()
	
	var lockers: [LockerGridRow]
}



struct Locker: Identifiable, Decodable {
    let id: UUID? = UUID()
    
    var uid: String
    var name: String
    var isLoaded: String
    var row: Int
    var column: Int
    var summary: String?
    var prepMessage: String?
    var orderUID: String?
    var disarmPeriod: String?
}



struct LockersData: Decodable {
    var lockers: [Locker]
}



struct MobileDeviceRow: Identifiable {
    let id: UUID = UUID()
    
    var row: [MobileDevice]
    var layoutPriority: Double
}

struct MobileDeviceTable: Identifiable {
    let id: UUID = UUID()
    
    var devices: [MobileDeviceRow]
}

struct SetupSession: Decodable {
	var lockerUID: String?
	var otherOwner: Bool?
	var noSession: Bool
}

struct LockerSetupExistingLocationPostData: Encodable {
	var tokenUID: String
	var token: String
	
	var lockerUID: String
	var locationUID: String
	var row: String
	var column: String
}


final class NetworkData: ObservableObject {
    @Published var tokenData: TokenData?
	
	@Published var launchDataDidLoad = false
    
    @Published var locations: [Location]? = nil
    @Published var locationsDataIsLoaded = false
    @Published var locationsError: String? = nil
    
    @Published var lockers: [Locker]? = nil
	@Published var lockerGrid: LockerGridTable? = nil
    @Published var lockersDataIsLoaded = false
    @Published var lockersError: String? = nil
    
    @Published var locker: Locker? = nil
    @Published var lockerDataIsLoaded = false
    @Published var lockerError: String? = nil
    
    @Published var orderSections: [OrderCategory]? = nil
    @Published var orderSectionsDataIsLoaded = false
    @Published var orderSectionsError: String? = nil
    
    @Published var orderDetailsDataIsLoaded = false
    @Published var orderDetails: OrderDetail? = nil
    @Published var orderDetailsError: String? = nil
    
    @Published var activityByDayDataIsLoaded = false
    @Published var activityByDay: [ActivityDay]? = nil
    @Published var activityByDayError: String? = nil
    
    
    @Published var accountDataIsLoaded = false
    @Published var account: Account? = nil
    @Published var mobileDeviceTable: MobileDeviceTable? = nil
    @Published var accountError: String? = nil
    @Published var signOutSuccess: Bool? = nil
    
    @Published var authenticatedDeviceViewDidDismiss: Bool? = nil
    @Published var deauthenticationInProgress: Bool? = nil
    @Published var deauthenticationSuccess: Bool? = nil
    @Published var deauthenticationError: String? = nil
	
	@Published var invitationRSVPInProgress: Bool? = nil
	@Published var invitationRSVPSuccess: Bool? = nil
	@Published var invitationRSVPError: String? = nil
	
	
	@Published var setupSessionDataIsLoaded = false
	@Published var setupSessionData: SetupSession? = nil
	@Published var setupSessionError: String? = nil
	
	
	@Published var setupForExistingLocationDataIsLoaded = false
	@Published var setupForExistingLocationSuccess: Bool? = nil
	@Published var setupForExistingLocationOfflineKey: String? = nil
	@Published var setupForExistingLocationError: String? = nil
    
    private let devicesPerRow = 2.0
    
    
    private let rootURL = "https://web.engr.oregonstate.edu/~nordlune/TekBox/app/"
    
    //private var tokenData: TokenData?
    
    init(token: TokenData) {
        self.tokenData = token
		//requestLocations(completion: nil)
		requestLaunchData()
    }
    
    func encodeJSON<T: Encodable>(encodable: T) -> Data? {
        guard let data = try? JSONEncoder().encode(encodable) else {
            return nil
        }
        
        return data
    }
    
    enum PostError {
        case urlError
        case serverError
        case dataGuardError
        case otherError
    }
    
    func errorMessage(fromPostError: PostError) -> String {
        switch fromPostError {
        case .urlError:
            return "URL Error."
        case .serverError:
            return "Server Error."
        case .dataGuardError:
            return "Data Guard Error."
        default:
            return "Unknown Error."
        }
    }
    
    func post<T: Encodable>(postData: T, endpoint: String, completion: @escaping (Data?, Error?, PostError?) -> Void) {
        
        guard let requestURL = URL(string: rootURL + endpoint) else {
            print("Error with url")
            completion(nil, nil, .urlError)
            return
        }
        
        var request = URLRequest(url: requestURL)
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.httpMethod = "POST"
        request.httpBody = encodeJSON(encodable: postData)
        
        //print(request.httpBody)
            
        URLSession.shared.dataTask(with: request) { (data, response, error) in
            // ~~~~~~~~~~~~~~~~~~~~~~~~~ handle error
            if let error = error {
                print("error: \(error)")
                completion(nil, error, .otherError)
                return
            }
            // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ check response for 200 OK
            guard let response = response as? HTTPURLResponse,
                (200...299).contains(response.statusCode) else {
                    print("server error")
                    completion(nil, error, .serverError)
                    return
            }
            
            guard let data = data else {
                print("Error with data guard")
                completion(nil, error, .dataGuardError)
                return
            }
            
            let dataString = String(data: data, encoding: .utf8)
            print(dataString as Any)
            
			DispatchQueue.main.async {
				completion(data, nil, nil)
			}
//            completion(data, nil, nil)
            
        }.resume()
    }
    
    
    struct TokenError: Decodable {
        var tokenError: Bool
    }
    
    func isTokenError(data: Data) -> Bool {
        do {
            let _ = try JSONDecoder().decode(TokenError.self, from: data)
        } catch {
            return false
        }
        
        return true
    }
	
	
	// App launch stuff
	
	func requestLaunchData() {
		self.requestLocations() { _, _ in
			self.launchDataDidLoad = true
//			self.requestOrders()
//			self.requestActivityByDay()
//			self.requestAccount()
		}
		
	}
	
	
	
    
    // Location stuff
	
	func resetLocationsData() {
		self.locationsDataIsLoaded = false
        self.locations = nil
        self.locationsError = nil
	}
    
	func requestLocations(completion: ((_ data: [Location]?, _ error: String?) -> Void)?) {
        
		self.resetLocationsData()
        
        let endpoint = "locations/"
        
        let postData = self.tokenData
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(LocationsData.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.locations = decodedData.locations
                        
                        self.locationsDataIsLoaded = true
						completion?(self.locations, nil)
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.locationsError = jsonError.localizedDescription
                        print(dataString as Any)
						
						completion?(nil, self.locationsError)
                    }
                    
                }
            } else if let postError = postError {
                self.locationsError = self.errorMessage(fromPostError: postError)
				
				completion?(nil, self.locationsError)
            }
			
			self.resetLocationsData()
            
        }))
    }
    /*
    func decodeLocations(fromData: Data?) -> [Location]? {
        if fromData != nil {
            do {
                // construct entries from JSON
                let locationsData = try JSONDecoder().decode(LocationsData.self, from: self.data!)
                //print(locationsData.locations)
                return locationsData.locations
                
                /*
                DispatchQueue.main.async {
                    self.locations = locationsData.locations
                    
                    //print(self.locations)
                    
                    
                    self.dataIsLoaded = true
                }
                */
            } catch let jsonError {
                print("Error decoding JSON: ", jsonError)
                let dataString = String(data: self.data!, encoding: .utf8)
                print(dataString as Any)
            }
        }
        return nil
    }
    */
    
    
    
    
    
    // lockers stuff
    struct LockersPostData: Encodable {
        var locationUID: String
        var tokenUID: String
        var token: String
    }
	
	func resetLockersData() {
		self.lockersDataIsLoaded = false
        self.lockers = nil
        self.lockersError = nil
	}
    
	func requestLockers(fromLocationUID: String, completion: ((_ lockers: [Locker]?, _ error: String?) -> Void)?) {
        
		self.resetLockersData()
        
        let endpoint = "lockers/"
        
        let postData = LockersPostData(locationUID: fromLocationUID, tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token)
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(LockersData.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.lockers = decodedData.lockers
                        
                        self.lockersDataIsLoaded = true
						
						completion?(self.lockers, nil)
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.lockersError = jsonError.localizedDescription
						
						completion?(nil, self.lockersError)
						
                        print(dataString as Any)
                    }
                    
                }
            } else if let postError = postError {
                self.lockersError = self.errorMessage(fromPostError: postError)
				
				completion?(nil, self.lockersError)
            }
			self.resetLockersData()
        }))
    }
	
	
	func constructLockerGrid(forLockers: [Locker], rowCount: Int, columnCount: Int) -> LockerGridTable {
		// ONLY CALL IF LOCKERS ARE LOADED AND SORTED
//		var tempTable = LockerGridTable(lockers: [LockerGridRow(row: [LockerGridElement]())])
		
		// append rows with number of columns
		var rowIndex = 0
		
		print("rowCount: \(rowCount)")
		print("columnCount: \(columnCount)")
		
		var lockers = [LockerGridRow]()
		
		// adding one for new spaces on top
		while rowIndex < (rowCount + 1) {
			// create a row with number of columns
			var row = LockerGridRow(row: [LockerGridElement]())//[LockerGridElement]()
			
			// columnCount + 1 so user can add locker to the left of existing lockers
			var columnIndex = 0
			while columnIndex < (columnCount + 2) {
				row.row.append(LockerGridElement(state: .empty, row: rowIndex - 1, column: columnIndex - 1))
				print(row.row.count)
				columnIndex += 1
			}
			
			// add the row to the table
			lockers.append(row)
			
			print(lockers.count)
			print(lockers[0].row.count)
			
			rowIndex += 1
		}
		
		let tableRowCount = lockers.count
		let tableColumnCount = lockers[0].row.count
		
		// put open elements on endcaps of lockers
		lockers[tableRowCount - 1].row[0].state = .open
		lockers[tableRowCount - 1].row[tableColumnCount - 1].state = .open
		
		
		var tempTable = LockerGridTable(lockers: lockers)
		
		print("Table rowCount: \(tempTable.lockers.count)")
		print("Table columnCount: \(tempTable.lockers[0].row.count)")
		
		// now populate the table with "true" where lockers exist.
		// assuming this is only being called after lockers are loaded
		for locker in forLockers {
			// new positions, accounting for available spaces
			let row = locker.row + 1 // bumping down for empty row
			let column = locker.column + 1// bumping right to account for empty row
			
			tempTable.lockers[row].row[column].state = .locker
			
			// lockers are pre-sorted with upper-left origin from the server
			if row > 0 {
				if tempTable.lockers[row - 1].row[column].state == .empty {
					tempTable.lockers[row - 1].row[column].state = .open
				}
			}
			
		}
		
		
		return tempTable
	}
	
	func resetLockerGridData() {
		self.resetLockersData()
		self.lockerGrid = nil
	}
	
	func requestLockerGrid(fromLocationUID: String, rowCount: Int, columnCount: Int, completion: ((_ data: LockerGridTable?, _ error: String?) -> Void)?) {
        
		self.resetLockerGridData()
        
        let endpoint = "lockers/"
        
        let postData = LockersPostData(locationUID: fromLocationUID, tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token)
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(LockersData.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.lockers = decodedData.lockers
						
						self.lockerGrid = self.constructLockerGrid(forLockers: self.lockers!, rowCount: rowCount, columnCount: columnCount)
                        
                        self.lockersDataIsLoaded = true
						
						completion?(self.lockerGrid, nil)
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.lockersError = jsonError.localizedDescription
                        print(dataString as Any)
						
						completion?(nil, jsonError.localizedDescription)
                    }
                    
                }
            } else if let postError = postError {
                self.lockersError = self.errorMessage(fromPostError: postError)
				completion?(nil, self.errorMessage(fromPostError: postError))
            }
            self.resetLockerGridData()
        }))
    }
    
    
    
    
    // locker stuff
    struct LockerPostData: Encodable {
        var lockerUID: String
        var tokenUID: String
        var token: String
    }
	
	func resetLockerData() {
		self.lockerDataIsLoaded = false
        self.locker = nil
        self.lockerError = nil
	}
    
	func requestLocker(withUID: String, completion: ((_ data: Locker?, _ error: String?) -> Void)?) {
		self.resetLockerData()
        
        let endpoint = "locker/"
        
        let postData = LockerPostData(lockerUID: withUID, tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token)
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(Locker.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.locker = decodedData
                        
                        self.lockerDataIsLoaded = true
						
						completion?(self.locker, nil)
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.lockerError = jsonError.localizedDescription
                        print(dataString as Any)
						
						completion?(nil, self.lockerError)
                    }
                    
                }
            } else if let postError = postError {
                self.lockerError = self.errorMessage(fromPostError: postError)
				completion?(nil, self.lockerError)
            }
			self.resetLockerData()
        }))
    }
    
    struct OrderPostData: Encodable {
        var lockerUID: String
        var orderNumber: String
        var customerName: String
        var customerEmail: String
        var tokenUID: String
        var token: String
    }
    
	func sendOrderInfo(forLockerUID: String, orderNumber: String, customerName: String, customerEmail: String, completion: ((_ data: Locker?, _ error: String?) -> Void)?) {
		self.resetLockerData()
        
        let endpoint = "locker/"
        
        let postData = OrderPostData(lockerUID: forLockerUID, orderNumber: orderNumber, customerName: customerName, customerEmail: customerEmail, tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token)
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(Locker.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.locker = decodedData
                        
                        self.lockerDataIsLoaded = true
						completion?(self.locker, nil)
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.lockerError = jsonError.localizedDescription
                        print(dataString as Any)
						completion?(nil, self.lockerError)
                    }
                    
                }
            } else if let postError = postError {
                self.lockerError = self.errorMessage(fromPostError: postError)
				completion?(nil, self.lockerError)
            }
			self.resetLockerData()
        }))
    }
    
    struct DisarmPostData: Encodable {
        var lockerUID: String
        var disarm: Bool
        var forPrep: Bool
        var tokenUID: String
        var token: String
    }
    
	func disarmLocker(withUID: String, forPrep: Bool, completion: ((_ data: Locker?, _ error: String?) -> Void)?) {
		self.resetLockerData()
        
        let endpoint = "locker/"
        
        let postData = DisarmPostData(lockerUID: withUID, disarm: true, forPrep: forPrep, tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token)
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(Locker.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.locker = decodedData
                        
                        self.lockerDataIsLoaded = true
						
						completion?(self.locker, nil)
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.lockerError = jsonError.localizedDescription
                        print(dataString as Any)
						
						completion?(nil, self.lockerError)
                    }
                    
                }
            } else if let postError = postError {
                self.lockerError = self.errorMessage(fromPostError: postError)
				
				completion?(nil, self.lockerError)
            }
            
			self.resetLockerData()
			
        }))
    }
    
    
    
    
    
    // orders stuff
	
	func resetOrders() {
		self.orderSectionsDataIsLoaded = false
		self.orderSections = nil
		self.orderSectionsError = nil
	}
	
	func requestOrders(withReset: Bool) {
		if withReset {
			self.resetOrders()
		}
        
        
        let endpoint = "orders/"
        
        let postData = self.tokenData
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(OrderData.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.orderSections = decodedData.activeThenInactiveOrders
                        
                        self.orderSectionsDataIsLoaded = true
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.orderSectionsError = jsonError.localizedDescription
                        print(dataString as Any)
                    }
                    
                }
            } else if let postError = postError {
                self.orderSectionsError = self.errorMessage(fromPostError: postError)
            }
            
        }))
    }
    
    
    struct OrderDetailsPostData: Encodable {
        var orderUID: String
        var tokenUID: String
        var token: String
    }
    
    func requestOrderDetails(forOrderUID: String) {
        self.orderDetailsDataIsLoaded = false
        self.orderDetails = nil
        self.orderDetailsError = nil
        
        let endpoint = "order/"
        
        let postData = OrderDetailsPostData(orderUID: forOrderUID, tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token)
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(OrderDetail.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.orderDetails = decodedData
                        
                        self.orderDetailsDataIsLoaded = true
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.orderDetailsError = jsonError.localizedDescription
                        print(dataString as Any)
                    }
                    
                }
            } else if let postError = postError {
                self.orderDetailsError = self.errorMessage(fromPostError: postError)
            }
            
        }))
    }
    
    
    
    
    // Activity stuff
	
	func resetActivityByDay() {
		self.activityByDayDataIsLoaded = false
        self.activityByDay = nil
        self.activityByDayError = nil
	}
	
	func requestActivityByDay(withReset: Bool) {
		
		if withReset {
			self.resetActivityByDay()
		}
		
		
        
        let endpoint = "activity/"
        
        let postData = self.tokenData
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(ActivityData.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.activityByDay = decodedData.activityDays
                        
                        self.activityByDayDataIsLoaded = true
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.activityByDayError = jsonError.localizedDescription
                        print(dataString as Any)
                    }
                    
                }
            } else if let postError = postError {
                self.activityByDayError = self.errorMessage(fromPostError: postError)
            }
            
        }))
    }
    
    
    
    
    
    
    // account stuff
    func requestAccount() {
        self.accountDataIsLoaded = false
        self.account = nil
        self.accountError = nil
        
        let endpoint = "account/"
        
        let postData = self.tokenData
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(Account.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.account = decodedData
                        
                        
                        //
                        //let mobileDevicesRowCount = Int(ceil(Double(self.account!.mobileDevices.count) / self.devicesPerRow))
                        
                        var newDeviceTable = MobileDeviceTable(devices: [MobileDeviceRow]())
                        var newDeviceRow = MobileDeviceRow(row: [MobileDevice](), layoutPriority: 0)
                        
                        var columnIndex = 0
                        
                        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ modify this to make long names twice as wide (single element in a row for phones, one of two or three on iPad)
                        // ~~~~~~~~~~~~~~~ elaborating, this might need subcolumns to handle the iPad case of having four devices in a row, or three with one double-wide, or two with both double-wide.
                        for device in self.account!.mobileDevices {
                            var newDevice = device
                            if device.vendorID == String(describing: UIDevice.current.identifierForVendor!) {
                                
                                newDevice.model = "This " + device.model
                            }
                            
                            if columnIndex > 1 {
                                newDeviceTable.devices.append(newDeviceRow)
                                newDeviceRow = MobileDeviceRow(row: [MobileDevice](), layoutPriority: 0)
                                newDeviceRow.row.append(newDevice)
                                if newDevice.model.contains("This") {
                                    newDeviceRow.layoutPriority = 1
                                }
                                columnIndex = 1
                            } else {
                                newDeviceRow.row.append(newDevice)
                                if newDevice.model.contains("This") {
                                    newDeviceRow.layoutPriority = 1
                                }
                                columnIndex += 1
                            }
                        }
                        
                        if newDeviceRow.row.count > 0 {
                            newDeviceTable.devices.append(newDeviceRow)
                        }
                        
                        self.mobileDeviceTable = newDeviceTable
                        
                        self.accountDataIsLoaded = true
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.accountError = jsonError.localizedDescription
                        print(dataString as Any)
                    }
                    
                }
            } else if let postError = postError {
                self.accountError = self.errorMessage(fromPostError: postError)
            }
            
        }))
    }
    
    func requestSignOut() {
        print("requestSignOut()")
        self.accountDataIsLoaded = false
        self.account = nil
        self.accountError = nil
        
        let endpoint = "deauth/"
        
        let postData = self.tokenData
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(TaskReport.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.signOutSuccess = decodedData.didComplete
						
						if decodedData.didComplete {
							deleteAllTokens()
						}
                        
                        self.accountDataIsLoaded = true
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.accountError = jsonError.localizedDescription
                        print(dataString as Any)
                    }
                    
                }
            } else if let postError = postError {
                self.accountError = self.errorMessage(fromPostError: postError)
            }
            
        }))
    }
    
    
    struct DeauthenticationData: Encodable {
        var tokenUID: String
        var token: String
        var deauthenticatedTokenUID: String
    }
	
	
	
	// invitations
	struct InvitationRSVPData: Encodable {
		var tokenUID: String
		var token: String
		
		var invitationUID: String
		var accepted: Bool
	}
	
	func resetInvitationRSVPVariables() {
		self.invitationRSVPInProgress = nil
		self.invitationRSVPSuccess = nil
		self.invitationRSVPError = nil
	}
	
	func requestInvitationRSVP(forInvitationUID: String, accepted: Bool, completion: ((_ didComplete: Bool, _ error: String?) -> Void)?) {
		self.resetInvitationRSVPVariables()
		
		let endpoint = "rsvp/"
		
		let postData = InvitationRSVPData(tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token, invitationUID: forInvitationUID, accepted: accepted)
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(TaskReport.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.accountDataIsLoaded = false
                        
                        self.invitationRSVPSuccess = decodedData.didComplete
                        
                        
                        
                        self.invitationRSVPInProgress = false
						
						completion?(decodedData.didComplete, decodedData.errorMessage)
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.invitationRSVPError = jsonError.localizedDescription
                        print(dataString as Any)
						
						completion?(false, jsonError.localizedDescription)
                    }
                    
                }
            } else if let postError = postError {
                self.invitationRSVPError = self.errorMessage(fromPostError: postError)
				completion?(false, self.errorMessage(fromPostError: postError))
            }
            
        }))
	}
	
	
	
    
    func eraseData() {
        self.tokenData = nil
		deleteAllTokens()
        
        self.locations = nil
        self.locationsDataIsLoaded = false
        self.locationsError = nil
        
        self.lockers = nil
        self.lockersDataIsLoaded = false
        self.lockersError = nil
        
        self.locker = nil
        self.lockerDataIsLoaded = false
        self.lockerError = nil
        
        self.orderSections = nil
        self.orderSectionsDataIsLoaded = false
        self.orderSectionsError = nil
        
        self.orderDetailsDataIsLoaded = false
        self.orderDetails = nil
        self.orderDetailsError = nil
        
        self.activityByDayDataIsLoaded = false
        self.activityByDay = nil
        self.activityByDayError = nil
        
        
        self.accountDataIsLoaded = false
        self.account = nil
        self.mobileDeviceTable = nil
        self.accountError = nil
        self.signOutSuccess = nil
        
        self.authenticatedDeviceViewDidDismiss = nil
        self.deauthenticationInProgress = nil
        self.deauthenticationSuccess = nil
        self.deauthenticationError = nil
    }
    
    func requestDeviceDeauthentication(forDevice: MobileDevice) {
        self.deauthenticationInProgress = true
        self.deauthenticationSuccess = nil
        self.deauthenticationError = nil
        
        let endpoint = "deauth/"
        
        let postData = DeauthenticationData(tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token, deauthenticatedTokenUID: forDevice.tokenUID)
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(TaskReport.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.accountDataIsLoaded = false
                        
                        self.deauthenticationSuccess = decodedData.didComplete
                        
						if decodedData.didComplete {
							deleteAllTokens()
						}
                        
                        
                        self.deauthenticationInProgress = false
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.deauthenticationError = jsonError.localizedDescription
                        print(dataString as Any)
                    }
                    
                }
            } else if let postError = postError {
                self.deauthenticationError = self.errorMessage(fromPostError: postError)
            }
            
        }))
    }
	
	
	struct SetupSessionRequestData: Encodable {
		var tokenUID: String
        var token: String
		var sessionUID: String
	}
	
	
	
	func resetSetupSession() {
		resetSetupSessionVariables()
		resetSetupForExistingLocationVariables()
		requestLocations(completion: nil)
	}
	
    // Locker setup stuff
	func resetSetupSessionVariables() {
		self.setupSessionDataIsLoaded = false
        self.setupSessionData = nil
        self.setupSessionError = nil
	}
	
	func requestSetupSession(withSessionUID: String, completion: ((_ data: SetupSession?, _ error: String?) -> Void)?) {
		print("requestSetupSession(withSessionUID: \(withSessionUID))")
        resetSetupSessionVariables()
        
        let endpoint = "locker-setup/"
        
		let postData = SetupSessionRequestData(tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token, sessionUID: withSessionUID)
        
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(SetupSession.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
                        self.setupSessionData = decodedData
                        
                        self.setupSessionDataIsLoaded = true
						
						completion?(self.setupSessionData, nil)
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.setupSessionError = jsonError.localizedDescription
                        print(dataString as Any)
						
						completion?(nil, self.setupSessionError)
                    }
                    
                }
            } else if let postError = postError {
                self.setupSessionError = self.errorMessage(fromPostError: postError)
				
				completion?(nil, self.setupSessionError)
            }
			self.resetSetupSessionVariables()
        }))
    }
	
	func resetSetupForExistingLocationVariables() {
		self.setupForExistingLocationDataIsLoaded = false
        self.setupForExistingLocationSuccess = nil
		self.setupForExistingLocationOfflineKey = nil
        self.setupForExistingLocationError = nil
	}
	
	func requestLockerSetupExistingLocation(forLockerUID: String, locationUID: String, row: Int, column: Int, completion: ((_ success: Bool, _ offlineKey: String?, _ error: String?) -> Void)?) {
		print("requestLockerSetupExistingLocation()")
		print(forLockerUID)
		print(locationUID)
		print("Row: \(row)")
		print("Column: \(column)")
        
        resetSetupForExistingLocationVariables()
        
        let endpoint = "locker-setup/"
        
		let postData = LockerSetupExistingLocationPostData(tokenUID: self.tokenData!.tokenUID, token: self.tokenData!.token, lockerUID: forLockerUID, locationUID: locationUID, row: "\(row)", column: "\(column)")
		
        post(postData: postData, endpoint: endpoint, completion: ({data, error, postError in
            if let data = data {
                // decode the data, and make it available to the view
                do {
                    // construct entries from JSON
                    let decodedData = try JSONDecoder().decode(TaskReport.self, from: data)
                    //print(locationsData.locations)
                    
                    DispatchQueue.main.async {
                        //self.data = data
                        
                        //print(self.locations)
						self.setupForExistingLocationSuccess = decodedData.didComplete
						
						self.setupForExistingLocationError = decodedData.errorMessage
						
						self.setupForExistingLocationOfflineKey = decodedData.asset
                        
                        self.setupForExistingLocationDataIsLoaded = true
						
						completion?(decodedData.didComplete, decodedData.asset, decodedData.errorMessage)
                    }
                    
                } catch let jsonError {
                    if self.isTokenError(data: data) {
                        self.eraseData()
                    } else {
                        print("Error decoding JSON: ", jsonError)
                        let dataString = String(data: data, encoding: .utf8)
                        self.setupForExistingLocationError = jsonError.localizedDescription
                        print(dataString as Any)
						
						completion?(false, nil, jsonError.localizedDescription)
                    }
                    
                }
            } else if let postError = postError {
                self.setupForExistingLocationError = self.errorMessage(fromPostError: postError)
				
				completion?(false, nil, self.errorMessage(fromPostError: postError))
            }
            
//			self.resetSetupForExistingLocationVariables()
        }))
    }
    
}

