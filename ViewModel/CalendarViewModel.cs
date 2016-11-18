using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;
using Windows.UI.Xaml;
using Windows.Web.Http;

namespace Grappbox.ViewModel
{
    public class CalendarViewModel : ViewModelBase
    {
        public bool IsBusy;


        public Visibility ListEmptyVisibility
        {
            get
            {
                if (FilteredEvents == null || FilteredEvents.Count == 0)
                    return Visibility.Visible;
                return Visibility.Collapsed;
            }
        }

        private DateTime _currentDate;

        public DateTime CurrentDate
        {
            get { return _currentDate; }
            set { _currentDate = value; }
        }

        private ObservableCollection<Event> events;

        private ObservableCollection<Event> _filteredEvents;

        public ObservableCollection<Event> FilteredEvents
        {
            get
            {
                return _filteredEvents;
            }
            set
            {
                _filteredEvents = value;
                NotifyPropertyChanged("FilteredEvents");
            }
        }

        public CalendarViewModel()
        {
            _currentDate = DateTime.Today;
            _filteredEvents = null;
            IsBusy = false;
        }

        public async Task GetMonthApi(DateTime date)
        {
            events?.Clear();
            HttpRequest.HttpRequestManager httpclient = HttpRequest.HttpRequestManager.Instance;
            object[] objects = new object[1] { date.ToString("yyyy-MM-01") };
            var response = await httpclient.Get(objects, Constants.CalendarMonthCall);
            if (response.IsSuccessStatusCode)
            {
                string json = await response.Content.ReadAsStringAsync();
                try
                {
                    events = HttpRequest.HttpRequestManager.DeserializeArrayJson<CalendarModel>(json).Events;
                }
                catch (Exception) { }
            }
        }

        public async Task PickDay(DateTime date)
        {
            if (CurrentDate.Date.Month != date.Month || events == null)
                await GetMonthApi(date);
            CurrentDate = date;
            FilteredEvents?.Clear();
            FilteredEvents = new ObservableCollection<Event>(events.Where(
                d => CurrentDate.Date >= DateTime.Parse(d.BeginDate).Date && CurrentDate.Date <= DateTime.Parse(d.EndDate).Date));
            NotifyPropertyChanged("ListEmptyVisibility");
        }
    }
}