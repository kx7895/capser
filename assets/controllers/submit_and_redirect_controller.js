import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = { submit: String, href: String };
    redirect() {
        const submitButton = document.getElementById(this.submitValue);
        submitButton.click();
        window.open(this.hrefValue, "_blank");
    }
}