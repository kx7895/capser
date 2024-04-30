import { Controller } from "@hotwired/stimulus";
import { Toast } from "bootstrap";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const delay = parseInt(this.element.dataset.bsDelay) || 6000;

        const toast = new Toast(this.element);
        toast.show();

        // TODO: Einbauen einer animierten Progressbar vor Schliessung (https://codepen.io/404ryannotfound/pen/KKZgagL)
    }

    disconnect() {
        const toast = Toast.getInstance(this.element);
        if (toast) {
            toast.hide();
        }
    }
}



