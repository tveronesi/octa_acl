<?php

declare(strict_types=1);

namespace Gunz\OctaAcl\Tests;

use Gunz\OctaAcl\Acl;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Acl::class)]
final class AclTest extends TestCase
{
    public function testNoneGrantsNothing(): void
    {
        $acl = Acl::none();

        self::assertFalse($acl->grants(1));
        self::assertFalse($acl->grants(2));
        self::assertFalse($acl->grantsAny(1, 2));
        self::assertSame(0, $acl->toInt());
    }

    public function testFromIntRoundTrip(): void
    {
        self::assertSame(7, Acl::fromInt(7)->toInt());
        self::assertSame(0, Acl::fromInt(0)->toInt());
    }

    public function testGrantsSinglePermission(): void
    {
        $acl = Acl::fromInt(1 | 4); // bits 5

        self::assertTrue($acl->grants(1));
        self::assertTrue($acl->grants(4));
        self::assertFalse($acl->grants(2));
        self::assertFalse($acl->grants(8));
    }

    public function testGrantsRequiresAllBits(): void
    {
        $acl = Acl::fromInt(1 | 4); // has 1 and 4, not 2

        self::assertTrue($acl->grants(1, 4));   // has both
        self::assertFalse($acl->grants(1, 2));  // missing 2
        self::assertFalse($acl->grants(2, 4));  // missing 2
        self::assertFalse($acl->grants(1, 2, 4)); // missing 2
    }

    public function testGrantsZeroIsAlwaysTrue(): void
    {
        // Passing literal 0 is an explicit (if odd) choice — vacuously satisfied
        self::assertTrue(Acl::none()->grants(0));
        self::assertTrue(Acl::fromInt(7)->grants(0));
    }

    public function testGrantsWithNoArgsThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Acl::none()->grants();
    }

    public function testGrantsAnyWithNoArgsThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Acl::none()->grantsAny();
    }

    public function testGrantsAnyReturnsTrueWhenAtLeastOneMatches(): void
    {
        $acl = Acl::fromInt(1);

        self::assertTrue($acl->grantsAny(1, 2));  // has 1
        self::assertTrue($acl->grantsAny(2, 1));  // order irrelevant
    }

    public function testGrantsAnyReturnsFalseWhenNoneMatch(): void
    {
        $acl = Acl::fromInt(1);

        self::assertFalse($acl->grantsAny(2, 4));
    }

    public function testWithGrantAddsPermissions(): void
    {
        $acl = Acl::none()->withGrant(1, 4);

        self::assertTrue($acl->grants(1));
        self::assertTrue($acl->grants(4));
        self::assertFalse($acl->grants(2));
        self::assertSame(5, $acl->toInt());
    }

    public function testWithGrantIsImmutable(): void
    {
        $original = Acl::fromInt(1);
        $expanded = $original->withGrant(4);

        self::assertFalse($original->grants(4)); // original unchanged
        self::assertTrue($expanded->grants(1));
        self::assertTrue($expanded->grants(4));
    }

    public function testWithRevokeRemovesPermissions(): void
    {
        $acl = Acl::fromInt(1 | 2 | 4); // 7

        $result = $acl->withRevoke(1, 4);

        self::assertFalse($result->grants(1));
        self::assertTrue($result->grants(2));
        self::assertFalse($result->grants(4));
        self::assertSame(2, $result->toInt());
    }

    public function testWithRevokeIsImmutable(): void
    {
        $original = Acl::fromInt(1 | 4);
        $reduced  = $original->withRevoke(4);

        self::assertTrue($original->grants(4)); // original unchanged
        self::assertTrue($reduced->grants(1));
        self::assertFalse($reduced->grants(4));
    }

    public function testWithRevokingUngrantedPermissionIsNoop(): void
    {
        $acl    = Acl::fromInt(1);
        $result = $acl->withRevoke(4); // 4 was never granted

        self::assertSame($acl->toInt(), $result->toInt());
    }

    public function testNegativeBitsThrow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Acl::fromInt(-1);
    }

    public function testEquals(): void
    {
        $a = Acl::fromInt(5);
        $b = Acl::fromInt(5);
        $c = Acl::fromInt(3);

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
        self::assertTrue(Acl::none()->equals(Acl::none()));
    }

    public function testWithGrantRejectsNonPowerOfTwo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Acl::none()->withGrant(3);
    }

    public function testWithRevokeRejectsNonPowerOfTwo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Acl::fromInt(7)->withRevoke(6);
    }

    /** @return array<string, array{int}> */
    public static function validBitsProvider(): array
    {
        return [
            'zero'        => [0],
            'power 2^0'   => [1],
            'power 2^1'   => [2],
            'power 2^7'   => [128],
            'combination' => [1 | 2 | 128],
        ];
    }

    #[DataProvider('validBitsProvider')]
    public function testFromIntAcceptsValidBits(int $bits): void
    {
        self::assertSame($bits, Acl::fromInt($bits)->toInt());
    }
}
