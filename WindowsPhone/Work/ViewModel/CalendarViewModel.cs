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

namespace GrappBox.ViewModel
{
    class CalendarViewModel : ViewModelBase
    {
        #region Private members
        private DateTime currentDateTime;
        #endregion
        #region Public Properties
        public int CurrentYear
        {
            get { return currentDateTime.Year; }
        }

        public int MonthIndex
        {
            get { return currentDateTime.Month-1; }
            set
            {
                Debug.WriteLine("Value= {0} - MonthIndex= {1}", value, MonthIndex);
                if (value > MonthIndex)
                {
                    //if (MonthIndex == 0 && value == 11)
                    //{
                    //    currentDateTime.Subtract(new TimeSpan())
                    //}
                    currentDateTime.AddMonths(value - MonthIndex);
                }
                else if (value < MonthIndex)
                {
                    currentDateTime.AddMonths(MonthIndex - value);
                }
                Debug.WriteLine(currentDateTime);
            }
        }

        public int CurrentMonth
        {
            get { return currentDateTime.Month; }
        }

        public int CurrentDay
        {
            get { return currentDateTime.Day; }
        }
        #endregion

        public CalendarViewModel()
        {
            currentDateTime = DateTime.Now;
            MonthList = new ObservableCollection<CalendarModel>();
            for (int i = 0; i < 12; ++i)
            {
                MonthList.Add(new CalendarModel(i, ref currentDateTime));
            }
        }

        public ObservableCollection<CalendarModel> MonthList { get; set; }

        public async void UpdateMonth()
        {
            //Debug.WriteLine("currentDateTime: {0}", currentDateTime);
            //Planning plan = await GetMonthPlanning(currentDateTime);
            //Events = new ObservableCollection<Event>(plan.Events);
            //Tasks = new ObservableCollection<Model.Task>(plan.Tasks);
        }

        #region ApiGetters
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
