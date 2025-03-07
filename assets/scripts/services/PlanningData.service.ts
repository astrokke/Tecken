import PlannedTask from "../interfaces/PlannedTask";

export default class PlanningData {
  private static baseUrl: string = window.location.origin;

  public static async getPlannedTasks(week: string): Promise<PlannedTask[]> {
    const response = await fetch(`${this.baseUrl}/planning/ajax/${week}`);
    if (!response.ok) {
      throw new Error(
        "impossible de récupérer le planning: " + response.statusText,
      );
    }
    const jsonPlanning: PlannedTask[] = await response.json();
    console.log("ici", jsonPlanning);
    return jsonPlanning;
  }
}
