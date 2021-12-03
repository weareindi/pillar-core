<?php

namespace Pillar\Services;

use Exception;
use RecursiveDirectoryIterator;

/**
 * Pillar Core Data Service
 */
class DataService {
    /**
     * Iterate through directory and merge all json files
     * @param  string $path An absolute path
     * @return array        An array of prepared pattern data
     */
    public static function get(string $path) {
        $data = [];

        $di = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);

        // Process library pattern data
        foreach ($di as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') {
                continue;
            }

            $json = !empty(file_get_contents($file)) ? self::validateJson(file_get_contents($file)) : '{}';

            $array = json_decode($json, true);

            $data = array_merge($data, $array);
        }

        return $data;
    }

    /**
     * Validate JSON string
     * @param  string $json
     * @return string $json
     */
    public static function validateJson(string $json) {
        json_decode($json);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $json;
                break;
            case JSON_ERROR_DEPTH:
                throw new Exception('JSON Error: Maximum stack depth exceeded');
                break;
            case JSON_ERROR_STATE_MISMATCH:
                throw new Exception('JSON Error: Underflow or the modes mismatch');
                break;
            case JSON_ERROR_CTRL_CHAR:
                throw new Exception('JSON Error: Unexpected control character found');
                break;
            case JSON_ERROR_SYNTAX:
                throw new Exception('JSON Error: Syntax error, malformed JSON');
                break;
            case JSON_ERROR_UTF8:
                throw new Exception('JSON Error: Malformed UTF-8 characters, possibly incorrectly encoded');
                break;
            default:
                throw new Exception('JSON Error: Unknown error');
                break;
        }
    }
}
