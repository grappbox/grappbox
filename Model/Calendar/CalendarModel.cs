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
            EventsEmpty = Visibility.Collapsed;
        }
        private void UpdateLists()
        {
            if (Events != null && Events.Count != 0)
            {
                ListEventsEmpty = Visibility.Visible;
                EventsEmpty = Visibility.Collapsed;
            }
            else
            {
                ListEventsEmpty = Visibility.Collapsed;
                EventsEmpty = Visibility.Visible;
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
        public Visibility _eventsEmpty;
        public Visibility EventsEmpty
        {
            get { return _eventsEmpty; }
            set { _eventsEmpty = value; NotifyPropertyChanged("EventsEmpty"); }
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
        #endregion
    }
}
