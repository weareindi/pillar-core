<?php

namespace Pillar\App;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;

/**
 * Pillar Core Routes
 */
class Routes {
    protected static $routes;

    /**
     * Register all URL routes
     */
    public static function register() {
        $routes = new RouteCollection();
        $routes->add('isolate', new Route('/patterns/{pattern}/isolate', ['_controller' => 'Pillar\Controllers\PatternsController::isolate'], ['pattern' => '.+']));
        $routes->add('pattern', new Route('/patterns/{pattern}', ['_controller' => 'Pillar\Controllers\PatternsController::list'], ['pattern' => '.+']));
        $routes->add('patterns', new Route('/patterns', ['_controller' => 'Pillar\Controllers\PatternsController::list']));
        $routes->add('index', new Route('/', ['_controller' => 'Pillar\Controllers\PatternsController::list']));
        $routes->add('pages', new Route('/pages', ['_controller' => 'Pillar\Controllers\PagesController::list']));
        $routes->add('page', new Route('/pages/{pattern}', ['_controller' => 'Pillar\Controllers\PageController::show'], ['pattern' => '.+']));
        $routes->add('image', new Route('/image/{pattern}', ['_controller' => 'Pillar\Controllers\ImageController::show'], ['pattern' => '.+']));

        self::$routes = $routes;
    }

    /**
     * Handle route request
     */
    public static function run() {
        $request = Request::createFromGlobals();
        $requestContext = new RequestContext();
        $requestContext->fromRequest($request);

        $urlMatcher = new UrlMatcher(self::$routes, $requestContext);

        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();

        try {
            $pathInfo = $requestContext->getPathInfo();
            $request->attributes->add($urlMatcher->match($pathInfo));
            $controller = $controllerResolver->getController($request);
            $arguments = $argumentResolver->getArguments($request, $controller);
            $response = call_user_func_array($controller, $arguments);
        } catch (ResourceNotFoundException $exception) {
            $response = new Response('Not Found', 404);
            $response->send();
        } catch (Exception $exception) {
            $response = new Response('An error occurred', 500);
            $response->send();
        }
    }
}
