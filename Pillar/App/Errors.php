<?php

namespace Pillar\App;

use Symfony\Component\ErrorHandler\Debug;

/**
 * Pillar Debug
 */
class Errors {
    public static function register() {
        Debug::enable();
    }
}
