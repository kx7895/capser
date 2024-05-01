import { Controller } from "@hotwired/stimulus"
import debounce from 'debounce'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    initialize() {
        this.debouncedSubmit = debounce(this.debouncedSubmit.bind(this), 200)
    }

    submit(e) {
        this.element.requestSubmit()
    }

    debouncedSubmit() {
        this.submit()
    }

    submitOnChange() {
        this.element.submit();
    }
}