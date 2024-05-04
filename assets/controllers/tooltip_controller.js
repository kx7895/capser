import { Controller } from "@hotwired/stimulus";
import { Tooltip } from 'bootstrap';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    showTooltip() {
        if (!this.tooltip) {
            this.tooltip = new Tooltip(this.element, {
                title: this.data.get("content"),
                trigger: 'manual'
            });
        }
        this.tooltip.show();
    }

    hideTooltip() {
        if (this.tooltip) {
            this.tooltip.hide();
        }
    }
}