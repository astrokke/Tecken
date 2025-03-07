export interface Task {
  id: number;
  name: string;
  startDateForecast: Date;
  endDateForecast: Date;
  durationForecast: number;
  status: "Overdue" | "Upcoming";
}
