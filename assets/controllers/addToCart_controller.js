import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        $(this.element).on('click', function (event) {
            event.preventDefault();
            $.ajax({
                url: $(this).attr('href'),
                type: "GET",
                success: function(response) {
                    // Change #total text
                    $('#counter').text(response.total);
                }
            });
        })
    }
}