# TekBox Server
TekBox Server is the central point of contact for all other components of the system.

## Installation
After cloning the repository, the contents of `/Server/` should be moved to the directory that will act as the TekBox root URL. 
### Note
`/TekBox_Library/` contains dependencies and supporting files, but no endpoints. It can be moved anywhere, as long as its location is accessible at runtime. If moved, be sure to change the value of `TEKBOX_LIBRARY_FILE_PATH` in `tekbox_config.php` (see Configuration below). If not moved, consider blocking web access to the folder.

## Configuration
The upper portion of `tekbox_config.php` should be configured with the file paths associated with your chosen location. All directory paths should end with a trailing forward slash.
### Installation file paths
* `HOME_DIRECTORY_PATH`: The full path to the home directory of the user hosting the installation.
* `TEKBOX_DIRECTORY_PATH`: The full path to the root `/TekBox/` directory.
* `TEKBOX_LIBRARY_FILE_PATH`: The full path to the installation's `/TekBox_Library/` directory.
### Database information
* `DB_HOST`: The hostname for the database in use (with no trailing forward slash).
* `DB_NAME`: The name of the database in use.
* `DB_USER`: The username for the database in use.
* `DB_PASS`: The password for the database in use.
### CAS information
* `CAS_HOSTNAME`: The hostname for the CAS server in use.
* `CAS_CA_CHAIN_FILE_PATH`: The file path for the `*.pem` CA chain certificate file from the CAS server in use.
### URLs and email addresses
* `TEKBOX_ROOT_URL`: The full public URL to the root level of the TekBox Server installation, including the leading `https://` transfer protocol.
* `TEKBOX_EMAIL_FROM_ADDRESS`: The email address that will appear in emails sent to customers and TekBox Dashboard users.
* `TAMPERING_EMAIL_ADDRESS`: The email address that will receive tampering alerts for the installation.

The constants in the lower portion of `tekbox_config.php` are generally meant to be left as-is.

## Maintenance
The CA chain certificate for the CAS server will expire eventually, and will need to be updated. It is important to monitor the expiration date of the certificate to prevent system downtime.

### Dependencies
#### phpCAS
[phpCAS](https://github.com/apereo/phpCAS) makes it possible to support CAS authentication without starting from scratch. A tested copy of the library is included in `/TekBox_Library/`.
#### random_compat
[random_compat](https://github.com/paragonie/random_compat) provides PHP 5.x support for random_bytes(), which is used to generate random offline keys during locker setup. A tested copy is included in `/TekBox_Library/`.