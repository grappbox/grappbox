using GrappBox.Model;
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

namespace GrappBox.ViewModel
{
    public class CalendarViewModel : ViewModelBase
    {
        public Visibility ListEmptyVisibility
        {
            get
            {
                if (Events == null || Events.Count == 0)
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

        private ObservableCollection<Event> _events;

        public ObservableCollection<Event> Events
        {
            get { return _events; }
            set
            {
                _events = value;
                NotifyPropertyChanged("Events");
            }
        }

        public CalendarViewModel()
        {
            _currentDate = DateTime.Today;
            _events = null;
        }

        public async Task GetDay(DateTime date)
        {
            if (Events != null)
                Events.Clear();
            HttpRequest.HttpRequestManager httpclient = HttpRequest.HttpRequestManager.Instance;
            object[] objects = new object[1]
                {
                    date.ToString("yyyy-MM-dd")
        };

            HttpResponseMessage response = await httpclient.Get(objects, Constants.CalendarCall);
            if (response.IsSuccessStatusCode)
            {
                string json = await response.Content.ReadAsStringAsync();
                try
                {
                    Events = HttpRequest.HttpRequestManager.DeserializeArrayJson<CalendarModel>(json).Events;
                }
                catch (Exception) { }
            }
            else
            {
                MessageDialog dialog = new MessageDialog("Can't get day events", "Error");
            }
            NotifyPropertyChanged("ListEmptyVisibility");
        }
    }
}