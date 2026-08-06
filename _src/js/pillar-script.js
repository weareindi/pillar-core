// Directives
import { CodeViewer } from './directives/codeviewer.js';
import { PatternBg } from './directives/patternbg.js';

// Run
new CodeViewer();

const patternBgInputs = document.querySelectorAll('input[name="pillar-pattern-bg"]');
new PatternBg(patternBgInputs);
