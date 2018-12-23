<?php

namespace Pillar\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Pillar Core Patterns Service
 */
class PatternService {
    protected static $patterns;

    /**
     * Get patterns
     * @param  String $path Absolute path to root directory of patterns
     * @return Array
     */
    public static function get(String $path = PATTERNS) {
        self::$patterns = [];

        $di = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $ii = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::SELF_FIRST);

        // Process library patterns
        foreach($ii as $file) {
            if (
                // Is expected file type
                in_array(pathinfo($file, PATHINFO_EXTENSION), ['twig', 'json'])
                    // Hasn't already been processed
                    && !array_key_exists(dirname($file), self::$patterns)
                        // Isn't hidden
                        && !self::isHidden(dirname($file))
                ) {

                // Populate $patterns
                self::$patterns[self::pathToPattern(dirname($file))] = self::populate(dirname($file));;
            }
        }

        // Now sort $patterns alphabetically by base
        ksort(self::$patterns);

        return self::$patterns;
    }

    /**
     * Get patterns and split into groups
     * @param  String $path Absolute path to root directory of patterns
     * @return Array
     */
    public static function getGroups($path = PATTERNS) {
        $patterns = self::get($path);

        $groups = [];

        if (empty($patterns)) {
            return $groups;
        }

        foreach ($patterns as $name => $pattern) {
            if (!array_key_exists($pattern['group'], $groups)) {
                $groups[$pattern['group']] = [];
            }

            $groups[$pattern['group']][$name] = $pattern;
        }

        $groups = self::orderGroups($groups);

        return $groups;
    }

    /**
     * Test if a pattern resides one level deep inside an 'alt' directory
     * Pillar doesn't currently allow for nested alternative patterns
     * @param  String  $path An absolute path
     * @return Boolean       True if the $path is an alternative of it's parent
     */
    protected static function isAlternative(String $path) {
        return basename(dirname($path)) === 'alt' ? true : false;
    }

    /**
     * Do we need to hide the pattern?
     * @param  String $path A path to a template
     * @return Boolean
     */
    protected static function isHidden(String $path) {
        $hidden = getenv('HIDDEN') ? explode('|', getenv('HIDDEN')) : [];

        if (in_array(self::base($path), $hidden)) {
            return true;
        }

        return false;
    }

    /**
     * Get the pattern path base pattern directory
     * @param  String $path A full system path to the pattern file
     * @return String $base A directory name
     */
    protected static function base(String $path) {
        // Explode path and return the first pattern "base" parameter
        return explode('/', self::pathToPattern($path))[0];;
    }

    /**
     * Remove system path from start of pattern path string
     * @param  String $path A full system path to the pattern file
     * @return String       A string of the pattern directory structure
     */
    protected static function pathToPattern(String $path) {
        // Remove PATTERNS string from beginning of path
        // Remove surrounding slashes
        // 'PATTERNS' parameter is defined in 'pillar-core/paths.php'
        return trim(substr($path, strlen(PATTERNS)), '/');
    }

    /**
     * [patternPopulate description]
     * @return [type] [description]
     */
    protected static function populate(String $path) {
        $pattern = [];

        $pattern['url'] = self::pathToPattern($path);
        $pattern['group'] = self::base($path);
        $pattern['template'] = self::template($path);
        $pattern['data'] = self::data($path);
        $pattern['data_parent'] = self::dataParent($path);

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
        if (self::isAlternative($path)) {
            $template = self::parentDirname($path);
        }

        return self::pathToPattern($template);
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

        if (!self::isAlternative($path)) {
            return $data;
        }

        $data_parent = self::dataParent($path);
        $data = array_merge($data_parent, $data);

        return $data;
    }

    /**
     * Get and prepare the pattern data
     * @param  String $path An absolute path
     * @return Array        An array of prepared pattern data
     */
    public static function dataParent(String $path) {
        if (!self::isAlternative($path)) {
            return self::data($path);
        }

        $parent_path = self::parentDirname($path);
        $file = is_file($parent_path . '/data.json') ? $parent_path . '/data.json' : NULL;
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

    /**
     * Order root level patterns array by the order defined in the projects .env
     * Any directories defined in the .env take priority over those that are not defined.
     * @return Array An array of pattern data
     */
    public static function orderGroups(Array $groups) {
        $order = getenv('ORDER') ?: NULL;

        if (!isset($order)) {
            return $groups;
        }

        $order = explode('|', $order);

        $ordered_groups = [];

        foreach ($order as $order_name) {
            $ordered_groups[$order_name] = $groups[$order_name];
            unset($groups[$order_name]);
        }

        return array_merge($ordered_groups, $groups);
    }
}
