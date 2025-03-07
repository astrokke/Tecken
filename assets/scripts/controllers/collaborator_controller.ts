import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["select", "button"];

  connect() {
    this.toggleButton();
  }

  toggleButton() {
    const selectElement = this.selectTarget as HTMLSelectElement;
    const addButton = this.buttonTarget as HTMLButtonElement;

    if (selectElement.value === "") {
      addButton.classList.add("text-decoration-line-through", "disabled");
      addButton.disabled = true;
    } else {
      addButton.classList.remove("text-decoration-line-through", "disabled");
      addButton.disabled = false;
    }
  }

  handleChange() {
    this.toggleButton();
  }
}
