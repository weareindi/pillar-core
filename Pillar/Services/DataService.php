<?php

namespace Pillar\Services;

use JsonException;
use RecursiveDirectoryIterator;

/**
 * Pillar Core Data Service
 */
class DataService {
    /** @var array<string, array> */
    private static array $cache = [];

    /**
     * Iterate through directory and merge all json files
     * @param  string $path An absolute path
     * @return array        An array of prepared pattern data
     */
    public static function get(string $path) {
        $cacheKey = realpath($path) ?: $path;
        if (array_key_exists($cacheKey, self::$cache)) {
            return self::$cache[$cacheKey];
        }

        $data = [];

        $di = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);

        // Process library pattern data
        foreach ($di as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') {
                continue;
            }

            $json = file_get_contents($file->getPathname());
            if ($json === false || trim($json) === '') {
                continue;
            }

            try {
                $array = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new JsonException(
                    sprintf('JSON Error in "%s": %s', $file->getPathname(), $exception->getMessage()),
                    $exception->getCode(),
                    $exception
                );
            }

            $data = array_merge($data, $array);
        }

        return self::$cache[$cacheKey] = $data;
    }

    /**
     * Validate JSON while retaining the original public API.
     */
    public static function validateJson(string $json): string {
        json_decode($json, flags: JSON_THROW_ON_ERROR);

        return $json;
    }
}
