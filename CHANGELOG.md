# Change Log

## [Unreleased]

### Added
- Db lastInsertId
- Installer: ask driver (mysql, postgresql)
- Tests for Database Functions

### Fixed
- Db removeUserFromChannel: if username contains backslashes
- Db addWrite: added missing return value
- Db getStatus: if status parameter is an array
- Irc connect: $n was a string
- Irc inChannel: wrong return position
- Translate trans: works now
- Config ctcp: works now

### Changed
- Db addChannel: added return value
- Db addUserToChannel: added return value
- Db addControl: added return value
- Db addStatus: added return value

## [1.5.0]

### Added
- Change Log
- Symfony Translation Component
- Plugin Whois
- Error Handling for addPluginEvent

### Changed
- PHPUnit 5.3

[Unreleased]: https://github.com/tronsha/cerberus/compare/v1.5.0...HEAD
[1.5.0]: https://github.com/tronsha/cerberus/compare/v1.4.1...v1.5.0
