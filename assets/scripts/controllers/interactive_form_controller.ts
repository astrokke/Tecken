import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["label", "input", "form", "submit"];

  activateInput(event: Event) {
    const label: HTMLElement = event.target as HTMLElement;
    const input: HTMLElement = this.inputTargets[event.target.dataset.index];
    label.style.opacity = "0";
    label.style.position = "absolute";
    label.style.height = "0";
    label.style.width = "0";
    input.style.opacity = "100%";
    input.style.position = "unset";
    input.style.height = "auto";
    input.style.width = "auto";
  }

  closeInput(event: Event) {
    const input: HTMLElement = event.target as HTMLElement;
    const label: HTMLElement = this.labelTargets[event.target.dataset.index];
    input.style.opacity = "0";
    input.style.position = "absolute";
    input.style.height = "0";
    input.style.width = "0";
    label.style.opacity = "100%";
    label.style.position = "unset";
    label.style.height = "auto";
    label.style.width = "auto";
    if (input.dataset.type === "date") {
      this.formatDate(input, label);
    } else if (input.dataset.type === "select") {
      this.findOption(input, label);
    } else if (input.dataset.type === "duration") {
      this.formatDuration(input, label);
    } else {
      label.innerHTML = input.value;
    }
    this.submitTarget.click();
  }

  formatDate(input: HTMLElement, label: HTMLElement) {
    const months = [
      "janvier",
      "février",
      "mars",
      "avril",
      "mai",
      "juin",
      "juillet",
      "août",
      "septembre",
      "octobre",
      "novembre",
      "décembre",
    ];
    const date = new Date(input.value);
    label.innerHTML = `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
  }

  formatDuration(input: HTMLElement, label: HTMLElement) {
    label.innerHTML = `${input.value}h`;
  }

  findOption(input: HTMLElement, label: HTMLElement) {
    for (const option of input.options) {
      if (option.selected === true) {
        label.innerHTML = option.innerHTML;
      }
    }
  }
}
