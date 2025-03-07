import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["content"];

  showContent(event) {
    event.preventDefault();
    const targetId = event.target.getAttribute("href").slice(1);
    const contentDiv = document.getElementById(targetId);

    this.hideAllContents();

    contentDiv.style.display = "block";
  }

  hideAllContents() {
    const contents = document.querySelectorAll("#content > div");
    contents.forEach((content) => {
      content.style.display = "none";
    });
  }
}
