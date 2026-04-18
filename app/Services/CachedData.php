<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CachedData
{
    public static function getJson($folder, $jsonFile)
    {
        try {

            $acceptedFolders = ['static/inbox', 'static/outbox'];

            if (!in_array($folder, $acceptedFolders)) {
                Log::error('Json file fetch failed', ['error' => "Folder $folder has no access"]);
                return null;
            }

            $disk     = Storage::disk("local");
            $path     = "$folder/$jsonFile.json";
            $cacheKey = "json_cache_" . hash('sha256', $folder . '_' . $jsonFile);


            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            if (!$disk->exists($path)) {
                Log::error('Json file fetch failed', ['error' => "file $jsonFile not found at $folder"]);
                return null;
            }

            $data = json_decode($disk->get($path), TRUE);
            if ($data === null) {
                Log::error('Json file fetch failed', ['error' => "invalid JSON in $jsonFile at $folder"]);
                return null;
            }

            Cache::put($cacheKey, $data, 3600);
            return $data;

        }
        catch (\Throwable $e) {
            Log::error('Json file fetch failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}