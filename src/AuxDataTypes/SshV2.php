<?php
declare(strict_types=1);
namespace FediE2EE\PKD\Extensions\AuxDataTypes;

use FediE2EE\PKD\Extensions\ExtensionInterface;
use Override;

class SshV2 implements ExtensionInterface
{
    public const AUX_DATA_TYPE = 'ssh-v2';
    private const VALID_KEY_TYPES = [
        'ssh-rsa',
        'ssh-ed25519',
        'ecdsa-sha2-nistp256',
        'ecdsa-sha2-nistp384',
        'ecdsa-sha2-nistp521',
    ];

    private string $lastRejection = '';

    #[Override]
    public function getAuxDataType(): string
    {
        return self::AUX_DATA_TYPE;
    }

    #[Override]
    public function getRejectionReason(): string
    {
        return $this->lastRejection;
    }

    #[Override]
    public function isValid(string $auxData): bool
    {
        $this->lastRejection = '';
        $auxData = trim($auxData);

        if (empty($auxData)) {
            $this->lastRejection = 'Empty aux data provided';
            return false;
        }

        // Split by whitespace
        $parts = preg_split('/\s+/', $auxData, 3);

        // Must have exactly key type and base64 data, no comments
        if (count($parts) !== 2) {
            $this->lastRejection = 'SSH keys are expected to have 2 parts; no comments';
            return false;
        }

        $keyType = $parts[0];
        $keyData = $parts[1];

        // Validate key type
        if (!in_array($keyType, self::VALID_KEY_TYPES, true)) {
            $this->lastRejection = 'Invalid ssh key tyoe given (DSA is not allowed)';
            return false;
        }

        // Validate base64 encoding
        if (!$this->isValidBase64($keyData)) {
            $this->lastRejection = 'Invalid base64';
            return false;
        }

        // Optionally validate the decoded key structure
        return $this->isValidKeyStructure($keyType, $keyData);
    }

    private function isValidBase64(string $data): bool
    {
        if (!preg_match('/^[A-Za-z0-9+\/]*={0,2}$/', $data)) {
            return false;
        }
        $decoded = base64_decode($data, true);
        return $decoded !== false;
    }

    private function isValidKeyStructure(string $keyType, string $keyData): bool
    {
        $decoded = base64_decode($keyData, true);
        if ($decoded === false || strlen($decoded) < 4) {
            $this->lastRejection = 'An error occured while decoding the base64';
            return false;
        }

        $typeLength = unpack('N', substr($decoded, 0, 4))[1];

        if ($typeLength > strlen($decoded) - 4 || $typeLength > 50) {
            $this->lastRejection = 'Type length was invalid';
            return false;
        }

        $declaredType = substr($decoded, 4, $typeLength);

        if ($keyType === 'ssh-rsa') {
            return $this->isValidRsaKeyStructure($decoded, $typeLength);
        }

        $result = hash_equals($declaredType, $keyType);
        if (!$result) {
            $this->lastRejection = 'Declared type did not match key type';
        }
        return $result;
    }

    private function isValidRsaKeyStructure(string $decoded, int $typeLength): bool
    {
        $offset = 4 + $typeLength;

        if ($offset + 4 > strlen($decoded)) {
            $this->lastRejection = 'Public exponent encoded length is too long';
            return false;
        }
        $exponentLength = unpack('N', substr($decoded, $offset, 4))[1];
        $offset += 4;

        // Validate exponent length
        if ($exponentLength > 4 || $exponentLength < 1 || $offset + $exponentLength > strlen($decoded)) {
            return false;
        }

        // Read and validate public exponent (must be 65537)
        $exponentBytes = substr($decoded, $offset, $exponentLength);
        $exponent = 0;
        for ($i = 0; $i < $exponentLength; $i++) {
            $exponent = ($exponent << 8) | ord($exponentBytes[$i]);
        }

        if ($exponent !== 65537) {
            $this->lastRejection = 'Only public exponents e=65537 are allowed.';
            return false;
        }

        $offset += $exponentLength;

        if ($offset + 4 > strlen($decoded)) {
            $this->lastRejection = 'Error reading modulus length';
            return false;
        }
        $modulusLength = unpack('N', substr($decoded, $offset, 4))[1];

        // Validate modulus is at least 2048-bit (256 bytes)
        if ($modulusLength < 256) {
            $this->lastRejection = 'Modulus must be at least 2048-bit (256 bytes)';
            return false;
        }
        return true;
    }
}
