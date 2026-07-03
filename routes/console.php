<?php

use App\Services\MetaGamesImportService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('meta-games:import', function (MetaGamesImportService $importer): int {
    $total = $importer->import($this);

    $this->info('Импорт Meta Games завершен. Всего записей: '.$total);

    return Command::SUCCESS;
})->purpose('Импортировать игры и дополнения из Meta Games API');

Schedule::command('meta-games:import')
    ->cron((string) config('services.meta_games.import_cron'))
    ->withoutOverlapping(55);
