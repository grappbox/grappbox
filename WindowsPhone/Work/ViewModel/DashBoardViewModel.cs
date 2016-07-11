using System;
using System.Linq;
using GrappBox.Model;
using GrappBox.ApiCom;
using Windows.Web.Http;
using System.Diagnostics;
using GrappBox.Ressources;
using System.Collections.ObjectModel;

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

        public async System.Threading.Tasks.Task InitialiseAsync()
        {
            await this.getTeam();
            await this.getNextMeetings();
        }

        public async System.Threading.Tasks.Task getUserLogo(Occupations model)
        {
            await model.LogoUpdate();
            await model.SetLogo();
            NotifyPropertyChanged("Avatar");
        }

        public async System.Threading.Tasks.Task getTeam()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            int id = SettingsManager.getOption<int>("ProjectIdChoosen");
            object[] token = { User.GetUser().Token, id};
            HttpResponseMessage res = await api.Get(token, "dashboard/getteamoccupation");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
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

        public async System.Threading.Tasks.Task getNextMeetings()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "dashboard/getnextmeetings");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                MeetingList = api.DeserializeArrayJson<ObservableCollection<MeetingDashBoard>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("MeetingList");
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
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