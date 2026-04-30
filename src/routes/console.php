<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:clean-checkout-request')
    ->dailyAt('03:45')
    ->appendOutputTo(storage_path('logs/cron.log'));

Schedule::command('app:clean-tmp-image')
    ->dailyAt('03:50')
    ->appendOutputTo(storage_path('logs/cron.log'));

Schedule::command('app:reset-database')
    ->dailyAt('03:55')
    ->appendOutputTo(storage_path('logs/cron.log'));

