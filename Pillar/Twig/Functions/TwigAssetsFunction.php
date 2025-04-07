<?php

namespace Pillar\Twig\Functions;

use Twig\Extension\AbstractExtension as TwigAbstractExtension;
use Twig\TwigFunction;
use Pillar\App\Assets;

/**
 * This function gets assets as their required by the Pillar Core templates
 */
class TwigAssetsFunction extends TwigAbstractExtension {
    public function getFunctions(): array {
        return array(
            new TwigFunction('asset', array($this, 'getAsset')),
        );
    }

    public function getAsset($asset) {
        return Assets::get($asset);
    }
}
