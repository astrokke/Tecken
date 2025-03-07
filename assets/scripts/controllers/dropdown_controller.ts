import { Controller } from "@hotwired/stimulus";

interface DropdownTargets {
  menu: HTMLElement;
}

export default class DropdownController extends Controller<DropdownTargets> {
  static targets = ["menu"];

  declare readonly menuTarget: HTMLElement;

  connect(): void {
    console.log("Dropdown controller connected");
    document.addEventListener("click", this.close.bind(this));
  }

  disconnect(): void {
    document.removeEventListener("click", this.close.bind(this));
  }

  toggle(event: Event): void {
    event.preventDefault();
    console.log("Toggle method called");
    this.menuTarget.classList.toggle("d-none");
  }

  close(event: MouseEvent): void {
    if (!this.element.contains(event.target as Node)) {
      this.menuTarget.classList.add("d-none");
    }
  }
}

