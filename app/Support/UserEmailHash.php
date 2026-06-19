<?php

namespace App\Support;

class UserEmailHash
{
    public static function hash(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)));
    }

    public static function isHashed(?string $value): bool
    {
        return is_string($value) && preg_match('/^[a-f0-9]{64}$/', $value) === 1;
    }

    /**
     * Получить plain-text email из значения в БД (plain, Laravel encrypt или уже hash).
     */
    public static function resolvePlain(?string $storedEmail, ?string $storedEmailHash = null): ?string
    {
        if (! $storedEmail) {
            return null;
        }

        if (self::isHashed($storedEmail)) {
            return null;
        }

        if (self::isEncryptedPayload($storedEmail)) {
            try {
                return decrypt($storedEmail);
            } catch (\Throwable) {
                // Не удалось расшифровать — резерв из email_hash невозможен (это тоже hash).
            }
        }

        if (filter_var($storedEmail, FILTER_VALIDATE_EMAIL)) {
            return $storedEmail;
        }

        return null;
    }

    public static function isEncryptedPayload(string $value): bool
    {
        if (self::isHashed($value) || str_contains($value, '@')) {
            return false;
        }

        try {
            decrypt($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function targetHash(?string $storedEmail, ?string $storedEmailHash = null): ?string
    {
        if ($storedEmail && self::isHashed($storedEmail)) {
            return $storedEmail;
        }

        $plain = self::resolvePlain($storedEmail, $storedEmailHash);

        if ($plain !== null) {
            return self::hash($plain);
        }

        if ($storedEmailHash && self::isHashed($storedEmailHash)) {
            return $storedEmailHash;
        }

        return null;
    }

    public static function forDisplay(?string $stored): ?string
    {
        if (! $stored || self::isHashed($stored)) {
            return null;
        }

        return $stored;
    }

    public static function displayLabel(?string $stored): string
    {
        return self::forDisplay($stored) ?? 'скрыт';
    }
}
