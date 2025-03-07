import User from "./User.ts";

export default interface Milestone {
    activityId: number;
    milestoneId: number;
    label: string;
    startDate: string;
    endDate: string;
    completedTasks: number;
    totalTasks: number;
    planningStart: string;
    planningStop: string;
    users?: User[];
    percentCompletion?: number;
    startPercent: number;
    totalDay: number;
    planningTotalDays: number;
    activityLabel?: string;
} 
