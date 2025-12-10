<?php
declare(strict_types=1);
namespace FediE2EE\PKD\Extensions\Tests\AuxDataTypes;

use FediE2EE\PKD\Extensions\AuxDataTypes\SshV2;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SshV2::class)]
class SshV2Test extends TestCase
{
    private SshV2 $validator;

    protected function setUp(): void
    {
        $this->validator = new SshV2();
    }

    public function testValidRsaKey(): void
    {
        // 3072-bit RSA key, freshly generated:
        $key = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQCk2yWX7mRMkc/2MA/LoK09B5XvpnWrXitbzyDzvG3Iqx2k65bnIFeZnmfM1tpFY22crVGgMcpz9R55r3LvLTK1SfwODojy1VPefvnpG0CA3Sdy0GnEJlL4ugMkXLNkGn3xwsvEutLq1+qGepMkVBwJ14vW7sAJ+7cXeypOn8evOVuO9mN4iMvGl/oqfBSmR7U2BGR72TFzp6TvJA+5ZGO6ZU7d/GjF7jh/F6X4TzB+gkANTvN8rE3YppOqbUbWRowghNSHUaJKDBv3R4V/QWuv5j+UWYNjC8quIGkR/8HErovS1lGBwDH3rBpQ9iCh/TQZT51gBGWtwzYWix+uHZM5ihdNTchfitH9MTE8ya60dpi8BUDWPA0tvc3SEFAxjGRN6wX6Cly/YAwxwdewsQ796ZU7n0yJ16HegQ3jzrFXA+lji28a1lTj9GcRp8I2vRzCAqKeE1kbagZqN7MQAxPTwYMjtoZFWUxm7UyAvXU+4SbMe4eifi8bTOs0RD3kI6E=';
        $this->assertTrue($this->validator->isValid($key));
    }

    public function testWeakRsaKeyRejected(): void
    {
        // 1024-bit RSA public key, too weak:
        $key = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAAAgQDeZjDFiSGM8g3bkLw8mFqrmuZr2zhSfBUVM1waij0btM5yUHqTBGir7c4FQFOMtpLKRWs76Ib2YbV/N/IKMyYvEGkAnBtR+i3QAwyLYFL0aM8b5h4qif3K1z36HC9trBIHKW1njyEeob+JwCXMdyVx1hHO4Z1ihB4CN7k2j4VgWw==';
        $this->assertFalse($this->validator->isValid($key));

        // 3072-bit RSA public key, but e=3 rather than e=65537
        $key2 = 'ssh-rsa AAAAB3NzaC1yc2EAAAABAwAAAYEAxIIZDttOiIfeD998NBAy40NWw5t42GTAF5AfgrE+B/fr+Bpbi5+OF4HdQ2msJM86RFqx2+8rlG8ffai/Q2kMJ4XmUF+274fVeumNj7g70qwX9ED9yBH6eX0HrUpBOtj0IbJEQDNsOOMvBv9ANJwjg1CdrpK+14ezAtQ3Yz3IZqBlBxrj2NzYs3X9slSkjG9o8awVRUtsP97XiYhWOywCNZSNvXMigOWf+xqjLW4jmFTYOPVBduqq94AI8B43RViZ5m305ffeK5+LTBo9ZTXzjjlf/0f1bujVrrq4JdCGLy+++wm868EmELukcW1v8Tb2JbuW6nqcNRYOVIqsOOTcwU0sXUuzGw2xtEOE1FYWCHE0GyvZ/ec6ZN2CF6vBfTcunCW7hB7kUMSDFw79G6lprDXEyyd3O1/rQJFYy1C7gpI/LNzJ52OpAI06XRYUU1z8BiCPoidWkrdJAJ8L0AjlNCMIrk2b3FJV/ZcA3ysl9G4ch+tomwWRrlieAa0UZoGN';
        $this->assertFalse($this->validator->isValid($key2));
    }

    public function testValidEd25519Key(): void
    {
        $key = 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIAfqfxnT/L5vcsF';
        $this->assertTrue($this->validator->isValid($key));
    }

    public function testValidEcdsaKey(): void
    {
        $key = 'ecdsa-sha2-nistp256 AAAAE2VjZHNhLXNoYTItbmlzdHAyNTYAAAAIbmlzdHAyNTYAAABBBGhlyE2yNxuenfqVcqqVpH';
        $this->assertTrue($this->validator->isValid($key));
    }

    public function testInvalidKeyTypeIsRejected(): void
    {
        $key = 'ssh-invalid AAAAB3NzaC1yc2EAAAADAQABAAABgQC7VJTUt9Us8cKjMzEfYyji';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testInvalidBase64IsRejected(): void
    {
        $key = 'ssh-rsa !!!invalid!!!base64!!!';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testMissingKeyDataIsRejected(): void
    {
        $key = 'ssh-rsa';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testCommentIsRejected(): void
    {
        $key = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC7VJTUt9Us8cKjMzEfYyjiWA4 user@hostname';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testEmptyStringIsRejected(): void
    {
        $this->assertFalse($this->validator->isValid(''));
    }

    public function testWhitespaceOnlyIsRejected(): void
    {
        $this->assertFalse($this->validator->isValid('   '));
    }

    public function testWhitespaceIsTrimmed(): void
    {
        $key = '  ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIAfqfxnT/L5vcsF  ';
        $this->assertTrue($this->validator->isValid($key));
    }

    public function testInvalidKeyStructureIsRejected(): void
    {
        // Valid base64 but doesn't decode to valid OpenSSH structure
        $key = 'ssh-rsa aGVsbG8gd29ybGQ=';
        $this->assertFalse($this->validator->isValid($key));
    }

    /**
     * We explicitly do not support DSA keys.
     */
    public function testDsaKeyIsRejected(): void
    {
        $key = 'ssh-dss AAAAB3NzaC1kc3MAAACBAIqKj4iKj4iKj4iKj4iKj4iKj4iKj4i';
        $this->assertFalse($this->validator->isValid($key));
    }
}
