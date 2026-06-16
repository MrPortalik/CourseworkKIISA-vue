<?php

namespace App\Http\Requests\Auth;

use App\Support\UserBlocking;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();
        UserBlocking::refreshStatus($user);

        if (UserBlocking::isActive($user)) {
            $info = UserBlocking::info($user);
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => self::blockedLoginMessage($info),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * @param  array{reason: string, permanent: bool, blocked_until: ?string}  $info
     */
    private static function blockedLoginMessage(array $info): string
    {
        $message = 'Аккаунт заблокирован. Причина: '.$info['reason'];

        if ($info['permanent']) {
            return $message.' Блокировка перманентная.';
        }

        if ($info['blocked_until']) {
            $until = \Carbon\Carbon::parse($info['blocked_until'])->timezone(config('app.timezone'))
                ->format('d.m.Y H:i');

            return $message.' Разблокировка: '.$until.'.';
        }

        return $message;
    }
}
