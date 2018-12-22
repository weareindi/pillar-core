<?php

namespace PillarCore\App;

use Symfony\Component\Debug\Debug;

/**
 * Pillar Debug
 */
class Errors {
    public static function register() {
        Debug::enable();
    }
}
