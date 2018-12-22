<?php

namespace PillarCore\Controllers;

use PillarCore\Services\PageService;
use PillarCore\App\App;
use PillarCore\Twig\TwigService as Twig;

/**
 * Patterns Controller
 */
class SitemapController {
    /**
     * List pages in library
     */
    public static function list() {
        $data = [
            'pages' => PageService::get()
        ];

        Twig::render('pillar/layouts/pillar-sitemap', $data);
    }
}
