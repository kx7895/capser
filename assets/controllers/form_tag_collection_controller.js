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

    addCollectionElement(event) {
        const newItemOuter = document.createElement('div');
        newItemOuter.classList.add('btn-group');
        newItemOuter.classList.add('mb-1');
        newItemOuter.classList.add('me-1');

        const newItem = document.createElement('div');
        newItem.classList.add('btn-outline-primary');
        newItem.classList.add('btn-sm');

        // Entferne umschliessendes <div class="mb-3">-Element von Symfony
        let prototypeValue = this.prototypeValue;
        const regex = /<div class="mb-3">([\s\S]*?)<\/div>/;
        const match = regex.exec(prototypeValue);
        if (match !== null) {
            prototypeValue = prototypeValue.replace(match[0], match[1]);
        }

        // Ergänze form-control-sm zu form-control von Symfony
        const regex2 = /class="([^"]*form-control[^"]*)"/;
        const match2 = regex2.exec(prototypeValue);
        if (match2 !== null) {
            const modifiedContent = match2[0].replace('form-control', 'form-control form-control-sm');
            prototypeValue = prototypeValue.replace(match2[0], modifiedContent);
        }

        newItem.innerHTML = prototypeValue.replace(/__name__/g, this.indexValue);
        console.log(newItem);

        newItemOuter.appendChild(newItem);

        this.collectionContainerTarget.appendChild(newItemOuter);
        this.indexValue++;
        this.addCollectionDeleteButtonElement(newItemOuter);
    }

    addAllInitialDeleteButtons() {
        this.element.querySelectorAll('div.btn-group').forEach((item) => {
            this.addCollectionDeleteButtonElement(item);
        })
    }

    addCollectionDeleteButtonElement(item) {
        const deleteButton = document.createElement('button');
        deleteButton.classList.add('btn');
        deleteButton.classList.add('btn-sm');
        deleteButton.classList.add('btn-primary');
        deleteButton.innerHTML = '<i class="fa-solid fa-trash me-1"></i>';

        item.append(deleteButton);

        deleteButton.addEventListener('click', (e) => {
            e.preventDefault();
            item.remove();
        })
    }
}