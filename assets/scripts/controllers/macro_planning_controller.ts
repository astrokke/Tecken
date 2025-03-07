import { Controller } from "@hotwired/stimulus";
import DomManager from "../services/DomManager.service.ts";
import MacroPlanningData from "../services/MacroPlanningData.service.ts";
import Milestone from "../interfaces/Milestone.ts";
import User from "../interfaces/User.ts";
import { Modal } from "bootstrap";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    date: String,
  };
  static targets = ["row", "modal", "modalBody"];
  private domManager: DomManager = new DomManager();
  private parentWidth: number;

  connect() {
    window.addEventListener("resize", () => this.handleResize());
    this.parentWidth = this.rowTargets[0]?.getBoundingClientRect().width || 0;

    const result = MacroPlanningData.getMilestones(this.dateValue);
    result
      .then((milestones: Milestone[]) => {
        milestones.forEach((milestone: Milestone) => {
          if (
            milestone.startDate < milestone.planningStart &&
            milestone.endDate > milestone.planningStop
          ) {
            this.createMacroPlanning(milestone, 0);
          } else if (
            milestone.startDate < milestone.planningStart &&
            milestone.endDate < milestone.planningStop
          ) {
            this.createMacroPlanning(milestone, 0);
          } else {
            const left = (this.parentWidth * milestone.startPercent) / 100;
            this.createMacroPlanning(milestone, left);
          }
        });
      })
      .catch((e) => {
        console.log(`erreur macro_planning: ${e}`);
      });
  }

  createMacroPlanning(milestone: Milestone, left: number): void {
    this.rowTargets.forEach((row: HTMLElement) => {
      if (Number(row.dataset.activity) === milestone.activityId) {
        const width =
          (this.parentWidth / milestone.planningTotalDays) * milestone.totalDay;
        const start_date = new Date(milestone.startDate);
        const end_date = new Date(milestone.endDate);
        const now = new Date();
        let color = `background-color: #7FA9C0;`;
        const one_day = 1000 * 60 * 60 * 24;
        if (Math.round(end_date.getTime() - now.getTime()) / one_day <= 30) {
          color = `background-color: #CD9380;`;
        }

        const date = `${new Intl.DateTimeFormat("fr-FR").format(
          start_date
        )}-${new Intl.DateTimeFormat("fr-FR").format(end_date)}`;
        const tasks = `Taches: ${milestone.completedTasks}/${milestone.totalTasks}`;
        const completion = `${milestone.percentCompletion?.toFixed(0)}%`;
        if (completion === "100%") {
          color = `background-color: #94C07F;`;
        }

        const container = this.domManager.createHtml("div", "", row, [
          {
            style: `position: relative; height: 100%; width: 100%; margin-bottom: 1px;`,
            class: ``,
          },
        ]);
        const card = this.domManager.createHtml("div", "", container, [
          {
            style: `left: ${left}px; width: ${width}px; ${color} cursor: pointer;`,
            class: `card macro-planning-card`,
            "data-action": "click->macro-planning#openModal",
            "data-macro-planning-id-param": milestone.milestoneId,
          },
        ]);

        const card_row = this.domManager.createHtml("div", "", card, [
          { class: `row g-0` },
        ]);
        const card_left_block = this.domManager.createHtml(
          "div",
          "",
          card_row,
          [{ class: `col-md-6 macro-planning-left` }]
        );
        const card_right_block = this.domManager.createHtml(
          "div",
          "",
          card_row,
          [{ class: `col-md-6 pe-3 macro-planning-right` }]
        );

        const card_body = this.domManager.createHtml(
          "div",
          "",
          card_left_block,
          [{ class: `card-body ps-3 p-0` }]
        );
        // const update_link = this.domManager.createHtml('a', '', card_body, [{ class: ``, href: `${window.location.origin}/macro-planning/edit/${milestone.milestoneId}` }]);
        const _title = this.domManager.createHtml(
          "h5",
          `${milestone.label} `,
          card_body,
          [{ class: `card-title fw-bold mb-3 macro-planning-text` }]
        );
        const users_block = this.domManager.createHtml("div", "", card_body, [
          { class: `d-flex` },
        ]);
        if (milestone.users && milestone.users?.length > 0) {
          milestone.users?.forEach((user: User) => {
            const user_badge = this.domManager.createHtml(
              "p",
              ``,
              users_block,
              [
                {
                  class: `m-0 bg-primary text-white text-center d-inline fw-bold rounded-circle p-1 font-monospace`,
                  style: `z-index: 3; width:25px; height:25px; margin-right: 2px!important;`,
                },
              ]
            );
            const user_initial = this.domManager.createHtml(
              "abbr",
              `${user.firstName.slice(0, 1)}${user.lastName.slice(0, 1)}`,
              user_badge,
              [
                {
                  class: `m-0`,
                  title: `${user.firstName} ${user.lastName}`,
                  style: `text-decoration: none;`,
                },
              ]
            );
          });
        }

        const dates_block = this.domManager.createHtml(
          "p",
          date,
          card_right_block,
          [
            {
              class: `card-text fw-bold mb-1 text-end fs-6 macro-planning-text`,
            },
          ]
        );
        const tasks_block = this.domManager.createHtml(
          "p",
          tasks,
          card_right_block,
          [{ class: `card-text fw-bold mb-1 text-end macro-planning-text` }]
        );
        const advancement_block = this.domManager.createHtml(
          "p",
          completion,
          card_right_block,
          [{ class: `card-text fw-bold mb-1 text-end macro-planning-text` }]
        );
      }
    });
  }
  async openModal(event: Event) {
    this.modalBodyTarget.innerHTML = await MacroPlanningData.getMilestone(
      event.params.id
    );
    const modal = new Modal(this.modalTarget);
    modal.show();
    const observer = new MutationObserver(this.reloadOnClose);
    const options = {
      attributes: true,
    };
    observer.observe(this.modalTarget, options);
  }

  reloadOnClose(mutationList, observer) {
    mutationList.forEach(function (mutation) {
      if (
        mutation.type === "attributes" &&
        mutation.attributeName === "class"
      ) {
        if (mutation.target.className === "modal fade") {
          location.reload();
        }
      }
    });
  }

  private handleResize() {
    this.parentWidth = this.rowTargets[0]?.getBoundingClientRect().width || 0;
    // Recalculer les positions existantes
  }
}
