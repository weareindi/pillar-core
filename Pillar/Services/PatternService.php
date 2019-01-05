<?php

namespace Pillar\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Pillar Core Patterns Service
 */
class PatternService {
    /**
     * Get patterns
     * @param  String $path Absolute path to root directory of patterns
     * @return Array
     */
    public static function get(String $path = PATTERNS) {
        $patterns = [];

        $di = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $ii = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::SELF_FIRST);

        // Process library patterns
        foreach($ii as $file) {
            if (
                // Is expected file type
                in_array(pathinfo($file, PATHINFO_EXTENSION), ['twig', 'json'])
                    // Hasn't already been processed
                    && !array_key_exists(dirname($file), $patterns)
                        // Isn't hidden
                        && !self::isHidden(dirname($file))
                ) {

                // Populate $patterns
                $patterns[self::pathToPattern(dirname($file))] = dirname($file);
            }
        }

        // Now sort $patterns alphabetically by base
        ksort($patterns);

        // Populate patterns now they're in correct order
        foreach ($patterns as $pattern_path => $pattern_file) {
            $patterns[$pattern_path] = self::populate($pattern_file);
        }

        return $patterns;
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
        $pattern['template'] = 'patterns/' . self::template($path);
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
        if (self::isAlternative($path)) {
            $template = self::parentDirname($path);
        }

        return self::pathToPattern($template);
    }

    /**
     * Get and prepare the pattern data
     * @param  String $path An absolute path
     * @return Array        An array containing the relative data for the template and its parent data
     */
    public static function data(String $path) {
        $data = DataService::get($path);

        $data_parent = $data;
        if (self::isAlternative($path)) {
            $data_parent = DataService::get(self::parentDirname($path));
        }

        $data = self::merge($data_parent, $data);

        return [
            'relative' => $data,
            'parent' => $data_parent
        ];
    }

    /**
     * Recursive merge function totally stolen from https://medium.com/@kcmueller/php-merging-two-multi-dimensional-arrays-overwriting-existing-values-8648d2a7ea4f
     * Thanks https://medium.com/@kcmueller | https://www.kcmueller.de/
     *
     * PHP's built-in `array_merge_recursive` function turned string values with the same key into an array of values. We don't want that.
     *
     * @param  Array  $array1
     * @param  Array  $array2
     * @return Array
     */
    public static function merge(Array $array1, Array $array2) {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::merge($merged[$key], $value);
                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
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
            // If group name doesn't exist in current list of groups, we can skip
            if (!array_key_exists($order_name, $groups)) {
                continue;
            }

            $ordered_groups[$order_name] = $groups[$order_name];
            unset($groups[$order_name]);
        }

        return array_merge($ordered_groups, $groups);
    }
}
