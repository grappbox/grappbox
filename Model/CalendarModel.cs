using GrappBox.ApiCom;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class CalendarModel
    {
        public enum ViewType
        {
            DAY,
            WEEK,
            MONTH
        }
        #region PrivateMembers
        private ViewType _currentViewType;
        #endregion
        #region PublicAccessor
        public ObservableCollection<Event> Events
        { get; private set; }
        public ObservableCollection<Task> Tasks
        { get; private set; }
        public ViewType Currentype
        {
            get { return _currentViewType; }
            set { _currentViewType = value; }
        }
        #endregion
        #region Constructor and Init
        public CalendarModel()
        {
            _currentViewType = ViewType.MONTH;
        }
        #endregion
        #region ApiGetters
        public async Task<Planning> GetDayPlanning(DateTime day)
        {
            Debug.WriteLine("day {0}", day.ToString("yyyy-MM-dd"));
            ApiCom.ApiCommunication api = ApiCom.ApiCommunication.GetInstance();
            object[] token = { ApiCom.User.GetUser().Token, day.ToString("yyyy-MM-dd") };
            HttpResponseMessage res = await api.Get(token, "planning/getday");
            Debug.WriteLine(await res.Content.ReadAsStringAsync());
            return api.DeserializeArrayJson<Planning>(await res.Content.ReadAsStringAsync());
        }
        public async Task<Planning> GetWeekPlanning(DateTime week)
        {
            Debug.WriteLine("date {0}", week.ToString("yyyy-MM-dd"));
            ApiCom.ApiCommunication api = ApiCom.ApiCommunication.GetInstance();
            object[] token = { ApiCom.User.GetUser().Token, week.ToString("yyyy-MM-dd") };
            HttpResponseMessage res = await api.Get(token, "planning/getweek");
            Debug.WriteLine(await res.Content.ReadAsStringAsync());
            return api.DeserializeArrayJson<Planning>(await res.Content.ReadAsStringAsync());
        }
        public async Task<Planning> GetMonthPlanning(DateTime month)
        {
            Debug.WriteLine("date {0}", month.ToString("yyyy-MM-dd"));
            ApiCom.ApiCommunication api = ApiCom.ApiCommunication.GetInstance();
            object[] token = { ApiCom.User.GetUser().Token, month.ToString("yyyy-MM-dd") };
            HttpResponseMessage res = await api.Get(token, "planning/getmonth");
            string resString = await res.Content.ReadAsStringAsync();
            Debug.WriteLine(resString);
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
            ApiCommunication api = ApiCommunication.GetInstance();
            await api.Post(dict, "event/postevent");
        }
        #endregion
    }
}
