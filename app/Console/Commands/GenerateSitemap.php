<?php

namespace App\Console\Commands;

use App\Services\SitemapGenerator;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--force : Régénère tous les groupes même s\'ils sont récents}';

    protected $description = 'Génère les sitemaps découpés par groupe (artist, album, title)';

    public function handle(SitemapGenerator $generator): int
    {
        $done = $generator->refreshStale((bool) $this->option('force'));

        if ($done === []) {
            $this->info('Sitemaps à jour, rien à régénérer.');
        }
        foreach ($done as $group => $count) {
            $this->info("{$group} : {$count} URL, ".count($generator->files($group)).' fichier(s)');
        }

        return self::SUCCESS;
    }
}
