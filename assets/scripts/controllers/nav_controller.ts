import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["links"];

  connect() {
    this.updateLinks();
    window.addEventListener("popstate", () => this.updateLinks());
  }

  updateLinks() {
    const links = this.linksTargets;
    const currentPath = window.location.pathname;

    links.forEach((link: HTMLElement) => {
      if (
        link.getAttribute("href") === currentPath ||
        link.getAttribute("href") === "/" + currentPath.split("/")[1]
      ) {
        link.classList.add("active");
        link.setAttribute("aria-current", "page");
      } else {
        link.classList.remove("active");
        link.removeAttribute("aria-current");
      }
    });
  }
}
