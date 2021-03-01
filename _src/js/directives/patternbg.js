export class PatternBg {
    constructor(inputs) {
        this.inputs = inputs;

        this.restoreInput();
        this.binds();
    }

    restoreInput() {
        this.restoredInput = window.localStorage.getItem('patternbg');

        if (this.restoredInput) {
            this.setCheckedInput();
        }
    }

    store(id) {
        window.localStorage.setItem('patternbg', id);
    }

    getCheckedInput() {
        Array.from(this.inputs, (input) => {
            if (input.checked) {
                this.store(input.id);
            }
        });
    }

    setCheckedInput() {
        Array.from(this.inputs, (input) => {
            if (input.id === this.restoredInput) {
                input.checked = true;
            }
        });
    }

    binds() {
        Array.from(this.inputs, (input) => {
            input.addEventListener('change', () => {
                this.getCheckedInput();
            });
        });
    }
}
