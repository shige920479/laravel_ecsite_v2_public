<?php

namespace App\Listeners;

use App\Events\GoogleAccountLinked;
use App\Notifications\Auth\GoogleAccountLinkedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendGoogleLinkedMailListener implements ShouldQueue
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
    public function handle(GoogleAccountLinked $event): void
    {
        $event->user->notify(new GoogleAccountLinkedNotification());
    }
}
