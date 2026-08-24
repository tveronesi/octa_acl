# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `Acl` — immutable value object for bitmask ACL checks (`grants`, `grantsAny`, `withGrant`, `withRevoke`, `toInt`, `fromInt`, `none`)
- `AclRegistry` — maps string permission names to bit values; provides `namesFor(Acl)` introspection
- PHPUnit 10 test suite with attribute-based coverage tracking

### Changed
- Replaced the session-coupled `OctaAcl` static class with the framework-agnostic `Acl` value object
- Namespace changed from `Gunz` to `Gunz\OctaAcl`
- Minimum PHP version raised to 8.1

### Removed
- `OctaAcl` static class and its `$_SESSION` dependency
- Global `$_acl_definitions` variable and side-effect constants at include time

## [0.1.0] - 2024-01-01

### Added
- Initial release with `OctaAcl` static class
