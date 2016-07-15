using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Model.Global;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Web.Http;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.UI.Xaml;
using GrappBox.Ressources;

namespace GrappBox.ViewModel
{
    class CalendarViewModel : ViewModelBase
    {
        #region Private members
        private MyDateTime _currentDateTime;
        public MyDateTime CurrentDateTime
        {
            get { return _currentDateTime; }
            set { _currentDateTime = value; }
        }
        #endregion
        #region Public Properties
        public int MonthIndex
        {
            get { return CurrentDateTime.Month - 1; }
            set
            {
                if (value > MonthIndex)
                {
                    if (MonthIndex == 0 && value == 11)
                    {
                        CurrentDateTime.AddMonths(-1);
                        NotifyPropertyChanged("CurrentYear");
                    }
                    else
                        CurrentDateTime.AddMonths(value - MonthIndex);
                }
                else if (value < MonthIndex)
                {
                    if (MonthIndex - value > 1)
                    {
                        CurrentDateTime.AddMonths(value + 12 - MonthIndex);
                        NotifyPropertyChanged("CurrentYear");
                    }
                    else
                        CurrentDateTime.AddMonths(-1);
                }
            }
        }
        private bool _isEventConfirmed;
        public bool IsEventConfirmed
        {
            get { return _isEventConfirmed; }
            set { _isEventConfirmed = value; }
        }
        public int CurrentYear
        {
            get { return CurrentDateTime.Year; }
        }
        public int CurrentMonth
        {
            get { return CurrentDateTime.Month; }
        }
        #endregion

        public CalendarViewModel()
        {
            CurrentDateTime = new MyDateTime();
            MonthList = new ObservableCollection<CalendarModel>();
            for (int i = 0; i < 12; ++i)
            {
                MonthList.Add(new CalendarModel(i, ref _currentDateTime));
            }
        }
        public ObservableCollection<CalendarModel> MonthList { get; set; }

        public async System.Threading.Tasks.Task<Planning> UpdateMonth()
        {
            Planning plan = await GetMonthPlanning(CurrentDateTime.DateTimeAccess);
            return plan;
        }

        #region ApiGetters
        public async Task<Planning> GetMonthPlanning(DateTime month)
        {
            ApiCom.ApiCommunication api = ApiCom.ApiCommunication.Instance;
            object[] token = { ApiCom.User.GetUser().Token, month.ToString("yyyy-MM-dd") };
            HttpResponseMessage res = await api.Get(token, "planning/getmonth");
            string resString = await res.Content.ReadAsStringAsync();
            return api.DeserializeArrayJson<Planning>(resString);
        }
        public async Task<Planning> GetDayPlanning(DateTime day)
        {
            ApiCom.ApiCommunication api = ApiCom.ApiCommunication.Instance;
            object[] token = { ApiCom.User.GetUser().Token, day.ToString("yyyy-MM-dd") };
            HttpResponseMessage res = await api.Get(token, "planning/getday");
            string resString = await res.Content.ReadAsStringAsync();
            return api.DeserializeArrayJson<Planning>(resString);
        }
        public async void AddEvent(Event evt)
        {
            Dictionary<string, object> dict = new Dictionary<string, object>();
            dict.Add("token", User.GetUser().Token);
            dict.Add("title", evt.Title);
            dict.Add("description", evt.Description);
            dict.Add("icon", "");
            dict.Add("typeId", 1);
            dict.Add("begin", evt.BeginDate.date);
            dict.Add("end", evt.EndDate.date);
            ApiCommunication api = ApiCommunication.Instance;
            await api.Post(dict, "event/postevent");
        }
        #endregion
    }
}
