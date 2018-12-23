<?php

namespace Pillar\Controllers;

use Pillar\Services\PageService;
use Pillar\App\App;
use Pillar\Twig\TwigService as Twig;

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
