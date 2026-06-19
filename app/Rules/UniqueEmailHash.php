<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueEmailHash implements ValidationRule
{
    public function __construct(private ?int $ignoreUserId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = User::where('email', User::hashEmail((string) $value));

        if ($this->ignoreUserId) {
            $query->where('id', '!=', $this->ignoreUserId);
        }

        if ($query->exists()) {
            $fail('Пользователь с таким email уже зарегистрирован.');
        }
    }
}
