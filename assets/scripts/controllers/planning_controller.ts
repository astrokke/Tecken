import { Controller } from "@hotwired/stimulus";
import DomManager from "../services/DomManager.service.ts";
import PlanningData from "../services/PlanningData.service.ts";
import PlannedTask from "../interfaces/PlannedTask.ts";

export default class extends Controller {
  static values = {
    week: String,
  };
  static targets = ["day"]; // correspond aux <td data-planning-target="day">

  private domManager: DomManager = new DomManager();

  connect() {
    console.log("Planning pour la semaine :", this.weekValue);

    // Récupération des tâches planifiées depuis l'API/serveur
    PlanningData.getPlannedTasks(this.weekValue)
      .then((tasks: PlannedTask[]) => {
        tasks.forEach((task) => {
          this.dayTargets.forEach((dayElement: HTMLElement) => {
            if (
              dayElement.dataset.date === task.day &&
              Number(dayElement.dataset.user) === task.userId
            ) {
              this.createTaskCard(dayElement, task);
            }
          });
        });
      })
      .catch((err) =>
        console.error("Erreur de récupération du planning :", err),
      );
  }

  private createTaskCard(dayElement: HTMLElement, task: PlannedTask) {
    const card = document.createElement("div");
    card.classList.add("card", "mb-2", "shadow-sm");
    console.log("couleur", task.color);

    if (task.color === "primary") {
      card.classList.add("bg-primary", "text-white");
    } else if (task.color === "danger") {
      card.classList.add("bg-danger", "text-white");
    }

    // En-tête de la carte (clientName)
    if (task.clientName) {
      const cardHeader = document.createElement("div");
      cardHeader.classList.add("card-header", "p-1", "text-truncate");
      cardHeader.textContent = task.clientName.toUpperCase();
      card.appendChild(cardHeader);
    }

    // Corps de la carte (activité, nom de tâche, horaires)
    const cardBody = document.createElement("div");
    cardBody.classList.add("card-body", "p-2");

    // Titre (activité + nom de la tâche)
    const title = document.createElement("h6");
    title.classList.add("card-title", "mb-1");
    title.textContent = `${task.activityName} - ${task.taskName}`;

    // Paragraphe pour les horaires
    const hours = document.createElement("p");
    hours.classList.add("card-text", "mb-0");
    hours.textContent = `${task.startHour} - ${task.endHour}`;

    cardBody.appendChild(title);
    cardBody.appendChild(hours);

    card.appendChild(cardBody);

    dayElement.appendChild(card);
  }
}
