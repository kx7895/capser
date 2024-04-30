import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        this.replaceClass();
    }

    replaceClass() {
        const childDivs = this.element.querySelectorAll('.form-check');
        childDivs.forEach(function(childDiv) {
            childDiv.classList.remove('form-check');
            const parent = childDiv.parentNode;
            while (childDiv.firstChild) {
                parent.insertBefore(childDiv.firstChild, childDiv);
            }
            parent.removeChild(childDiv);
        });

        const checkboxes = this.element.querySelectorAll('.form-check-input');
        checkboxes.forEach(checkbox => {
            checkbox.classList.remove('form-check-input');
            checkbox.classList.add('btn-check');
        });

        const labels = this.element.querySelectorAll('.form-check-label');
        labels.forEach(label => {
            label.classList.remove('required', 'form-check-label');
            label.classList.add('btn');
            label.classList.add('btn-light');
            label.classList.add('me-2');
        });
    }
}