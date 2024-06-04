<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ProcessWebsites;
use App\Console\Commands\DNSScan;
use App\Console\Commands\SSLCheck;

Schedule::command('app:dns_scan')->everyMinute()->runInBackground();

Schedule::command('app:ssl_check')->everyMinute()->runInBackground();

Schedule::command('app:process-websites')->everyFifteenMinutes()->runInBackground();



