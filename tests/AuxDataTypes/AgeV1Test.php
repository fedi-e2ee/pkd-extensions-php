<?php
declare(strict_types=1);
namespace FediE2EE\PKD\Extensions\Tests\AuxDataTypes;

use FediE2EE\PKD\Extensions\AuxDataTypes\AgeV1;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AgeV1::class)]
class AgeV1Test extends TestCase
{
    private AgeV1 $validator;

    protected function setUp(): void
    {
        $this->validator = new AgeV1;
    }

    public function testValidAgeKey(): void
    {
        // Valid age key with correct checksum
        $key = 'age1ql3z7hjy54pw3hyww5ayyfg7zqgvc7w3j2elw8zmrj2kg5sfn9aqmcac8p';
        $this->assertTrue($this->validator->isValid($key));
    }

    public function testAnotherValidAgeKey(): void
    {
        $key = 'age1lggyhqrw2nlhcxprm67z43rta597azn8gknawjehu9d9dl0jq3yqqvfafg';
        $this->assertTrue($this->validator->isValid($key));
    }

    public function testMissingAge1PrefixIsRejected(): void
    {
        $key = 'gde3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73sknmamu';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testWrongPrefixIsRejected(): void
    {
        $key = 'bc1qde3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testIncorrectLengthIsRejected(): void
    {
        $key = 'age1gde3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testTooLongIsRejected(): void
    {
        $key = 'age1gde3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73sknmamuxxx';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testInvalidBech32CharacterIsRejected(): void
    {
        // Contains 'b' which is not valid in Bech32
        $key = 'age1bde3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73sknmamu';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testInvalidBech32CharacterOIsRejected(): void
    {
        // Contains 'o' which is not valid in Bech32
        $key = 'age1ode3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73sknmamu';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testInvalidBech32CharacterIIsRejected(): void
    {
        // Contains 'i' which is not valid in Bech32
        $key = 'age1ide3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73sknmamu';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testUppercaseIsRejected(): void
    {
        $key = 'age1GDE3NCMAHLQXYHELR7HCJVC54WTP2NVSQ33PRU3F5DXNZXVU73SKNMAMU';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testWhitespaceIsRejected(): void
    {
        $key = 'age1 gde3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73sknmamu';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testLeadingWhitespaceIsRejected(): void
    {
        $key = '  age1gde3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73sknmamu';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testTrailingWhitespaceIsRejected(): void
    {
        $key = 'age1gde3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73sknmamu  ';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testInvalidChecksumIsRejected(): void
    {
        // Valid format and characters, but invalid checksum
        $key = 'age1gde3ncmahlqxyhelr7hcjvc54wtp2nvsq33pru3f5dxnzxvu73sknmamz';
        $this->assertFalse($this->validator->isValid($key));
    }

    public function testEmptyStringIsRejected(): void
    {
        $this->assertFalse($this->validator->isValid(''));
    }
}
