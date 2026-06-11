<?php

namespace App\Listeners;

use App\Events\GoogleLoginCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogGoogleLoginListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(GoogleLoginCompleted $event): void
    {
        Log::debug($event->user->email . 'がGoogle認証でログインしました');
    }
}
