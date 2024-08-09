import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = { vatRate: Number };

    connect() {
        this.calculateSum();
    }

    // Funktion zum Parsen einer Dezimalzahl aus einem String
    makeFloat(value) {
        return parseFloat(value.replace(',', '.'));
    }

    // Funktion zum Runden einer Zahl auf eine bestimmte Anzahl von Dezimalstellen
    round(value, decimals) {
        return value.toFixed(decimals);
    }

    // Funktion zur Berechnung der Summe pro Zeile
    calculateSumPerRow(item) {
        const dataIndex = item.getAttribute('data-index');
        let amount = this.makeFloat(item.querySelector(`#invoice_positions_form_invoicePositions_${dataIndex}_amount`).value);
        let price = this.makeFloat(item.querySelector(`#invoice_positions_form_invoicePositions_${dataIndex}_price`).value);
        let sum = this.round((amount * price), 2);
        const sumHolder = item.querySelector('.sumHolder');
        sumHolder.innerHTML = ''; // clear
        const sumDiv = document.createElement('div');
        sumDiv.innerText = sum.replace('.', ',');
        sumHolder.append(sumDiv);
        return sum;
    }

    // Funktion zum Setzen des Netto-Betrags
    setAmountNet(value) {
        const amountNetHolder = document.querySelector('.amountNetHolder');
        if(amountNetHolder) {
            amountNetHolder.innerHTML = '';
            const amountNetDiv = document.createElement('div');
            const roundedValue = this.round(value, 2);
            amountNetDiv.innerText = Number(roundedValue).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            amountNetHolder.append(amountNetDiv);
        }
    }

    // Funktion zum Setzen des Steuerbetrags
    setAmountTax(value) {
        let amountTax = value * (this.vatRateValue / 100);
        let amountTaxRounded = this.round(amountTax, 2);
        const amountTaxHolder = document.querySelector('.amountTaxHolder');
        if(amountTaxHolder) {
            amountTaxHolder.innerHTML = ''; // clear
            const amountTaxDiv = document.createElement('div');
            amountTaxDiv.innerText = Number(amountTaxRounded).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            amountTaxHolder.append(amountTaxDiv);
        }
        return amountTaxRounded;
    }

    // Funktion zum Setzen des Bruttobetrags
    setAmountGross(value, amountTax) {
        let amountGross = parseFloat(value) + parseFloat(amountTax);
        let amountGrossRounded = this.round(amountGross, 2);
        const amountGrossHolder = document.querySelector('.amountGrossHolder');
        if(amountGrossHolder) {
            amountGrossHolder.innerHTML = '';
            const amountGrossDiv = document.createElement('div');
            amountGrossDiv.innerText = Number(amountGrossRounded).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            amountGrossHolder.append(amountGrossDiv);
        }
    }

    // Hauptfunktion zur Berechnung der Gesamtsumme
    calculateSum() {
        let totalSum = 0;

        document.querySelectorAll('#invoicePositionRows .row')
            .forEach((invoicePosition) => {
                let sum = this.calculateSumPerRow(invoicePosition);
                totalSum = totalSum + this.makeFloat(sum);
            });

        this.setAmountNet(totalSum);
        let amountTax = this.setAmountTax(totalSum);
        this.setAmountGross(totalSum, amountTax);
    }

    // Funktion zum Aktualisieren der Summe bei Bedarf (z. B. beim Ändern der Eingabefelder)
    refresh() {
        this.calculateSum();
    }
}
