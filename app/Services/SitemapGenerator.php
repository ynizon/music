<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Sitemaps découpés par groupe (artist, album, title) puis en lots de 50 000 URL
 * (limite du protocole), écrits en flux pour ne jamais charger une table entière en mémoire.
 * Fichiers produits : storage/app/sitemaps/sitemap-{groupe}-{n}.xml, servis par la route
 * /sitemap-{groupe}-{n}.xml et référencés par l'index /sitemap.xml.
 */
class SitemapGenerator
{
    public const GROUPS = [
        'artist' => 'artists',
        'album' => 'albums',
        'title' => 'titles',
    ];

    private const URLS_PER_FILE = 50000;

    private const MAX_AGE_DAYS = 3;

    public function stateFile(): string
    {
        return storage_path('sitemap_maj.txt');
    }

    /**
     * Régénère les groupes dont la dernière génération date de plus de MAX_AGE_DAYS jours.
     *
     * @return array<string, int> nombre d'URL par groupe régénéré
     */
    public function refreshStale(bool $force = false): array
    {
        $state = $this->readState();
        $done = [];

        foreach (array_keys(self::GROUPS) as $group) {
            $last = $state[$group] ?? null;
            $stale = $last === null || (strtotime($last) < strtotime('-'.self::MAX_AGE_DAYS.' days'));

            if ($force || $stale || $this->files($group) === []) {
                $done[$group] = $this->generate($group);
                $state[$group] = date('Y-m-d');
                file_put_contents($this->stateFile(), json_encode($state));
            }
        }

        return $done;
    }

    /**
     * Génère les fichiers d'un groupe et supprime les lots devenus inutiles.
     */
    public function generate(string $group): int
    {
        $table = self::GROUPS[$group];
        $baseUrl = $this->baseUrl();

        $count = 0;
        $fileIndex = 0;
        $handle = null;
        $tmpFiles = [];

        DB::table($table)->select(['id', 'slug', 'updated_at'])
            ->lazyById(5000)
            ->each(function ($row) use (&$count, &$fileIndex, &$handle, &$tmpFiles, $group, $baseUrl) {
                if ($count % self::URLS_PER_FILE === 0) {
                    if ($handle) {
                        $this->closeFile($handle);
                    }
                    $fileIndex++;
                    $tmp = $this->path($group, $fileIndex).'.tmp';
                    $tmpFiles[$fileIndex] = $tmp;
                    $handle = fopen($tmp, 'w');
                    fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
                }

                $loc = htmlspecialchars($baseUrl.'/artist/'.$row->slug, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $line = "\t<url><loc>{$loc}</loc>";
                if ($row->updated_at) {
                    $line .= '<lastmod>'.date('c', strtotime($row->updated_at)).'</lastmod>';
                }
                fwrite($handle, $line."<changefreq>monthly</changefreq><priority>0.2</priority></url>\n");
                $count++;
            });

        if ($handle) {
            $this->closeFile($handle);
        }

        // Remplacement des anciens fichiers seulement une fois la génération terminée
        foreach ($this->files($group) as $old) {
            unlink($old);
        }
        foreach ($tmpFiles as $i => $tmp) {
            rename($tmp, $this->path($group, $i));
        }

        return $count;
    }

    /**
     * Index des sitemaps (sitemap.xml).
     */
    public function index(): string
    {
        $baseUrl = $this->baseUrl();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach (array_keys(self::GROUPS) as $group) {
            foreach ($this->files($group) as $file) {
                $loc = htmlspecialchars($baseUrl.'/'.basename($file), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= "\t<sitemap><loc>{$loc}</loc><lastmod>".date('c', filemtime($file))."</lastmod></sitemap>\n";
            }
        }

        return $xml.'</sitemapindex>'."\n";
    }

    /**
     * Fichiers existants d'un groupe, triés par numéro de lot.
     *
     * @return list<string>
     */
    public function files(string $group): array
    {
        $files = glob($this->dir()."/sitemap-{$group}-*.xml") ?: [];
        $files = array_values(array_filter($files, fn ($f) => preg_match('/-\d+\.xml$/', $f)));
        natsort($files);

        return array_values($files);
    }

    private function baseUrl(): string
    {
        return rtrim((string) (config('app.sitemap_url') ?: config('app.url')), '/');
    }

    /**
     * Fichier d'un lot à servir (nom du type "album-2"), ou null s'il n'existe pas.
     */
    public function file(string $name): ?string
    {
        if (! preg_match('/^([a-z]+)-(\d+)$/', $name, $m) || ! isset(self::GROUPS[$m[1]])) {
            return null;
        }
        $path = $this->path($m[1], (int) $m[2]);

        return is_file($path) ? $path : null;
    }

    /**
     * Les sitemaps sont des données (storage, donc /data en prod), pas du code de l'image.
     */
    private function dir(): string
    {
        $dir = storage_path('app/sitemaps');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private function path(string $group, int $index): string
    {
        return $this->dir()."/sitemap-{$group}-{$index}.xml";
    }

    /**
     * @param  resource  $handle
     */
    private function closeFile($handle): void
    {
        fwrite($handle, '</urlset>'."\n");
        fclose($handle);
    }

    /**
     * @return array<string, string>
     */
    private function readState(): array
    {
        if (! file_exists($this->stateFile())) {
            return [];
        }

        return json_decode(file_get_contents($this->stateFile()), true) ?: [];
    }
}
