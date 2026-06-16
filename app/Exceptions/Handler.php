<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response
    {
        $response = parent::render($request, $e);

        if ($response->getStatusCode() === 419) {
            return back()->with([
                'message' => 'Страница устарела, обновите её и попробуйте снова.',
            ]);
        }

        if ($request->expectsJson()) {
            return $response;
        }

        $status = $response->getStatusCode();

        if ($status === 405 && $request->isMethod('GET')) {
            return Inertia::render('Errors/Show', ['status' => 404])
                ->toResponse($request)
                ->setStatusCode(404);
        }

        if (! in_array($status, [403, 404, 500, 503], true)) {
            return $response;
        }

        return Inertia::render('Errors/Show', ['status' => $status])
            ->toResponse($request)
            ->setStatusCode($status);
    }
}
