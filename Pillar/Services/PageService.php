<?php

namespace Pillar\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Pillar Core Page Service
 */
class PageService {
    protected static $pages;

    /**
     * Get patterns
     * @param  String $path Absolute path to root directory of patterns
     * @return Array
     */
    public static function get(String $path = PAGES) {
        self::$pages = [];

        $di = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $ii = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::SELF_FIRST);

        // Process library patterns
        foreach($ii as $file) {
            if (
                // Is expected file type
                in_array(pathinfo($file, PATHINFO_EXTENSION), ['twig', 'json'])
                    // Hasn't already been processed
                    && !array_key_exists(dirname($file), self::$pages)
                ) {

                // Populate $patterns
                self::$pages[self::pathToPages(dirname($file))] = self::populate(dirname($file));;
            }
        }

        // Now sort $patterns alphabetically by base
        ksort(self::$pages);

        return self::$pages;
    }

    /**
     * Get the pattern path base pattern directory
     * @param  String $path A full system path to the pattern file
     * @return String $base A directory name
     */
    protected static function base(String $path) {
        // Explode path and return the first pattern "base" parameter
        return explode('/', self::pathToPages($path))[0];;
    }

    /**
     * Remove system path from start of pattern path string
     * @param  String $path A full system path to the pattern file
     * @return String       A string of the pattern directory structure
     */
    protected static function pathToPages(String $path) {
        // Remove PAGES string from beginning of path
        // Remove surrounding slashes
        // 'PAGES' parameter is defined in 'pillar-core/paths.php'
        return trim(substr($path, strlen(PAGES)), '/');
    }

    /**
     * [patternPopulate description]
     * @return [type] [description]
     */
    protected static function populate(String $path) {
        $pattern = [];

        $pattern['url'] = self::pathToPages($path);
        $pattern['group'] = self::base($path);
        $pattern['template'] = self::template($path);
        $pattern['data'] = self::data($path);

        return $pattern;
    }

    /**
     * Get the pattern parent directory path
     * @param  String $path An absolute path
     * @return String       An absolute path to a pattern parent
     */
    public static function parentDirname(String $path) {
        return dirname($path, 2);
    }

    /**
     * Get and prepare the pattern template
     * @param  String $path An absolute path
     * @return Array        An array of prepared template data
     */
    public static function template(String $path) {
        $template = $path;

        return self::pathToPages($template);
    }

    /**
     * Get and prepare the pattern data
     * @param  String $path An absolute path
     * @return Array        An array of prepared pattern data
     */
    public static function data(String $path) {
        $data = [];

        $file = is_file($path . '/data.json') ? $path . '/data.json' : NULL;
        $json = isset($file) && !empty(file_get_contents($file)) ? self::validateJson(file_get_contents($file)) : '{}';
        $data = json_decode($json, true);

        return $data;
    }

    /**
     * Validate JSON string
     * @param  String $json
     * @return String $json
     */
    public static function validateJson(String $json) {
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
