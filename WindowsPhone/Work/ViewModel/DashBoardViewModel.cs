using System;
using GrappBox.Model;
using GrappBox.ApiCom;
using Windows.Web.Http;
using System.Diagnostics;
using GrappBox.Ressources;
using System.Collections.ObjectModel;
using System.Threading.Tasks;

namespace GrappBox.ViewModel
{
    class DashBoardViewModel : ViewModelBase
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

        public async Task getUserLogo(Occupations model)
        {
            await model.LogoUpdate();
            await model.SetLogo();
            NotifyPropertyChanged("Avatar");
        }

        public async Task getTeam()
        {
            ApiCommunication api = ApiCommunication.Instance;
            int id = SettingsManager.getOption<int>("ProjectIdChoosen");
            object[] token = { User.GetUser().Token, id};
            HttpResponseMessage res = await api.Get(token, "dashboard/getteamoccupation");
            if (res.IsSuccessStatusCode)
            {
                OccupationList = api.DeserializeArrayJson<ObservableCollection<Occupations>>(await res.Content.ReadAsStringAsync());
                foreach (Occupations item in OccupationList)
                {
                    await getUserLogo(item);
                    NotifyPropertyChanged("Avatar");
                }
                NotifyPropertyChanged("OccupationList");
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        public async Task<bool> getNextMeetings()
        {
            ApiCommunication api = ApiCommunication.Instance;
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "dashboard/getnextmeetings");
            if (res == null)
                return false;
            string json = await res.Content.ReadAsStringAsync();
            if (res.IsSuccessStatusCode)
            {
                ObservableCollection<MeetingDashBoard> tmp;
                try
                {
                    tmp = api.DeserializeArrayJson<ObservableCollection<MeetingDashBoard>>(json);
                }
                catch(ArgumentException aEx)
                {
                    Debug.WriteLine("Argument Exception on Name {0} because of paramName {1}", aEx.Source, aEx.ParamName);
                    return false;
                }
                catch(Exception ex)
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