import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    click() {
        fetch("/blog/post/blog/test", {
            headers: {
                Accept: "text/vnd.turbo-stream.html"
            }
        })
            .then(response => response.text())
            .then(html => Turbo.renderStreamMessage(html))
        ;
    }
}
