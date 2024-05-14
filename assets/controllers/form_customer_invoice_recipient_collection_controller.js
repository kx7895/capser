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
        this.element.querySelectorAll('#customerInvoiceRecipientRows .row').forEach((item) => {
            this.addCollectionDeleteButtonElement(item);
        })
    }

    addCollectionDeleteButtonElement(item) {
        const deleteButtonHolder = item.querySelector('.deleteCustomerInvoiceRecipientButtonHolder');

        const deleteButton = document.createElement('button');
        deleteButton.classList.add('btn');
        deleteButton.classList.add('btn-outline-primary');
        deleteButton.classList.add('btn-xs');
        deleteButton.innerHTML = '<i class="fa-solid fa-trash"></i>';

        deleteButtonHolder.append(deleteButton);

        deleteButton.addEventListener('click', (e) => {
            e.preventDefault();

            const confirm = window.confirm('Soll dieser Rechnungsempfänger wirklich gelöscht werden?');
            if(confirm)
                item.remove();
        })
    }
}