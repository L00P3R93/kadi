<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature   = 'sitemap:generate';
    protected $description = 'Generate the XML sitemap to public/sitemap.xml';

    public function handle(): int
    {
        Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0)->setChangeFrequency('daily'))
            ->add(Url::create('/lobby')->setPriority(0.9)->setChangeFrequency('weekly'))
            ->add(Url::create('/sportsbook')->setPriority(0.9)->setChangeFrequency('weekly'))
            ->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap written to public/sitemap.xml');

        return self::SUCCESS;
    }
}
