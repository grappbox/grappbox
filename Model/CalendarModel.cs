using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class CalendarModel
    {
        private Planning _currentPlanning;
        public Planning CurrentPlanning
        {
            get { return _currentPlanning; }
            set { _currentPlanning = value; }
        }
        public async void GetDayPlanning(DateTime day)
        {
            Debug.WriteLine("day {0}", day.ToString("yyyy-MM-dd"));
            ApiCom.ApiCommunication api = ApiCom.ApiCommunication.GetInstance();
            object[] token = { ApiCom.User.GetUser().Token, day.ToString("yyyy-MM-dd") };
            HttpResponseMessage res =  await api.Get(token, "planning/getday");
            Debug.WriteLine(await res.Content.ReadAsStringAsync());
        }
        public async void GetMonthPlanning(DateTime month)
        {
            Debug.WriteLine("date {0}", month.ToString("yyyy-MM-dd"));
            ApiCom.ApiCommunication api = ApiCom.ApiCommunication.GetInstance();
            object[] token = { ApiCom.User.GetUser().Token, month.ToString("yyyy-MM-dd") };
            HttpResponseMessage res = await api.Get(token, "planning/getday");
            Debug.WriteLine(await res.Content.ReadAsStringAsync());
        }
        public async void GetWeekPlanning(DateTime week)
        {
            Debug.WriteLine("date {0}", week.ToString("yyyy-MM-dd"));
            ApiCom.ApiCommunication api = ApiCom.ApiCommunication.GetInstance();
            object[] token = { ApiCom.User.GetUser().Token, week.ToString("yyyy-MM-dd") };
            HttpResponseMessage res = await api.Get(token, "planning/getday");
            Debug.WriteLine(await res.Content.ReadAsStringAsync());
        }
    }
}
