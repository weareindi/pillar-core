<?php

namespace PillarCore\Controllers;

use Symfony\Component\HttpFoundation\Request;
use PillarCore\Services\PatternService;
use PillarCore\App\App;
use PillarCore\Twig\TwigService as Twig;

/**
 * Patterns Controller
 */
class PatternsController {
    /**
     * List required patterns in library
     */
    public static function list() {
        // Prepare data
        $data = [
            'groups' => PatternService::getGroups(self::getPath())
        ];

        Twig::render('pillar/layouts/pillar-patterns', $data);
    }

    /**
     * Display isloated pattern in library
     */
    public static function isolate() {
        // Remove '/isolate' from end of uri
        $path = substr(self::getPath(), 0, -strlen('/isolate'));

        $patterns = PatternService::getGroups($path);
        $pattern = reset($patterns);
        $pattern = reset($pattern);

        // Prepare data
        $data = [
            'pattern' => $pattern
        ];

        Twig::register();
        Twig::render('pillar/layouts/pillar-isolate', $data);
    }

    /**
     * Get currently required path
     */
    public static function getPath() {
        // Get current request
        $request = Request::createFromGlobals();

        // Combine request uri with absolute library path
        $path = rtrim(LIBRARY . $request->getRequestUri(), '/');

        // If a path matches library path
        if ($path === LIBRARY) {
            $path = PATTERNS;
        }

        return $path;
    }
}
