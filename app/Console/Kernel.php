<?php

namespace App\Console;

use App\Console\Commands\ResetDemoAdminAccount;
use Common\Channels\UpdateAllChannelsContent;
use App\Console\Commands\sitemapGenerate;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        UpdateAllChannelsContent::class,
        \App\Console\Commands\ImportSpotifyTest::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command(UpdateAllChannelsContent::class)
            ->dailyAt('03:20')
            ->withoutOverlapping();

        if (config('common.site.demo')) {
            $schedule
                ->command(ResetDemoAdminAccount::class)
                ->dailyAt('03:30')
                ->withoutOverlapping();
        }
        $schedule
            ->command(sitemapGenerate::class)->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
