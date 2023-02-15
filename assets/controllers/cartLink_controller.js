import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        $(this.element).on('click', function () {
            window.location.href = $('#cart-link').attr('href')
        })
    }
}