import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
       // this.element.addEventListener('/blog/post/list', this.handleUpdate);
    }

    disconnect() {
    //    this.element.removeEventListener('/blog/post/list', this.handleUpdate);
    }

    handleUpdate(event) {
        const update = event.detail.update.message;


        console.log(update);

    }
}
