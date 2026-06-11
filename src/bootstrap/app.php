<?php

use App\Exceptions\ApiException;
use App\Exceptions\GoogleAccountMismatchException;
use App\Exceptions\GoogleAuthenticationException;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Support\AppLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'guest' => RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /** 認証例外 */
        $exceptions->render(function (AuthenticationException $e, $request) {
            
            $guard = $e->guards()[0] ?? null;

            $login = match($guard) {
            'web_superuser' => '/superuser/login',
            'web_admin' => '/admin/login',
            'web_owner' => '/owner/login',
            default => '/login'
            };

            return $request->expectsJson()
                    ? response()->json([
                        'success' => false,
                        'code'    => 'UNAUTHENTICATED',
                        'message' => 'ログインしていません'
                    ], 401)
                    : redirect()->guest($login);
        });

        // Google認証エラー
        $exceptions->render(function (GoogleAuthenticationException|GoogleAccountMismatchException $e, $request) {

            return to_route('login')->with([
                'status' => 'alert',
                'message' => $e->getMessage()
            ]);
        });

        /** バリデーション例外 API */
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code'    => 'VALIDATION_ERROR',
                    'message' => '入力内容に誤りがあります',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        /** リソースが見つからない API */
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code'    => 'NOT_FOUND',
                    'message' => 'データが見つかりません',
                ], 404);
            }
        });

        /** 権限が無い場合 */
        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, $request) {
            // if ($request->is('api/*')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code'    => 'FORBIDDEN',
                    'message' => 'この操作を行う権限がありません',
                ], 403);
            }
        });

        /** アプリケーションエラー */
        $exceptions->render(function (ApiException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => $e->errorCode,
                    'message' => $e->getMessage()
                ], $e->status);
            }
        });

        /** その他（500）API */
        $exceptions->render(function (\Throwable $e, $request) {
            AppLog::error('システムエラー', $e);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code'    => 'SERVER_ERROR',
                    'message' => app()->environment('local')
                        ? $e->getMessage()
                        : 'サーバーエラーが発生しました',
                ], 500);
            }
        });

    })->create();
