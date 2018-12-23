<?php

namespace Pillar\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Pillar\Services\PageService;
use Pillar\App\App;
use Pillar\Twig\TwigService as Twig;

/**
 * Patterns Controller
 */
class PagesController {
    /**
     * List required patterns in library
     */
    public static function show() {

        // Prepare data
        $page = PageService::get(self::getPath());

        $template = reset($page);

        Twig::render($template['template'], $template['data']);
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
            $path = PAGES;
        }

        return $path;
    }
}
