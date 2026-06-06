<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;

class ImageUploadRules
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    private const BLOCKED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'exe', 'bat', 'cmd', 'com', 'sh', 'bash',
        'js', 'html', 'htm', 'svg', 'xml', 'dll', 'msi',
    ];

    public static function rule(string $field, int $maxKilobytes = 5120, bool $required = false): array
    {
        $rules = [
            $required ? 'required' : 'nullable',
            File::types(['jpg', 'jpeg', 'png', 'webp'])
                ->max($maxKilobytes),
            function (string $attribute, mixed $value, \Closure $fail) {
                if (! $value instanceof UploadedFile) {
                    return;
                }

                self::assertSafeImage($value, $fail);
            },
        ];

        return [$field => $rules];
    }

    public static function assertSafeImage(UploadedFile $file, ?\Closure $fail = null): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            self::fail($fail, 'Загружаемый файл должен быть изображением (JPEG, PNG или WebP).');

            return;
        }

        $mime = $file->getMimeType();
        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            self::fail($fail, 'Разрешены только изображения JPEG, PNG и WebP.');

            return;
        }

        if (@getimagesize($file->getRealPath()) === false) {
            self::fail($fail, 'Файл не является корректным изображением.');
        }
    }

    private static function fail(?\Closure $fail, string $message): void
    {
        if ($fail) {
            $fail($message);
        } else {
            throw new \InvalidArgumentException($message);
        }
    }
}
