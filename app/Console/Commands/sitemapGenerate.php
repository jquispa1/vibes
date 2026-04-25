<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use  Common\Admin\Sitemap\BaseSitemapGenerator;

class sitemapGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sitemap = class_exists('App\Services\SitemapGenerator')
            ? app('App\Services\SitemapGenerator')
            : app(BaseSitemapGenerator::class);

        $sitemap->generate();

        $this->info('Sitemap generated successfully');
    }
}
