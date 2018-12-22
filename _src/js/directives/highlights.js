import 'prismjs';
import 'prismjs/components/prism-twig';
import 'prismjs/components/prism-json';

export class Highlights {
    constructor(surface, type) {
        this.code = surface.innerText;
        this.html = Prism.highlight(this.code, Prism.languages[type]);
        surface.innerHTML = this.html;
    }
}
