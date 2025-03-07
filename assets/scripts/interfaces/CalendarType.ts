export interface DueTask {
  id: string;
  start: string;
  end: string;
  user: string;
  description?: string;
  state?: string;
  activity?: string;
  type?: string;
  duration?: string;
}

export interface EventData {
  id: string;
  title: string;
  start: string;
  end: string;
  description?: string;
  user: string;
  activity: string;
  state: string;
  duration?: string;
}

export interface CalendarState {
  currentView: string;
  startDate: string;
  endDate: string;
  scrollTop: number;
}
