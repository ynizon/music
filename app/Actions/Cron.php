<?php

namespace App\Actions;

use App\Models\Spotdl;

class Cron
{
    public function makeCronFile()
    {
        $cronFile = storage_path("cron.sh");
        $fp = fopen($cronFile, "w+");
        fputs($fp, "#!/bin/bash" . PHP_EOL);
	fputs($fp, "wget ".env("APP_URL")."/cron" . PHP_EOL);
        $spots = Spotdl::where("avoid","=", false)->get();
        foreach ($spots as $spot){
            if ($spot->isTodo() && !$spot->isDone()){
                $spotsTodo[] = $spot;
                fputs($fp, 'mkdir -p "' . $spot->getPath(). '"'.PHP_EOL);
                fputs($fp, 'chmod g+w  "' . $spot->getPath(). '"'.PHP_EOL);
                $docker = env("SPOTIFY_SH")." \"".$spot->getPath()."\" \"". $spot->getSpotifyurl(). "\" 2>&1";
                fputs($fp, $docker . PHP_EOL);
            }
        }
        fclose($fp);
        chmod($cronFile, 0755);
    }
}
