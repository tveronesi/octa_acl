# OctaAcl

Lightweight, framework-agnostic bitmask ACL library for PHP.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4.svg)](https://php.net)

## How it works

Each permission is a distinct power-of-2 integer. A user's ACL is the **bitwise OR** of all their permissions. Checking access uses the identity `($required | $userBits) === $userBits`.

| User bits | Required | OR result | Access |
|-----------|----------|-----------|--------|
| `0101`    | `0100`   | `0101`    | ✅     |
| `0101`    | `0010`   | `0111`    | ❌     |

## Requirements

PHP 8.1+. No additional dependencies.

## Installation

```bash
composer require gunz/octa-acl
```

## Quick start

```php
use Gunz\OctaAcl\Acl;

const EDITOR    = 1;  // 0001
const PUBLISHER = 2;  // 0010
const ADMIN     = 4;  // 0100

// Load a user's ACL from storage (database, session, etc.)
$acl = Acl::fromInt($user->aclBits); // e.g. 5 = EDITOR | ADMIN

$acl->grants(EDITOR);            // true
$acl->grants(ADMIN);             // true
$acl->grants(PUBLISHER);         // false

$acl->grants(EDITOR, ADMIN);     // true  — both required, both present
$acl->grants(EDITOR, PUBLISHER); // false — PUBLISHER not granted

$acl->grantsAny(PUBLISHER, ADMIN); // true — at least one present
```

## Defining permissions

Use any positive power-of-2 integer constants: 1, 2, 4, 8, 16, …

```php
const ROLE_READER    = 1;
const ROLE_EDITOR    = 2;
const ROLE_PUBLISHER = 4;
const ROLE_ADMIN     = 8;
const ROLE_OWNER     = 16;
```

Assign a user their ACL as the bitwise OR of all their roles, and store the integer:

```php
// Editor who is also a publisher
$aclValue = ROLE_EDITOR | ROLE_PUBLISHER; // 6 — persist this integer
```

## Named permissions with AclRegistry

`AclRegistry` maps string names to bit values and can introspect an `Acl`:

```php
use Gunz\OctaAcl\Acl;
use Gunz\OctaAcl\AclRegistry;

$registry = (new AclRegistry())
    ->define('reader',    1)
    ->define('editor',    2)
    ->define('publisher', 4)
    ->define('admin',     8);

// Build an Acl from named permissions
$bits = $registry->get('editor') | $registry->get('publisher');
$acl  = Acl::fromInt($bits); // 6

$acl->grants($registry->get('editor'));    // true
$acl->grants($registry->get('admin'));     // false

// Which permissions does this user have?
$registry->namesFor($acl); // ['editor', 'publisher']
```

`define()` enforces that each value is a positive power of 2, that names are unique, and that bit values are unique — it throws `\InvalidArgumentException` or `\LogicException` otherwise.

`AclRegistry` is immutable: every `define()` call returns a **new instance** with the added permission. The original registry is never modified, so chaining is safe and sharing a base registry across modules is side-effect-free:

```php
$base  = (new AclRegistry())->define('reader', 1)->define('editor', 2);
$admin = $base->define('admin', 8); // new instance — $base is unchanged

$base->has('admin');  // false
$admin->has('admin'); // true
```

## Storing and loading

`Acl` is a value object. Persist the integer; reconstruct it on every request.

```php
// Persist after granting a permission
$newBits = Acl::fromInt($user->aclBits)->withGrant(ROLE_EDITOR)->toInt();
$db->update('users', ['acl_bits' => $newBits], ['id' => $user->id]);

// Load
$acl = Acl::fromInt($user->aclBits);

// Session example
$_SESSION['acl'] = $acl->toInt();
$acl = Acl::fromInt((int) ($_SESSION['acl'] ?? 0));
```

## Modifying permissions

`Acl` is immutable — `withGrant()` and `withRevoke()` return new instances:

```php
$base     = Acl::fromInt(ROLE_EDITOR);
$elevated = $base->withGrant(ROLE_ADMIN);

$base->grants(ROLE_ADMIN);      // false — original unchanged
$elevated->grants(ROLE_ADMIN);  // true

$demoted = $elevated->withRevoke(ROLE_ADMIN);
$demoted->grants(ROLE_ADMIN);   // false
```

Both methods accept multiple permissions at once:

```php
$acl = Acl::none()->withGrant(ROLE_READER, ROLE_EDITOR, ROLE_PUBLISHER);
$acl = $acl->withRevoke(ROLE_READER, ROLE_PUBLISHER);
// $acl now grants only ROLE_EDITOR
```

Both `withGrant()` and `withRevoke()` validate that every bit is a positive power of 2 and throw `\InvalidArgumentException` otherwise — the same constraint `AclRegistry::define()` enforces.

## API reference

### `Acl`

| Method | Description |
|--------|-------------|
| `Acl::none()` | Returns an Acl with no permissions. |
| `Acl::fromInt(int $bits)` | Reconstructs an Acl from a stored integer. |
| `grants(int ...$permissions): bool` | `true` if **all** given bits are granted. Requires at least one argument. |
| `grantsAny(int ...$permissions): bool` | `true` if **any** given bits are granted. Requires at least one argument. |
| `withGrant(int ...$permissions): static` | New Acl with permissions added. Each bit must be a positive power of 2. |
| `withRevoke(int ...$permissions): static` | New Acl with permissions removed. Each bit must be a positive power of 2. |
| `equals(Acl $other): bool` | `true` if both instances hold the same bit value. |
| `toInt(): int` | Raw integer for storage. |

### `AclRegistry`

| Method | Description |
|--------|-------------|
| `define(string $name, int $bits): static` | Returns a new registry with the permission added. Enforces positive power-of-2 and uniqueness of both name and bit value. |
| `get(string $name): int` | Returns the bit value for a name. |
| `has(string $name): bool` | Checks whether a name is registered. |
| `all(): array` | Returns all registered `name => bits` pairs. |
| `namesFor(Acl $acl): list<string>` | Returns names of all permissions the Acl grants. |

## Running tests

### With Docker (no local PHP or Composer required)

```bash
docker build -t octa-acl-test .
docker run --rm octa-acl-test
```

### With Composer

```bash
composer install
composer test
```

## License

MIT. See [LICENSE](LICENSE).
