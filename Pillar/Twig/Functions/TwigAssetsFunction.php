<?php

namespace PillarCore\Twig\Functions;

use Twig_Extension;
use Twig_Function;
use PillarCore\App\Assets;

/**
 * This function gets assets as their required by the Pillar Core templates
 */
class TwigAssetsFunction extends Twig_Extension {
    public function getFunctions() {
        return array(
            new Twig_Function('asset', array($this, 'getAsset')),
        );
    }

    public function getAsset($asset) {
        return Assets::get($asset);
    }
}
