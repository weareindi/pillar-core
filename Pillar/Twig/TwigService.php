<?php

namespace Pillar\Twig;

use Twig;
use Twig\Extension\DebugExtension as TwigDebugExtension;
use Twig\Extension\ExtensionInterface as TwigExtensionInterface;
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

        $loader = new Twig\Loader\ChainLoader([
            new Twig\Loader\FilesystemLoader($paths),
            new TwigCustomLoader($paths)
        ]);

        self::$twig = new Twig\Environment($loader, [
            'debug' => true
        ]);

        self::addExtension(new TwigDebugExtension());
        self::addExtension(new TwigAssetsFunction());
    }

    /**
     * Render TWIG template
     * @param  string $template [description]
     * @param  array  $data     [description]
     * @return [type]           [description]
     */
    public static function render(string $template, array $data = []) {
        echo self::compile($template, $data);
    }

    /**
     * Add TWIG extensions
     * @param Twig_Extension $extension [description]
     */
    public static function addExtension(TwigExtensionInterface $extension) {
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
     * @param  string $template [description]
     * @param  array  $data     [description]
     * @return [type]           [description]
     */
    public static function compile(string $template, array $data = []) {
        return self::$twig->render($template, $data);
    }
}
