import { Controller } from "@hotwired/stimulus";
import "fullcalendar"; //this is needed.
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPluggin from "@fullcalendar/timegrid";
import listPluggin from "@fullcalendar/list";
import interactionPluggin, { Draggable } from "@fullcalendar/interaction";
import {
  getDueTasks,
  deleteDueTask,
  updateDueTask,
  createDueTask,
} from "../services/CalendarData.service.ts";
import {
  DueTask,
  EventData,
  CalendarState,
} from "../interfaces/CalendarType.ts";

export default class extends Controller {
  private calendar!: Calendar;
  static targets = ["calendar", "externalEvents", "taskDetails"];

  connect() {
    this.calendar = this.initCalendar();
    this.initDraggable();

    const offcanvasElement = document.getElementById("offcanvasTaskList");
    if (!offcanvasElement) {
      return;
    }
    offcanvasElement.addEventListener("shown.bs.offcanvas", () => {
      this.resizeCalendar(false);
      this.calendar.reloadCalendar();
    });

    offcanvasElement.addEventListener("hidden.bs.offcanvas", () => {
      this.resizeCalendar(true);
      this.calendar.reloadCalendar(this.initCalendar());
    });
  }
  resizeCalendar(isFullWidth) {
    const calendarContainer = document.getElementById("calendar-container");
    if (!calendarContainer) {
      return;
    }
    if (isFullWidth) {
      calendarContainer.style.width = "100%";
    } else {
      calendarContainer.style.width = "calc(100% - 300px)";
    }
    if (this.calendar) {
      this.calendar.updateSize();
    }
  }
  initDraggable() {
    new Draggable(this.externalEventsTarget, {
      itemSelector: ".fc-event",
      eventData: function (eventEl: HTMLElement): EventData {
        const dataStr = eventEl.getAttribute("data-event");
        if (dataStr === null) {
          throw new Error("Event data attribute is missing");
        }
        try {
          const data: EventData = JSON.parse(dataStr);
          return data;
        } catch (error) {
          console.error("Failed to parse event data", error);
          throw error;
        }
      },
    });
  }

  initCalendar() {
    const cal = new Calendar(this.calendarTarget, {
      plugins: [
        dayGridPlugin,
        timeGridPluggin,
        listPluggin,
        interactionPluggin,
      ],

      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "timeGridDay,timeGridWeek,dayGridMonth,listWeek",
      },

      buttonText: {
        today: "Aujourd'hui",
        month: "Mois",
        week: "Semaine",
        day: "Jour",
        list: "Liste",
      },

      initialView: "timeGridWeek",
      droppable: true,
      allDaySlot: false,
      handleWindowResize: true,
      expandRows: true,
      nowIndicator: true,
      editable: true,
      eventStartEditable: true,
      eventResizableFromStart: true,
      dayMaxEventRows: true,
      navLinks: true,
      businessHours: {
        startTime: "09:30",
        endTime: "17:30",
      },
      slotMinTime: "09:30:00",
      slotMaxTime: "17:30:00",
      longPressDelay: 3000, // (3 secondes)
      views: {
        timeGridDay: {
          titleFormat: { year: "numeric", month: "long", day: "numeric" },
        },
        listWeek: { hiddenDays: [0, 6] },
        timeGridWeek: { eventMaxStack: 4, hiddenDays: [0, 6] },
        dayGridMonth: { dayMaxEventRows: 6, hiddenDays: [0, 6] },
      },

      events: async function (info, successCallBack, failureCallBack) {
        const startDate = info.start.toISOString().split("T")[0];
        const endDate = info.end.toISOString().split("T")[0];
        try {
          const data: DueTask[] = await getDueTasks(startDate, endDate);
          successCallBack(data);
        } catch (error) {
          console.error("Error Fetch", error);
          failureCallBack(error);
        }
      },

      datesSet: function (info) {
        cal.refetchEvents();
      },

      eventDidMount: function (info) {
        let eventElement = info.el;
        let comment = info.event.extendedProps.description;
        let type = info.event.extendedProps.type;
        let activity = info.event.extendedProps.activity;
        let state = info.event.extendedProps.state;

        eventElement.classList.add("custom-border-top");
        let commentElement = document.createElement("div");

        let iconContainer = document.createElement("div");
        iconContainer.classList.add(
          "fc-event-icon-container",
          "d-flex",
          "col-auto",
        );
        iconContainer.appendChild(commentElement);

        let editIcon = document.createElement("span");
        editIcon.classList.add(
          "fc-event-icon-edit",
          "fa",
          "fa-pencil-alt",
          "mb-3",
          "mx-3",
          "mt-3",
        );

        editIcon.style.color = "#6c757d";
        iconContainer.appendChild(editIcon);

        let modalIcon = document.createElement("span");
        modalIcon.classList.add(
          "fc-event-icon-modal",
          "fa",
          "fa-info-circle",
          "mb-3",
          "mx-3",
          "mt-3",
        );
        modalIcon.style.color = "#09044f";
        iconContainer.appendChild(modalIcon);

        let deleteIcon = document.createElement("span");
        deleteIcon.classList.add(
          "fc-event-icon-delete",
          "fa",
          "fa-circle-xmark",
          "mb-3",
          "mx-3",
          "text-end",
          "btn-delete",
          "mt-3",
        );
        deleteIcon.style.color = "#e01b24";
        iconContainer.appendChild(deleteIcon);

        let classElement = eventElement.querySelector(
          ".fc-event-main-frame",
          "col-md-12",
          "d-flex",
        );
        if (classElement) {
          classElement.appendChild(iconContainer);
        }

        modalIcon.addEventListener("click", () => {
          const details = `
                        <p><strong>Nom:</strong> ${info.event.title}</p>
                        <p><strong>Début:</strong> ${info.event.start?.toLocaleString()}</p>
                        <p><strong>Fin:</strong> ${
                          info.event.end
                            ? info.event.end.toLocaleString()
                            : "N/A"
                        }</p>
                        <p><strong>Commentaire:</strong> ${
                          comment ? comment : "Pas de commentaire 👎"
                        }</p>
                        <p><strong>Activité:</strong> ${activity}</p>
                        <p><strong>État:</strong> ${state}</p>
                    `;

          const taskDetailsElement = document.getElementById("task-details");
          if (taskDetailsElement) {
            taskDetailsElement.innerHTML = details;
          }

          const modal = document.getElementById("taskDetailModal");
          if (modal) {
            modal.style.display = "block";

            const closeButton = modal.querySelector(".close");
            if (closeButton) {
              closeButton.addEventListener("click", () => {
                modal.style.display = "none";
              });
            }

            window.addEventListener("click", (event) => {
              if (event.target === modal) {
                modal.style.display = "none";
              }
            });
          }
        });

        deleteIcon.addEventListener("click", async function () {
          const eventId = info.event.id;
          saveCalendarState(cal);
          try {
            await deleteDueTask(eventId);
            info.event.remove();
            reloadCalendar(cal);
          } catch (error) {
            console.error("Error fetch for delete", error);
          }
        });

        editIcon.addEventListener("click", async function () {
          const modalHtml = `
        <div id="customModal" class="modal fade show" style="display: block;">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Modifier le commentaire</h5>
              
            </div>
            <div class="modal-body">
              <textarea id="editCommentText" class="form-control" rows="3">${
                comment || ""
              }</textarea>
            </div>
            <div style="margin-top: 10px;">
                <button class="btn btn-outline-success" id="saveCommentBtn">Enregistrer</button>
                <button class="btn btn-outline-warning" id="cancelCommentBtn">Annuler</button>
              </div>
          </div>
        </div>
      </div>
        `;

          document.body.insertAdjacentHTML("beforeend", modalHtml);

          const modal = document.getElementById("customModal");
          const textarea = document.getElementById(
            "editCommentText",
          ) as HTMLTextAreaElement;
          const saveBtn = document.getElementById("saveCommentBtn");
          const cancelBtn = document.getElementById("cancelCommentBtn");

          saveBtn.onclick = async () => {
            const newComment = textarea.value;
            if (newComment !== null && newComment !== comment) {
              commentElement.textContent = newComment;
              const dueTaskId = info.event.id;
              const start = info.event.startStr;
              const end = info.event.endStr;
              const user = info.event.extendedProps.user;

              saveCalendarState(cal);
              try {
                await updateDueTask(dueTaskId, {
                  start: start,
                  end: end,
                  user: user,
                  description: newComment,
                  id: dueTaskId,
                });
                reloadCalendar(cal);
              } catch (error) {
                console.error("Error fetch update comment", error);
              }
            }
            modal.remove();
          };

          cancelBtn.onclick = () => {
            modal.remove();
          };
        });

        const typeBorderColors: { [key: string]: string } = {
          Développement: "#00537a",
          Conception: "#06d6a0",
          Maintenance: "#b9375e",
          "Veille Technologique": "#ffd166",
          Régie: "#6a4c93",
          Réunion: "#ff7b00",
          Autres: "#5F5F5F",
        };

        const typeBackgroundColors: { [key: string]: string } = {
          Développement: "#e6f6ff",
          Conception: "#d0f4de",
          Maintenance: "#ff99c8",
          "Veille Technologique": "#fcf6bd",
          Régie: "#e4c1f9",
          Réunion: "#fec8c3",
          Autres: "#e5e5e5",
        };

        eventElement.style.backgroundColor = typeBackgroundColors[type];
        eventElement.style.borderColor = typeBorderColors[type];
      },

      drop: async function (info: { draggedEl: HTMLElement; dateStr: string }) {
        const eventEl = info.draggedEl;
        const start = info.dateStr;

        const eventDataStr = eventEl.dataset["event"];
        if (!eventDataStr) {
          console.error("no data");
          return;
        }
        const datas = JSON.parse(eventDataStr) as EventData;

        const end = new Date(
          new Date(start).getTime() +
            (datas.duration ? parseInt(datas.duration) * 60000 : 3600000),
        ).toISOString();

        saveCalendarState(cal);
        try {
          await createDueTask({
            id: datas.id,
            start: start,
            end: end,
            duration: datas.duration,
            description: datas.description,
            user: datas.user,
            state: datas.state,
          });
          const unwantedEvents = document.querySelectorAll(
            'a.fc-timegrid-event.fc-v-event.fc-event.fc-event-draggable.fc-event-resizable.fc-event-start.fc-event-end:not([style*="background-color"])',
          );
          unwantedEvents.forEach((event) => {
            (event as HTMLElement).style.display = "none";
          });
          cal.refetchEvents();
        } catch (error) {
          console.error("Error fetch", error);
        }
      },

      eventClick(info) {
        const event = info.event;

        function parseTimeString(timeString: string): {
          hours: number;
          minutes: number;
          seconds: number;
        } {
          const [hours, minutes, seconds] = timeString.split(":").map(Number);
          return { hours, minutes, seconds };
        }

        function calculateDuration(start: string, end: string): string {
          const startTime = parseTimeString(start);
          const endTime = parseTimeString(end);

          const startTotalMinutes = startTime.hours * 60 + startTime.minutes;
          const endTotalMinutes = endTime.hours * 60 + endTime.minutes;

          const durationMinutes = endTotalMinutes - startTotalMinutes;

          const durationHours = Math.floor(durationMinutes / 60);
          const remainingMinutes = durationMinutes % 60;
          const durationSeconds = endTime.seconds - startTime.seconds;

          return `${String(durationHours).padStart(2, "0")}:${String(
            remainingMinutes,
          ).padStart(2, "0")}:${String(durationSeconds).padStart(2, "0")}`;
        }
        const start = event.start?.toLocaleString();
        let startHour = start.split(" ")[1];
        const end = event.end.toLocaleString();
        let endHour = end.split(" ")[1];

        const duration = calculateDuration(startHour, endHour);
      },

      eventDrop: async function (info) {
        const event = info.event;
        const start = event.startStr;
        const end = event.endStr;
        const updatedTask: DueTask = {
          id: event.id,
          start: start,
          end: end,
          description: event.extendedProps.description,
          user: event.extendedProps.user,
          state: event.extendedProps.state,
        };
        const state = event.extendedProps.state;

        if (state === "En cours" || state === "Non débuté") {
          saveCalendarState(cal);
          try {
            await updateDueTask(event.id, updatedTask);
            reloadCalendar(cal);
          } catch (error) {
            console.error("Error fetch", error);
          }
        } else {
          window.location.reload();
        }
      },

      eventResize: async function (info) {
        saveCalendarState(cal);
        const event = info.event;
        const start = event.startStr;
        const end = event.endStr;
        const dueTaskId = event.id;
        const user = event.extendedProps.user;
        const description = event.extendedProps.description;
        const state = event.extendedProps.state;

        if (state === "En cours" || state === "Non débuté") {
          try {
            await updateDueTask(dueTaskId, {
              start: start,
              end: end,
              id: dueTaskId,
              user: user,
              description: description,
            });
            reloadCalendar(cal);
          } catch (error) {
            console.error("Error fetch", error);
          }
        } else {
          window.location.reload();
        }
      },

      windowResize: function (view) {
        if (window.innerWidth < 768) {
          cal.changeView("listWeek");
        } else {
          cal.changeView("timeGridWeek");
        }
      },
    });
    cal.setOption("locale", "fr");
    cal.render();
    return cal;
  }
}

function saveCalendarState(calendarInstance: Calendar) {
  const view = calendarInstance.view;
  const state: CalendarState = {
    currentView: view.type,
    startDate: view.activeStart.toISOString(),
    endDate: view.activeEnd.toISOString(),
    scrollTop: calendarInstance.el.scrollTop,
  };
  localStorage.setItem("calendarState", JSON.stringify(state));
}

function reloadCalendar(calendarInstance: Calendar) {
  if (!this.cal) return;
  const stateString = localStorage.getItem("calendarState");
  if (stateString) {
    const state = JSON.parse(stateString);
    this.calendar.refetchEvents();
    this.calendar.changeView(state.currentView);
    this.calendar.gotoDate(state.startDate);
    this.calendar.el.scrollTop = state.scrollTop;
  } else {
    this.calendar.render();
  }

  const unwantedEvents = document.querySelectorAll(
    'a.fc-timegrid-event.fc-v-event.fc-event.fc-event-draggable.fc-event-resizable:not([style*="background-color"])',
  );
  unwantedEvents.forEach((event) => {
    (event as HTMLElement).style.display = "none";
  });
}
