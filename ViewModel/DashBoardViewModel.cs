using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.UI.Xaml.Controls;
using GrappBox.View;
using GrappBox.Model;
using GrappBox.ApiCom;
using System.Net.Http;
using System.Diagnostics;
using GrappBox.Ressources;

namespace GrappBox.ViewModel
{
    class DashBoardViewModel : ViewModelBase
    {
        static private DashBoardViewModel instance = null;
        private List<ProjectListModel> _projectList;

        static public DashBoardViewModel GetViewModel()
        {
            return instance;
        }
        public DashBoardViewModel()
        {
            instance = this;
        }

        public async void getProjectList()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "dashboard/getprojectlist");
            if (res.IsSuccessStatusCode)
            {
                _projectList = api.DeserializeArrayJson<List<ProjectListModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("ProjectList");
                SettingsManager.setOption("ProjectIdChoosen", _projectList.ElementAt(1).Id);
                SettingsManager.setOption("ProjectNameChoosen", _projectList.ElementAt(1).Name);
            }
            else {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        public List<ProjectListModel> ProjectList
        {
            get { return _projectList; }
        }
    }
}