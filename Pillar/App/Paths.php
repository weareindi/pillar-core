<?php

namespace PillarCore\App;

/**
 * Pillar Core Paths
 */
class Paths {
    public static function define() {
        // Define our paths
        define('ROOT', getcwd());
        define('COMMANDS', ROOT . '/commands');
        define('LIBRARY', ROOT . '/library');
        define('PAGES', LIBRARY . '/pages');
        define('PATTERNS', LIBRARY . '/patterns');
        define('VENDOR', ROOT . '/vendor');
        define('CORE', VENDOR . '/weareindi/pillar-core');
    }
}
