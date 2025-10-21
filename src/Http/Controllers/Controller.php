<?php

namespace crudPackage\Http\Controllers;

abstract class Controller
{
    public static function cacheClear()
    {
        $url = self::getFrontCacheUrl();

        if (!empty($url))
        {
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }
    }

    public static function getFrontCacheUrl()
    {
        return env('APP_CACHE_URL');
    }
}
