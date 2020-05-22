//
//  DateStringFormatting.swift
//  TekBox
//
//  Created by Erik Nordlund on 2/22/20.
//  Copyright © 2020 Erik Nordlund. All rights reserved.
//

import Foundation



/*
    Function: isNewDay()
 
*/
func isNewDay(newDate: Date, lastDate: Date) -> Bool {
    let dateFormatter = DateFormatter()
    dateFormatter.dateStyle = .short
    
    let newDateString = dateFormatter.string(from: newDate)
    let lastDateString = dateFormatter.string(from: lastDate)
    
    if (newDateString != lastDateString) {
        return true
    } else {
        return false
    }
}


/*
    Function: isNewYear()
 
*/
func isNewYear(newDate: Date, lastDate: Date) -> Bool {
    let dateFormatter = DateFormatter()
    //dateFormatter.dateStyle = .short
    dateFormatter.setLocalizedDateFormatFromTemplate("YYYY")
    
    let newDateString = dateFormatter.string(from: newDate)
    let lastDateString = dateFormatter.string(from: lastDate)
    
    if (newDateString != lastDateString) {
        return true
    } else {
        return false
    }
}


/*
    Function: getDate()
        • fromDownload is the mySQL DateTime string, 0 seconds from GMT
 
        • returns Swift Date
*/
func getDate(fromDateTime: String) -> Date {
    
    let dateFormatter = DateFormatter()
    dateFormatter.dateFormat = "yyyy-MM-dd HH:mm:ss"

    
    if let formattedDate = dateFormatter.date(from: fromDateTime) {
        return formattedDate
    } else {
        return Date()
    }
}


func formatDate(fromDateTime: String) -> String {
    let from = getDate(fromDateTime: fromDateTime)
    
    let today = Date()
    let yesterday = Date(timeIntervalSinceNow: -86400)
    
    if (!isNewDay(newDate: from, lastDate: today)) {
        return "Today"
    } else if (!isNewDay(newDate: from, lastDate: yesterday)) {
        return "Yesterday"
    } else if (!isNewYear(newDate: from, lastDate: today)) {
        let dateFormatter = DateFormatter()
        dateFormatter.setLocalizedDateFormatFromTemplate("MMMM d")
        return dateFormatter.string(from: from)
    } else {
        let dateFormatter = DateFormatter()
        dateFormatter.dateStyle = .long
        dateFormatter.timeStyle = .none
        return dateFormatter.string(from: from)
    }
    
}

func formatDateLowercase(fromDateTime: String) -> String {
    let from = getDate(fromDateTime: fromDateTime)
    
    let today = Date()
    let yesterday = Date(timeIntervalSinceNow: -86400)
    
    if (!isNewDay(newDate: from, lastDate: today)) {
        return "today"
    } else if (!isNewDay(newDate: from, lastDate: yesterday)) {
        return "yesterday"
    } else if (!isNewYear(newDate: from, lastDate: today)) {
        let dateFormatter = DateFormatter()
        dateFormatter.setLocalizedDateFormatFromTemplate("MMMM d")
        return dateFormatter.string(from: from)
    } else {
        let dateFormatter = DateFormatter()
        dateFormatter.dateStyle = .long
        dateFormatter.timeStyle = .none
        return dateFormatter.string(from: from)
    }
    
}

func formatTime(from: Date) -> String {
    let dateFormatter = DateFormatter()
    dateFormatter.dateStyle = .none
    dateFormatter.timeStyle = .short
    return dateFormatter.string(from: from)
}

func formatTime(fromDateTime: String) -> String {
    let date = getDate(fromDateTime: fromDateTime)
    
    return formatTime(from: date)
}
