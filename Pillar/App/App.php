<?php

namespace PillarCore\App;

use Twig_Extension;
use PillarCore\App\Errors;
use PillarCore\App\Paths;
use PillarCore\App\Assets;
use PillarCore\App\Routes;
use PillarCore\Twig\TwigService as Twig;

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
