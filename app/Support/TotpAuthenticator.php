<?php

namespace App\Support;

class TotpAuthenticator
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random 16-character Base32 secret key (80 bits) for Google Authenticator
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $max = strlen(self::BASE32_ALPHABET) - 1;
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_ALPHABET[random_int(0, $max)];
        }
        return $secret;
    }

    /**
     * Build standard otpauth:// URI for Google Authenticator / Authy QR scanning
     */
    public static function getOtpAuthUri(string $accountEmail, string $secret, string $issuer = 'DocVerify IPPTI'): string
    {
        $label = rawurlencode($issuer . ':' . $accountEmail);
        $query = http_build_query([
            'secret' => strtoupper(trim($secret)),
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);
        return "otpauth://totp/{$label}?{$query}";
    }

    /**
     * Verify a 6-digit TOTP code against a Base32 secret (with ±1 30-second window tolerance)
     */
    public static function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $cleanCode = preg_replace('/[^0-9]/', '', trim($code));
        if (strlen($cleanCode) !== 6) {
            return false;
        }

        $secretBytes = self::base32Decode($secret);
        if ($secretBytes === '') {
            return false;
        }

        $currentTimeSlice = (int)floor(time() / 30);

        for ($i = -$window; $i <= $window; $i++) {
            $calculated = self::calculateCode($secretBytes, $currentTimeSlice + $i);
            if (hash_equals($calculated, $cleanCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate 6-digit TOTP for a given time slice (RFC 6238 / RFC 4226)
     */
    private static function calculateCode(string $secretBytes, int $timeSlice): string
    {
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretBytes, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $hashPart = substr($hash, $offset, 4);
        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        return str_pad((string)($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode a Base32 string into raw binary bytes
     */
    private static function base32Decode(string $base32): string
    {
        $base32 = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $base32));
        if ($base32 === '') {
            return '';
        }

        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        $len = strlen($base32);
        for ($i = 0; $i < $len; $i++) {
            $val = strpos(self::BASE32_ALPHABET, $base32[$i]);
            if ($val === false) {
                continue;
            }
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
