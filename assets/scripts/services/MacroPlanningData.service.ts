import Milestone from "../interfaces/Milestone.ts";

export default class MacroPlanningData {
    private static baseUrl: string = window.location.origin;

    public static async getMilestones(date: string): Promise<Milestone[]> {
        const response = await fetch(`${this.baseUrl}/macro-planning/ajax/${date}`);
        if (!response.ok) {
            throw new Error('impossible de récupérer le macro-planning: ' + response.statusText);
        }
        const jsonMacroPlanning: Array<string> = await response.json();
        const milestones: Milestone[] = jsonMacroPlanning.map((e) => {
            return JSON.parse(e);
        });
        return milestones;
    }

    public static async getMilestone(id: number): Promise<string> {
        const response = await fetch(`${this.baseUrl}/macro-planning/milestone/${id}`);
        if (!response.ok) {
            throw new Error('impossible de récupérer la milestone: ' + response.statusText);
        }
        const milestone: string = await response.text();
        return milestone;
    }
}
