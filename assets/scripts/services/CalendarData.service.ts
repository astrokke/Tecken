import { DueTask } from "../interfaces/CalendarType.ts";

// get DueTask for user from interval
export const getDueTasks = async (
  startDate: string,
  endDate: string,
): Promise<DueTask[]> => {
  const response = await fetch(
    `/home/ajax/calendar/getDueTasks?start_date=${startDate}&end_date=${endDate}`,
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    },
  );
  if (!response.ok) {
    throw new Error("Erreur durant le fetch");
  }
  return response.json();
};

// delete dueTask by Id
export const deleteDueTask = async (eventId: string): Promise<void> => {
  const response = await fetch(`/home/ajax/calendar/deleteDueTask/${eventId}`, {
    method: "DELETE",
    headers: {
      "Content-Type": "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  });
  if (!response.ok) {
    throw new Error("Erreur pour supprimer");
  }
  return response.json();
};

// update dueTask partially
export const updateDueTask = async (
  dueTaskId: string,
  data: Partial<DueTask>,
): Promise<DueTask> => {
  const response = await fetch(
    `/home/ajax/calendar/updateDueTask/${dueTaskId}`,
    {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(data),
    },
  );
  if (!response.ok) {
    throw new Error("Failed to update comment");
  }
  return response.json();
};

// create DueTask without id bc serial id
export const createDueTask = async (
  data: Omit<DueTask, "id"> & { id: string },
): Promise<DueTask[]> => {
  const response = await fetch("/home/ajax/calendar/createDueTask", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
    body: JSON.stringify(data),
  });
  if (!response.ok) {
    throw new Error("Erreur pour créer");
  }
  return response.json();
};
