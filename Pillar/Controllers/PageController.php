<?php

namespace Pillar\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Pillar\Services\PageService;
use Pillar\App\App;
use Pillar\Twig\TwigService as Twig;

class PageController {
    /**
     * Show requested page
     */
    public static function show() {
        // Prepare data
        $page = PageService::get(self::getPath());

        $template = reset($page);

        Twig::render($template['template'], $template['data']['relative']);
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

    /**
     * Compile and return requested page as html
     */
    public static function html($path) {
        // Prepare data
        $page = PageService::get($path);

        $template = reset($page);

        return (string) Twig::compile($template['template'], $template['data']['relative']);
    }
}
