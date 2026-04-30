<?php
namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppLog
{
  public static function error(string $message, Throwable $e, array $context = []): void
  {
    Log::error($message, array_merge($context, [
      'error_message' => $e->getMessage(),
      'file' => $e->getFile(),
      'line' => $e->getLine(),
      'user_id'   => Auth::id(),
      'ip'        => request()->ip(),
      'url'       => request()->fullUrl(),
      'method'    => request()->method(),
    ]));
  }
}