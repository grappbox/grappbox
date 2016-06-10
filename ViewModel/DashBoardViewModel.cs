using System;
using System.Linq;
using GrappBox.Model;
using GrappBox.ApiCom;
using System.Net.Http;
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
            instance = this;
        }

        static public async System.Threading.Tasks.Task InitialiseAsync(DashBoardViewModel dvm)
        {
            await dvm.getProjectList();
            await dvm.getTeam();
            dvm.NotifyPropertyChanged("ProjectList");
        }

        public async System.Threading.Tasks.Task getProjectList()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "dashboard/getprojectlist");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                ProjectList = api.DeserializeArrayJson<ObservableCollection<ProjectListModel>>(await res.Content.ReadAsStringAsync());
                foreach (ProjectListModel p in ProjectList)
                    Debug.WriteLine(p.Name);
            }
            else {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        public async System.Threading.Tasks.Task getTeam()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res = await api.Get(token, "dashboard/getteamoccupation");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                OccupationList = api.DeserializeArrayJson<ObservableCollection<Occupations>>(await res.Content.ReadAsStringAsync());
                foreach (Occupations p in OccupationList)
                {
                    Debug.WriteLine(p.Name);
                    Debug.WriteLine(p.User.FirstName);
                    Debug.WriteLine(p.Occupation);
                    Debug.WriteLine(p.Tasks_begun);
                    Debug.WriteLine(p.Tasks_Ongoing);
                }
                NotifyPropertyChanged("OccupationList");
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        private int _currentProjectId = 0;
        public int CurrentProjectId
        {
            get { return _currentProjectId; }
            set { _currentProjectId = value; NotifyPropertyChanged("CurrentProjectId");}
        }

        private ObservableCollection<ProjectListModel> _projectList;
        public ObservableCollection<ProjectListModel> ProjectList
        {
            get { return _projectList; }
            set { _projectList = value; NotifyPropertyChanged("ProjectList"); }
        }
        private ObservableCollection<Occupations> _occupationList;
        public ObservableCollection<Occupations> OccupationList
        {
            get { return _occupationList; }
            set { _occupationList = value; NotifyPropertyChanged("OccupationList"); }
        }
    }
}