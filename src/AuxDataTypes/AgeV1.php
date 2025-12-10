<?php
declare(strict_types=1);
namespace FediE2EE\PKD\Extensions\AuxDataTypes;

use FediE2EE\PKD\Extensions\ExtensionInterface;
use Override;

class AgeV1 implements ExtensionInterface
{
    public const AUX_DATA_TYPE = 'age-v1';
    private const KEY_PREFIX = 'age1';
    private const KEY_LENGTH = 62;
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

        // Age keys should contain no whitespace
        if (preg_match('/\s/', $auxData)) {
            $this->lastRejection = 'age public keys cannot contain whitespace';
            return false;
        }

        // Check prefix and basic format
        if (!str_starts_with($auxData, self::KEY_PREFIX)) {
            $this->lastRejection = 'Header is incorrect';
            return false;
        }
        if (strlen($auxData) !== self::KEY_LENGTH) {
            $this->lastRejection = 'Incorrect key length';
            return false;
        }
        $decoded = $this->bech32Decode($auxData);
        return $decoded !== false;
    }

    private function bech32Decode(string $bech): array|false
    {
        // Bech32 character set (standard)
        $charset = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';

        // Find the last occurrence of '1' to separate hrp from data
        $pos = strrpos($bech, '1');
        if ($pos === false || $pos < 1 || $pos + 7 > strlen($bech)) {
            $this->lastRejection = 'found an errant 1 after the header';
            return false;
        }

        $hrp = substr($bech, 0, $pos);
        $data = substr($bech, $pos + 1);

        // Convert data part to values
        $values = [];
        for ($i = 0; $i < strlen($data); $i++) {
            $char = $data[$i];
            $charPos = strpos($charset, $char);
            if ($charPos === false) {
                $this->lastRejection = 'invalid character found at position ' . $i;
                return false;
            }
            $values[] = $charPos;
        }

        // Validate checksum
        if (!$this->bech32VerifyChecksum($hrp, $values)) {
            $this->lastRejection = 'invalid bech32 checksum';
            return false;
        }

        return [
            'hrp' => $hrp,
            'data' => array_slice($values, 0, -6) // Remove 6-char checksum
        ];
    }

    private function bech32VerifyChecksum(string $hrp, array $data): bool
    {
        $values = $this->bech32HrpExpand($hrp);
        $values = array_merge($values, $data);
        return $this->bech32Polymod($values) === 1;
    }

    private function bech32HrpExpand(string $hrp): array
    {
        $result = [];

        // Add high bits of each character
        for ($i = 0; $i < strlen($hrp); $i++) {
            $result[] = ord($hrp[$i]) >> 5;
        }

        // Add separator
        $result[] = 0;

        // Add low bits of each character
        for ($i = 0; $i < strlen($hrp); $i++) {
            $result[] = ord($hrp[$i]) & 31;
        }

        return $result;
    }

    private function bech32Polymod(array $values): int
    {
        $GENERATOR = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
        $chk = 1;

        foreach ($values as $value) {
            $b = ($chk >> 25) & 0xff;
            $chk = (($chk & 0x1ffffff) << 5) ^ $value;

            for ($i = 0; $i < 5; $i++) {
                if (($b >> $i) & 1) {
                    $chk ^= $GENERATOR[$i];
                }
            }
        }

        return $chk;
    }
}
