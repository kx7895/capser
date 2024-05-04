import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["collectionContainer"]

    static values = {
        index: Number,
        prototype: String,
    }

    connect() {
        this.addAllInitialDeleteButtons();
    }

    // noinspection JSUnusedGlobalSymbols
    addCollectionElement() {
        const newItem = document.createElement('div');
        const prototypeValue = this.prototypeValue;

        newItem.innerHTML = prototypeValue.replace(/__name__/g, this.indexValue);

        this.collectionContainerTarget.appendChild(newItem);
        this.indexValue++;
        this.addCollectionDeleteButtonElement(newItem);
    }

    addAllInitialDeleteButtons() {
        this.element.querySelectorAll('#invoicePositionRows .row').forEach((item) => {
            this.addCollectionDeleteButtonElement(item);
        })
    }

    addCollectionDeleteButtonElement(item) {
        const deleteButtonHolder = item.querySelector('.deleteInvoicePositionButtonHolder');

        const deleteButton = document.createElement('button');
        deleteButton.classList.add('btn');
        deleteButton.classList.add('btn-outline-primary');
        deleteButton.classList.add('btn-sm');
        deleteButton.innerHTML = '<i class="fa-solid fa-trash"></i>';

        deleteButtonHolder.append(deleteButton);

        deleteButton.addEventListener('click', (e) => {
            e.preventDefault();

            const confirm = window.confirm('Soll diese Position wirklich gelöscht werden?');
            if(confirm)
                item.remove();
        })
    }
}