<?php

namespace Pillar\Twig;

use Twig_Extension;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Chain;
use Twig_Loader_Filesystem;
use Pillar\Twig\TwigCustomLoader;
use Pillar\Twig\Functions\TwigAssetsFunction;

/**
 * Twig Service
 */
class TwigService {
    protected static $twig;

    /**
     * Initialise and prepare our Twig environment
     */
    public static function register() {
        $paths = [
            ROOT,
            LIBRARY,
            PATTERNS,
            PAGES,
            CORE . '/Pillar/Views/Templates'
        ];

        $loader = new Twig_Loader_Chain([
            new Twig_Loader_Filesystem($paths),
            new TwigCustomLoader($paths)
        ]);

        self::$twig = new Twig_Environment($loader, [
            'debug' => true
        ]);

        self::addExtension(new Twig_Extension_Debug());
        self::addExtension(new TwigAssetsFunction());
    }

    /**
     * Render TWIG template
     * @param  String $template [description]
     * @param  array  $data     [description]
     * @return [type]           [description]
     */
    public static function render(String $template, Array $data = []) {
        echo self::compile($template, $data);
    }

    /**
     * Add TWIG extensions
     * @param Twig_Extension $extension [description]
     */
    public static function addExtension(Twig_Extension $extension) {
        self::$twig->addExtension($extension);
    }

    /**
     * Add to global context
     * @param string $name
     * @param string|array $value
     */
    public static function addGlobal($name, $value) {
        self::$twig->addGlobal($name, $value);
    }

    /**
     * Compile TWIG template
     * @param  String $template [description]
     * @param  array  $data     [description]
     * @return [type]           [description]
     */
    public static function compile(String $template, Array $data = []) {
        return self::$twig->render($template, $data);
    }
}
