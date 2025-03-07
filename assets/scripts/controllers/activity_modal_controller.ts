import { Controller } from "@hotwired/stimulus";
import { Modal } from "bootstrap";

export default class extends Controller {
  static values = {
    id: Number,
  };

  async openModal() {
    const modal = new Modal(document.getElementById("activityModal"));
    modal.show();

    const response = await fetch(`/activity/${this.idValue}`);
    const activityHtml = await response.text();
    document.getElementById("activityModalBody").innerHTML = activityHtml;
  }
}

