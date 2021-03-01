export class CheckboxRadio {
    constructor(surface) {
        this.checkboxElement = document.querySelector('#' + surface.getAttribute('for'));
        this.groupElements = document.querySelectorAll('[name=' + this.checkboxElement.name + ']');

        surface.addEventListener('click', (event) => {
            event.preventDefault();

            let checkActive = false;
            if (this.checkboxElement.getAttribute('checked')) {
                checkActive = true;
            }

            Array.from(this.groupElements, (element) => {
                element.removeAttribute('checked');
            });

            if (!checkActive) {
                this.checkboxElement.setAttribute('checked', 'checked');
            }
        });
    }
}
