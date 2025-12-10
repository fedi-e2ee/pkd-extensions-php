<?php
declare(strict_types=1);
namespace FediE2EE\PKD\Extensions\Tests;

use FediE2EE\PKD\Extensions\AuxDataTypes\AgeV1;
use FediE2EE\PKD\Extensions\AuxDataTypes\SshV2;
use FediE2EE\PKD\Extensions\ExtensionException;
use FediE2EE\PKD\Extensions\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Registry::class)]
class RegistryTest extends TestCase
{
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
