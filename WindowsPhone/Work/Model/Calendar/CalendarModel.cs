using GrappBox.ApiCom;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Web.Http;
using System.Text;
using System.Threading.Tasks;
using GrappBox.Model.Global;
using Newtonsoft.Json;
using System.ComponentModel;
using Windows.UI.Xaml;
using GrappBox.Ressources;

namespace GrappBox.Model
{
    class CalendarModel : INotifyPropertyChanged
    {
        #region Private members
        private MyDateTime CurrentDateTime { get; set; }
        #endregion
        public CalendarModel()
        {
            ListEventsEmpty = Visibility.Visible;
            ListTasksEmpty = Visibility.Visible;
            EventsEmpty = Visibility.Collapsed;
            TasksEmpty = Visibility.Collapsed;
        }
        private void UpdateLists()
        {
            if ( Events.Count == 0)
            {
                ListEventsEmpty = Visibility.Collapsed;
                EventsEmpty = Visibility.Visible;
            }
            else
            {
                ListEventsEmpty = Visibility.Visible;
                EventsEmpty = Visibility.Collapsed;
            }
            if (Tasks == null)
                return;
            if (Tasks.Count == 0)
            {
                ListTasksEmpty = Visibility.Collapsed;
                TasksEmpty = Visibility.Visible;
            }
            else
            {
                ListTasksEmpty = Visibility.Visible;
                TasksEmpty = Visibility.Collapsed;
            }
        }
        #region Notifier
        public event PropertyChangedEventHandler PropertyChanged;
        public void NotifyPropertyChanged(string property)
        {
            if (PropertyChanged != null)
            {
                PropertyChanged(this, new PropertyChangedEventArgs(property));
            }
        }
        #endregion
        #region Public_Accessors
        public Visibility _listEventsEmpty;
        public Visibility ListEventsEmpty
        {
            get { return _listEventsEmpty; }
            set { _listEventsEmpty = value; NotifyPropertyChanged("ListEventsEmpty"); }
        }
        public Visibility _listTasksEmpty;
        public Visibility ListTasksEmpty
        {
            get { return _listTasksEmpty; }
            set { _listTasksEmpty = value; NotifyPropertyChanged("ListTasksEmpty"); }
        }
        public Visibility _eventsEmpty;
        public Visibility EventsEmpty
        {
            get { return _eventsEmpty; }
            set { _eventsEmpty = value; NotifyPropertyChanged("EventsEmpty"); }
        }
        public Visibility _tasksEmpty;
        public Visibility TasksEmpty
        {
            get { return _tasksEmpty; }
            set { _tasksEmpty = value; NotifyPropertyChanged("TasksEmpty"); }
        }
        public int CurrentDay
        {
            get { return CurrentDateTime.Day; }
        }
        public int CurrentDayIndex
        {
            get { return CurrentDateTime.Day -1; }
            set
            {
                int diff = value - CurrentDayIndex;
                CurrentDateTime.AddDays(diff);
                Debug.WriteLine(CurrentDateTime);
            }
        }
        public int MonthIndex { get; private set; }
        public string MonthName
        {
            get { return DateTimeFormator.GetMonthName(CurrentDateTime.DateTimeAccess, MonthIndex+1); }
        }
        public void SetCurrentDateTime(ref MyDateTime dt)
        {
            CurrentDateTime = dt;
            NotifyPropertyChanged("CurrentDayIndex");
        }
        public CalendarModel(int monthIndex, ref MyDateTime currentDateTime)
        {
            MonthIndex = monthIndex;
            SetCurrentDateTime(ref currentDateTime);
        }
        public IEnumerable<string> DaysList
        {
            get { return DateTimeFormator.GetDayList(CurrentDateTime.DateTimeAccess, MonthIndex+1); }
        }
        private ObservableCollection<Event> _events;
        public ObservableCollection<Event> Events
        {
            get { return _events; }
            set { _events = value;  NotifyPropertyChanged("Events"); UpdateLists(); }
        }
        private ObservableCollection<Model.Task> _tasks;
        public ObservableCollection<Model.Task> Tasks
        {
            get { return _tasks; }
            set { _tasks = value; NotifyPropertyChanged("Tasks"); UpdateLists(); }
        }
        #endregion
    }
}
