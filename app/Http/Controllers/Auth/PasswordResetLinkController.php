<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PasswordResetLinkController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = mb_strtolower(trim($request->input('email')));
        $user = User::findByEmail($email);

        if ($user && ! $user->ensureEncryptedEmail($email)) {
            throw ValidationException::withMessages([
                'email' => ['Не удалось подготовить адрес почты для этого аккаунта. Обратитесь в поддержку.'],
            ]);
        }

        try {
            $status = Password::sendResetLink(['email' => $email]);
        } catch (Throwable $exception) {
            Log::error('Password reset mail failed', [
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['Не удалось отправить письмо. Проверьте настройки почты на сервере или попробуйте позже.'],
            ]);
        }

        if ($status == Password::RESET_LINK_SENT) {
            $user = User::findByEmail($email);

            if (! $user?->getPlainEmail()) {
                throw ValidationException::withMessages([
                    'email' => ['Письмо не отправлено: для аккаунта не сохранён адрес email. Обновите email в профиле или обратитесь к администратору.'],
                ]);
            }

            return back()->with('status', 'Ссылка для сброса пароля отправлена на ваш email.');
        }

        throw ValidationException::withMessages([
            'email' => [self::passwordResetErrorMessage($status)],
        ]);
    }

    private static function passwordResetErrorMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'Пользователь с таким email не найден.',
            Password::RESET_THROTTLED => 'Подождите перед повторным запросом ссылки.',
            default => 'Не удалось отправить ссылку для сброса пароля.',
        };
    }
}
