<?php

namespace Pillar\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pillar\Services\PatternService;
use Pillar\App\App;
use Pillar\Twig\TwigService as Twig;

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

        Twig::render('pillar/layouts/pillar-isolate', $data);
    }

    /**
     * Return expensive code-viewer content only when a viewer is opened.
     */
    public static function codeviewer(): void {
        $pattern = (string) Request::createFromGlobals()->query->get('pattern', '');
        $patternsRoot = realpath(PATTERNS);
        $path = realpath(PATTERNS . '/' . $pattern);

        if (
            $patternsRoot === false
            || $path === false
            || !is_dir($path)
            || !str_starts_with($path, $patternsRoot . DIRECTORY_SEPARATOR)
        ) {
            (new JsonResponse(['error' => 'Pattern not found'], JsonResponse::HTTP_NOT_FOUND))->send();
            return;
        }

        $patterns = PatternService::get($path);
        $item = reset($patterns);
        if ($item === false || $item['url'] !== trim($pattern, '/')) {
            (new JsonResponse(['error' => 'Pattern not found'], JsonResponse::HTTP_NOT_FOUND))->send();
            return;
        }

        (new JsonResponse([
            'twig-pre' => trim(Twig::source($item['template'])),
            'twig-post' => trim((string) Twig::compile($item['template'], $item['data']['relative'])),
            'json-pre' => json_encode($item['data']['parent'], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
            'json-post' => json_encode($item['data']['relative'], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
        ]))->setPrivate()->setMaxAge(0)->send();
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
