# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Running tests

**Always use Docker.** A `Dockerfile` is already defined in the project root — use it rather than installing PHP or Composer locally.

```bash
docker build -t octa-acl-test .
docker run --rm octa-acl-test
```

## Commands

```bash
# Run all tests (Docker — no local PHP or Composer required)
docker build -t octa-acl-test .
docker run --rm octa-acl-test

# Install dependencies (local PHP)
composer install

# Run all tests (local)
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
- `grants(int ...$permissions)` — ALL given bits must be present; throws if called with no arguments
- `grantsAny(int ...$permissions)` — ANY of the given bits must be present; throws if called with no arguments
- `withGrant(int ...)` / `withRevoke(int ...)` — return new instances with bits added/removed; each bit must be a positive power of 2
- `equals(Acl $other)` — value equality (same bits); use instead of `===` which compares object identity
- `none()` — factory for the zero-permissions starting point

**`src/AclRegistry.php`** (`Gunz\OctaAcl\AclRegistry`) — named permission map, immutable:
- `define(string $name, int $bits)` — returns a **new instance** with the permission added; enforces power-of-2, name uniqueness, and bit value uniqueness
- `get(string $name)` — returns the bit value
- `has(string $name)` — checks if a name is registered
- `all()` — returns all `name => bits` pairs
- `namesFor(Acl $acl)` — returns names of all permissions the Acl grants

**Namespace:** `Gunz\OctaAcl\` maps to `src/` via PSR-4. Tests live in `tests/` under `Gunz\OctaAcl\Tests\`.

**Tests:** PHPUnit 10 with `#[CoversClass]` attributes. Each test class covers exactly one source class. Data providers use `#[DataProvider]` attribute.
