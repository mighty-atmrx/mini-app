<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tester', function () {
    /** @var \DefStudio\Telegraph\Models\TelegraphBot $bot */
   $bot = \DefStudio\Telegraph\Models\TelegraphBot::find(1);

   $bot->registerCommands([
       'openapp' => 'Opens the mini-app'
   ])->send();
});

Artisan::command('logs:clear', function() {

    exec('rm -f ' . storage_path('logs/*.log'));

    exec('rm -f ' . base_path('*.log'));

    $this->comment('Logs have been cleared!');

})->describe('Clear log files');
