<?php

namespace App\Http\Middleware;

use App\Support\UserBlocking;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $user = UserBlocking::refreshStatus($user);

            if (UserBlocking::isActive($user)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with('block_error', UserBlocking::info($user));
            }
        }

        return $next($request);
    }
}
