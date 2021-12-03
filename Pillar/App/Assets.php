<?php

namespace Pillar\App;

use Twig\Error\Error as TwigError;

/**
 * Pillar Core Assets
 *
 * Used by the Pillar front end
 */
class Assets {

    protected static $assets;

    /**
     * Register assets
     */
    public static function register() {
        self::$assets['pillar-css'] = self::fetchCss();
        self::$assets['pillar-js'] = self::fetchJs();
    }

    /**
     * Fetch Pillar CSS as a string
     * @return string
     */
    protected static function fetchCss() {
        $path = CORE . '/Pillar/Views/Assets/css/pillar-style.css';

        if (!file_exists($path)) {
            throw new TwigError('Asset: Required CSS file does not exist');
        }

        return file_get_contents($path);
    }

    /**
     * Fetch Pillar Javscript as a string
     * @return string
     */
    protected static function fetchJs() {
        $path = CORE . '/Pillar/Views/Assets/js/pillar-script.js';

        if (!file_exists($path)) {
            throw new TwigError('Asset: Required JS file does not exist');
        }

        return file_get_contents($path);
    }

    /**
     * Get required asset
     * @param  string/bool $asset Name of required asset
     * @return string/array       array of assets or string of specific asset
     */
    public static function get($asset = false) {
        if (!$asset) {
            return self::$assets;
        }

        if (!isset(self::$assets[$asset])) {
            throw new TwigError('Required asset has not been defined');
        }

        return self::$assets[$asset];
    }
}
