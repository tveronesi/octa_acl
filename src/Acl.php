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
        if (count($permissions) === 0) {
            throw new \InvalidArgumentException('grants() requires at least one permission.');
        }

        $required = array_reduce($permissions, static fn(int $c, int $p) => $c | $p, 0);

        return ($required | $this->bits) === $this->bits;
    }

    /**
     * Returns true if ANY of the given permission bits are granted.
     */
    public function grantsAny(int ...$permissions): bool
    {
        if (count($permissions) === 0) {
            throw new \InvalidArgumentException('grantsAny() requires at least one permission.');
        }

        $mask = array_reduce($permissions, static fn(int $c, int $p) => $c | $p, 0);

        return ($this->bits & $mask) !== 0;
    }

    /**
     * Returns a new Acl with the given permissions added.
     */
    public function withGrant(int ...$permissions): static
    {
        array_walk($permissions, static fn(int $p) => self::assertValidBit($p));
        $bits = array_reduce($permissions, static fn(int $c, int $p) => $c | $p, $this->bits);

        return new static($bits);
    }

    /**
     * Returns a new Acl with the given permissions removed.
     */
    public function withRevoke(int ...$permissions): static
    {
        array_walk($permissions, static fn(int $p) => self::assertValidBit($p));
        $mask = array_reduce($permissions, static fn(int $c, int $p) => $c | $p, 0);

        return new static($this->bits & ~$mask);
    }

    private static function assertValidBit(int $bit): void
    {
        if ($bit <= 0 || ($bit & ($bit - 1)) !== 0) {
            throw new \InvalidArgumentException(
                sprintf('Permission bit must be a positive power of 2, %d given.', $bit)
            );
        }
    }

    public function equals(Acl $other): bool
    {
        return $this->bits === $other->bits;
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
