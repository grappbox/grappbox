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

namespace GrappBox.Model
{
    class CalendarModel : INotifyPropertyChanged
    {
        #region Private members
        private DateTime[] currentDateTimeRef;
        private Planning planning;
        #endregion

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
        #region Public Accessors
        public DateTime CurrentDateTime
        {
            get { return currentDateTimeRef[0]; }
        }
        public int MonthIndex { get; private set; }
        public string MonthName
        {
            get { return DateTimeFormator.GetMonthName(CurrentDateTime, MonthIndex+1); }
        }
        public void SetCurrentDateTime(ref DateTime dt)
        {
            currentDateTimeRef = new DateTime[] { dt };
        }
        public CalendarModel(int monthIndex, ref DateTime currentDateTime)
        {
            MonthIndex = monthIndex;
            SetCurrentDateTime(ref currentDateTime);
        }
        public IEnumerable<string> DaysList
        {
            get { return DateTimeFormator.GetDayList(CurrentDateTime, MonthIndex+1); }
        }
        public ObservableCollection<Event> Events
        {
            get { return planning.Events; }
        }

        public ObservableCollection<Model.Task> Tasks
        {
            get { return planning.Tasks; }
        }
        #endregion
    }
}
