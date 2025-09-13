<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class Helpers
{
    public static function getPic($lastfm_mbid, $default): string{
        $pic = "";
        try{
            $url = "https://webservice.fanart.tv/v3/music/".$lastfm_mbid."&?api_key=".
                env("FANART_KEY")."&format=json";

            $options = [
                "ssl" => [
                    "verify_peer"=>false,
                    "verify_peer_name"=>false
                ]
            ];

            $json = file_get_contents($url, false, stream_context_create($options));
            if ($json != ""){
                $json = json_decode($json,true);
                if (isset($json["artistthumb"])){
                    if (isset($json["artistthumb"][0])){
                        $pic = $json["artistthumb"][0]["url"];
                    }
                }
            }
        }catch(\Exception $e){
            //echo $e->getMessage();
        }
        if ($pic == ""){
            $pic = $default;
        }
        if ($pic == ""){
            $pic = "/images/default_pic.png";
        }
        return $pic;
    }

    /* Convertit une date de 2016-01-31 a 01/2016 */
    public static function showMonth($sDate, $bJustDate = false) : string
    {
        if ($sDate == "" or $sDate == "1970-01-01"){
            return "";
        }else{
            if ($bJustDate){
                return substr($sDate,5,2)."/".substr($sDate,0,4);
            }else{
                return substr($sDate,5,2)."/".substr($sDate,0,4).substr($sDate,10);
            }
        }
    }

    /* Convertit une date de 2016-01-31 a 31/01/2016 */
    public static function formatDateFR($sDate, $bJustDate = false) : string
    {
        if ($sDate == "" or $sDate == "1970-01-01"){
            return "";
        }else{
            if ($bJustDate){
                return substr($sDate,8,2)."/".substr($sDate,5,2)."/".substr($sDate,0,4);
            }else{
                return substr($sDate,8,2)."/".substr($sDate,5,2)."/".substr($sDate,0,4).substr($sDate,10);
            }
        }
    }

    /* Convertit une date de 31/01/2016 a 2016-01-31*/
    public static function formatDateSQL($sDate) : string
    {
        return substr($sDate,6,4)."-".substr($sDate,3,2)."-".substr($sDate,0,2);
    }

    /* Renvoie une date pour les calendriers JS avec new Date(2018,12,31) a partir de 31-12-2018 */
    public static function formatDateCalendarJS($sDate) : string{
        $iJour = substr($sDate,0,2);
        $iMois = substr($sDate,3,2);
        $iAnnee = substr($sDate,6,4);
        return "new Date(".$iAnnee.",".$iMois.",".$iJour.")" ;
    }

    public static function formatDureeHeureMin($iSecondes) : string
    {
        $iHeure = 0;
        $iMin = 0;
        $iSec = 0;
        while ($iSecondes>3600){
            $iHeure++;
            $iSecondes  = $iSecondes - 3600;
        }
        while ($iSecondes>60){
            $iMin++;
            $iSecondes  = $iSecondes - 60;
        }
        $iSec = $iSecondes;

        $sHeure = $iHeure;
        if (strlen($iHeure)<2){
            $sHeure = "0".$iHeure;
        }
        $sMin = $iMin;
        if (strlen($iMin)<2){
            $sMin = "0".$iMin;
        }

        return $sHeure.":".$sMin;
    }

    public static function formatDureeHeureMinSec($iSecondes) : string
    {
        $iHeure = 0;
        $iMin = 0;
        $iSec = 0;
        while ($iSecondes>3600){
            $iHeure++;
            $iSecondes  = $iSecondes - 3600;
        }
        while ($iSecondes>60){
            $iMin++;
            $iSecondes  = $iSecondes - 60;
        }
        $iSec = $iSecondes;

        $sHeure = $iHeure;
        if (strlen($iHeure)<2){
            $sHeure = "0".$iHeure;
        }
        $sMin = $iMin;
        if (strlen($iMin)<2){
            $sMin = "0".$iMin;
        }
        $sSec = $iSec;
        if (strlen($iSec)<2){
            $sSec = "0".$iSec;
        }
        return $sHeure.":".$sMin.":".$sSec;
    }


    /* Renvoie le chiffre avec les bons separateurs */
    public static function showNumber($sNumber, $sCurrency, $iVirgule = 0) : string
    {
        $r = number_format($sNumber, $iVirgule, ',', ' ');
        if ($sCurrency != ""){
            $r .= " " .$sCurrency;
        }
        return $r;
    }

    /**
     * Affiche un nombre avec les bons séparateurs (>FR) 10 000.00
     */
    public static function num($number, $bEuro = true, $iDecimale = 2) : string{
        if ($number == ""){
            $number = 0;
        }
        if (round($number,$iDecimale) == 0){
            $number = 0;
        }
        $s = number_format($number, $iDecimale, ',', ' ');
        if ($bEuro){
            $s .= " &euro;";
        }
        return $s;
    }

    /**
     * Renvoie le nom du mois
     */
    public static function getMois($iMois, $bPrefixe = false): string {
        $iMois = (int) $iMois;
        $sMois = "";
        $sPrefix = "de ";
        switch ($iMois){
            case 0:
                $sMois = "Décembre";
                break;
            case 1:
                $sMois = "Janvier";
                break;
            case 2:
                $sMois = "Février";
                break;
            case 3:
                $sMois = "Mars";
                break;
            case 4:
                $sPrefix = "d'";
                $sMois = "Avril";
                break;
            case 5:
                $sMois = "Mai";
                break;
            case 6:
                $sMois = "Juin";
                break;
            case 7:
                $sMois = "Juillet";
                break;
            case 8:
                $sPrefix = "d'";
                $sMois = "Août";
                break;
            case 9:
                $sMois = "Septembre";
                break;
            case 10:
                $sPrefix = "d'";
                $sMois = "Octobre";
                break;
            case 11:
                $sMois = "Novembre";
                break;
            case 12:
                $sMois = "Décembre";
                break;
            case 13:
                $sMois = "Janvier";
                break;
        }

        if (!$bPrefixe){
            return $sMois;
        }else{
            return $sPrefix . $sMois;
        }
    }

    /* Effectue le total d'un champ d'un tableau */
    public static function sum($tab, $field){
        $r = 0;
        foreach ($tab as $t){
            $r = $r + $t[$field];
        }
        return $r;
    }

    /* Effectue la moyenne d'un champ d'un tableau */
    public static function average($tab, $field) : float {
        $k=0;
        $r = 0;
        foreach ($tab as $t){
            $r = $r + $t[$field];
            $k++;
        }
        if ($k>0){
            return ($r/$k);
        }else{
            return $r;
        }

    }

    /* Remplace les accents */
    public static function remove_accents($str, $charset='utf-8') : string {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})lig;#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }

    /**
    Obtenir le domaine a partir d'une url
     */
    public static function getdomain($url) : string {

        preg_match (
            "/^(http:\/\/|https:\/\/)?([^\/]+)/i",
            $url, $matches
        );

        $host = "";
        if (isset($matches[2])){
            $host = $matches[2];
        }
        preg_match (
            "/[^\/]+\.[^.\/]+$/",
            $host, $matches
        );

        if (isset($matches[0])){
            return strtolower("{$matches[0]}");
        }else{
            return "";
        }
    }

    // Renomme lurl .. en HP
    public static function renameurl($url) : string {
        $shorturl = $url;
        if ($shorturl == "." or $shorturl == ".."){
            $shorturl = "-HP-";
        }
        return $shorturl;
    }

    // Ecrit une duree au format Heure:minute:seconde
    public static function formatTime($iDuree) : string {
        $iDuree = round($iDuree,0);
        $iHeure = round(floor($iDuree/3600),0);
        $iDuree = $iDuree - $iHeure*3600;
        $iMin = round(floor($iDuree/60),0);
        $iDuree = $iDuree - $iMin*60;
        $iSec = $iDuree;

        $result = sprintf("%02d",$iHeure).":".sprintf("%02d",$iMin).":".sprintf("%02d",$iSec);
        return $result;
    }

    //Malgré l'encodage, certain titres ont mal etes importés dans last fm, on les corrige comme ca
    public static function strangeChar($s) : string {
        $s = str_replace("ã©","é",$s);
        $s = str_replace("Ã©","é",$s);
        $s = str_replace("Ă©","é",$s);
        $s = str_replace("Ã¨","è",$s);
        $s = str_replace("Ã«","ë",$s);
        return $s;
    }

    //Recup du cache
    public static function getCache($artist_name = null, $album_name = null, $title_name = null, $sBloc = "view") :
    array {
        $r = array();
        $filename = "";
        if ($artist_name != null){
            $cachefile = storage_path()."/cache/artist/";
            $filename = strtolower($artist_name);
        }
        if ($album_name != null){
            $cachefile = storage_path()."/cache/album/";
            $filename =strtolower($artist_name)."-".strtolower($album_name);
        }

        if ($title_name != null){
            $cachefile = storage_path()."/cache/title/";
            $filename =strtolower($artist_name)."-".strtolower($album_name)."-".strtolower($title_name);
        }
        $cachefile .= rawurlencode($filename.".gz");

        if (config("app.CACHE")){
            if (file_exists($cachefile)){
                $cache = json_decode(gzuncompress(file_get_contents($cachefile)),true);

                $date1 = strtotime($cache["updated_at"]);
                $date2 = strtotime(date("Y-m-d"));
                $nbJoursTimestamp = $date2 - $date1;
                $nbJours = round($nbJoursTimestamp/86400,0); // 86 400 = 60*60*24

                if ($nbJours<=config("app.DELAY_CACHE")){
                    $r = $cache;

                    //On envoie le last modified uniquement lorsqu on affiche toute la page (et pas les blocs ajax)
                    if ($sBloc == "view"){
                        //echo var_dump($cache);exit();
                        //$last_modified_time = gmdate("D, d M Y H:i:s",mktime(0,0,0,substr($cache["updated_at"],5,2),substr($cache["updated_at"],8,2),substr($cache["updated_at"],0,4))." GMT");
                        $last_modified_time = gmdate('r', filemtime($cachefile));
                        $etag = md5_file($cachefile);
                        // always send headers
                        header("Last-Modified: ".$last_modified_time);
                        header("Etag: $etag");
                        header('Cache-Control: public');

                        // exit if not modified
                        //Ces 2 variables ne fonctionnent pas sur tous les environnements...
                        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
                            if (trim($_SERVER['HTTP_IF_NONE_MATCH']) == trim($etag)) {
                                header("HTTP/1.1 304 Not Modified");
                                exit();
                            }
                        }
                        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
                            if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $last_modified_time) {
                                header("HTTP/1.1 304 Not Modified");
                                exit();
                            }
                        }
                    }
                }
            }
        }

        return $r;
    }

    //Ajout du cache
    public static function setCache($artist = null, $album = null, $title = null) : void{
        try{
            //Creation de la vue principale
            //Creation des blocs
            //On creer pas de cache pour les robots (ca prend trop de place pour rien)
            $cachefile = storage_path("/cache");
            if (!is_dir($cachefile)){
                mkdir($cachefile);
            }
            if (stripos($_SERVER['HTTP_USER_AGENT'],"bot")===false){
                if ($title != null){
                    $view = View::make('search/index', compact('artist','album','title'))->render();
                    $videos = View::make('livewire/videos', compact('artist','album','title'))->render();

                    $cachefile = storage_path("/cache/title/");
                    if (!is_dir($cachefile)){
                        mkdir($cachefile);
                    }
                    $filename =strtolower($artist->name);
                    if (isset($album)){
                        $filename .= "-".strtolower($album->name);
                        if (isset($title)){
                            $filename .= "-".strtolower($title->name);
                        }
                    }

                    $cachefile .= rawurlencode($filename.".gz");

                    $cache = array();
                    $cache["view"] = $view;
                    $cache["videos"] = $videos;
                    $cache["updated_at"] = date("Y-m-d");
                    file_put_contents($cachefile,gzcompress(json_encode($cache),9));
                }else{
                    if ($album != null){
                        $view = View::make('search/index', compact('artist','album'))->render();
                        $biography = View::make('livewire/biography', compact('artist'))->render();
                        $infoalbum = View::make('livewire/infoalbum', compact('artist','album'))->render();
                        $videos = View::make('livewire/videos', compact('artist','album'))->render();

                        $cachefile = storage_path("/cache/album/");
                        if (!is_dir($cachefile)){
                            mkdir($cachefile);
                        }
                        $filename =strtolower($artist->name)."-".strtolower($album->name);
                        $cachefile .= rawurlencode($filename.".gz");

                        $cache = array();
                        $cache["view"] = $view;
                        $cache["infoalbum"] = $infoalbum;
                        $cache["videos"] = $videos;
                        $cache["updated_at"] = date("Y-m-d");
                        file_put_contents($cachefile,gzcompress(json_encode($cache),9));
                    }else{
                        if ($artist != null){
                            $view = View::make('search/index', compact('artist','album'))->render();
                            $biography = View::make('livewire/biography', compact('artist'))->render();
                            $albums = View::make('livewire/albums', compact('artist'))->render();
                            $similars = View::make('livewire/similars', compact('artist'))->render();
                            $videos = View::make('livewire/videos', compact('artist'))->render();
                            $lives = View::make('livewire/lives', compact('artist'))->render();

                            $cachefile = storage_path("/cache/artist/");
                            if (!is_dir($cachefile)){
                                mkdir($cachefile);
                            }
                            $filename = strtolower($artist->name);
                            $cachefile .= rawurlencode($filename.".gz");

                            $cache = array();
                            $cache["view"] = $view;
                            $cache["biography"] = $biography;
                            $cache["albums"] = $albums;
                            $cache["similars"] = $similars;
                            $cache["videos"] = $videos;
                            $cache["lives"] = $lives;
                            $cache["updated_at"] = date("Y-m-d");

                            file_put_contents($cachefile,gzcompress(json_encode($cache),9));
                        }
                    }
                }
            }
        }catch(\Exception $e){
            //Ca ne marche pas, c est pas tres grave (exception google, quota disque...)
            echo $e->getMessage();
        }
    }


    public static function replaceUpperChar($s) : string {
        $s = str_replace("à","A",$s);
        $s = str_replace("â","A",$s);
        $s = str_replace("é","E",$s);
        $s = str_replace("è","E",$s);
        $s = str_replace("ê","E",$s);
        $s = str_replace("ï","I",$s);
        $s = str_replace("î","I",$s);
        $s = str_replace("ö","O",$s);
        $s = str_replace("ô","O",$s);
        $s = str_replace("œ","OE",$s);
        $s = str_replace("ù","U",$s);
        $s = str_replace("û","U",$s);
        $s = str_replace("ü","U",$s);
        $s = strtoupper($s);

        return $s;
    }

    public static function extrait($string, $start = 150, $end = 0, $sep = ' ...') : string {
        $extrait = substr($string,0,$start);
        $extrait = substr($string,0,strrpos($extrait,' ')).$sep;
        $extrait2 = strstr(substr($string, -$end,$end),' ');
        return str_replace("\n","",$extrait.' '.$extrait2);
    }

    public static function getYoutubeData($url) : string|null {
        try{
            return file_get_contents($url);
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
        return null;
    }
}
