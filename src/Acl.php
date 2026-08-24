<?php

declare(strict_types=1);

namespace Gunz\OctaAcl;

/**
 * Immutable bitmask ACL value object.
 *
 * Each permission is a power-of-2 integer. A user's ACL is the bitwise OR of
 * all their permissions. Authorization check: ($required | $bits) === $bits.
 */
final class Acl
{
    public function __construct(private readonly int $bits = 0)
    {
        if ($bits < 0) {
            throw new \InvalidArgumentException(
                sprintf('ACL bits must be a non-negative integer, %d given.', $bits)
            );
        }
    }

    /**
     * Returns true if ALL of the given permission bits are granted.
     */
    public function grants(int ...$permissions): bool
    {
        $required = array_reduce($permissions, static fn(int $c, int $p) => $c | $p, 0);

        return ($required | $this->bits) === $this->bits;
    }

    /**
     * Returns true if ANY of the given permission bits are granted.
     */
    public function grantsAny(int ...$permissions): bool
    {
        $mask = array_reduce($permissions, static fn(int $c, int $p) => $c | $p, 0);

        return ($this->bits & $mask) !== 0;
    }

    /**
     * Returns a new Acl with the given permissions added.
     */
    public function withGrant(int ...$permissions): static
    {
        $bits = array_reduce($permissions, static fn(int $c, int $p) => $c | $p, $this->bits);

        return new static($bits);
    }

    /**
     * Returns a new Acl with the given permissions removed.
     */
    public function withRevoke(int ...$permissions): static
    {
        $mask = array_reduce($permissions, static fn(int $c, int $p) => $c | $p, 0);

        return new static($this->bits & ~$mask);
    }

    /**
     * Returns the raw integer for storage (database, session, etc.).
     */
    public function toInt(): int
    {
        return $this->bits;
    }

    public static function fromInt(int $bits): static
    {
        return new static($bits);
    }

    /**
     * Returns an Acl with no permissions granted.
     */
    public static function none(): static
    {
        return new static(0);
    }
}
