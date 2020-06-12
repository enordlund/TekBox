# TekBox Database
TekBox Database was created with phpMyAdmin, and it is recommended to run your database with phpMyAdmin as well for reliability.

## Installation
After establishing a phpMyAdmin MySQL server, import `phpMyAdmin SQL Dump.txt` to create the tables required for system operation.
#### Note
The following information for your phpMyAdmin server will be required for server setup:
* Database name
* Database URL
* Database username
* Database password

## Manual Entry

### Creating a new location
New locations may be manually created by creating a new row in `TekBox-Clusters`. The following columns should be included for best results:
- `Name`: The displayed name of the location to all users
- `UUID`: A unique identifier for the location
- `Rows`: The number of rows of lockers in the location
- `Columns`: The number of columns of lockers in the location
- `Disarm-For-Minutes`: The length of time that a locker will be disarmed for unlocking, in minutes
- `Unlock-For-Minutes`: Unsupported, set to `0`
- `Latitude`: The geographic latitude of the location, for sorting by location
- `Longitude`: The geographic longitude of the location, for sorting by location
- `Admin1-UUID`: The `OSUUID` (from TekBox-Users) for the first administrator of the location

Additional administrators or managers are not required for expected functionality.