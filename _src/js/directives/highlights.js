import Prism from 'prismjs';
import 'prismjs/components/prism-markup-templating';
import 'prismjs/components/prism-twig';
import 'prismjs/components/prism-json';

// Pillar highlights code viewers as they are opened, not during page load.
Prism.manual = true;

export class Highlights {
    constructor(surface, type) {
        if (surface.dataset.highlighted === 'true') {
            return;
        }

        surface.innerHTML = Prism.highlight(surface.textContent, Prism.languages[type], type);
        surface.dataset.highlighted = 'true';
    }
}
