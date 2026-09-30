<?php

declare(strict_types=1);

namespace Gunz\OctaAcl;

/**
 * Maps named permissions to their power-of-2 bit values.
 */
final class AclRegistry
{
    /** @var array<string, int> */
    private array $permissions = [];

    /**
     * Registers a named permission.
     *
     * @param string $name Unique permission name (e.g. 'admin', 'editor')
     * @param int    $bits A positive power-of-2 integer: 1, 2, 4, 8, …
     *
     * @throws \InvalidArgumentException if $bits is not a positive power of 2
     * @throws \LogicException           if the name is already registered
     */
    public function define(string $name, int $bits): static
    {
        if ($bits <= 0 || ($bits & ($bits - 1)) !== 0) {
            throw new \InvalidArgumentException(
                sprintf("Permission bits must be a positive power of 2, %d given for '%s'.", $bits, $name)
            );
        }

        if (isset($this->permissions[$name])) {
            throw new \LogicException(sprintf("Permission '%s' is already defined.", $name));
        }

        $existingName = array_search($bits, $this->permissions, true);
        if ($existingName !== false) {
            throw new \LogicException(sprintf(
                "Permission bit %d is already used by '%s'.",
                $bits,
                $existingName
            ));
        }

        $clone = clone $this;
        $clone->permissions[$name] = $bits;

        return $clone;
    }

    /**
     * Returns the bit value for a named permission.
     *
     * @throws \InvalidArgumentException if the name is not registered
     */
    public function get(string $name): int
    {
        return $this->permissions[$name]
            ?? throw new \InvalidArgumentException(sprintf("Undefined permission: '%s'.", $name));
    }

    public function has(string $name): bool
    {
        return isset($this->permissions[$name]);
    }

    /**
     * Returns all registered permissions as name => bits.
     *
     * @return array<string, int>
     */
    public function all(): array
    {
        return $this->permissions;
    }

    /**
     * Returns the names of all permissions granted by the given Acl.
     *
     * @return list<string>
     */
    public function namesFor(Acl $acl): array
    {
        return array_keys(array_filter($this->permissions, $acl->grants(...)));
    }
}
