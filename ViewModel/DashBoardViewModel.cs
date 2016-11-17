using GrappBox.Helpers;
using GrappBox.HttpRequest;
using GrappBox.Model;

using System;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Threading.Tasks;
using Windows.ApplicationModel.Resources.Core;
using Windows.Web.Http;

namespace GrappBox.ViewModel
{
    internal class DashBoardViewModel : ViewModelBase
    {
        static private DashBoardViewModel instance = null;

        static public DashBoardViewModel GetViewModel()
        {
            if (instance == null)
                instance = new DashBoardViewModel();
            return instance;
        }

        public DashBoardViewModel()
        {
            _occupationList = null;
            _meetingList = null;
            instance = this;
        }

        public async Task InitialiseAsync()
        {
            await this.getTeam();
            await this.getNextMeetings();
        }

        public async Task getTeam()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { AppGlobalHelper.ProjectId };
            HttpResponseMessage res = await api.Get(token, Constants.DashboardTeamOccupationCall);
            if (res.IsSuccessStatusCode)
            {
                OccupationList = HttpRequestManager.DeserializeArrayJson<ObservableCollection<Occupations>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("OccupationList");
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        public async Task<bool> getNextMeetings()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { AppGlobalHelper.ProjectId };
            HttpResponseMessage res = await api.Get(token, Constants.DashboardMeetingsCall);
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                ObservableCollection<MeetingDashBoard> tmp;
                try
                {
                    tmp = HttpRequestManager.DeserializeArrayJson<ObservableCollection<MeetingDashBoard>>(json);
                }
                catch (ArgumentException aEx)
                {
                    Debug.WriteLine("Argument Exception on Name {0} because of paramName {1}", aEx.Source, aEx.ParamName);
                    return false;
                }
                catch (Exception ex)
                {
                    Debug.WriteLine("DashBoard.getNextMeetings: {0}", ex.Message);
                    return false;
                }
                MeetingList = tmp;
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(json));
                return false;
            }
            return true;
        }

        private ObservableCollection<Occupations> _occupationList;

        public ObservableCollection<Occupations> OccupationList
        {
            get { return _occupationList; }
            set { _occupationList = value; NotifyPropertyChanged("OccupationList"); }
        }

        private ObservableCollection<MeetingDashBoard> _meetingList;

        public ObservableCollection<MeetingDashBoard> MeetingList
        {
            get { return _meetingList; }
            set { _meetingList = value; NotifyPropertyChanged("MeetingList"); }
        }
    }
}