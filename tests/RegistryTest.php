<?php
declare(strict_types=1);
namespace FediE2EE\PKD\Extensions\Tests;

use FediE2EE\PKD\Extensions\AuxDataTypes\AgeV1;
use FediE2EE\PKD\Extensions\AuxDataTypes\SshV2;
use FediE2EE\PKD\Extensions\ExtensionException;
use FediE2EE\PKD\Extensions\ExtensionInterface;
use FediE2EE\PKD\Extensions\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Registry::class)]
class RegistryTest extends TestCase
{
    /**
     * @throws ExtensionException
     */
    public function testGet(): void
    {
        $registry = new Registry();
        $got = $registry->get('age', [AgeV1::AUX_DATA_TYPE]);
        $this->assertInstanceOf(AgeV1::class, $got);

        $got = $registry->get('ssh', [SshV2::AUX_DATA_TYPE]);
        $this->assertInstanceOf(SshV2::class, $got);

        $this->expectException(ExtensionException::class);
        $registry->get('ssh', [AgeV1::AUX_DATA_TYPE]);
    }

    public function testAddExtension(): void
    {
        $registry = new Registry();
        $custom = new class implements ExtensionInterface {
            public function getAuxDataType(): string
            {
                return 'foo-v1';
            }

            public function getRejectionReason(): string
            {
                return '';
            }

            public function isValid(string $auxData): bool
            {
                return true;
            }
        };
        $registry->addAuxDataType($custom, 'foo');
        $got = $registry->get('foo', ['foo-v1']);
        $this->assertSame('foo-v1', $got->getAuxDataType());
    }

    /**
     * @throws ExtensionException
     */
    public function testAliasBehavior(): void
    {
        $registry = new Registry();
        $age = $registry->lookup('age');
        $this->assertInstanceOf(AgeV1::class, $age);
        $age = $registry->lookup('ssh');
        $this->assertInstanceOf(SshV2::class, $age);

        // What happens when this is wrong?
        $this->expectException(ExtensionException::class);
        $registry->lookup('InvalidType');
    }
}
