export default interface PlannedTask {
    userId: number,
    taskName: string,
    clientName?: string,
    activityName: string,
    taskType: string,
    day: string,
    startHour: string,
    endHour: string,
    startPercent: number,
    endPercent: number,
    color: string,
}
