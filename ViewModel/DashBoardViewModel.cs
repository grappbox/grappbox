using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model;

using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Threading.Tasks;
using Windows.ApplicationModel.Resources.Core;
using Windows.Web.Http;

namespace Grappbox.ViewModel
{
    public class DashBoardViewModel : ViewModelBase
    {
        #region StaticProperties
        /// <summary>
        /// Static list of modular panels
        /// </summary>
        static public List<ModularModel> ModularList = new List<ModularModel>()
        {
            new ModularModel() { DisplayName="Occupation", Selected=true },
            new ModularModel() { DisplayName="Meeting", Selected=true },
            new ModularModel() { DisplayName="Project Stats", Selected=true },
            new ModularModel() { DisplayName="Bugtracker Stats", Selected=true },
            new ModularModel() { DisplayName="Tasks Stats", Selected=false },
            new ModularModel() { DisplayName="Talks Stats", Selected=false },
            new ModularModel() { DisplayName="Customer Access Stats", Selected=false }
        };
        /// <summary>
        /// Instance of the ViewModel
        /// </summary>
        static private DashBoardViewModel instance = null;

        /// <summary>
        /// Get an instance of the DashboardViewModel
        /// </summary>
        /// <returns>Return a DashBoardViewModel object</returns>
        static public DashBoardViewModel GetViewModel()
        {
            if (instance == null)
                instance = new DashBoardViewModel();
            return instance;
        }
        #endregion

        #region PublicProperties
        private ObservableCollection<Occupations> _occupationList;

        /// <summary>
        /// List of user occupations
        /// </summary>
        public ObservableCollection<Occupations> OccupationList
        {
            get { return _occupationList; }
            set { _occupationList = value; NotifyPropertyChanged("OccupationList"); }
        }

        private ObservableCollection<MeetingDashBoard> _meetingList;

        /// <summary>
        /// List of next meetings
        /// </summary>
        public ObservableCollection<MeetingDashBoard> MeetingList
        {
            get { return _meetingList; }
            set { _meetingList = value; NotifyPropertyChanged("MeetingList"); }
        }
        #endregion

        public DashBoardViewModel()
        {
            _occupationList = null;
            _meetingList = null;
            instance = this;
        }

        /// <summary>
        /// Initialize the ViewModel, invoke api calls
        /// </summary>
        /// <returns></returns>
        public async Task InitialiseAsync()
        {
            await this.getTeam();
            await this.getNextMeetings();
        }

        #region ApiCalls_Methods
        /// <summary>
        /// Fetch the team occupation datas from api
        /// </summary>
        /// <returns></returns>
        public async Task getTeam()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, Constants.DashboardTeamOccupationCall);
            if (res.IsSuccessStatusCode)
            {
                OccupationList = SerializationHelper.DeserializeArrayJson<ObservableCollection<Occupations>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("OccupationList");
            }
            else
            {
                Debug.WriteLine(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        /// <summary>
        /// Fetch the nex meetings datas from api
        /// </summary>
        /// <returns></returns>
        public async Task<bool> getNextMeetings()
        {
            object[] token = { SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res = await HttpRequestManager.Get(token, Constants.DashboardMeetingsCall);
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                ObservableCollection<MeetingDashBoard> tmp;
                try
                {
                    tmp = SerializationHelper.DeserializeArrayJson<ObservableCollection<MeetingDashBoard>>(json);
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
                Debug.WriteLine(HttpRequestManager.GetErrorMessage(json));
                return false;
            }
            return true;
        }
        #endregion
    }
}