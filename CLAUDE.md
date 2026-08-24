# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Install dependencies
composer install

# Run all tests
composer test           # alias for vendor/bin/phpunit
vendor/bin/phpunit      # direct

# Run a single test file
vendor/bin/phpunit tests/AclTest.php

# Run a single test method
vendor/bin/phpunit --filter testGrantsRequiresAllBits tests/AclTest.php

# Generate coverage report (requires Xdebug or PCOV)
composer test:coverage
```

## Architecture

A small PHP library (`gunz/octa-acl`) for bitmask-based access control. Two classes, no dependencies.

**Core concept:** each permission is a power-of-2 integer. A user's ACL is the bitwise OR of all their permissions. Authorization: `($required | $userBits) === $userBits` — all required bits must already be set in the user's value.

**`src/Acl.php`** (`Gunz\OctaAcl\Acl`) — immutable value object:
- `fromInt(int)` / `toInt()` — construct from and convert to storable integer
- `grants(int ...$permissions)` — ALL given bits must be present
- `grantsAny(int ...$permissions)` — ANY of the given bits must be present
- `withGrant(int ...)` / `withRevoke(int ...)` — return new instances with bits added/removed
- `none()` — factory for the zero-permissions starting point

**`src/AclRegistry.php`** (`Gunz\OctaAcl\AclRegistry`) — named permission map:
- `define(string $name, int $bits)` — registers a name; enforces power-of-2 and uniqueness
- `get(string $name)` — returns the bit value
- `namesFor(Acl $acl)` — returns names of all permissions the Acl grants

**Namespace:** `Gunz\OctaAcl\` maps to `src/` via PSR-4. Tests live in `tests/` under `Gunz\OctaAcl\Tests\`.

**Tests:** PHPUnit 10 with `#[CoversClass]` attributes. Each test class covers exactly one source class. Data providers use `#[DataProvider]` attribute.
