# Intégration de FullCalendar

## Vue d'ensemble

Cette documentation fournit une vue d'ensemble de l'intégration de FullCalendar dans votre projet en utilisant TypeScript.
Elle inclut des détails sur le contrôleur, les interfaces et les services utilisés pour gérer les données du calendrier.

## Installation et Configuration

```bash
symfony console importmap:require @fullcalendar/'nameplugin'@5.11.5
```

## Import des Modules, Interfaces et Services

```js
import { Controller } from "@hotwired/stimulus";
import "fullcalendar"; // Importation nécessaire pour FullCalendar
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
```

## Initialisation du Contrôleur

- Ciblage des Éléments :

```js
static targets = ['calendar', 'externalEvents', "taskDetails"];
```

## Méthode `connect`

Cette méthode est appelée automatiquement lorsque le contrôleur est connecté au DOM. Elle initialise le calendrier et les fonctionnalités de glisser-déposer.

```js
connect() {
    this.initCalendar();
    this.initDraggable();
}
```

## Initialisation des Événements Draggable

- Méthode `initDraggable`
  Cette méthode initialise les événements qui peuvent être glissés et déposés dans le calendrier.

```js
  initDraggable() {
    new Draggable(this.externalEventsTarget, {
      itemSelector: ".fc-event",
      eventData: function (eventEl: HTMLElement): EventData {
        const dataStr = eventEl.getAttribute("data-event");
        if (dataStr === null) {
          throw new Error("L'attribut de données de l'événement est manquant");
        }
        try {
          const data: EventData = JSON.parse(dataStr);
          return data;
        } catch (error) {
          console.error("Échec de l'analyse des données de l'événement", error);
          throw error;
        }
      },
    });
  }
```

## Initialisation du Calendrier

- Méthode `initCalendar`
  Cette méthode initialise le calendrier avec diverses configurations et options.

```js
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
      longPressDelay: 3000, // 3 secondes

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
              console.error("Erreur lors de la récupération des données", error);
              failureCallBack(error);
            }
        },

        datesSet: function (info) {
            cal.refetchEvents();
        },
        eventDidMount: this.setupEventIcons,
        drop: this.handleEventDrop,
        eventClick: this.handleEventClick,

        eventDrop: async function (info) {
            const newStart = info.event.startStr;
            const newEnd = info.event.endStr;
            const dueTaskId = info.event.id;
            const user = info.event.extendedProps.user;
            const description = info.event.extendedProps.description;

            saveCalendarState(cal);

            try {
              await updateDueTask(dueTaskId, {
                start: newStart,
                end: newEnd,
                user: user,
                description: description,
                id: dueTaskId,
              });
              reloadCalendar(cal);
            } catch (error) {
              console.error("Erreur lors de la mise à jour", error);
              info.revert();
            }
        },

        eventResize: async function (info) {
            const newStart = info.event.startStr;
            const newEnd = info.event.endStr;
            const dueTaskId = info.event.id;
            const user = info.event.extendedProps.user;
            const description = info.event.extendedProps.description;

            saveCalendarState(cal);

            try {
              await updateDueTask(dueTaskId, {
                start: newStart,
                end: newEnd,
                user: user,
                description: description,
                id: dueTaskId,
              });
              reloadCalendar(cal);
            } catch (error) {
              console.error("Erreur lors de la mise à jour", error);
              info.revert();
            }
        },
        eventReceive: async function (info) {
            const eventData = info.event.extendedProps;

            try {
              await createDueTask(eventData);
              reloadCalendar(cal);
            } catch (error) {
              console.error("Erreur lors de la création de l'événement", error);
              info.revert();
            }
        },
      });
    cal.setOption('locale', 'fr');
    cal.render();
}

```

## Interfaces

- Les interfaces définissent la structure des données utilisées dans le calendrier, y compris les tâches dues, les événements et l'état du calendrier.

```js
export interface DueTask {
  id: string;
  title: string;
  start: string;
  end: string;
  description: string;
  user: string;
  activity: string;
  type: string;
  state: string;
}

export interface EventData {
  title: string;
  start: string;
  end: string;
  description: string;
  user: string;
  activity: string;
  type: string;
  state: string;
}

export interface CalendarState {
  events: DueTask[];
  view: string;
  currentDate: string;
}
```

## Services

- Les services sont utilisés pour interagir avec le backend, notamment pour récupérer, créer, mettre à jour et supprimer des tâches dues.

```js
import { DueTask } from "../interfaces/CalendarType.ts";

export async function getDueTasks(
  startDate: string,
  endDate: string
): Promise<DueTask[]> {
  try {
    const response = await axios.get(`/api/duetasks?start=${startDate}&end=${endDate}`);
    return response.data;
  } catch (error) {
    console.error("Erreur lors de la récupération des tâches dues", error);
    throw error;
  }
}

export async function createDueTask(dueTask: DueTask): Promise<DueTask> {
  try {
    const response = await axios.post("/api/duetasks", dueTask);
    return response.data;
  } catch (error) {
    console.error("Erreur lors de la création de la tâche due", error);
    throw error;
  }
}

export async function updateDueTask(
  dueTaskId: string,
  updatedData: Partial<DueTask>
): Promise<DueTask> {
  try {
    const response = await axios.put(`/api/duetasks/${dueTaskId}`, updatedData);
    return response.data;
  } catch (error) {
    console.error("Erreur lors de la mise à jour de la tâche due", error);
    throw error;
  }
}

export async function deleteDueTask(dueTaskId: string): Promise<void> {
  try {
    await axios.delete(`/api/duetasks/${dueTaskId}`);
  } catch (error) {
    console.error("Erreur lors de la suppression de la tâche due", error);
    throw error;
  }
}
```

## Méthodes Complémentaires

- Méthode `fetchEvents`
  Cette méthode est utilisée pour récupérer les événements du serveur.

```js
fetchEvents(info, successCallBack, failureCallBack) {
    const startDate = info.start.toISOString().split('T')[0];
    const endDate = info.end.toISOString().split('T')[0];
    fetch(`/ajax/calendar/getDueTasks?start_date=${startDate}&end_date=${endDate}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur durant le fetch');
            }
            return response.json();
        })
        .then(data => {
            successCallBack(data);
        })
        .catch(error => {
            console.error('Error fetch', error);
            failureCallBack(error);
        });
}
```

## Méthode `setupEventIcons`

Cette méthode est utilisée pour configurer les icônes et les interactions de chaque événement.

```js
    setupEventIcons(info) {
        let eventElement = info.el;
        let comment = info.event.extendedProps.description;
        let type = info.event.extendedProps.type;
        let activity = info.event.extendedProps.activity;
        let state = info.event.extendedProps.state;

        // Code pour ajouter des icônes et configurer les interactions des événements

        eventElement.style.backgroundColor = typeBackgroundColors[type];
        eventElement.style.borderColor = typeBorderColors[type];
    }
```

## Méthode handleEventDrop

Cette méthode gère l'ajout d'un événement au calendrier après avoir été glissé-déposé.

```js
    handleEventDrop(info) {
        const eventEl = info.draggedEl;
        const start = info.dateStr;
        const datas = JSON.parse(eventEl.dataset['event']);

        saveCalendarState(cal);
        fetch('/ajax/calendar/createDueTask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                start: start,
                duration: datas.duration,
                id: datas.id,
            })
        }).then(response => {
            if (!response.ok) {
                console.log('bad response', response.statusText);
            }
            cal.refetchEvents();
            return response.json();
        }).then(response => {
            reloadCalendar(cal);
        }).catch(error => {
            console.error('Error fetch', error);
        })
    }
```

## Méthode handleEventClick

Cette méthode gère les clics sur les événements pour afficher les détails.

```js
    handleEventClick(info) {
        const event = info.event;

        function parseTimeString(timeString) {
            const [hours, minutes, seconds] = timeString.split(':').map(Number);
            return { hours, minutes, seconds };
        }

        function calculateDuration(start, end) {
            const startTime = parseTimeString(start);
            const endTime = parseTimeString(end);

            const startTotalMinutes = startTime.hours * 60 + startTime.minutes;
            const endTotalMinutes = endTime.hours * 60 + endTime.minutes;

            const durationMinutes = endTotalMinutes - startTotalMinutes;

            const durationHours = Math.floor(durationMinutes / 60);
            const remainingMinutes = durationMinutes % 60;
            const durationSeconds = endTime.seconds - startTime.seconds;

            return `${String(durationHours).padStart(2, '0')}:${String(remainingMinutes).padStart(2, '0')}:${String(durationSeconds).padStart(2, '0')}`;
        }

        const start = event.start?.toLocaleString();
        let startHour = start.split(' ')[1];
        const end = event.end.toLocaleString();
        let endHour = end.split(' ')[1];

        const duration = calculateDuration(startHour, endHour);
    }
```

## Méthode updateEvent

Cette méthode gère la mise à jour des événements lorsqu'ils sont déplacés ou redimensionnés.

```js
    updateEvent(info) {
        const event = info.event;
        const start = event.startStr;
        const end = event.endStr;
        const dueTaskId = event.id;
        const user = event.extendedProps.user;
        const description = event.extendedProps.description || 'N/A';
        const state = event.extendedProps.state;

        if (state === "En cours" || state === "Non débuté") {
            saveCalendarState(cal);
            fetch('/ajax/calendar/updateDueTask/' + dueTaskId, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    start: start,
                    end: end,
                    id: dueTaskId,
                    user: user,
                    description: description,
                })
            }).then(response => {
                if (!response.ok) {
                    console.log('bad response', response.statusText);
                }
                return response.json();
            }).then(response => {
                reloadCalendar(cal);
            }).catch(error => {
                console.error('Error fetch', error);
            })
        } else {
            window.location.reload();
        }
    }
```

## Fonctions Utilitaires

#### `reloadCalendar`

Cette fonction rafraîchit les événements du calendrier.

```js
function reloadCalendar(calendar) {
  calendar.refetchEvents();
}
```

#### `saveCalendarState`

Cette fonction sauvegarde l'état actuel du calendrier dans le localStorage.

```js
function saveCalendarState(calendar) {
  const calendarState = {
    events: calendar.getEvents().map((event) => ({
      id: event.id,
      start: event.start,
      end: event.end,
      extendedProps: event.extendedProps,
    })),
  };
  localStorage.setItem("calendarState", JSON.stringify(calendarState));
}
```

#### `loadCalendarState`

Cette fonction charge l'état du calendrier à partir du localStorage.

```js
function loadCalendarState(calendar) {
  const savedState = localStorage.getItem("calendarState");
  if (savedState) {
    const calendarState = JSON.parse(savedState);
    calendarState.events.forEach((eventData) => {
      calendar.addEvent(eventData);
    });
  }
}
```

## Méthode `calendar.render()`

La méthode `calendar.render()` est appelée pour rendre et afficher le calendrier avec toutes ses configurations et données. Cette méthode est cruciale pour afficher visuellement le calendrier dans l'élément HTML ciblé.

### Fonctionnement de `calendar.render()`

Une fois que toutes les configurations du calendrier ont été définies et que les événements ont été chargés, calendar.render() est appelée pour :

- Afficher le calendrier : Le calendrier est rendu visuellement dans l'élément spécifié (this.dashboardTarget dans notre exemple) en respectant les vues et les configurations définies.

- Afficher les événements : Les événements chargés à partir de la source de données définie sont placés aux dates correspondantes et stylisés selon les règles définies dans eventDidMount.

- Intégrer les plugins : Les plugins comme timeGridPlugin, listPlugin, et dayGridPlugin sont intégrés pour permettre différentes vues et fonctionnalités dans le calendrier.

### Utilisation de `calendar.render()`

```js
calendar.render();
```

Cette méthode est généralement appelée à la fin de la configuration du calendrier pour garantir que toutes les options sont prises en compte et que le calendrier est correctement rendu avec les données et les événements actuels.
En utilisant les objets info, info.el, info.event, et info.event.extendedProps, ainsi que la méthode calendar.render(), le contrôleur de calendrier parvient à fournir une intégration robuste et personnalisable pour afficher et gérer les événements.

## Traitement des données

- Objet `info`

L'objet info est une structure fournie par FullCalendar lorsqu'il gère un événement. Il contient des informations cruciales sur l'événement en cours de traitement.

### Propriétés de `info`

- `info.el` :
  Référence à l'élément du DOM correspondant à l'événement. C'est essentiellement l'élément HTML dans lequel l'événement est rendu sur le calendrier.

- `info.event` :
  Contient toutes les informations détaillées sur l'événement. Cela inclut des propriétés telles que le titre, la date de début, la date de fin, la durée, et d'autres métadonnées pertinentes.

- `info.event.extendedProps` :
  Contient des propriétés étendues associées à l'événement. Ces propriétés peuvent être définies lors de la création de l'événement ou ajoutées dynamiquement. Elles sont utiles pour stocker des informations supplémentaires telles que le type d'événement, l'activité associée, l'état, la description, etc.

## Options du Calendrier

- `plugins`: Charge les plugins nécessaires pour le calendrier, comme timeGridPlugin, listPlugin, interactionPlugin, et dayGridPlugin.

- `headerToolbar`: Définit la configuration de la barre d'outils du calendrier avec des boutons de navigation et des vues.

- `initialView`: Définit la vue initiale du calendrier lorsqu'il est chargé pour la première fois.

- `buttonText`: Personnalise les libellés des boutons dans le calendrier pour différentes vues et actions.

- `nowIndicator`: Affiche un indicateur pour l'heure actuelle dans le calendrier.

- `expandRows`: Permet l'expansion des rangées d'événements pour afficher davantage d'informations.

- `allDaySlot`: Désactive l'affichage de la ligne pour les événements de toute la journée.

- `businessHours`: Définit les heures de travail du calendrier.

- `handleWindowResize`: Gère le redimensionnement automatique du calendrier lorsque la fenêtre du navigateur est redimensionnée.

- `navLinks`: Active les liens de navigation sur les dates pour passer à la vue correspondante.
- `selectable`: Permet la sélection d'une plage de dates sur le calendrier.
- `views`: Configure les options spécifiques à chaque vue du calendrier comme timeGridWeek, listWeek, et dayGridMonth.
- `slotMinTime` et `slotMaxTime`: Définit les heures minimum et maximum pour les créneaux horaires affichés.
- `events`: Charge les événements depuis une source externe en utilisant une fonction asynchrone.
- `eventDidMount`: Fonction appelée lorsque chaque événement est rendu dans le calendrier pour personnaliser son apparence et ajouter des informations supplémentaires.

# Conclusion

Ce contrôleur offre une interface complète pour gérer les événements d'un calendrier en utilisant FullCalendar.
Il prend en charge l'ajout, la modification et la suppression des événements, ainsi que des interactions avancées comme le glisser-déposer.
Grâce aux méthodes et fonctions utilitaires, il permet une gestion flexible et puissante des événements.
