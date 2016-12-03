using Grappbox.Helpers;
using Grappbox.HttpRequest;
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

        private ObservableCollection<EventViewModel> events;

        private ObservableCollection<EventViewModel> _filteredEvents;

        public ObservableCollection<EventViewModel> FilteredEvents
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

            object[] objects = new object[1] { date.ToString("yyyy-MM-01") };
            var response = await HttpRequestManager.Get(objects, Constants.CalendarMonthCall);
            if (response.IsSuccessStatusCode)
            {
                string json = await response.Content.ReadAsStringAsync();
                try
                {
                    var calendarModel = SerializationHelper.DeserializeArrayJson<CalendarModel>(json);
                    if (calendarModel != null && calendarModel is CalendarModel)
                        events = calendarModel.Events ?? new ObservableCollection<EventViewModel>();
                }
                catch (Exception)
                {
                    events = new ObservableCollection<EventViewModel>();
                }
            }
        }

        public async Task PickDay(DateTime date)
        {
            if (CurrentDate.Date.Month != date.Month || events == null)
                await GetMonthApi(date);
            CurrentDate = date;
            FilteredEvents?.Clear();
            FilteredEvents = new ObservableCollection<EventViewModel>(events.Where(
                d => CurrentDate.Date >= DateTime.Parse(d.BeginDate).Date && CurrentDate.Date <= DateTime.Parse(d.EndDate).Date));
            NotifyPropertyChanged("ListEmptyVisibility");
        }
    }
}