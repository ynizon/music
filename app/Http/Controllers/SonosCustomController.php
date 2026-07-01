<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SonosCustomController extends Controller
{
    /**
     * Render the main Sonos control page.
     */
    public function index()
    {
        $speakers = config('app.SONOS_SPEAKERS') ?: [
        ];
        return view('sonos.index', compact('speakers'));
    }

    /**
     * Autocomplete artists from the NAS music folder.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $term = $request->input('term');
        $baseFolder = config("app.MUSIC_FOLDER") ?: "/music/";
        $artists = [];

        if (is_dir($baseFolder)) {
            $dirs = scandir($baseFolder);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }
                if (is_dir($baseFolder . '/' . $dir)) {
                    if (empty($term) || stripos($dir, $term) !== false) {
                        $artists[] = $dir;
                    }
                }
            }
        }

        // Sort alphabetically, case-insensitive
        natcasesort($artists);

        return response()->json(array_values($artists));
    }

    /**
     * Get albums (directories) and M3U8 files for an artist.
     */
    public function albums(Request $request): JsonResponse
    {
        $artist = $request->input('artist');
        $path = $request->input('path', '');
        if (empty($artist)) {
            return response()->json(['error' => 'Artiste requis'], 400);
        }

        $baseFolder = config("app.MUSIC_FOLDER") ?: "/music/";
        $artistFolder = $baseFolder . '/' . $artist;

        if (!is_dir($artistFolder)) {
            return response()->json(['error' => 'Dossier artiste non trouvé : ' . $artist], 404);
        }

        $targetFolder = $artistFolder;
        if (!empty($path)) {
            $path = str_replace('\\', '/', $path);

            // Basic path traversal prevention
            if (strpos($path, '..') !== false) {
                return response()->json(['error' => 'Chemin invalide'], 400);
            }

            $targetFolder = $artistFolder . '/' . $path;
            if (!is_dir($targetFolder)) {
                return response()->json(['error' => 'Dossier non trouvé : ' . $path], 404);
            }
        }

        $m3us = [];
        $folders = [];

        try {
            if (is_dir($targetFolder)) {
                $files = scandir($targetFolder);

                $dirNames = [];
                $m3uNames = [];

                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    $fullPath = $targetFolder . '/' . $file;
                    if (is_dir($fullPath)) {
                        $dirNames[] = $file;
                    } elseif (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'm3u8') {
                        $m3uNames[] = $file;
                    }
                }

                natcasesort($dirNames);
                natcasesort($m3uNames);

                foreach ($dirNames as $dirName) {
                    $fullPath = $targetFolder . '/' . $dirName;
                    $relativePath = empty($path) ? $dirName : $path . '/' . $dirName;

                    $matchingM3uFile = $fullPath . '/' . $dirName . '.m3u8';
                    $hasMatchingM3u = false;//is_file($matchingM3uFile);
                    $m3uRelativePath = $relativePath . '/' . $dirName . '.m3u8';
                    $coverFilename = $this->findCoverImage($fullPath);

                    $folders[] = [
                        'cover' => $coverFilename === '' ? '' : "data:image/jpeg;base64," . base64_encode(file_get_contents($coverFilename)),
                        'path' => $relativePath,
                        'name' => $dirName,
                        'has_matching_m3u' => $hasMatchingM3u,
                        'm3u_path' => $hasMatchingM3u ? $m3uRelativePath : null
                    ];
                }

                foreach ($m3uNames as $m3uName) {
                    $relativePath = empty($path) ? $m3uName : $path . '/' . $m3uName;
                    $coverFilename = $this->findCoverImage($targetFolder);
                    $m3us[] = [
                        'cover' => $coverFilename === '' ? '' : "data:image/jpeg;base64," . base64_encode(file_get_contents($coverFilename)),
                        'path' => $relativePath,
                        'name' => $m3uName
                    ];
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'folders' => array_values($folders),
            'm3us' => array_values($m3us)
        ]);
    }

    /**
     * Play selected M3U playlist or album folder on Sonos.
     */
    public function play(Request $request): JsonResponse
    {
        $speakers = config('app.SONOS_SPEAKERS') ?: ['Salon' => '192.168.1.15'];
        $defaultIp = array_values($speakers)[0] ?? '192.168.1.15';
        $ip = $request->input('ip', $defaultIp);
        $type = $request->input('type'); // 'm3u' or 'folder'
        $artist = $request->input('artist');
        $path = $request->input('path'); // relative path of m3u or folder

        if (empty($artist)) {
            return response()->json(['error' => 'Artiste requis'], 400);
        }

        $baseFolder = str_replace('\\', '/', config("app.NAS_MUSIC_FOLDER") ?: "/music/");
        $sonos = new SonosPHPController($ip);

        try {
            $sonos->RemoveAllTracksFromQueue();
            $sonos->Stop();

            // Full path to folder (local for scanning, then mapped to NAS path)
            $localFolder = str_replace('\\', '/', config("app.MUSIC_FOLDER") ?: "/music/");
            $localFolderPath = $localFolder . '/' . $artist;
			$init = false;
            if (!empty($path)) {
                $localFolderPath .= '/' . $path;
            }

            if ($type === 'm3u8') {
                // Full path to M3U
                $m3uPath = $baseFolder . '/' . $artist;
                if (!empty($path)) {
                    $m3uPath .= '/' . $path;
                }

                $file = str_replace($baseFolder, $localFolder, $m3uPath);
                $content = file_get_contents($file);
                foreach (explode("\n", $content) as $line) {
                    $line = trim($line);
                    if (!empty($line) && strpos($line, '#EXT') === false) {
                        $nasAudioFile = $baseFolder . '/' . $artist."/".$line;
                        $file = str_replace('&', '%26', $nasAudioFile);
                        $file = utf8_decode($file);//Fix accent
						if (!$init){							
							$sonos->SetQueue("x-file-cifs:" . $file);
							$init = true;
						} else {
							$sonos->AddURIToQueue("x-file-cifs:" . $file);
						}
                    }
                }
            } else {
                $audioFiles = $this->getAudioFiles($localFolderPath);
                if (empty($audioFiles)) {
                    return response()->json(['error' => 'Aucune musique trouvée dans ce dossier'], 404);
                }

                foreach ($audioFiles as $audioFile) {
                    $nasAudioFile = str_replace($localFolder, $baseFolder, $audioFile);
                    $file = str_replace('&', '%26', $nasAudioFile);
                    $file = utf8_decode($file);//Fix accent
					if (!$init){							
						$sonos->SetQueue("x-file-cifs:" . $file);
						$init = true;
					} else {
						$sonos->AddURIToQueue("x-file-cifs:" . $file);
					}
                }
            }

            $sonos->Play();
            return response()->json(['success' => true, 'message' => 'Lecture lancée avec succès !']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send general control commands to Sonos.
     */
    public function control(Request $request): JsonResponse
    {
        $speakers = config('app.SONOS_SPEAKERS') ?: ['Salon' => '192.168.1.15'];
        $defaultIp = array_values($speakers)[0] ?? '192.168.1.15';
        $ip = $request->input('ip', $defaultIp);
        $action = $request->input('action');
        $value = $request->input('value');

        $sonos = new SonosPHPController($ip);

        try {
            switch ($action) {
                case 'play':
                    $sonos->Play();
                    break;
                case 'pause':
                    $sonos->Pause();
                    break;
                case 'stop':
                    $sonos->Stop();
                    break;
                case 'next':
                    $sonos->Next();
                    break;
                case 'previous':
                    $sonos->Previous();
                    break;
                case 'volume':
                    $sonos->SetVolume((int)$value);
                    break;
                case 'mute':
                    $sonos->SetMute((int)$value);
                    break;
                case 'status':
                    $info = $sonos->GetPositionInfo();
                    $volume = $sonos->GetVolume();
                    $mute = $sonos->GetMute();
                    $transport = $sonos->GetTransportInfo();
                    return response()->json([
                        'success' => true,
                        'info' => $info,
                        'volume' => $volume,
                        'mute' => $mute,
                        'transport' => $transport
                    ]);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Recursively list all audio files under a directory.
     */
    private function getAudioFiles(string $dir): array
    {
        $audioFiles = [];
        if (!is_dir($dir)) {
            return [];
        }

        try {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $allowedExtensions = ['mp3', 'm4a', 'flac', 'wav', 'wma', 'ogg', 'aac'];

            foreach ($it as $file) {
                if ($file->isFile() && in_array(strtolower($file->getExtension()), $allowedExtensions)) {
                    $audioFiles[] = str_replace('\\', '/', $file->getPathname());
                }
            }
        } catch (\Exception $e) {
            // Ignore directory read errors
        }

        natcasesort($audioFiles);
        return array_values($audioFiles);
    }

    /**
     * Recherche la meilleure image de couverture dans un dossier.
     * Priorité à 'cover.jpg', sinon le premier .jpg/.jpeg trouvé.
     *
     * @param string $absolutePath Le chemin absolu du dossier sur le serveur
     * @return string Le nom du fichier trouvé (ex: 'cover.jpg' ou 'photo.jpg') ou null
     */
    function findCoverImage($absolutePath) {
        if (!is_dir($absolutePath)) {
            return '';
        }

        // 1. Priorité absolue : vérifier si cover.jpg existe
        if (is_file($absolutePath . '/cover.jpg')) {
            return $absolutePath . '/cover.jpg';
        }

        // 2. Repli : scanner le dossier pour trouver le premier fichier .jpg ou .jpeg
        $files = scandir($absolutePath);
        foreach ($files as $file) {
            // On ignore les dossiers parents/courants (. et ..)
            if ($file === '.' || $file === '..') {
                continue;
            }

            // On vérifie l'extension du fichier (insensible à la casse)
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($extension === 'jpg' || $extension === 'jpeg') {
                return $absolutePath . '/'. $file; // On a trouvé le premier JPG, on s'arrête ici
            }
        }

        return '';
    }
}
