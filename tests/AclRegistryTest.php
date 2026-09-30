<?php

declare(strict_types=1);

namespace Gunz\OctaAcl\Tests;

use Gunz\OctaAcl\Acl;
use Gunz\OctaAcl\AclRegistry;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AclRegistry::class)]
final class AclRegistryTest extends TestCase
{
    private AclRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = (new AclRegistry())
            ->define('reader',    1)
            ->define('editor',    2)
            ->define('publisher', 4)
            ->define('admin',     8);
    }

    public function testGetKnownPermission(): void
    {
        self::assertSame(1, $this->registry->get('reader'));
        self::assertSame(8, $this->registry->get('admin'));
    }

    public function testGetUnknownPermissionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Undefined permission: 'superadmin'.");

        $this->registry->get('superadmin');
    }

    public function testHas(): void
    {
        self::assertTrue($this->registry->has('editor'));
        self::assertFalse($this->registry->has('superadmin'));
    }

    public function testAllReturnsEveryRegisteredPermission(): void
    {
        self::assertSame(
            ['reader' => 1, 'editor' => 2, 'publisher' => 4, 'admin' => 8],
            $this->registry->all()
        );
    }

    public function testNamesForReturnsGrantedPermissionNames(): void
    {
        $acl = Acl::fromInt(1 | 4); // reader + publisher

        self::assertSame(['reader', 'publisher'], $this->registry->namesFor($acl));
    }

    public function testNamesForWithNoneReturnsEmptyArray(): void
    {
        self::assertSame([], $this->registry->namesFor(Acl::none()));
    }

    public function testNamesForWithAllPermissionsGranted(): void
    {
        $acl = Acl::fromInt(1 | 2 | 4 | 8);

        self::assertSame(['reader', 'editor', 'publisher', 'admin'], $this->registry->namesFor($acl));
    }

    public function testDefineIsFluentAndChainable(): void
    {
        $registry = new AclRegistry();
        $result   = $registry->define('a', 1)->define('b', 2);

        self::assertTrue($result->has('a'));
        self::assertTrue($result->has('b'));
    }

    public function testDefineIsImmutable(): void
    {
        $original = new AclRegistry();
        $extended = $original->define('a', 1);

        self::assertNotSame($original, $extended);
        self::assertFalse($original->has('a'));
        self::assertTrue($extended->has('a'));
    }

    public function testDefineDuplicateNameThrows(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Permission 'editor' is already defined.");

        $this->registry->define('editor', 16);
    }

    /** @return array<string, array{int}> */
    public static function invalidBitsProvider(): array
    {
        return [
            'zero'        => [0],
            'negative'    => [-1],
            'non-power 3' => [3],
            'non-power 5' => [5],
            'non-power 6' => [6],
        ];
    }

    #[DataProvider('invalidBitsProvider')]
    public function testDefineNonPowerOfTwoThrows(int $bits): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new AclRegistry())->define('bad', $bits);
    }

    public function testDefineDuplicateBitValueThrows(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Permission bit 8 is already used by 'admin'.");

        $this->registry->define('superadmin', 8);
    }
}
