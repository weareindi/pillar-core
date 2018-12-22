// Directives
import { Highlights } from './directives/highlights.js';
import { CheckboxRadio } from './directives/checkboxradio.js';
import { PatternBg } from './directives/patternbg.js';

// Run
const highlightsTwigSurfaces = document.querySelectorAll('.pillar-codeviewer__highlight--twig code');
if (highlightsTwigSurfaces.length > 0) {
    Array.from(highlightsTwigSurfaces).forEach((surface) => {
        new Highlights(surface, 'twig');
    });
}

const highlightsHtmlSurfaces = document.querySelectorAll('.pillar-codeviewer__highlight--html code');
if (highlightsHtmlSurfaces.length > 0) {
    Array.from(highlightsHtmlSurfaces).forEach((surface) => {
        new Highlights(surface, 'markup');
    });
}

const highlightsJsonSurfaces = document.querySelectorAll('.pillar-codeviewer__highlight--json code');
if (highlightsJsonSurfaces.length > 0) {
    Array.from(highlightsJsonSurfaces).forEach((surface) => {
        new Highlights(surface, 'json');
    });
}

const checkboxradioSurfaces = document.querySelectorAll('.checkboxradio');
if (checkboxradioSurfaces.length > 0) {
    Array.from(checkboxradioSurfaces).forEach((surface) => {
        new CheckboxRadio(surface);
    });
}

const patternBgInputs = document.querySelectorAll('input[name="pillar-pattern-bg"]');
new PatternBg(patternBgInputs);
