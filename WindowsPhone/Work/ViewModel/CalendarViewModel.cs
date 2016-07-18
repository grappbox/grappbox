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
using Windows.UI.Popups;

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
            HttpResponseMessage res = null;
            res = await api.Get(token, "planning/getmonth");
            if (res == null)
                return null;
            string resString = await res.Content.ReadAsStringAsync();
            Planning plan = null;
            try
            {
                plan = api.DeserializeArrayJson<Planning>(resString);
            }
            catch (Exception ex)
            {
                MessageDialog msgDialog = new MessageDialog(ex.Message);
                await msgDialog.ShowAsync();
                return null;
            }
            return plan;
        }
        public async Task<Planning> GetDayPlanning(DateTime day)
        {
            ApiCom.ApiCommunication api = ApiCom.ApiCommunication.Instance;
            object[] token = { ApiCom.User.GetUser().Token, day.ToString("yyyy-MM-dd") };
            HttpResponseMessage res = null;
            res = await api.Get(token, "planning/getday");
            if (res == null)
                return null;
            string resString = await res.Content.ReadAsStringAsync();
            Debug.WriteLine(resString);
            Planning plan = null;
            try
            {
                plan = api.DeserializeArrayJson<Planning>(resString);
            }
            catch (Exception ex)
            {
                MessageDialog msgDialog = new MessageDialog(ex.Message);
                await  msgDialog.ShowAsync();
                return null;
            }
            return plan;
        }
        #endregion
    }
}
