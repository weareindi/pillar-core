<?php

namespace Pillar\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Pillar\Services\PatternService;

/**
 * Pillar Core Page Service
 */
class PageService {
    /**
     * Get pages
     * @param  String $path Absolute path to root directory of patterns
     * @return Array
     */
    public static function get(String $path = PAGES) {
        $pages = [];

        $di = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $ii = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::SELF_FIRST);

        // Process library patterns
        foreach($ii as $file) {
            if (
                // Is expected file type
                in_array(pathinfo($file, PATHINFO_EXTENSION), ['twig', 'json'])
                    // Hasn't already been processed
                    && !array_key_exists(dirname($file), $pages)
                ) {

                // Populate $pages
                $pages[self::pathToPages(dirname($file))] = dirname($file);
            }
        }

        // Now sort $pages alphabetically by base
        ksort($pages);

        // Populate pages now they're in correct order
        foreach ($pages as $page_path => $page_file) {
            $pages[$page_path] = self::populate($page_file);
        }

        return $pages;
    }

    /**
     * Get the pattern path base pattern directory
     * @param  String $path A full system path to the pattern file
     * @return String $base A directory name
     */
    protected static function base(String $path) {
        // Explode path and return the first pattern "base" parameter
        return explode('/', self::pathToPages($path))[0];
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
        $pattern['template'] = 'pages/' . self::template($path);
        $pattern['data'] = PatternService::data($path);

        return $pattern;
    }

    /**
     * Get and prepare the pattern template
     * @param  String $path An absolute path
     * @return Array        An array of prepared template data
     */
    public static function template(String $path) {
        $template = $path;
        if (PatternService::isAlternative($path)) {
            $template = PatternService::parentDirname($path);
        }

        return self::pathToPages($template);
    }
}
