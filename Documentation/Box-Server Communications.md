# Locker/Server Communications

All locker/server communications are initiated by lockers with POST requests. All locker POST requests are logged in the database’s TekBox-Requests table, either as new requests with a request UID that is sent with server responses, or as confirmations of server responses.

## Locker to server functions
###### In TekBoxLockerWireless.hpp

### `String sendPostRequest(String requestType)`
This function sends a POST request with the `x-www-form-urlencoded` content type to the server. The following data is sent with each request:
- `uid`: Unique identifier of the locker
- `password`: Unique password checked with the server’s database
- `totp`: Currently a constant value, reserved for time-based one time password
- `request`: The value of the `requestType` parameter

The server should respond, and its response data is returned as a String.

### `void sendRequestConfirmation(String requestUID, String confirmation)`
This function sends a POST request with the `x-www-form-urlencoded` content type to the server. It is to be used to communicate the results of actions derived form server responses. The following data is sent with each request:
- `uid`: Unique identifier of the locker
- `password`: Unique password checked with the server’s database
- `totp`: Currently a constant value, reserved for time-based one time password
- `confirmation`: The value of the `confirmation` parameter, indicating the result of the locker’s action derived from the server’s last response
- `requestUID`: The UID of the request being confirmed, which is provided in the server’s last response

## Communication cases
### Authentication test
In the connection test process, `serverAuthenticationIsGood()` is called to test authentication credentials. This function parses the return value of `sendPostRequest(“TEST”)` to determine the result of the test. The function sends a request confirmation, and returns `true` or `false` to indicate the success or failure of the test.

#### Request types (locker to server)
- `”TEST”`: Indicates an authentication test of uid/password/totp

#### Response types (server to locker)
- `”PASS”`: Indicates valid authentication credentials
- `”FAIL”`: Indicates invalid authentication credentials

#### Confirmation types (locker to server)
- `”PASS”`: Indicates recognized authentication test pass
- `”FAIL”`: Indicates recognized authentication test failure
- `”NULL”`: Indicates unrecognized server response

### Button press
When a locker button is pressed, `postPress()` is called. This function parses the return value of `sendPostRequest(“PRESS”)` to determine whether it should lock or unlock. After a locker calls `lock()` or `unlock()`, it sends a confirmation or error back to the server with `void sendRequestConfirmation(String requestUID, String confirmation)`.

#### Request types (locker to server)
- `”PRESS”`: Indicates a button press

#### Response types (server to locker)
- `”LOCK”`: Indicates lock command
- `”UNLOCK”`: Indicates unlock command

#### Confirmation types (locker to server)
- `”LOCK”`: Indicates successful lock
- `”UNLOCK”`: Indicates successful unlock
- `”FAILED”`: Indicates failure to lock or unlock

### Tamper detection
When the locker detects tampering, `postTamper()` should be called (the implementation is currently incomplete). This function notifies the server of tampering by calling `sendPostRequest(“TAMPER”)`. The server’s response is unused by the locker.

#### Request types (locker to server)
- `”TAMPER”`: Indicates a button press

#### Response types (server to locker)
- `”LOCK”`: Indicates lock command
- `”UNLOCK”`: Indicates unlock command

#### Confirmation types (locker to server)
Tamper responses are not confirmed, because the locker does not act on the response.