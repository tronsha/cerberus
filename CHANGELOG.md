# Change Log

## [Unreleased]

### Added
- Event on412

### Fixed
- Console prepare: length fix for travis-ci

### Changed
- Ccryption
- Console wordwrap: cut long words
- Symfony Console 3.2
- Symfony Translation 3.2
- PHPUnit 5.6

### Removed
- Cerberus getMicrotime

## [1.6.0]

### Added
- Plugin List
- Action channelList
- Event on263
- Db lastInsertId
- Db clearChannellist
- Db addChannelToChannellist
- Installer: ask driver (mysql, postgresql)
- Tests for Database Functions
- Tests for Events

### Fixed
- Db removeUserFromChannel: if username contains backslashes
- Db addWrite: added missing return value
- Db getStatus: if status parameter is an array
- Irc connect: $n was a string
- Irc inChannel: wrong return position
- Translate trans: works now
- Config ctcp: works now
- MySQL 5.7

### Changed
- Db addChannel: added return value
- Db addUserToChannel: added return value
- Db addControl: added return value
- Db addStatus: added return value
- Irc addPluginEvent: added method param

## [1.5.0]

### Added
- Change Log
- Symfony Translation Component
- Plugin Whois
- Error Handling for addPluginEvent

### Changed
- PHPUnit 5.3

[Unreleased]: https://github.com/tronsha/cerberus/compare/v1.6.0...HEAD
[1.6.0]: https://github.com/tronsha/cerberus/compare/v1.5.0...v1.6.0
[1.5.0]: https://github.com/tronsha/cerberus/compare/v1.4.1...v1.5.0
