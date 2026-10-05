# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.4] - 2025-10-04

### Changed
- Refactor: update return types to `Acl` in permission methods

### Fixed
- Fix LICENSE text to restore the full MIT License

## [1.0.3] - 2025-10-04

### Changed
- Renames

## [1.0.2] - 2025-10-04

### Changed
- Update composer.json

## [1.0.1] - 2025-10-04

### Changed
- Stop tracking CLAUDE.md

## [1.0.0] - 2025-10-04

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
