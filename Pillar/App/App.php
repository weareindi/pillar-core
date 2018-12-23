<?php

namespace Pillar\App;

use Twig_Extension;
use Pillar\App\Errors;
use Pillar\App\Paths;
use Pillar\App\Assets;
use Pillar\App\Routes;
use Pillar\Twig\TwigService as Twig;

/**
 * Pillar Core
 */
class App {
    /**
     * Prepare Pillar
     */
    public static function register() {
        // Register error management
        Errors::register();

        // Define Paths
        Paths::define();

        // Register Assets
        Assets::register();

        // Register Routes
        Routes::register();

        // Register Twig
        Twig::register();
    }

    /**
     * A handy helper to push Twig extentions through to the Twig engine
     * @param Twig_Extension $extension A twig extension
     */
    public static function addTwigExtension(Twig_Extension $extension) {
        Twig::addExtension($extension);
    }

    /**
     * Run Pillar
     */
    public static function run() {
        Routes::run();
    }
}
