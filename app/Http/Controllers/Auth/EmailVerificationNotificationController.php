<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        if (! $request->user()->getPlainEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Для отправки письма нужен сохранённый email. Обновите email в профиле или запросите сброс пароля.'],
            ]);
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::error('Verification mail failed', [
                'user_id' => $request->user()->id,
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['Не удалось отправить письмо. Проверьте настройки почты на сервере.'],
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }
}
