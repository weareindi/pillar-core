<?php

namespace Pillar\App;

use Twig\Extension\ExtensionInterface as TwigExtensionInterface;
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
    public static function addTwigExtension(TwigExtensionInterface $extension) {
        Twig::addExtension($extension);
    }

    /**
     * A handy helper to push Twig global context data through to the Twig engine
     * @param TwigGlobalContextData $data
     */
    public static function addTwigGlobalContextData(\TwigGlobalContextData $data) {
        foreach ($data as $name => $value) {
            Twig::addGlobal($name, $value);
        }
    }

    /**
     * Run Pillar
     */
    public static function run() {
        Routes::run();
    }
}
