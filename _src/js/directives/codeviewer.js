import { Highlights } from './highlights.js';

const TYPES = {
    'twig-pre': 'twig',
    'twig-post': 'markup',
    'json-pre': 'json',
    'json-post': 'json'
};

export class CodeViewer {
    constructor(root = document) {
        this.requests = new WeakMap();

        root.addEventListener('click', (event) => {
            const control = event.target.closest('.checkboxradio');
            if (!control) {
                return;
            }

            const input = document.getElementById(control.htmlFor);
            const viewer = control.closest('.pillar-codeviewer');
            if (!input || !viewer) {
                return;
            }

            event.preventDefault();

            const opening = !input.checked;
            for (const groupInput of viewer.querySelectorAll(`input[name="${input.name}"]`)) {
                groupInput.checked = false;
            }
            input.checked = opening;

            if (opening) {
                this.open(viewer, input.id);
            }
        });
    }

    async open(viewer, inputId) {
        const mode = Object.keys(TYPES).find((type) => inputId.includes(`-${type}-`));
        if (!mode) {
            return;
        }

        const surface = viewer.querySelector(`.pillar-codeviewer__code--${mode} code`);
        if (!surface || surface.dataset.highlighted === 'true') {
            return;
        }

        surface.textContent = 'Loading…';

        try {
            const content = await this.load(viewer);
            surface.textContent = content[mode] ?? '';
            new Highlights(surface, TYPES[mode]);
        } catch (error) {
            surface.textContent = error.message;
        }
    }

    load(viewer) {
        if (!this.requests.has(viewer)) {
            this.requests.set(viewer, fetch(viewer.dataset.codeviewerUrl, {
                headers: {'Accept': 'application/json'}
            }).then((response) => {
                if (!response.ok) {
                    throw new Error(`Unable to load code viewer (${response.status})`);
                }

                return response.json();
            }));
        }

        return this.requests.get(viewer);
    }
}
